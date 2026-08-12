<?php

use App\Enums\BillingCycle;
use App\Enums\CustomerStatus;
use App\Enums\ServiceStatus;

it('exposes every customer status value', function () {
    expect(CustomerStatus::values())->toBe(['active', 'inactive'])
        ->and(CustomerStatus::Active->label())->toBe('Active');
});

it('exposes every service status value', function () {
    expect(ServiceStatus::values())->toBe(['pending', 'active', 'suspended', 'cancelled'])
        ->and(ServiceStatus::Suspended->label())->toBe('Suspended');
});

it('only treats an active service as billable', function (ServiceStatus $status, bool $billable) {
    expect($status->isBillable())->toBe($billable);
})->with([
    [ServiceStatus::Pending, false],
    [ServiceStatus::Active, true],
    [ServiceStatus::Suspended, false],
    [ServiceStatus::Cancelled, false],
]);

it('exposes every billing cycle value', function () {
    expect(BillingCycle::values())->toBe(['one_time', 'monthly', 'quarterly', 'yearly'])
        ->and(BillingCycle::OneTime->label())->toBe('One Time');
});

it('knows how many times each cycle bills within a year', function (BillingCycle $cycle, int $occurrences) {
    expect($cycle->occurrencesPerYear())->toBe($occurrences);
})->with([
    [BillingCycle::OneTime, 1],
    [BillingCycle::Monthly, 12],
    [BillingCycle::Quarterly, 4],
    [BillingCycle::Yearly, 1],
]);
