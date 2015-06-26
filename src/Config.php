<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\DBAL\Connection;

/**
 * Configures the Filter.
 *
 * There should be one config class for each Filter
 * This class is just and example and for the purpose of UnitTesting
 * The clients should create their own config class
 * which should implement the \CypryRadu\AdvancedFilter\FilterConfigInterface
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class Config implements FilterConfigInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    public function __construct(Connection $db = null)
    {
        $this->db = $db;
    }

    /**
     * Array of tables used in filtering and how they chain together.
     *
     * @return array
     */
    public function tables()
    {
        return array(
            'mv.marketing__volunteers' => array(),
            'l.log' => array('leftJoin', 'l.id = mv.log_id'),
            'ro.recruitment_offices' => array('leftJoin', 'ro.id = mv.recruitment_office_id'),
            'mvdr.marketing__volunteers_destinations_rel' => array('leftJoin', 'mvdr.vol_id = mv.id'),
            'mvpr.marketing__volunteers_projects_rel' => array(
                'leftJoin',
                'mvpr.vol_id = mv.id AND mvpr.destination_id = mvdr.destination_id AND mvpr.destination_order = mvdr.destination_order',
            ),
        );
    }

    /**
     * Array of fields and their other details.
     *
     * @return array
     */
    public function fields()
    {
        return array(
            'firstname' => array(
                'table_alias' => 'mv',
                'use_tables' => array(
                    'mv.marketing__volunteers',
                ),
            ),
            'volunteer_surname' => array(
                'table_alias' => 'mv',
                'db_field' => 'surname',
                'use_tables' => array(
                    'mv.marketing__volunteers',
                ),
            ),
            'recruitment_office' => array(
                'table_alias' => 'ro',
                'use_tables' => array(
                    'mv.marketing__volunteers',
                    'ro.recruitment_offices',
                ),
            ),
            'application_date' => array(
                'table_alias' => 'l',
                'db_field' => 'date_created',
                'type' => 'date',
                'operators' => array('>=', '<=', 'LIKE', 'NOT LIKE'),
                'use_tables' => array(
                    'mv.marketing__volunteers',
                    'l.log',
                ),
            ),
        );
    }

    /**
     * This is not mandatory to implement. Mainly used by client
     * in rendering the View. It contains the necessary callbacks
     * for each field in case we want to display let's say a
     * drop-down with options instead of a blank textbox.
     *
     * The implementation is entirely up to the client
     *
     * @return array
     */
    public function fieldsCallback()
    {
        return array(
            'recruitment_office' => function () {
                $stmt = $this->db->prepare(
                    'SELECT id, recruitment_office
                     FROM recruitment_offices
                     ORDER BY recruitment_office'
                );
                $stmt->execute();

                return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            },
        );
    }

    /**
     * This is not mandatory to implement. Mainly used by client
     * to limit his results per page.
     *
     * The implementation is entirely up to the client
     *
     * @return array
     */
    public function maxResults()
    {
        return 30;
    }
}
