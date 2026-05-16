# phel-log

Data-driven logging library for [Phel Lang](https://phel-lang.org/). Inspired by
[Timbre](https://github.com/taoensso/timbre) (Clojure) and
[Monolog](https://github.com/Seldaek/monolog) (PHP).

- Pure-data config: one map, swap it at runtime.
- Levels with namespace + per-appender filters.
- Pluggable appenders: console, file, in-memory, Monolog handler bridge.
- Pluggable formatters: line, JSON, custom fn.
- Processors (middleware) for masking / enriching events.
- PSR-3 `LoggerInterface` adapter for Symfony / Laravel / Slim interop.

```phel
(ns my-app.users
  (:require phel.log :as log))

(log/info "user logged in" {:user-id 42})
;; 2026-05-16T10:21:33.123Z [INFO ] my-app.users - user logged in array (...)
```

## Install

```bash
composer require phel-lang/phel-log
```

Requires PHP 8.4+ and `phel-lang/phel-lang` 0.37+.
Optional: `monolog/monolog` 3.x for the Monolog handler bridge.

## Configure

```phel
(log/set-config!
  {:min-level :debug
   :appenders [(log/console-appender)
               (log/file-appender {:path "/var/log/my-app.log"
                                   :formatter :json
                                   :min-level :warn})]
   :ns-filter {:allow ["my-app.**"]
               :deny  ["my-app.noisy.**"]}
   :processors [(fn [ev] (assoc ev :data (merge (:data ev) {:trace-id (request-id)})))]
   :base-data  {:app "my-app" :env "prod"}})
```

## Levels

`:trace` -> `:debug` -> `:info` -> `:warn` -> `:error` -> `:fatal` -> `:report`

Each level has a macro: `log/trace`, `log/debug`, ..., `log/report`. The call-site
namespace is captured at macro-expand time, no extra plumbing.

```phel
(log/debug "raw payload" {:body request-body})
(log/error "failed to charge card" {:order-id 7 :error e})
(log/report "deployment finished" {:sha "abc123"})  ; always loud
```

## Appenders

| Appender                  | Purpose                                                        |
|---------------------------|----------------------------------------------------------------|
| `console-appender`        | stdout for info-, stderr for warn+                             |
| `file-appender`           | append-only, lock-on-write                                     |
| `memory-appender`         | capture events into an atom (tests / REPL)                     |
| `monolog-appender`        | bridge any `Monolog\Handler\HandlerInterface`                  |

All appenders share the same shape:

```phel
{:name      :my-appender
 :min-level :warn          ; optional, per-appender override
 :formatter :line          ; or :json, or (fn [event] string)
 :write!    (fn [event line] ...)}
```

Write your own by returning a map of the same shape; everything in
`log/write-event!` is data-driven.

## Formatters

- `:line` - human one-liner (timestamp, level, ns, message, data).
- `:json` - one JSON object per line; pipe straight into Loki / ELK / CloudWatch.
- `(fn [event] string)` - anything you want; the event is a plain map.

```phel
{:level :info
 :ns "my-app.users"
 :time-ms 1715846400123
 :message "user logged in"
 :data {:user-id 42}
 :error nil}
```

## Monolog handler bridge

Re-use every existing Monolog handler (Slack, Sentry, Syslog, RotatingFile, ...):

```phel
(let [slack (php/new \Monolog\Handler\SlackWebhookHandler
                     webhook-url
                     "#alerts"
                     "phel-bot"
                     true
                     nil
                     false
                     false
                     (php/-> \Monolog\Level (Error)))]
  (log/update-config!
    {:appenders [(log/console-appender)
                 (log/monolog-appender {:handler slack
                                        :channel "my-app"
                                        :min-level :error})]}))
```

## PSR-3 adapter

Drop the Phel logger into a PSR-3-aware PHP framework:

```php
use Phel\PhelLog\PsrLogger;

$dispatcher = static function (string $level, string $message, array $ctx): void {
    \Phel::run('my-bootstrap', 'log-from-psr', $level, $message, $ctx);
};

$logger = new PsrLogger($dispatcher, channel: 'symfony');
$container->set(\Psr\Log\LoggerInterface::class, $logger);
```

## Docs

- [Quickstart](docs/quickstart.md): smallest useful setup.
- [Config](docs/config.md): every option in `set-config!`.
- [Appenders](docs/appenders.md): console / file / memory / custom.
- [Monolog bridge](docs/monolog-bridge.md): forward into Monolog handlers.
- [PSR-3](docs/psr3.md): plug into PHP frameworks.

## Contributing

Run tests:

```bash
composer test
```

Conventional commits, PR template in `.github/PULL_REQUEST_TEMPLATE.md`.
