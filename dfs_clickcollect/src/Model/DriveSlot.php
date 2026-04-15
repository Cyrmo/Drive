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

class DriveSlot extends ObjectModel
{
    public $id_creneau;
    public $id_store;
    public $id_shop;
    public $id_week;
    public $id_day;
    public $id_cart;
    public $id_order;
    public $cause;
    public $hour;
    public $day;
    public $pin_code;
    public $manual_slot = 0;
    public $reminded = 0;
    public $store_informed = 0;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'dfs_clickcollect_creneau',
        'primary' => 'id_creneau',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'id_store' =>       ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop' =>        ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_week' =>        ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_day' =>         ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_cart' =>        ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_order' =>       ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'cause' =>          ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'hour' =>           ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'day' =>            ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 10],
            'pin_code' =>       ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 10],
            'manual_slot' =>    ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'reminded' =>       ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'store_informed' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' =>       ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' =>       ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
