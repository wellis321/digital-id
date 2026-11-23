# Care Core integration

Digital ID can use **Care Core** as the master record of staff. When an organisation is linked, staff are created, updated and ended in Care Core, and Digital ID follows. (The earlier **Staff Service** link still exists for organisations that use it; see `docs/archive/INTEGRATION-STAFF-SERVICE.md`. Care Core replaced that product.)

The suite documentation explains the whole picture: https://docs.findcarescotland.com/products/digital-id and https://docs.findcarescotland.com/the-joins

## What it does

- **Staff as employees.** Each active member of staff in Care Core becomes an employee in Digital ID, with their name, role and photo. A leaver's card stops working when Care Core records the leaving.
- **Changes arrive within about a minute** through a Care Core webhook. A nightly reconcile catches anything a notification missed.
- **Care Core is the master.** Change staff details in Care Core, not in Digital ID.
- **Magic-link sign-in** for staff (hidden in the demonstration).

## Setting it up

1. In Care Core, make an API key under Admin, Integrations with read access to people and staff (the settings page says what it needs), and add a webhook to this site (see the settings page for the address and token).
2. In Digital ID, open **Admin, Care Core** (`/admin/care-core-settings.php`) and paste Care Core's address and the key.
3. Run the first sync from that page, and check the result it reports.

## The pieces

| Piece | Where |
|---|---|
| Connector classes | `src/classes/CareCore*.php` |
| Settings page | `public/admin/care-core-settings.php` |
| Webhook receiver | `public/api/care-core-webhook.php?t=...` |
| Nightly reconcile | `scripts/sync-care-core.php` (schedule it for a real installation: it is not scheduled for customers yet) |
| Tests | `python3 tests/e2e/care_core_connector.py` (needs the demo database; see `CLAUDE.md`) |

## Backups

`php scripts/backup.php` writes the database and uploaded photos and logos to `../backups-digitalid/` (beside, not inside, the web folder). `php scripts/restore-check.php FILE --into=SCRATCH_DB` proves a backup restores. Schedule the backup, and copy the files off the server.
