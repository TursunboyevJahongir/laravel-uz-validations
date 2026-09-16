<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Tests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use TursunboyevJahongir\UzValidations\Rules\UzCarNumber;
use TursunboyevJahongir\UzValidations\Rules\UzPassport;
use TursunboyevJahongir\UzValidations\Rules\UzPhone;
use TursunboyevJahongir\UzValidations\Rules\UzPinfl;
use TursunboyevJahongir\UzValidations\Rules\UzStir;

final class RulesTest extends TestCase
{
    #[DataProvider('validValues')]
    public function test_accepts_valid_values(string $ruleClass, string $value): void
    {
        $rule = new $ruleClass;
        $this->assertInstanceOf(ValidationRule::class, $rule);
        $this->assertTrue(Validator::make(['value' => $value], ['value' => ['required', $rule]])->passes());
    }

    public static function validValues(): iterable
    {
        // Explicit test oracle, independent of the implementation's prefix constant.
        $prefixes = ['20', '33', '50', '55', '61', '62', '65', '66', '67', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '88', '90', '91', '93', '94', '95', '97', '98', '99'];
        foreach ($prefixes as $prefix) {
            yield "phone +998 $prefix" => [UzPhone::class, '+998'.$prefix.'1234567'];
            yield "phone 998 $prefix" => [UzPhone::class, '998'.$prefix.'1234567'];
        }

        // Published examples from Resolution No. 177 and its predecessor No. 200.
        foreach (['31210932040247', '40201902050010', '31210632040244', '40201402050015'] as $pinfl) {
            yield "official PINFL $pinfl" => [UzPinfl::class, $pinfl];
        }

        foreach ([1, 2, 3, 4, 5, 6] as $century) {
            yield "century $century" => [UzPinfl::class, self::pinfl($century.'010100123001')];
        }

        yield 'leap day in 2000' => [UzPinfl::class, self::pinfl('5290200123001')];
        yield 'birth today' => [UzPinfl::class, self::pinfl('5160926123001')];

        foreach (['AA1234567', 'FA1234567', 'AD0000001', 'AB7654321', 'ZZ1234567'] as $passport) {
            yield "passport $passport" => [UzPassport::class, $passport];
        }

        foreach (['01 A 123 AA', '01A123AA', '01 123 AAA', '01123AAA', '99 Z 999 ZZ', '09A001BC', '24123XYZ', '85123ABC'] as $plate) {
            yield "plate $plate" => [UzCarNumber::class, $plate];
        }

        foreach (['123456789', '201122919', '012345678', '000000000', '999999999'] as $stir) {
            yield "STIR format $stir" => [UzStir::class, $stir];
        }
    }

    #[DataProvider('invalidValues')]
    public function test_rejects_invalid_values(string $ruleClass, mixed $value): void
    {
        $validator = Validator::make(['value' => $value], ['value' => [new $ruleClass]]);
        $this->assertTrue($validator->fails());
        $this->assertCount(1, $validator->errors()->get('value'));
        $this->assertStringNotContainsString('uz-validations::', $validator->errors()->first('value'));
    }

    public static function invalidValues(): iterable
    {
        $cases = [
            UzPhone::class => ['901234567', '+997901234567', '998001234567', '998921234567', '998961234567', '998891234567', '+998 90 123 45 67', '++998901234567', '99890123456', '9989012345678', '+998９01234567', "+998901234567\n", ' 998901234567', '998901234567 ', 998901234567],
            UzPinfl::class => ['31210932040248', '40201902050011', '01210932040247', '71210932040247', '3121093204024', '312109320402477', "31210932040247\n", '３1210932040247', 31210932040247, self::pinfl('3310493123001'), self::pinfl('3290200123001'), self::pinfl('5290223123001'), self::pinfl('5000100123001'), self::pinfl('5011300123001'), self::pinfl('5170926123001'), self::pinfl('5311299123001')],
            UzPassport::class => ['aa1234567', 'Aa1234567', 'A1234567', 'AAA1234567', 'AA123456', 'AA12345678', 'AA 1234567', 'АА1234567', 'AA１２３４５６７', "AA1234567\n", ' AA1234567', 'AA1234567 '],
            UzCarNumber::class => ['00A123AA', '00123AAA', '100A123AA', '01a123AA', '01А123AA', '01 A123 AA', '01A 123AA', '01  A 123 AA', "01\tA\t123\tAA", '01-A-123-AA', "01A123AA\n", '01A123AA ', ' 01A123AA', '01A12AA', '01A123AAA', '01D123456', '01 1234 AA'],
            UzStir::class => ['12345678', '1234567890', '12345678A', '+123456789', '123 456789', '１２３４５６７８９', "123456789\n", ' 123456789', '123456789 ', 123456789, 123456789.0],
        ];

        foreach ($cases as $class => $values) {
            foreach (array_merge($values, [null, true, false, [], ['123'], new \stdClass]) as $index => $value) {
                yield "$class invalid $index" => [$class, $value];
            }
        }
    }

    public function test_all_single_digit_pinfl_mutations_fail(): void
    {
        $valid = '31210932040247';
        for ($position = 0; $position < 14; $position++) {
            for ($digit = 0; $digit <= 9; $digit++) {
                if ((string) $digit === $valid[$position]) {
                    continue;
                }
                $mutated = substr_replace($valid, (string) $digit, $position, 1);
                $this->assertFalse(Validator::make(['value' => $mutated], ['value' => [new UzPinfl]])->passes(), $mutated);
            }
        }
    }

    public function test_every_allocated_plate_region_is_supported(): void
    {
        for ($region = 1; $region <= 99; $region++) {
            foreach (['A123BC', '123ABC'] as $suffix) {
                $this->assertTrue(Validator::make(['value' => sprintf('%02d', $region).$suffix], ['value' => [new UzCarNumber]])->passes());
            }
        }
    }

    public function test_rules_follow_laravel_required_and_nullable_semantics(): void
    {
        foreach ([UzPhone::class, UzPinfl::class, UzPassport::class, UzCarNumber::class, UzStir::class] as $class) {
            $this->assertTrue(Validator::make([], ['value' => [new $class]])->passes());
            $this->assertTrue(Validator::make(['value' => ''], ['value' => [new $class]])->passes());
            $this->assertTrue(Validator::make(['value' => null], ['value' => ['nullable', new $class]])->passes());
            $this->assertFalse(Validator::make([], ['value' => ['required', new $class]])->passes());
            $this->assertFalse(Validator::make(['value' => ''], ['value' => ['required', new $class]])->passes());
        }
    }

    public function test_rule_instance_can_be_reused_without_retaining_failure_state(): void
    {
        $rule = new UzPinfl;
        $this->assertFalse(Validator::make(['value' => 'invalid'], ['value' => [$rule]])->passes());
        $this->assertTrue(Validator::make(['value' => '31210932040247'], ['value' => [$rule]])->passes());
    }

    private static function pinfl(string $body): string
    {
        // Synthetic date fixtures; official checksum fixtures above remain independent.
        $digits = array_map('intval', str_split($body));
        $sum = 7 * array_sum(array_intersect_key($digits, array_flip([0, 3, 6, 9, 12])))
            + 3 * array_sum(array_intersect_key($digits, array_flip([1, 4, 7, 10])))
            + array_sum(array_intersect_key($digits, array_flip([2, 5, 8, 11])));

        return $body.($sum % 10);
    }
}
