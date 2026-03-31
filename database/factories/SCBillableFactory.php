<?php

namespace Database\Factories;

use App\Models\SCBillable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCBillable>
 */
class SCBillableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $month = (int) fake()->numberBetween(1, 12);
        $year = (int) fake()->numberBetween((int) date('Y') - 1, (int) date('Y'));
        $tenantId = fake()->uuid();
        $productGroup = fake()->randomElement(['endpoint', 'firewall', 'email']);
        $billableQuantity = fake()->numberBetween(1, 500);
        $orderedQuantity = fake()->numberBetween(1, 500);
        $actualQuantity = fake()->numberBetween(1, 500);
        $productCode = strtoupper(fake()->bothify('PROD-####'));
        $sku = strtoupper(fake()->bothify('SKU-########'));
        $orderLineItemNumber = strtoupper(fake()->bothify('OLI-#####'));

        // Generate realistic raw data based on actual Sophos API structure
        $rawData = [
            'orderLineItemNumber' => $orderLineItemNumber,
            'month' => $month,
            'year' => $year,
            'tenant' => [
                'id' => $tenantId,
            ],
            'productGroup' => $productGroup,
            'productCode' => $productCode,
            'sku' => $sku,
            'productDescription' => ucfirst($productGroup).' Protection - '.$year,
            'productName' => match ($productGroup) {
                'endpoint' => 'Sophos Endpoint Protection Advanced',
                'firewall' => 'Sophos XG Firewall Advanced',
                'email' => 'Sophos Email Security Gateway',
                default => 'Sophos Protection Product',
            },
            'billableQuantity' => $billableQuantity,
            'orderedQuantity' => $orderedQuantity,
            'actualQuantity' => $actualQuantity,
            'unitPrice' => fake()->randomFloat(2, 10, 500),
            'totalPrice' => $billableQuantity * fake()->randomFloat(2, 10, 500),
            'currency' => fake()->randomElement(['EUR', 'USD', 'GBP', 'CAD', 'AUD']),
            'billingPeriod' => 'monthly',
            'billingType' => fake()->randomElement(['subscription', 'perpetual', 'maintenance']),
            'status' => fake()->randomElement(['active', 'inactive', 'pending', 'expired']),
            'createdAt' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'updatedAt' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'links' => [
                'self' => [
                    'href' => 'https://api.central.sophos.com/billables/'.$orderLineItemNumber,
                    'rel' => 'self',
                ],
            ],
        ];

        return [
            'month' => $month,
            'year' => $year,
            'tenantId' => $tenantId,
            'orderLineItemNumber' => $orderLineItemNumber,
            'productGroup' => $productGroup,
            'billableQuantity' => $billableQuantity,
            'orderedQuantity' => $orderedQuantity,
            'actualQuantity' => $actualQuantity,
            'productCode' => $productCode,
            'sku' => $sku,
            'productDescription' => $rawData['productDescription'],
            'rawData' => $rawData,
            'sent_to_halo' => fake()->randomElement(['unplanned', 'planned', 'sent', 'failed']),
        ];
    }
}
