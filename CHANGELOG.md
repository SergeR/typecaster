# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `phpunit/phpunit` (`^9`) and `vimeo/psalm` (`^6.16`) as dev dependencies.
- Two Psalm configs to check type-checking compatibility against both ends of the
  supported PHP range: `psalm.xml` (PHP 7.2, the declared minimum) and
  `psalm-php85.xml` (PHP 8.5), runnable via `composer psalm:php72` /
  `composer psalm:php85`.

### Fixed

- `Typecast::scalarArrayValues()` no longer emits an `Undefined array key "null"`
  warning when a key's spec array omits the `null` option (e.g.
  `['type' => 'trim']`).

### Changed

- `Typecast::floatval()` now coerces `bool` to `1.0`/`0.0` instead of throwing
  `InvalidArgumentException`; `@param`/`@throws` docblocks updated to match.
  This also affects `Typecast::scalarArrayValues()` for `float`/`int` keys
  fed a boolean value.

## [1.0.1] - 2023-04-21

### Fixed

- `Typecast::floatval()` no longer skips locale-style decimal comma normalization
  (`,` → `.`) for non-empty, non-nullable string values.

## [1.0.0] - 2023-04-19

### Added

- Initial release of `Typecast::scalarArrayValues()` for coercing scalar array
  values to declared types (`trim`, `string`, `float`, `int`, `bool`, `json`).
- `Typecast::floatval()` numeric coercion helper with `precision`, `min`, `max`,
  and `nullable` support.

[Unreleased]: https://github.com/SergeR/typecaster/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/SergeR/typecaster/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/SergeR/typecaster/releases/tag/v1.0.0
