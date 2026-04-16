<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

namespace DigitalFoodSystem\DfsClickCollect\Model;

use ObjectModel;

class DriveVacation extends ObjectModel
{
    public $id_vacation;
    public $id_store;
    public $id_shop;
    public $vacation_start;
    public $vacation_end;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'dfs_clickcollect_vacation',
        'primary' => 'id_vacation',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'id_store' =>       ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_shop' =>        ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'vacation_start' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'vacation_end' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
        ],
    ];
}
