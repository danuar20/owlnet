<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Example Unit Test
|--------------------------------------------------------------------------
|
| Demonstrates a passing Pint-formatted Pest test. Billing feature tests
| will be added alongside their implementations.
|
*/
test('helpers produce expected output', function () {
    expect(human_bytes(0))->toBe('0 B')
        ->and(mac_normalize('a1-b2-c3-d4-e5-f6'))->toBe('A1:B2:C3:D4:E5:F6')
        ->and(strlen(random_voucher_code(8)))->toBe(8);
});
