<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\Common\Collections\ArrayCollection;
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;

/**
 * Holds and manipulates a collection of FieldVO objects.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class FieldCollection extends ArrayCollection
{
    /**
     * Adds a new FieldVO to the collection.
     *
     * @param \CypryRadu\AdvancedFilter\ValueObject\FieldVO $field
     */
    public function add($field)
    {
        parent::set($field->getKey(), $field);
    }
}
