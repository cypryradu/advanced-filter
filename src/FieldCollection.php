<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\Common\Collections\ArrayCollection;

class FieldCollection extends ArrayCollection
{
    public function add($field)
    {
        parent::set($field->getKey(), $field);
    }
}
