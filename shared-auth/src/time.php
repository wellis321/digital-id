<?php
// Saved times are UTC (the database connection says so). Dates, and everything people see, are UK ones.
// Never show a saved time with date(strtotime()), take its date with DATE(), or ask the database what today is.
if (!function_exists('db_ts')) {
    /** The moment a saved UTC time ("2026-06-14 23:30:00") happened, as a Unix time. A time that names its own zone is read as it says. 0 when empty. */
    function db_ts(?string $utc): int
    {
        if (!$utc) return 0;
        $t = strtotime(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2}(\.\d+)?)?$/', $utc) ? $utc . ' UTC' : $utc);
        return $t === false ? 0 : $t;
    }

    /** A saved time shown in UK time; '' when there is none. */
    function uk_time(?string $utc, string $format = 'd/m/Y H:i'): string
    {
        $t = db_ts($utc);
        return $t ? (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('Europe/London'))->format($format) : '';
    }

    /** Today's UK date, quoted for a query. */
    function sql_today(): string
    {
        return "'" . (new DateTimeImmutable('now', new DateTimeZone('Europe/London')))->format('Y-m-d') . "'";
    }

    /** The saved (UTC) time at which a UK date began: compare a saved time with this, never with the date itself. */
    function uk_day_start(string $date): string
    {
        return (new DateTimeImmutable($date . ' 00:00:00', new DateTimeZone('Europe/London')))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /** A date and time someone typed (UK time), as it is saved. null when it cannot be read. */
    function saved_from_uk(string $typed): ?string
    {
        try { return (new DateTimeImmutable($typed, new DateTimeZone('Europe/London')))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'); } catch (Exception $e) { return null; }
    }
}
