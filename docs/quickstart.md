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

Next: see [config.md](config.md) for every option, or
[appenders.md](appenders.md) to send events to files or Monolog handlers.
