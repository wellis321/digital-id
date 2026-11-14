<?php
/**
 * Keeps Digital ID's staff in step with Care Core, which is the single source of truth.
 *  - syncOrganisation(): the full pass (first connection, "Sync now", and the nightly reconcile that catches anything a notification missed).
 *  - applyEvent(): one notification from Care Core (someone joined, changed role, or left), applied the moment it arrives.
 * A person is a member of staff here exactly while Care Core says they have a current employment. When that ends their ID card is revoked, their
 * login is switched off and they stop verifying, in the same step. Nobody is ever created or edited by hand in Digital ID for a managed organisation.
 */
class CareCoreSync
{
    /** @return array{created:int,updated:int,deactivated:int,reactivated:int,skipped:int,photos:int,errors:list<string>} */
    public static function syncOrganisation(int $orgId, bool $images = true, ?CareCoreClient $client = null): array
    {
        CareCore::ensureSchema();
        $client ??= CareCore::client($orgId);
        $n = ['created' => 0, 'updated' => 0, 'deactivated' => 0, 'reactivated' => 0, 'skipped' => 0, 'photos' => 0, 'errors' => []];
        $people = [];
        foreach ($client->people() as $p) $people[$p['id']] = $p;
        $current = [];
        foreach ($client->employments('current') as $e) {
            $current[$e['person_id']] = $e;
            $p = $people[$e['person_id']] ?? null;
            if (!$p) { $n['skipped']++; continue; }
            try {
                $r = self::upsert($orgId, $p, $e, $images ? $client : null);
                $n[$r['action']]++; $n['photos'] += $r['photos'];
            } catch (Throwable $ex) { $n['errors'][] = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) . ': ' . $ex->getMessage(); }
        }
        // Anyone we hold from Care Core who no longer has a current employment has left.
        $db = getDbConnection();
        $s = $db->prepare('SELECT id, care_core_person_id FROM employees WHERE organisation_id = ? AND care_core_person_id IS NOT NULL AND (is_active = 1 OR leaves_on IS NOT NULL)');
        $s->execute([$orgId]);
        foreach ($s->fetchAll() as $row) {
            if (isset($current[$row['care_core_person_id']])) continue;
            if (self::deactivate((int) $row['id'])) $n['deactivated']++;
        }
        CareCore::set($orgId, 'care_core_last_sync', gmdate('c'));
        CareCore::set($orgId, 'care_core_last_result', json_encode($n));
        return $n;
    }

    /** @return array{action:string,photos:int} action: created | updated | reactivated */
    public static function upsert(int $orgId, array $person, array $employment, ?CareCoreClient $images): array
    {
        $db = getDbConnection();
        $email = strtolower(trim((string) ($person['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('has no email address in Care Core, so they cannot be given a sign-in');
        $first = trim((string) ($person['first_name'] ?? '')); $last = trim((string) ($person['last_name'] ?? ''));
        $ref = trim((string) ($employment['employee_ref'] ?? ''));
        $leavesOn = !empty($employment['end_date']) ? (string) $employment['end_date'] : null;   // a leaving date in the future

        $emp = self::find($orgId, (string) $person['id'], $email);
        $action = 'updated';
        if (!$emp) {
            $db->beginTransaction();
            try {
                $u = $db->prepare('SELECT id FROM users WHERE email = ? AND organisation_id = ?'); $u->execute([$email, $orgId]); $userId = (int) $u->fetchColumn();
                if (!$userId) {
                    $other = $db->prepare('SELECT 1 FROM users WHERE email = ?'); $other->execute([$email]);
                    if ($other->fetchColumn()) throw new RuntimeException('that email address already belongs to an account in another organisation');
                    $db->prepare('INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified) VALUES (?, ?, ?, ?, ?, 1, 1)')
                       ->execute([$orgId, $email, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), $first, $last]);   // no usable password: sign-in is by Microsoft or an emailed link
                    $userId = (int) $db->lastInsertId();
                    $role = $db->query("SELECT id FROM roles WHERE name = 'staff'")->fetchColumn();
                    if ($role) $db->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)')->execute([$userId, $role]);
                }
                $number = $ref !== '' ? $ref : 'CC-' . strtoupper(substr(preg_replace('/[^a-f0-9]/i', '', (string) $person['id']), 0, 8));
                $display = $ref !== '' ? $ref : $number;
                $card = json_encode(['employee_number' => $number, 'display_reference' => $display, 'full_name' => trim("$first $last"), 'organization' => ['id' => $orgId], 'issued_at' => date('c'), 'verification_levels' => ['visual', 'qr', 'nfc']]);
                $db->prepare('INSERT INTO employees (user_id, organisation_id, employee_number, employee_reference, display_reference, id_card_data, is_active, care_core_person_id, care_core_employment_id, leaves_on, last_synced_from_care_core)
                              VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())')
                   ->execute([$userId, $orgId, $number, $display, $display, $card, $person['id'], $employment['id'] ?? null, $leavesOn]);
                $emp = self::find($orgId, (string) $person['id'], $email);
                $db->commit();
                $action = 'created';
            } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
        } else {
            $reactivated = !$emp['is_active'];
            $set = ['care_core_person_id = ?', 'care_core_employment_id = ?', 'leaves_on = ?', 'is_active = 1', 'last_synced_from_care_core = NOW()'];
            $vals = [$person['id'], $employment['id'] ?? null, $leavesOn];
            if ($ref !== '' && $ref !== $emp['display_reference']) {   // Care Core owns the reference; a change there changes the card
                $dup = $db->prepare('SELECT 1 FROM employees WHERE organisation_id = ? AND id <> ? AND (display_reference = ? OR employee_reference = ?)'); $dup->execute([$orgId, $emp['id'], $ref, $ref]);
                if (!$dup->fetchColumn()) { $set[] = 'display_reference = ?'; $set[] = 'employee_reference = ?'; array_push($vals, $ref, $ref); }
            }
            $vals[] = $emp['id'];
            $db->prepare('UPDATE employees SET ' . implode(', ', $set) . ' WHERE id = ?')->execute($vals);
            $db->prepare('UPDATE users SET first_name = ?, last_name = ?, is_active = 1 WHERE id = ?')->execute([$first, $last, $emp['user_id']]);
            if ($reactivated) $action = 'reactivated';
        }
        $photos = $images ? self::images((int) $emp['id'], $orgId, (string) $person['id'], $images) : 0;
        return ['action' => $action, 'photos' => $photos];
    }

    /** @return array<string,mixed>|null */
    private static function find(int $orgId, string $personUuid, string $email): ?array
    {
        $db = getDbConnection();
        $s = $db->prepare('SELECT e.* FROM employees e WHERE e.organisation_id = ? AND e.care_core_person_id = ?'); $s->execute([$orgId, $personUuid]);
        if ($r = $s->fetch()) return $r;
        // A person we already hold from an earlier import or by hand: link them rather than creating a second card.
        $s = $db->prepare('SELECT e.* FROM employees e JOIN users u ON u.id = e.user_id WHERE e.organisation_id = ? AND u.email = ? AND e.care_core_person_id IS NULL'); $s->execute([$orgId, $email]);
        return $s->fetch() ?: null;
    }

    /** Photo and signature, stored here (so cards still show if Care Core is unreachable) and only re-saved when they change. */
    private static function images(int $employeeId, int $orgId, string $personUuid, CareCoreClient $client): int
    {
        $db = getDbConnection(); $saved = 0;
        $e = $db->prepare('SELECT care_core_photo_hash, care_core_signature_hash FROM employees WHERE id = ?'); $e->execute([$employeeId]); $cur = $e->fetch();
        foreach (['photo' => ['care_core_photo_hash', 'photo_path'], 'signature' => ['care_core_signature_hash', 'signature_path']] as $kind => [$hashCol, $pathCol]) {
            $img = $client->image($personUuid, $kind);
            if ($img === null) continue;
            [$bytes, $mime] = $img;
            $hash = hash('sha256', $bytes);
            if (($cur[$hashCol] ?? null) === $hash) continue;
            [$bytes, $ext] = self::normalise($bytes);
            if ($bytes === null) continue;
            $dir = dirname(__DIR__, 2) . '/uploads/employees/care-core/' . $orgId;
            if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('could not save the ' . $kind);
            $rel = 'uploads/employees/care-core/' . $orgId . '/' . bin2hex(random_bytes(16)) . '-' . $kind . '.' . $ext;
            file_put_contents(dirname(__DIR__, 2) . '/' . $rel, $bytes);
            $extra = $kind === 'photo' ? ", photo_approval_status = 'approved', photo_approved_at = NOW(), photo_pending_path = NULL, photo_rejection_reason = NULL, staff_service_photo_url = NULL" : '';
            $db->prepare("UPDATE employees SET $pathCol = ?, $hashCol = ?$extra WHERE id = ?")->execute([$rel, $hash, $employeeId]);
            $saved++;
        }
        return $saved;
    }

    /** Only PNG, JPEG and GIF are shown here: WebP is converted when the server can, otherwise skipped. @return array{0:?string,1:string} */
    private static function normalise(string $bytes): array
    {
        $info = @getimagesizefromstring($bytes);
        if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] > 8000 || $info[1] > 8000) return [null, ''];
        $mime = $info['mime'] ?? '';
        if ($mime === 'image/png') return [$bytes, 'png'];
        if ($mime === 'image/jpeg') return [$bytes, 'jpg'];
        if ($mime === 'image/gif') return [$bytes, 'gif'];
        if ($mime === 'image/webp' && function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $im = @imagecreatefromstring($bytes);
            if ($im) { ob_start(); imagepng($im); $png = (string) ob_get_clean(); return [$png, 'png']; }
        }
        return [null, ''];
    }

    /** They have left: revoke the card, stop the login, stop verification. Returns false if there was nothing to do. */
    public static function deactivate(int $employeeId): bool
    {
        $db = getDbConnection();
        $s = $db->prepare('SELECT id, user_id, is_active FROM employees WHERE id = ?'); $s->execute([$employeeId]); $e = $s->fetch();
        if (!$e) return false;
        $db->beginTransaction();
        try {
            $db->prepare('UPDATE employees SET is_active = 0, leaves_on = COALESCE(leaves_on, CURDATE()) WHERE id = ?')->execute([$employeeId]);
            $db->prepare('UPDATE digital_id_cards SET is_revoked = 1, revoked_at = NOW() WHERE employee_id = ? AND is_revoked = 0')->execute([$employeeId]);
            $db->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$e['user_id']]);
            $db->commit();
        } catch (Throwable $ex) { $db->rollBack(); throw $ex; }
        return true;
    }

    /**
     * One notification. Returns [outcome, detail]. outcome: ok | duplicate | ignored. Throws when it should be retried (Care Core retries on an error).
     * @param array<string,mixed> $event the decoded envelope {id, type, subject, data}
     * @return array{0:string,1:string}
     */
    public static function applyEvent(int $orgId, array $event, ?CareCoreClient $client = null): array
    {
        CareCore::ensureSchema();
        $db = getDbConnection();
        $id = (string) ($event['id'] ?? ''); $type = (string) ($event['type'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $id) || $type === '') throw new InvalidArgumentException('not a Care Core event');
        $ins = $db->prepare('INSERT IGNORE INTO care_core_events (event_id, organisation_id, type, outcome) VALUES (?, ?, ?, ?)');
        $ins->execute([$id, $orgId, $type, 'pending']);
        if ($ins->rowCount() === 0) {
            $st = $db->prepare('SELECT outcome FROM care_core_events WHERE event_id = ?'); $st->execute([$id]);
            if ($st->fetchColumn() !== 'pending') return ['duplicate', 'already handled'];   // a retry after we answered: nothing to do
        }
        $done = static function (string $outcome, string $detail) use ($db, $id): array { $db->prepare('UPDATE care_core_events SET outcome = ?, detail = ? WHERE event_id = ?')->execute([$outcome, mb_substr($detail, 0, 250), $id]); return [$outcome, $detail]; };
        try {
            if (!in_array($type, ['person.created', 'person.updated', 'employment.started', 'employment.changed', 'employment.ended'], true)) return $done('ignored', "$type is not used here");
            $client ??= CareCore::client($orgId);
            $personUuid = (string) ($event['subject'] ?? '');
            $empUuid = (string) ($event['data']['employment'] ?? '');
            if ($type === 'employment.ended') {
                $emp = $empUuid !== '' ? $client->employment($empUuid) : [];
                $endsOn = (string) ($emp['end_date'] ?? ($event['data']['end_date'] ?? ''));
                $local = $db->prepare('SELECT id FROM employees WHERE organisation_id = ? AND care_core_person_id = ?'); $local->execute([$orgId, $personUuid]); $eid = $local->fetchColumn();
                if (!$eid) return $done('ignored', 'not someone we hold');
                if ($endsOn !== '' && $endsOn > date('Y-m-d')) {   // leaving in the future: the card stays valid until then
                    $db->prepare('UPDATE employees SET leaves_on = ? WHERE id = ?')->execute([$endsOn, $eid]);
                    return $done('ok', "leaves on $endsOn");
                }
                self::deactivate((int) $eid);
                return $done('ok', 'card revoked');
            }
            // joined, changed, or their details changed: look at the person as Care Core now has them
            $person = $client->person($personUuid);
            $emp = $empUuid !== '' ? $client->employment($empUuid) : [];
            if ($emp === []) { foreach ($client->json('GET', '/people/' . rawurlencode($personUuid) . '/employments')['data'] ?? [] as $x) if (($x['status'] ?? '') === 'current') { $emp = $x; break; } }
            if (($emp['status'] ?? '') !== 'current') return $done('ignored', 'no current employment yet (picked up when it starts)');
            $r = self::upsert($orgId, $person, $emp, $client);
            return $done('ok', $r['action']);
        } catch (Throwable $e) {
            // Problems with the person's own record (no email, an address used elsewhere) will not fix themselves by retrying: record them and move on.
            if ($e instanceof RuntimeException && (str_contains($e->getMessage(), 'no email address') || str_contains($e->getMessage(), 'another organisation'))) return $done('ignored', $e->getMessage());
            $db->prepare('DELETE FROM care_core_events WHERE event_id = ? AND outcome = ?')->execute([$id, 'pending']);   // not handled: let Care Core retry
            throw $e;
        }
    }
}
