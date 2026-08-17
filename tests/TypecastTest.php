<?php

declare(strict_types=1);

namespace SergeR\Typecaster\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SergeR\Typecaster\Typecast;
use stdClass;

class TypecastTest extends TestCase
{
    public function testTrimCoercesAndStripsWhitespace(): void
    {
        $result = Typecast::scalarArrayValues(['name' => '  John  '], ['name' => 'trim']);

        $this->assertSame('John', $result['name']);
    }

    public function testStringCoercesScalarToString(): void
    {
        $result = Typecast::scalarArrayValues(['age' => 42], ['age' => 'string']);

        $this->assertSame('42', $result['age']);
    }

    public function testFloatCoercesLocaleStyleDecimalComma(): void
    {
        $result = Typecast::scalarArrayValues(['price' => '19,99'], ['price' => 'float']);

        $this->assertSame(19.99, $result['price']);
    }

    public function testFloatAppliesPrecisionBeforeClamping(): void
    {
        $result = Typecast::scalarArrayValues(
            ['price' => '1.96'],
            ['price' => ['type' => 'float', 'precision' => 1, 'max' => 1.5]]
        );

        // round(1.96, 1) = 2.0, then clamped to max 1.5
        $this->assertSame(1.5, $result['price']);
    }

    public function testIntCoercionRoundsRatherThanTruncates(): void
    {
        $result = Typecast::scalarArrayValues(['qty' => '15,6'], ['qty' => 'int']);

        $this->assertSame(16, $result['qty']);
    }

    public function testIntCoercionCoercesBooleanInputInsteadOfThrowing(): void
    {
        $result = Typecast::scalarArrayValues(['flag' => true], ['flag' => 'int']);

        $this->assertSame(1, $result['flag']);
    }

    public function testFloatCoercionCoercesBooleanInput(): void
    {
        $result = Typecast::scalarArrayValues(['flag' => false], ['flag' => 'float']);

        $this->assertSame(0.0, $result['flag']);
    }

    public function testBoolCoercionCastsTruthyAndFalsyValues(): void
    {
        $result = Typecast::scalarArrayValues(
            ['a' => 'yes', 'b' => 0, 'c' => ''],
            ['a' => 'bool', 'b' => 'bool', 'c' => 'bool']
        );

        $this->assertTrue($result['a']);
        $this->assertFalse($result['b']);
        $this->assertFalse($result['c']);
    }

    public function testBoolCoercionTreatsEmptyStringAsNullWhenAllowed(): void
    {
        $result = Typecast::scalarArrayValues(
            ['c' => '  '],
            ['c' => ['type' => 'bool', 'null' => true]]
        );

        $this->assertNull($result['c']);
    }

    public function testJsonCoercionDecodesToObjectByDefault(): void
    {
        $result = Typecast::scalarArrayValues(['payload' => '{"a":1}'], ['payload' => 'json']);

        $this->assertInstanceOf(stdClass::class, $result['payload']);
        $this->assertSame(1, $result['payload']->a);
    }

    public function testJsonCoercionDecodesToArrayWhenRequested(): void
    {
        $result = Typecast::scalarArrayValues(
            ['payload' => '{"a":1}'],
            ['payload' => ['type' => 'json', 'as_array' => true]]
        );

        $this->assertSame(['a' => 1], $result['payload']);
    }

    public function testJsonCoercionLeavesInvalidJsonUntouchedWhenNullNotAllowed(): void
    {
        $result = Typecast::scalarArrayValues(['payload' => 'not-json'], ['payload' => 'json']);

        $this->assertSame('not-json', $result['payload']);
    }

    public function testJsonCoercionSetsNullForEmptyStringWhenAllowed(): void
    {
        $result = Typecast::scalarArrayValues(
            ['payload' => '  '],
            ['payload' => ['type' => 'json', 'null' => true]]
        );

        $this->assertNull($result['payload']);
    }

    public function testMissingKeysAreIgnored(): void
    {
        $result = Typecast::scalarArrayValues(['name' => 'x'], ['other' => 'trim']);

        $this->assertSame(['name' => 'x'], $result);
    }

    public function testNonScalarValuesAreLeftUntouched(): void
    {
        $result = Typecast::scalarArrayValues(['tags' => ['a', 'b']], ['tags' => 'trim']);

        $this->assertSame(['a', 'b'], $result['tags']);
    }

    public function testNullIsPreservedWhenAllowedByTheSpec(): void
    {
        $result = Typecast::scalarArrayValues(
            ['name' => null],
            ['name' => ['type' => 'trim', 'null' => true]]
        );

        $this->assertNull($result['name']);
    }

    public function testNullIsCoercedWhenNotAllowedByTheSpec(): void
    {
        $result = Typecast::scalarArrayValues(['name' => null], ['name' => 'trim']);

        $this->assertSame('', $result['name']);
    }

    public function testSpecArrayWithoutNullOptionDoesNotWarn(): void
    {
        $result = Typecast::scalarArrayValues(['name' => null], ['name' => ['type' => 'trim']]);

        $this->assertSame('', $result['name']);
    }

    public function testFloatvalReturnsZeroForNullWhenNotNullable(): void
    {
        $this->assertSame(0.0, Typecast::floatval(null));
    }

    public function testFloatvalReturnsNullForNullWhenNullable(): void
    {
        $this->assertNull(Typecast::floatval(null, null, null, null, true));
    }

    public function testFloatvalReturnsNullForEmptyStringWhenNullable(): void
    {
        $this->assertNull(Typecast::floatval('  ', null, null, null, true));
    }

    public function testFloatvalCoercesBooleans(): void
    {
        $this->assertSame(1.0, Typecast::floatval(true));
        $this->assertSame(0.0, Typecast::floatval(false));
    }

    public function testFloatvalClampsToMinAndMax(): void
    {
        $this->assertSame(5.0, Typecast::floatval(1, null, 5, 10));
        $this->assertSame(10.0, Typecast::floatval(15, null, 5, 10));
    }

    public function testFloatvalThrowsForUnsupportedType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Typecast::floatval([1, 2, 3]);
    }
}
