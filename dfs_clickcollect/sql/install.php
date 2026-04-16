<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

$sql = [];

// Table des configurations des jours / horaires par magasin
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_store` (
    `id_dfs_clickcollect_store` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_store` INT(10) UNSIGNED NOT NULL,
    `id_day` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `day` VARCHAR(255) NOT NULL,
    `openning` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `hour_open_am` TIME NULL,
    `hour_close_am` TIME NULL,
    `hour_open_pm` TIME NULL,
    `hour_close_pm` TIME NULL,
    `nonstop` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `delay` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_dfs_clickcollect_store`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Table des vacances / fermetures exceptionnelles
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_vacation` (
    `id_vacation` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_store` INT(10) UNSIGNED NULL DEFAULT 0,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `vacation_start` DATE NULL,
    `vacation_end` DATE NULL,
    PRIMARY KEY  (`id_vacation`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Table des créneaux réservés
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` (
    `id_creneau` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_store` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `id_week` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_day` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_cart` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `cause` VARCHAR(255) NULL,
    `hour` TIME NULL,
    `day` VARCHAR(10) NULL,
    `pin_code` VARCHAR(10) NULL,
    `manual_slot` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `reminded` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `store_informed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_creneau`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Association Transporteur / Store
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_store_carrier` (
    `id_dfs_clickcollect_store_carrier` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_store` INT(10) UNSIGNED NOT NULL,
    `id_carrier` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `pin_code_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `pin_code_prefix` VARCHAR(50) NULL,
    PRIMARY KEY  (`id_dfs_clickcollect_store_carrier`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Délais de carence additionnels par produit
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_carence_supp_by_product` (
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `carence_supp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_product`, `id_product_attribute`, `id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Indisponibilités (jours) par produit
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_availability_by_product` (
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_day` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id_product`, `id_product_attribute`, `id_day`, `id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// Délais de carence de catégorie
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_category_association` (
    `id_dfs_clickcollect_category_association` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_category` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `delay_supp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_dfs_clickcollect_category_association`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dfs_clickcollect_cart` (
    `id_cart` int(11) NOT NULL,
    `id_store` int(11) NOT NULL,
    `day` varchar(20) NOT NULL,
    `hour` varchar(20) NOT NULL,
    `id_shop` int(11) NOT NULL,
    PRIMARY KEY (`id_cart`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
