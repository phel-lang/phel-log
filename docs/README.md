# phel-log docs

Data-driven logging for [Phel Lang](https://phel-lang.org/). Levels,
namespace + per-appender filtering, pluggable appenders + formatters,
PSR-3 adapter, Monolog handler bridge.

## Start here

1. **[Quickstart](quickstart.md)** — install, log a line, scoped context, time a block, test what gets logged. 5 minutes end-to-end.
2. **[Config](config.md)** — every key in the config map (`:min-level`, `:appenders`, `:ns-filter`, `:processors`, `:base-data`).
3. **[Appenders](appenders.md)** — console / file / rotating-file / memory + how to write your own.

## Integrations

- **[Monolog bridge](monolog-bridge.md)** — re-use any `Monolog\Handler\HandlerInterface` (Slack, Sentry, Syslog, Elasticsearch, ...) as a phel.log appender.
- **[PSR-3 adapter](psr3.md)** — drop the phel.log pipeline into Symfony / Laravel / Slim via `Psr\Log\LoggerInterface`.

## By goal

| I want to ...                                 | Read                                                       |
|-----------------------------------------------|------------------------------------------------------------|
| Get logs to stdout / a file                   | [Quickstart](quickstart.md), [Appenders](appenders.md)     |
| Filter by namespace                           | [Config — `:ns-filter`](config.md)                         |
| Mask or enrich every event                    | [Config — `:processors`](config.md)                        |
| Sample / rate-limit noisy levels              | [Config — `:processors`](config.md)                        |
| Attach a `request-id` to every nested call    | [Quickstart §5 (`with-context`)](quickstart.md)            |
| Time a block of code                          | [Quickstart §6 (`timed`)](quickstart.md)                   |
| Send to Slack / Sentry / Syslog               | [Monolog bridge](monolog-bridge.md)                        |
| Log from a Symfony / Laravel controller       | [PSR-3 adapter](psr3.md)                                   |
| Assert log output in a unit test              | [Quickstart §7 (`with-captured-events`)](quickstart.md)    |
| Strip `:trace` / `:debug` from prod builds    | [Quickstart §8 (`PHEL_LOG_MIN_LEVEL`)](quickstart.md)      |

## Examples

Runnable smoke scripts under [`examples/`](../examples/):

- `quickstart.phel` — step-by-step walk through the core API.
- `monolog_bridge.phel` — wiring a Monolog `TestHandler` as an appender.
- `psr3.php` — PSR-3 calls round-tripping through the pipeline.

Run them all via:

```bash
composer smoke
```

## API cheatsheet

```phel
;; Setup
(log/init! :info [(log/console-appender)])

;; Log
(log/info  "user logged in" {:user-id 42})
(log/error "payment failed" e)              ; bare throwable auto-wrapped

;; Scoped fields
(log/with-context {:request-id rid}
  (log/info "handling request"))

;; Time a block
(log/timed :info "fetch users"
  (db/query "SELECT ..."))

;; Capture in a test
(log/with-captured-events events
  (do-thing)
  (is (= 1 (count (deref events)))))
```
