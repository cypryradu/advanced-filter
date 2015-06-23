<?php
namespace CypryRadu\AdvancedFilter;

class Config {
    private $db;
    
    public function __construct($db = null)
    {
        $this->db = $db;
    }    
        
    public function tables()
    {
        return array(
            'mv.marketing__volunteers' => array(),
            'l.log' => array('leftJoin', 'l.id = mv.log_id'),
            'ro.recruitment_offices' => array('leftJoin', 'ro.id = mv.recruitment_office_id'),
            'mvdr.marketing__volunteers_destinations_rel' => array('leftJoin', 'mvdr.vol_id = mv.id'),
            'mvpr.marketing__volunteers_projects_rel' => array(
                'leftJoin', 
                'mvpr.vol_id = mv.id AND mvpr.destination_id = mvdr.destination_id AND mvpr.destination_order = mvdr.destination_order'
            )
        );        
    }
    
    public function fields()
    {
        return array(
            'firstname' => array(
                'table_alias' => 'mv',
                'use_tables' => array(
                    'mv.marketing__volunteers'
                )
            ),
            'volunteer_surname' => array(
                'table_alias' => 'mv',
                'db_field' => 'surname',
                'use_tables' => array(
                    'mv.marketing__volunteers'
                )
            ),
            'recruitment_office' => array(
                'table_alias' => 'ro',
                'use_tables' => array(
                    'mv.marketing__volunteers',
                    'ro.recruitment_offices'
                )
            ),
            'application_date' => array(
                'table_alias' => 'l',
                'db_field' => 'date_created',
                'type' => 'date',
                'operators' => array('>=', '<=', 'LIKE', 'NOT LIKE'),
                'use_tables' => array(
                    'mv.marketing__volunteers',
                    'l.log'
                )
            )
        );
    }
    
    public function fieldsCallback()
    {
        return array(
            'recruitment_office' => function() {
                $stmt = $this->db->prepare(
                    'SELECT id, recruitment_office
                     FROM recruitment_offices 
                     ORDER BY recruitment_office'
                );
                $stmt->execute();
                return $stmt->fetchAll();
            }
        );
        
    }
    
    public function maxResults()
    {
        return 30;    
    }
}
