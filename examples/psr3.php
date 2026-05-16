<?php

declare(strict_types=1);

/*
 * Smoke: wire `Phel\PhelLog\PsrLogger` end-to-end into the Phel pipeline.
 *
 * The dispatcher captures every event into a $captured array so a PHP-level
 * caller can assert that PSR-3 calls land as phel.log events with the right
 * level keyword, interpolated message, channel, and context.
 *
 * Run: php examples/psr3.php
 * Exits non-zero on assertion failure so it doubles as a CI gate.
 */

use Phel\PhelLog\PsrLogger;

require __DIR__ . '/../vendor/autoload.php';

$captured = [];
$dispatcher = static function (string $level, string $message, array $context) use (&$captured): void {
    $captured[] = [
        'level'   => $level,
        'message' => $message,
        'ns'      => $context['_ns'] ?? null,
        'context' => $context,
    ];
};

$logger = new PsrLogger($dispatcher, 'my-app.users');

$failures = [];
$check = static function (string $name, bool $pred) use (&$failures): void {
    if ($pred) {
        echo " [OK]  $name\n";
    } else {
        echo " [ERR] $name\n";
        $failures[] = $name;
    }
};

echo "\n== psr3 smoke ==\n";

$logger->info('user {id} logged in', ['id' => 42]);
$logger->warning('disk at {pct}%', ['pct' => 91]);
$logger->error('boom {kind}', ['kind' => 'segfault']);
$logger->emergency('PSR-3 emergency');
$logger->notice('notice line');
$logger->debug('debug line');

$check('captured 6 events',           count($captured) === 6);
$check('info  -> :info',              $captured[0]['level'] === 'info');
$check('warning -> :warn',            $captured[1]['level'] === 'warn');
$check('error -> :error',             $captured[2]['level'] === 'error');
$check('emergency -> :fatal',         $captured[3]['level'] === 'fatal');
$check('notice -> :info',             $captured[4]['level'] === 'info');
$check('debug -> :debug',             $captured[5]['level'] === 'debug');

$check('{id} interpolated into message',
    $captured[0]['message'] === 'user 42 logged in');
$check('{pct} interpolated into message',
    $captured[1]['message'] === 'disk at 91%');

$check('channel injected as _ns',
    $captured[0]['ns'] === 'my-app.users');
$check('original context preserved',
    $captured[0]['context']['id'] === 42);

// Per-call _ns override: caller passes _ns explicitly, channel default doesn't win.
$logger->info('explicit channel', ['_ns' => 'override-ns']);
$check('explicit _ns wins over channel default',
    end($captured)['ns'] === 'override-ns');

// Stringable interpolation.
$stringable = new class () implements Stringable {
    public function __toString(): string
    {
        return 'world';
    }
};
$logger->info('hello {who}', ['who' => $stringable]);
$check('Stringable {who} interpolates',
    end($captured)['message'] === 'hello world');

// Non-scalar context value (array) must NOT break interpolation, just stays out.
$logger->info('payload {data}', ['data' => ['nested' => true]]);
$check('non-scalar {data} left literal (no interp), no crash',
    end($captured)['message'] === 'payload {data}');

if (count($failures) === 0) {
    echo "\npsr3 OK\n\n";
    exit(0);
}

echo "\npsr3 FAIL: " . count($failures) . " check(s) failed:\n";
foreach ($failures as $f) {
    echo " - $f\n";
}
exit(1);
