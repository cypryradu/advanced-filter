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
            'c.clients' => array(),
            'l.log' => array('leftJoin', 'l.id = c.log_id'),
            'e.emails' => array('leftJoin', 'e.client_id = c.id'),
            'o.offices' => array('leftJoin', 'o.id = c.office_id'),
            'ord.orders' => array('leftJoin', 'ord.client_id = c.id'),
            'p.products' => array(
                'leftJoin',
                'p.id = ord.product_id'
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
                'table_alias' => 'c',
                'use_tables' => array(
                    'c.clients',
                ),
            ),
            'client_surname' => array(
                'table_alias' => 'c',
                'db_field' => 'surname',
                'use_tables' => array(
                    'c.clients',
                ),
            ),
            'office' => array(
                'table_alias' => 'o',
                'use_tables' => array(
                    'c.clients',
                    'o.offices',
                ),
            ),
            'emails' => array(
                'column_expr' => 'GROUP_CONCAT(e.email)',
                'use_tables' => array(
                    'c.clients',
                    'e.emails',
                ),
            ),
            'application_date' => array(
                'table_alias' => 'l',
                'db_field' => 'date_created',
                'type' => 'date',
                'operators' => array('>=', '<=', 'LIKE', 'NOT LIKE'),
                'use_tables' => array(
                    'c.clients',
                    'l.log',
                ),
            ),
            'invalid_table_field' => array(
                'table_alias' => 'inv',
                'use_tables' => array(
                    'inv.no_table'
                ),
            ),
        );
    }

    public function columns()
    {
        return array(
            'firstname' => 'First Name',
            'client_surname' => 'Last Name',
            'office' => 'Office'
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
