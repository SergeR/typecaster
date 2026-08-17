# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

`serger/typecaster` is a minimal, dependency-free PHP library for coercing scalar values in arrays
to expected types (e.g. normalizing request/form data before validation or persistence). The entire
library is currently a single class: `src/Typecast.php` (namespace `SergeR\Typecaster`).

Requires PHP >=7.2 and `ext-json` at runtime — no runtime dependencies otherwise. Dev tooling
(PHPUnit 9 for tests, Psalm 6 for static analysis) lives in `require-dev`; tests live in `tests/`
(PSR-4 `SergeR\Typecaster\Tests\`, mirrored via `autoload-dev`).

## Commands

- Install dependencies: `composer install`
- Run tests: `composer test` (or `vendor/bin/phpunit`)
- Static analysis against the declared minimum PHP version (7.2): `composer psalm:php72`
- Static analysis against the newest supported PHP version (8.5): `composer psalm:php85`

## Architecture

The library centers on `Typecast::scalarArrayValues(array $arr, array $keys): array`, which mutates
and returns a copy of `$arr` by coercing selected keys to declared types:

- `$keys` maps array key => either a type name string, or a spec array with keys `type`, `null`,
  and type-specific options (`precision`, `min`, `max`, `as_array`). Bare strings are normalized to
  `['type' => $v, 'null' => false]` up front.
- For each key present in `$arr`, non-scalar values are skipped, and `null` values are left alone
  unless the spec allows `null` (in which case they stay `null`).
- Supported `type` values: `trim`, `string`, `float`, `int`/`integer`/`intval`, `bool`/`boolean`/
  `boolval`, `json`. Unknown values from `$arr` are left untouched (the `switch` falls through).
- `int` coercion is implemented via `floatval()` with `precision = 0`, then cast to `int` — it is not
  a direct `(int)` cast, so min/max clamping and locale-style decimal commas apply to integers too.
- `json` coercion only acts on string values; empty strings become `null` only when `null` is
  allowed, and `json_decode` failures (returning `null`) are only written back when `null` is
  allowed — otherwise the original string is left in place.

`Typecast::floatval($value, ?precision, ?min, ?max, bool $nullable): ?float` is the shared numeric
coercion helper reused by the `float` and `int` cases above:

- Accepts `int|float|string|bool|null`; throws `InvalidArgumentException` for anything else. `bool`
  is coerced via `(float)` (so `true` → `1.0`, `false` → `0.0`).
- Treats `null` as `0.0` unless `$nullable` is true, in which case it returns `null`.
- For strings, trims whitespace and replaces `,` with `.` before casting (so `"1,5"` → `1.5`); an
  empty string after trimming returns `null` only if `$nullable`.
- Applies `round($value, $precision)` then clamps to `[$min, $max]`, in that order — precision
  rounding happens *before* clamping.

When extending this library with new types, follow the existing pattern: add a `case` in the
`switch` inside `scalarArrayValues`, and keep the `null`-handling / non-scalar-skip logic at the top
of the loop as the single gate that all types share.

## Commit messages and versioning

- Commit messages must be written in English, following the
  [Conventional Commits](https://www.conventionalcommits.org/) format (e.g. `fix: ...`,
  `feat: ...`, `docs: ...`, `chore: ...`, with `!` or a `BREAKING CHANGE:` footer for breaking
  changes).
- Version numbers follow [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`).
- `CHANGELOG.md` follows the [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format; add
  entries under an `[Unreleased]` section and move them under a versioned/dated section on release.
