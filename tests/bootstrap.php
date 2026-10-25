<?php
declare(strict_types=1);
function check(bool $ok, string $message): void {
    if (!$ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    $GLOBALS['checks'] = ($GLOBALS['checks'] ?? 0) + 1;
}
function fixture(array $replace = []): array {
    return array_replace(['title'=>'閱讀 📖','category'=>'reading','status'=>'active','progress'=>'讀到第十頁','next_step'=>'第十一頁','memo'=>'日本語メモ'], $replace);
}
function test_config(): array {
    $config = require dirname(__DIR__).'/config.local.php';
    $config['database'] = 'restart_test';
    return $config;
}
function done(string $suite): void { echo "PASS $suite: ", $GLOBALS['checks'] ?? 0, " assertions\n"; }
