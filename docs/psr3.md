# PSR-3 adapter

`Phel\PhelLog\PsrLogger` implements `Psr\Log\LoggerInterface`. Wire it into
any PHP framework so framework / library logs flow through your Phel
appenders, processors, and config.

## Wire it up

The shortest path is `log/make-psr-logger`, which builds a configured
`PsrLogger` whose dispatcher already forwards into `log-event!`:

```phel
(ns my-app.bootstrap
  (:require phel.log :as log))

(def $logger (log/make-psr-logger "my-app"))
```

Then expose it to PHP however your container expects:

```php
use Psr\Log\LoggerInterface;
$container->set(LoggerInterface::class, my_app_bootstrap_logger());
```

### Manual wiring

If you need a custom dispatcher (extra ns mapping, side-effects, batching),
construct `PsrLogger` directly:

```php
use Phel\PhelLog\PsrLogger;
use Psr\Log\LoggerInterface;

$dispatcher = static function (string $level, string $message, array $ctx): void {
    \Phel::run('my-app.bootstrap', 'log-from-psr', $level, $message, $ctx);
};

$logger = new PsrLogger($dispatcher, channel: 'symfony');
$container->set(LoggerInterface::class, $logger);
```

`log-from-psr` is a small Phel fn you define once:

```phel
(ns my-app.bootstrap
  (:require phel.log :as log))

(defn log-from-psr [level message data]
  (log/log-event! (keyword level)
                  (or (get data "_ns") "psr")
                  message
                  data))
```

## PSR-3 -> phel.log level map

| PSR-3        | phel.log  |
|--------------|-----------|
| `emergency`  | `:fatal`  |
| `alert`      | `:fatal`  |
| `critical`   | `:fatal`  |
| `error`      | `:error`  |
| `warning`    | `:warn`   |
| `notice`     | `:info`   |
| `info`       | `:info`   |
| `debug`      | `:debug`  |

## Placeholder interpolation

PSR-3 `{placeholder}` substitution is applied before dispatch:

```php
$logger->info('user {id} logged in', ['id' => 42]);
// rendered: "user 42 logged in"
```

Only scalar / `Stringable` values are substituted. The original `$context`
array is still forwarded as the event's `:data`.
