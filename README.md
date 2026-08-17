# Typecaster

Simple typecasting for PHP arrays, with no external dependencies.

`Typecaster` coerces scalar values in an array to expected types — handy for
normalizing request or form data before validation or persistence.

The library works on flat arrays only — it does not recurse into nested
arrays (see [Usage](#usage) below). This makes it a good fit for casting
single-row results from a database query, where every column is already a
scalar; for deeply nested structures you'd call `scalarArrayValues()`
separately per nested array.

## Requirements

- PHP >= 7.2
- `ext-json`

## Installation

```bash
composer require serger/typecaster
```

## Usage

```php
use SergeR\Typecaster\Typecast;

$data = [
    'name'   => '  John Doe  ',
    'age'    => '42',
    'price'  => '19,99',
    'active' => '1',
    'meta'   => '{"role":"admin"}',
];

$result = Typecast::scalarArrayValues($data, [
    'name'   => 'trim',
    'age'    => 'int',
    'price'  => ['type' => 'float', 'precision' => 2, 'min' => 0],
    'active' => 'bool',
    'meta'   => ['type' => 'json', 'as_array' => true],
]);

// [
//     'name'   => 'John Doe',
//     'age'    => 42,
//     'price'  => 19.99,
//     'active' => true,
//     'meta'   => ['role' => 'admin'],
// ]
```

### `scalarArrayValues(array $arr, array $keys): array`

Returns a copy of `$arr` with the keys listed in `$keys` coerced to the given
type. Keys not present in `$arr` are left absent unless the spec has a
`default` (see below), and non-scalar values are left untouched.

Each entry in `$keys` is either:

- a type name string (e.g. `'int'`), or
- a spec array: `['type' => ..., 'null' => bool, 'default' => ..., ...type-specific options]`

If a value is `null`, it is left as `null` when the spec allows it
(`'null' => true`); otherwise it is coerced like any other value.

If a key is missing from `$arr` entirely and its spec has a `default`, that
default is written to the result as-is — it is **not** coerced, so it should
already match the declared `type`. `default` only applies to missing keys; a
key present with an explicit `null` value is governed by `'null'` instead,
not by `default`.

```php
Typecast::scalarArrayValues($data, [
    'marking'    => ['type' => 'string', 'default' => ''],
    'paid_price' => ['type' => 'float', 'default' => 0.0],
]);
```

#### Supported types

| Type                          | Notes                                                                 |
|-------------------------------|------------------------------------------------------------------------|
| `trim`                         | `trim((string) $value)`                                               |
| `string`                       | `(string) $value`                                                     |
| `float`                        | See `floatval()` below; supports `precision`, `min`, `max`            |
| `int`, `integer`, `intval`     | Same as `float` with `precision = 0`, then cast to `int`              |
| `bool`, `boolean`, `boolval`   | `boolval($value)`; an empty string becomes `null` if `'null' => true` |
| `json`                         | Decodes a JSON string; supports `as_array`                            |

For `json`, empty strings become `null` only if `'null' => true`, and a
failed decode (`json_decode` returning `null`) is only written back when
`'null' => true` — otherwise the original string is kept.

### `floatval($value, ?int $precision = null, ?float $min = null, ?float $max = null, bool $nullable = false): ?float`

Shared numeric coercion helper used internally by the `float` and `int`
types, and also usable directly:

- Accepts `int|float|string|null`; throws `InvalidArgumentException` for
  anything else.
- `null` becomes `0.0`, unless `$nullable` is `true`, in which case it
  returns `null`.
- Strings are trimmed and have `,` replaced with `.` before casting (so
  `"1,5"` becomes `1.5`).
- Rounds to `$precision` (if given), then clamps to `[$min, $max]` (if
  given) — rounding happens before clamping.

## License

Released under the [MIT License](LICENSE).
