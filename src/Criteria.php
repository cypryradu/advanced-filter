<?php
namespace CypryRadu\AdvancedFilter;

class Criteria
{
    private $criteria = array();
    
    public function add(Criterion $criterion)
    {
        $this->criteria[] = $criterion;
    }
    
    public function getAll()
    {
        return $this->criteria;
    }
}