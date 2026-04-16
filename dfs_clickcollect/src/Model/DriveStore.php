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

class DriveStore extends ObjectModel
{
    public $id_dfs_clickcollect_store;
    public $id_store;
    public $id_shop;
    public $id_day;
    public $day;
    public $openning;
    public $hour_open_am;
    public $hour_close_am;
    public $hour_open_pm;
    public $hour_close_pm;
    public $nonstop;
    public $delay;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'dfs_clickcollect_store',
        'primary' => 'id_dfs_clickcollect_store',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'id_store' =>      ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop' =>       ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_day' =>        ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'day' =>           ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true],
            'openning' =>      ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'hour_open_am' =>  ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'hour_close_am' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'hour_open_pm' =>  ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'hour_close_pm' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'nonstop' =>       ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'delay' =>         ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
        ],
    ];
}
