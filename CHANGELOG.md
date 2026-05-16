# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `phel.log` namespace: data-driven logger inspired by Timbre + Monolog.
- Levels: `:trace` `:debug` `:info` `:warn` `:error` `:fatal` `:report` with numeric ordering.
- `log/event` map: `{:level :ns :timestamp :message :data}`.
- Formatters: `line` (human-readable) and `json` (one event per line).
- Appenders: `console` (stdout/stderr split by level), `memory` (capture for tests), `monolog-bridge` (forward to any `Monolog\Handler\HandlerInterface`).
- Global config: `set-config!`, `get-config`, with `:min-level`, `:ns-filter`, `:appenders`, `:base-data` (Timbre-style middleware via `:processors`).
- Macros: `log/trace`, `log/debug`, `log/info`, `log/warn`, `log/error`, `log/fatal`, `log/report`. Capture call-site namespace at expand time.
- PHP class `Phel\PhelLog\PsrLogger` implementing `Psr\Log\LoggerInterface` so Symfony / Laravel can log into the Phel pipeline.
- Docs tree under `docs/`: quickstart, config, appenders, monolog-bridge, psr-3.
- `log/init!` for one-call setup: `(log/init! :info [appenders] opts)`.
- `log/with-context`: fiber-local context merged into every event's
  `:data` between `:base-data` and the per-call data. Backed by a
  `^:dynamic` `*log-context*` var so nested frames stack cleanly.
- `log/timed`: time a block, emit one event with `:elapsed-ms` in
  `:data`. Re-throws on exception after logging an `:error` variant.
- Throwable auto-wrap on level macros: `(log/error "msg" e)` is now
  equivalent to `(log/error "msg" {:error e})`.
- `PHEL_LOG_MIN_LEVEL` env var stripping: level macros below the
  configured floor compile to literal `nil`, so production builds
  pay zero runtime cost for `log/trace` / `log/debug` calls.

### Changed
- `emit!` skips event construction entirely when no appender accepts
  the level (per-appender `:min-level` filter pre-checked).
- `emit!` reads the global config once per call instead of twice.
- `pattern->regex` results are memoised; ns-filter patterns now pay
  the regex-compile cost on first use and reuse the compiled regex
  for every subsequent event.

[Unreleased]: https://github.com/phel-lang/phel-log/compare/HEAD...HEAD
