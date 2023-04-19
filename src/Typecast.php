<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license MIT
 */

declare(strict_types=1);

namespace SergeR\Typecaster;

class Typecast
{
    /**
     * @param array $arr
     * @param array $keys
     * @return array
     */
    public static function scalarArrayValues(array $arr, array $keys): array
    {
        array_walk($keys, function (&$v) {
            if (!is_array($v)) $v = ['type' => $v, 'null' => false];
        });

        foreach ($keys as $key => $type) {
            if (!array_key_exists($key, $arr)) continue;
            $value = $arr[$key];
            if (null !== $value && !is_scalar($value)) continue;
            if (null === $value && $type['null']) continue;
            switch ($type['type']) {
                case 'trim':
                    $arr[$key] = trim((string)$value);
                    break;
                case 'string':
                    $arr[$key] = (string)$value;
                    break;
                case 'float':
                    $arr[$key] = self::floatval($value, $type['precision'] ?? null, $type['min'] ?? null, $type['max'] ?? null, boolval($type['null'] ?? false));
                    break;
                case 'int':
                case 'integer' :
                case 'intval' :
                    $arr[$key] = (int)self::floatval($value, 0, $type['min'] ?? null, $type['max'] ?? null, $type['null'] ?? false);
                    break;
                case 'bool':
                case 'boolean':
                case 'boolval':
                    if (is_string($value) && !strlen(trim($value)) && $type['null']) $arr[$key] = null;
                    else $arr[$key] = boolval($value);
                    break;
                case 'json':
                    if (is_string($value)) {
                        $value = trim($value);
                        if (!strlen($value) && ($type['null'] ?? false)) $arr[$key] = null;
                        elseif (strlen($value)) {
                            $value = json_decode($value, $type['as_array'] ?? false);
                            if (null !== $value || ($type['null'] ?? false)) $arr[$key] = $value;
                        }
                    }
                    break;
            }
        }
        return $arr;
    }

    /**
     * @param int|float|string|null $value
     * @param int|null $precision
     * @param float|int|null $min
     * @param float|int|null $max
     * @param bool $nullable
     * @return float|null
     */
    public static function floatval($value, ?int $precision = null, ?float $min = null, ?float $max = null, bool $nullable = false): ?float
    {
        if (null === $value) {
            if ($nullable) return null;
            $value = 0.0;
        } elseif (is_string($value)) {
            if (!strlen($value = trim($value))) {
                if ($nullable) return null;
                $value = (float)str_replace(',', '.', $value);
            }
        } elseif (is_numeric($value)) $value = (float)$value;
        else throw new \InvalidArgumentException("string, number or null required");

        if (null !== $precision) $value = round($value, $precision);
        if (null !== $min) $value = (float)max($min, $value);
        if (null !== $max) $value = (float)min($max, $value);

        return $value;
    }
}
