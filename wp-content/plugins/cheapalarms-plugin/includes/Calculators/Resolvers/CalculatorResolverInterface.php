<?php

namespace CheapAlarms\Plugin\Calculators\Resolvers;

use WP_Error;

interface CalculatorResolverInterface
{
    public function getBrand(): string;

    /**
     * @param array<string, mixed> $selections
     * @return true|WP_Error
     */
    public function validate(array $selections);

    /**
     * @param array<string, mixed> $selections
     * @return array<int, array<string, mixed>>
     */
    public function toLineItems(array $selections, string $locationId): array;

    /**
     * @param array<string, mixed> $selections
     * @return array<int, array{name:string,qty:int}>
     */
    public function toSummary(array $selections, string $locationId): array;

    /**
     * @param array<string, mixed> $selections
     * @param array<int, array<string, mixed>> $lineItems
     */
    public function installEstimate(array $selections, array $lineItems): float;
}
