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
(log/console-appender {:color :always})   ; force ANSI colours
(log/console-appender {:color :never})    ; force plain text
```

`:info` and quieter go to `stdout`. `:warn` and louder go to `stderr`.

`:color` defaults to `:auto`, which emits ANSI escapes only when the
destination stream is a TTY — so piping into a file or CI capture stays
plain text. Per-level colours live in `log/level->ansi`; override the map
to pick your own palette.

### `file-appender`

```phel
(log/file-appender {:path "/var/log/my-app.log"})
(log/file-appender {:path "/var/log/audit.log"
                    :formatter :json
                    :min-level :report})
```

Append-only, `LOCK_EX` on each write. No rotation; use `logrotate`,
`rotating-file-appender` below, or pair with the Monolog
`RotatingFileHandler` via the bridge.

### `rotating-file-appender`

```phel
(log/rotating-file-appender
  {:path-template "/var/log/my-app/{date}/{level}.log"})

(log/rotating-file-appender
  {:path-template "/var/log/my-app/{date}-{hour}.log"
   :formatter     :json})
```

The path template is rendered per event from the event's `:time-ms`,
not the host wall-clock. Substitutions: `{date}` (UTC `YYYY-MM-DD`),
`{hour}` (UTC `HH`), `{ns}` (caller namespace), `{level}` (lowercase
level name). Parent directories are created on demand; pass
`:ensure-dir? false` to skip the `mkdir`.

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
