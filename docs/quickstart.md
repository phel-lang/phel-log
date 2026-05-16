# Quickstart

Smallest useful setup. Three steps: install, require, log.

## 1. Install

```bash
composer require phel-lang/phel-log
```

## 2. Require + log

```phel
(ns my-app.main
  (:require phel.log :as log))

(log/info "service started" {:port 8080})
(log/warn "slow query" {:ms 1340 :sql "SELECT ..."})
(log/error "payment failed" {:order-id 7 :error e})
```

Default config:

| Key         | Default               |
|-------------|-----------------------|
| `:min-level`| `:info`               |
| `:appenders`| `[(console-appender)]`|
| `:ns-filter`| `nil` (no filter)     |
| `:processors`| `[]`                 |
| `:base-data`| `{}`                  |

Output (line format):

```
2026-05-16T10:21:33.123Z [INFO ] my-app.main - service started array (':port' => 8080)
```

## 3. Tune it

```phel
(log/update-config! {:min-level :debug
                     :base-data {:app "my-app" :env "prod"}})
```

Or wire everything in one call:

```phel
(log/init! :debug
           [(log/console-appender) (log/file-appender {:path "app.log"})]
           {:base-data {:app "my-app" :env "prod"}})
```

## 4. Errors

A bare `\Throwable` as the second arg is auto-wrapped into `{:error e}`:

```phel
(try (do-something)
  (catch \RuntimeException e
    (log/error "payment failed" e)))
```

## 5. Scoped context

`with-context` attaches fields to every log call inside its body:

```phel
(log/with-context {:request-id req-id :user-id user-id}
  (log/info "handling request")
  (process-payload data))         ; nested log calls inherit the context
```

## 6. Time a block

```phel
(log/timed :info "fetch users"
  (db/query "SELECT * FROM users"))
;; emits one event with :elapsed-ms in :data; logs an :error variant
;; with the throwable if the body throws.
```

## 7. Strip levels at compile time

Set `PHEL_LOG_MIN_LEVEL` before building. Any `log/<level>` call below
the threshold compiles to `nil` and pays zero runtime cost:

```bash
PHEL_LOG_MIN_LEVEL=info vendor/bin/phel build
# log/trace and log/debug calls vanish from compiled output
```

Next: see [config.md](config.md) for every option, or
[appenders.md](appenders.md) to send events to files or Monolog handlers.
