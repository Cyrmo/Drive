<?php
/**
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*/

$sql_requests = array();

//$result = Db::getInstance()->executeS($request);

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive
                    (
                    	id_prestatill_drive INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
                        id_day INT(10) UNSIGNED NOT NULL,
                        id_store INT(10) NOT NULL DEFAULT 1,
                        day varchar(255) NOT NULL,
                        openning tinyint UNSIGNED ,
                        hour_open_am time NULL,
                        hour_close_am time NULL,
                        hour_open_pm time NULL,
                        hour_close_pm time NULL,
                        nonstop tinyint NOT NULL,
                        id_shop INT(10) UNSIGNED NULL DEFAULT 1,
	                	id_shop_group INT(10) UNSIGNED NULL DEFAULT 1,
                        PRIMARY KEY (id_prestatill_drive,id_store,id_day)                       
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
					
$sql_requests[] = 'ALTER TABLE '._DB_PREFIX_.'prestatill_drive AUTO_INCREMENT = 1';					

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_vacation
                    (
                        id_vacation int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        vacation_start date NULL,
                        vacation_end date NULL
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_creneau
                    (
                        id_creneau int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        id_store INT(10) NOT NULL DEFAULT 0,
                        id_week int(10) UNSIGNED NOT NULL,
                        id_day int(10) UNSIGNED NOT NULL,
                        id_cart int(10) UNSIGNED NOT NULL,
                        id_order int(10) UNSIGNED NOT NULL,
                        cause varchar(255) NOT NULL,
                        hour time NULL,
                        day varchar(10) NOT NULL,
                        pin_code varchar(10) NULL DEFAULT NULL,
                        manual_slot INT(1) NULL DEFAULT 0,
                        reminded INT(10) UNSIGNED NULL DEFAULT 0,
                        store_informed INT(10) UNSIGNED NULL DEFAULT 0,
                        date_add DATETIME NULL,
	                	date_upd DATETIME NULL
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
					
// SINCE 2.0.0					
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_store_carrier
                (
                    id_prestatill_drive_store_carrier int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                    id_store INT(10) NOT NULL DEFAULT 1,
                    id_carrier INT(10) NOT NULL,
                    id_shop INT(10) UNSIGNED NULL DEFAULT 1,
                	id_shop_group INT(10) UNSIGNED NULL DEFAULT 1,
                	pin_code_active INT(10) UNSIGNED NULL DEFAULT 0,
                	pin_code_prefix VARCHAR(50) NULL DEFAULT NULL
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

// SINCE 2.1.0					
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_carence_supp_by_product
                (
                    id_product INT(10) NOT NULL,
                    id_product_attribute INT(10) NOT NULL DEFAULT 0,
                    carence_supp INT(10) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id_product`)
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
				
// SINCE 2.2.0					
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_availability_by_product
                (
                    id_product INT(10) NOT NULL,
                    id_product_attribute INT(10) NOT NULL DEFAULT 0,
                    id_day INT(10) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id_product`, `id_product_attribute`, `id_day`)
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

// SINCE 2.2.0 (Only for PS 1.7.X)
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_category_association
                (
                    id_prestatill_drive_category_association int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                    id_category int(10) UNSIGNED NOT NULL,
                    delay_supp int(10) UNSIGNED NOT NULL DEFAULT 0
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';


$tables = array(
	        'prestatill_drive_vacation' =>
	            array(
	                'id_store' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	            ),
	        'prestatill_drive_store_carrier' =>
	            array(
	                'pin_code_active' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                'pin_code_prefix' => 'VARCHAR(50) UNSIGNED NULL',
	            ),
	        'prestatill_drive_creneau' =>
	            array(
	                'reminded' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                'store_informed' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                'pin_code' => 'VARCHAR(10) NOT NULL',
					'manual_slot' => 'INT(1) UNSIGNED NULL DEFAULT 0',
	                'date_add' => 'DATETIME NULL',
	                'date_upd' => 'DATETIME NULL',
	            ),
	        'prestatill_drive' =>
	            array(
	                'id_shop' => 'INT(10) UNSIGNED NULL DEFAULT 1',
	                'id_shop_group' => 'INT(10) UNSIGNED NULL DEFAULT 1',
	                'id_store' => 'INT(10) UNSIGNED NOT NULL DEFAULT 1',
	            ),
        );

foreach ($tables as $table => $fields) {
    foreach ($fields as $field => $type) {
        $sql_requests[] = 'SET @s = (SELECT IF( (SELECT COUNT(column_name)
                    FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . _DB_PREFIX_ .pSQL($table). '"
                    AND table_schema = "'._DB_NAME_.'" AND column_name = "'.pSQL($field).'"
                ) > 0, "SELECT 1", "ALTER TABLE ' . _DB_PREFIX_ .pSQL($table). ' ADD '.pSQL($field).' '.pSQL($type).'"
            ));
        PREPARE stmt FROM @s;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;';
    }
}
