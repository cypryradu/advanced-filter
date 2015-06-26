<?php

namespace CypryRadu\AdvancedFilter;

/**
 * Defines methods required for a valid Config class.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
interface FilterConfigInterface
{
    public function tables();
    public function fields();
}
