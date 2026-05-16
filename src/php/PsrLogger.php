<?php

declare(strict_types=1);

namespace Phel\PhelLog;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

use function is_scalar;
use function is_string;

/**
 * PSR-3 LoggerInterface adapter.
 *
 * Lets PHP frameworks (Symfony, Laravel, Slim, ...) log into the phel.log
 * pipeline. Construct with a callable `$dispatcher` that mirrors
 * `phel.log/log-event!`:
 *
 *     fn(string $level, string $message, array $context): void
 *
 * From Phel:
 *
 *     (php/new \Phel\PhelLog\PsrLogger
 *              (fn [level message data]
 *                (log/log-event! (keyword level) (php/get $context "_ns" "psr") message data)))
 *
 * PSR-3 levels are mapped to phel.log levels:
 *   emergency -> :fatal
 *   alert     -> :fatal
 *   critical  -> :fatal
 *   error     -> :error
 *   warning   -> :warn
 *   notice    -> :info
 *   info      -> :info
 *   debug     -> :debug
 */
final class PsrLogger extends AbstractLogger implements LoggerInterface
{
    /** @var array<string,string> PSR-3 level -> phel.log level keyword name */
    private const LEVEL_MAP = [
        LogLevel::EMERGENCY => 'fatal',
        LogLevel::ALERT     => 'fatal',
        LogLevel::CRITICAL  => 'fatal',
        LogLevel::ERROR     => 'error',
        LogLevel::WARNING   => 'warn',
        LogLevel::NOTICE    => 'info',
        LogLevel::INFO      => 'info',
        LogLevel::DEBUG     => 'debug',
    ];

    /** @var callable(string,string,array<string,mixed>):void */
    private $dispatcher;

    private string $channel;

    /**
     * @param callable(string,string,array<string,mixed>):void $dispatcher
     *                                                                     Callable that forwards into the phel.log pipeline.
     * @param string                                           $channel
     *                                                                     Default namespace recorded against every event emitted by this
     *                                                                     logger. Use one PsrLogger per PHP module / channel.
     */
    public function __construct(callable $dispatcher, string $channel = 'psr')
    {
        $this->dispatcher = $dispatcher;
        $this->channel = $channel;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $phelLevel = self::LEVEL_MAP[(string) $level] ?? 'info';
        $rendered = $this->interpolate((string) $message, $context);
        $context['_ns'] ??= $this->channel;
        ($this->dispatcher)($phelLevel, $rendered, $context);
    }

    /**
     * PSR-3 placeholder interpolation: {key} -> $context[key].
     *
     * @param array<string,mixed> $context
     */
    private function interpolate(string $message, array $context): string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($key) && (is_scalar($val) || $val instanceof Stringable)) {
                $replace['{' . $key . '}'] = (string) $val;
            }
        }

        return $replace === [] ? $message : strtr($message, $replace);
    }
}
