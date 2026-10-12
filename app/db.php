<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db(): mysqli
{
    static $connection;
    if ($connection === null) {
        $c = app_config();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $connection = new mysqli($c['db_host'], $c['db_user'], $c['db_password'], $c['db_name'], $c['db_port']);
        $connection->set_charset('utf8mb4');
        $connection->query("SET time_zone = '+00:00'");
    }
    return $connection;
}
function sql(string $query, array $values = []): mysqli_stmt
{
    $statement = db()->prepare($query);
    if ($values) {
        $types = '';
        foreach ($values as $v) $types .= is_int($v) ? 'i' : (is_float($v) ? 'd' : 's');
        $statement->bind_param($types, ...$values);
    }
    $statement->execute();
    return $statement;
}
function rows(string $query, array $values = []): array { return sql($query, $values)->get_result()->fetch_all(MYSQLI_ASSOC); }
function row(string $query, array $values = []): ?array { return sql($query, $values)->get_result()->fetch_assoc(); }
