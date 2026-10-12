<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/../app/db.php';
try {
    $accounts = [
        ['email' => 'student-a@refind.test', 'full_name' => 'Student Demo A', 'student_id' => 'REFIND-DEMO-A', 'role' => 'STUDENT'],
        ['email' => 'student-b@refind.test', 'full_name' => 'Student Demo B', 'student_id' => 'REFIND-DEMO-B', 'role' => 'STUDENT'],
        ['email' => 'bm@refind.test', 'full_name' => 'Building Management', 'student_id' => null, 'role' => 'BUILDING_MANAGEMENT'],
    ];
    $path = storage_dir('setup') . '/demo-accounts.json';
    $lock = fopen(storage_dir('setup') . '/demo-accounts.lock', 'c+');
    if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('Run setup-db.php first.');
    $stored = is_file($path) ? json_decode(file_get_contents($path), true, 64, JSON_THROW_ON_ERROR) : ['database' => app_config()['db_name'], 'accounts' => []];
    if ($stored['database'] !== app_config()['db_name']) throw new RuntimeException('Demo credential database mismatch.');
    $created = 0;
    db()->begin_transaction();
    $committed = false;
    try {
        foreach ($accounts as $account) {
            $existing = row('SELECT user_id, role, is_active, student_id FROM users WHERE email = ? FOR UPDATE', [$account['email']]);
            if ($existing) {
                if ($existing['role'] !== $account['role'] || !(int) $existing['is_active'] || $existing['student_id'] !== $account['student_id']) throw new RuntimeException('Existing demo identity differs; no credentials were overwritten.');
                continue;
            }
            $password = 'Rf!' . bin2hex(random_bytes(8)) . 'aA';
            sql('INSERT INTO users (email, full_name, student_id, role, password_hash) VALUES (?, ?, ?, ?, ?)', [$account['email'], $account['full_name'], $account['student_id'], $account['role'], password_hash($password, PASSWORD_DEFAULT)]);
            $stored['accounts'][$account['email']] = $account + ['user_id' => (string) db()->insert_id, 'password' => $password];
            $created++;
        }
        // Persist generated demo credentials before commit; no personal passwords.
        $temp = $path . '.pending';
        if (file_put_contents($temp, json_encode($stored, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Cannot save demo credential receipt.');
        db()->commit();
        $committed = true;
        if (!rename($temp, $path)) throw new RuntimeException('Credential receipt rename failed. Keep the protected .pending file.');
        @chmod($path, 0600);
    } catch (Throwable $error) { if (!$committed) db()->rollback(); throw $error; }
    flock($lock, LOCK_UN); fclose($lock);
    echo json_encode(['created' => $created, 'accounts' => array_column($accounts, 'email'), 'passwords_file' => $path, 'existing_passwords_preserved' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $error) { fwrite(STDERR, 'Demo setup stopped: ' . $error->getMessage() . PHP_EOL); exit(1); }
