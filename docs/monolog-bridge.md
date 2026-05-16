# Monolog bridge

Re-use every existing [Monolog](https://github.com/Seldaek/monolog) handler
as a phel.log appender. Slack, Sentry, Syslog, RotatingFile, Elasticsearch,
NewRelic, Telegram, ... all become one-liner appenders.

## Install

`monolog/monolog` is a `suggest`, not a hard dep. Add it to your own project:

```bash
composer require monolog/monolog
```

## Use

```phel
(ns my-app.bootstrap
  (:require phel.log :as log))

(let [slack-url     "https://hooks.slack.com/..."
      slack-handler (php/new \Monolog\Handler\SlackWebhookHandler
                             slack-url
                             "#alerts"
                             "phel-bot"
                             true
                             nil
                             false
                             false
                             (php/-> \Monolog\Level (Error)))
      file-handler  (php/new \Monolog\Handler\RotatingFileHandler
                             "/var/log/my-app.log"
                             7
                             (php/-> \Monolog\Level (Info)))]
  (log/update-config!
    {:appenders [(log/console-appender)
                 (log/monolog-appender {:handler slack-handler
                                        :channel "my-app"
                                        :min-level :error})
                 (log/monolog-appender {:handler file-handler
                                        :channel "my-app"})]}))
```

## Level mapping

phel.log -> Monolog (`Monolog\Level` int value):

| phel.log   | Monolog level |
|------------|---------------|
| `:trace`   | DEBUG (100)   |
| `:debug`   | DEBUG (100)   |
| `:info`    | INFO (200)    |
| `:warn`    | WARNING (300) |
| `:error`   | ERROR (400)   |
| `:fatal`   | CRITICAL (500)|
| `:report`  | ALERT (600)   |

## How it works

`monolog-appender` constructs a `Monolog\LogRecord` per event with the event's
timestamp, channel, level, message, and `:data` map (as context), then calls
`$handler->handle($record)`. The handler's own formatter / processors take
over from there; phel.log's `:processors` still run upstream.
