# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-05-16

First public release of `phel.log` — a data-driven logging library for
Phel, inspired by Timbre and Monolog.

### Added
- 7 levels with caller-ns-aware macros.
- Pluggable appenders (console, file, rotating-file, memory, Monolog bridge).
- Pluggable formatters (`:line`, `:json`, custom fn).
- Processors with nil-drop short-circuit (`sampler`, `rate-limiter`, custom).
- Scoped context (`with-context`), block timing (`timed`), test capture (`with-captured-events`).
- PSR-3 adapter for Symfony / Laravel / Slim.
- Compile-time level stripping via `PHEL_LOG_MIN_LEVEL`.

See [docs/](docs/) for the full API.

[Unreleased]: https://github.com/phel-lang/phel-log/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/phel-lang/phel-log/releases/tag/v0.1.0
