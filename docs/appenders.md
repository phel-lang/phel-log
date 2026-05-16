# Appenders

An appender is a map. Write your own by returning a map of the same shape.

```phel
{:name      :my-appender
 :min-level :warn               ; optional per-appender threshold
 :formatter :line               ; :line | :json | (fn [event] string)
 :write!    (fn [event line] ...)} ; side-effecting writer, mandatory
```

## Built-in appenders

### `console-appender`

```phel
(log/console-appender)
(log/console-appender {:formatter :json :min-level :warn})
```

`:info` and quieter go to `stdout`. `:warn` and louder go to `stderr`.

### `file-appender`

```phel
(log/file-appender {:path "/var/log/my-app.log"})
(log/file-appender {:path "/var/log/audit.log"
                    :formatter :json
                    :min-level :report})
```

Append-only, `LOCK_EX` on each write. No rotation; use `logrotate` or pair
with the Monolog `RotatingFileHandler` via the bridge.

### `memory-appender`

```phel
(let [[app events] (log/memory-appender)]
  (log/set-config! {:appenders [app] :min-level :debug})
  (log/info "captured" {:k 1})
  (println (deref events)))
;; => [{:level :info :ns "..." :message "captured" :data {:k 1} ...}]
```

Useful for tests and REPL exploration.

### `monolog-appender`

See [monolog-bridge.md](monolog-bridge.md).

## Custom appender

```phel
(defn syslog-appender []
  {:name :syslog
   :formatter :line
   :write! (fn [event line]
             (php/syslog
               (case (get event :level)
                 :error (php/-> \LOG_ERR)
                 :warn  (php/-> \LOG_WARNING)
                 (php/-> \LOG_INFO))
               line))})

(log/update-config! {:appenders [(syslog-appender)]})
```

The event passed to `:write!` is the same plain map every other appender
sees, so processors and `:base-data` apply identically to custom appenders.
