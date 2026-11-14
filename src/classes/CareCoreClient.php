<?php
/**
 * A small client for Care Core's HTTP API (v1): a bearer API key, JSON in and out, cursor paging. Only what Digital ID needs: the staff list,
 * one person, a photo and a signature, and the webhook endpoint that tells us when someone joins, moves or leaves.
 * $http can be replaced in tests: fn(string $method, string $url, ?string $body, array $headers): array{0:int,1:string,2:array<string,string>}
 */
class CareCoreClient
{
    /** @var callable|null */
    public static $http = null;

    public function __construct(private string $baseUrl, private string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /** Why this address cannot be used (null when it is fine). Only https, except localhost for development; no embedded passwords. */
    public static function addressProblem(string $url): ?string
    {
        $p = parse_url(trim($url));
        if (!$p || empty($p['host']) || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true)) return 'Enter the full address of your Care Core, starting with https://';
        if (!empty($p['user']) || !empty($p['pass'])) return 'Do not put a username or password in the address.';
        $local = in_array(strtolower($p['host']), ['localhost', '127.0.0.1', '::1'], true);
        if (strtolower($p['scheme']) !== 'https' && !$local) return 'Use an https:// address.';
        $ip = filter_var($p['host'], FILTER_VALIDATE_IP) ? $p['host'] : gethostbyname($p['host']);
        if (filter_var($ip, FILTER_VALIDATE_IP) && (str_starts_with($ip, '169.254.') || $ip === '0.0.0.0')) return 'That address cannot be used.';
        return null;
    }

    /** @return array{0:int,1:string,2:array<string,string>} status, body, lower-cased response headers */
    private function request(string $method, string $path, ?array $json = null): array
    {
        $url = $this->baseUrl . '/api/v1' . $path;
        $body = $json === null ? null : json_encode($json, JSON_UNESCAPED_SLASHES);
        $headers = ['Authorization: Bearer ' . $this->apiKey, 'Accept: application/json, image/*'];
        if ($body !== null) $headers[] = 'Content-Type: application/json';
        if (self::$http) return (self::$http)($method, $url, $body, $headers);
        $ch = curl_init($url);
        $local = in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), ['localhost', '127.0.0.1', '::1'], true);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers, CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => $local ? (CURLPROTO_HTTPS | CURLPROTO_HTTP) : CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'DigitalID-CareCore/1.0']);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $raw = curl_exec($ch);
        if ($raw === false) throw new RuntimeException('Could not reach Care Core: ' . curl_error($ch));
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rh = [];
        foreach (explode("\r\n", substr((string) $raw, 0, $hs)) as $line) if (str_contains($line, ':')) { [$k, $v] = explode(':', $line, 2); $rh[strtolower(trim($k))] = trim($v); }
        return [$status, substr((string) $raw, $hs), $rh];
    }

    /** @return array<string,mixed> the decoded JSON, or throws with a message that is safe to show an administrator */
    public function json(string $method, string $path, ?array $json = null): array
    {
        [$st, $body] = $this->request($method, $path, $json);
        $d = json_decode($body, true);
        if ($st === 401) throw new RuntimeException('Care Core did not accept the API key.');
        if ($st === 403) throw new RuntimeException('That API key is missing a permission: ' . ($d['detail'] ?? $d['title'] ?? 'forbidden') . '.');
        if ($st === 429) throw new RuntimeException('Care Core asked us to slow down. It will be tried again shortly.');
        if ($st < 200 || $st >= 300) throw new RuntimeException('Care Core answered ' . $st . ($d['detail'] ?? '' ? ': ' . $d['detail'] : '') . '.');
        return is_array($d) ? $d : [];
    }

    /** Every item of a paged list. @return list<array<string,mixed>> */
    public function all(string $path, array $query = []): array
    {
        $out = []; $cursor = null;
        for ($i = 0; $i < 200; $i++) {
            $q = $query + ['limit' => 200] + ($cursor ? ['cursor' => $cursor] : []);
            $d = $this->json('GET', $path . '?' . http_build_query($q));
            foreach ($d['data'] ?? [] as $row) $out[] = $row;
            $cursor = $d['page']['next_cursor'] ?? null;
            if (!$cursor) break;
        }
        return $out;
    }

    /** Is the address and key right, and what may this key do? Returns the name Care Core gives the organisation when it can. */
    public function check(): array
    {
        $this->json('GET', '/people?limit=1');                 // needs people.read
        $this->json('GET', '/employments?limit=1');            // needs staff.read
        return ['ok' => true];
    }

    /** @return list<array<string,mixed>> */
    public function employments(string $status = 'current'): array { return $this->all('/employments', ['status' => $status]); }

    /** @return list<array<string,mixed>> */
    public function people(): array { return $this->all('/people'); }

    /** @return array<string,mixed> */
    public function person(string $uuid): array { return $this->json('GET', '/people/' . rawurlencode($uuid))['data'] ?? []; }

    /** @return array<string,mixed> */
    public function employment(string $uuid): array { return $this->json('GET', '/employments/' . rawurlencode($uuid))['data'] ?? []; }

    /** @return array{0:string,1:string}|null image bytes and content type, or null when they have none */
    public function image(string $personUuid, string $kind): ?array
    {
        [$st, $body, $h] = $this->request('GET', '/people/' . rawurlencode($personUuid) . '/' . ($kind === 'signature' ? 'signature' : 'photo'));
        if ($st === 404) return null;
        if ($st !== 200 || $body === '') throw new RuntimeException("Care Core could not give us the $kind (HTTP $st).");
        return [$body, $h['content-type'] ?? ''];
    }

    /** Tell Care Core where to send events. Returns the endpoint id and the signing secret (shown once). @return array{id:string,secret:string} */
    public function createWebhook(string $url, array $types): array
    {
        $d = $this->json('POST', '/webhooks', ['url' => $url, 'event_types' => $types])['data'] ?? [];
        if (empty($d['id']) || empty($d['secret'])) throw new RuntimeException('Care Core did not return a webhook secret.');
        return ['id' => (string) $d['id'], 'secret' => (string) $d['secret']];
    }

    public function deleteWebhook(string $id): void { $this->request('DELETE', '/webhooks/' . rawurlencode($id)); }
}
