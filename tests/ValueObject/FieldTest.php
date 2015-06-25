<?php
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;

class FieldTest extends PHPUnit_Framework_TestCase
{    
    public function testGetFieldNameNoDbField()
    {
        $field = new FieldVO('recruitment_office', array(
            'table_alias' => 'ro',
        ));
        
        $this->assertEquals($field->getFieldName(), 'ro.`recruitment_office`');
    }
    
}
