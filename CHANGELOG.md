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

[Unreleased]: https://github.com/phel-lang/phel-log/compare/HEAD...HEAD
