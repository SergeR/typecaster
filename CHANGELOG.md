# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
