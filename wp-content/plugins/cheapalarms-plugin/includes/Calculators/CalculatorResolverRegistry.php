<?php

namespace CheapAlarms\Plugin\Calculators;

use CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver;
use CheapAlarms\Plugin\Calculators\Resolvers\CalculatorResolverInterface;
use WP_Error;

class CalculatorResolverRegistry
{
    /** @var array<string, CalculatorResolverInterface> */
    private array $resolvers = [];

    public function __construct(AjaxResolver $ajaxResolver)
    {
        $this->register($ajaxResolver);
    }

    public function register(CalculatorResolverInterface $resolver): void
    {
        $this->resolvers[$resolver->getBrand()] = $resolver;
    }

    /**
     * @return CalculatorResolverInterface|WP_Error
     */
    public function get(string $brand)
    {
        $brand = strtolower(trim($brand));
        if ($brand === '' || !isset($this->resolvers[$brand])) {
            return new WP_Error('unknown_brand', 'Unknown calculator brand: ' . $brand, ['status' => 400]);
        }

        return $this->resolvers[$brand];
    }
}
