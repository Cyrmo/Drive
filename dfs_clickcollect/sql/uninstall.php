<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_store`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_vacation`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_cart`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_store_carrier`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_carence_supp_by_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_availability_by_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_category_association`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
