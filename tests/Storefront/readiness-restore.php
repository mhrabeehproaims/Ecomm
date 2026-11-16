<?php

// Backup/restore rehearsal for disposable fixtures only. Never deletes or overwrites a DB.
$test = require __DIR__.'/mysql-environment.php'; // Verifies loopback port and actual test datadir.
$sourceName = $databaseName;
$targetName = getenv('STOREFRONT_MYSQL_RESTORE_DATABASE') ?: '';
if (! preg_match('/^vanthara_checkout_test_\d{14}$/D', $targetName) || $targetName === $sourceName) {
    throw new RuntimeException('A different, explicitly named isolated restore database is required.');
}
if ($probe->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$targetName'")->fetchColumn()) {
    throw new RuntimeException('Restore target already exists; refusing to overwrite it.');
}
$directory = $test['directory'].'/backup-drill';
if (! is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$dump = $directory.'/'.$sourceName.'-to-'.$targetName.'.sql';
if (file_exists($dump)) {
    throw new RuntimeException('Backup file already exists; refusing to overwrite it.');
}
$bin = 'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/';
$run = function (array $arguments, ?string $input = null): void {
    $descriptors = [0 => $input ? ['file', $input, 'r'] : ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open($arguments, $descriptors, $pipes, null, null, ['bypass_shell' => true]);
    if (! is_resource($process)) {
        throw new RuntimeException('Cannot start isolated backup utility.');
    }
    if (! $input) {
        fclose($pipes[0]);
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException('Isolated backup/restore utility failed: '.$stderr);
    }
};
$run([$bin.'mysqldump.exe', '--no-defaults', '--host=127.0.0.1', '--port=3339', '--user=root', '--single-transaction',
    '--no-tablespaces', '--skip-comments', '--skip-add-drop-table', '--set-gtid-purged=OFF', '--result-file='.$dump, $sourceName]);
$probe->exec("CREATE DATABASE `$targetName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$run([$bin.'mysql.exe', '--no-defaults', '--host=127.0.0.1', '--port=3339', '--user=root', $targetName], $dump);
$tables = $probe->query("SHOW TABLES FROM `$sourceName`")->fetchAll(PDO::FETCH_COLUMN);
$checks = [];
foreach ($tables as $table) {
    $summaries = [];
    foreach ([$sourceName, $targetName] as $schema) {
        $rows = $probe->query("SELECT * FROM `$schema`.`$table`")->fetchAll(PDO::FETCH_ASSOC);
        $encoded = array_map(fn ($row) => json_encode($row, JSON_THROW_ON_ERROR), $rows);
        sort($encoded, SORT_STRING);
        $summaries[] = ['rows' => count($rows), 'sha256' => hash('sha256', implode("\n", $encoded))];
    }
    if ($summaries[0] !== $summaries[1]) {
        throw new RuntimeException('Restored fixture data differs: '.$table);
    }
    $checks[$table] = $summaries[0];
}
$result = ['audit_date' => '2026-11-16', 'source' => $sourceName, 'new_restore_target' => $targetName,
    'tables_verified' => count($tables), 'backup_sha256' => hash_file('sha256', $dump), 'backup_bytes' => filesize($dump),
    'all_row_hashes_match' => true, 'tables' => $checks,
    'scope' => 'Local synthetic fixtures only. No production backup, original database restore, or migration rollback executed.'];
file_put_contents($test['root'].'/storefront/artifacts/readiness-20261116/restore-drill.json', json_encode($result, JSON_PRETTY_PRINT));
echo json_encode(array_diff_key($result, ['tables' => true]), JSON_PRETTY_PRINT).PHP_EOL;
