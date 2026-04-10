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

function upgrade_module_2_2_0($module) {

	$sql_requests = [];

	// SINCE 2.2.0					
	$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_availability_by_product
	                (
	                    id_product INT(10) NOT NULL,
	                    id_product_attribute INT(10) NOT NULL DEFAULT 0,
	                    id_day INT(10) NOT NULL DEFAULT 0,
	                    PRIMARY KEY (`id_product`, `id_product_attribute`, `id_day`)
	                )
	                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

	$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_category_association
					(
						id_prestatill_drive_category_association int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
						id_category int(10) UNSIGNED NOT NULL,
						delay_supp int(10) UNSIGNED NOT NULL DEFAULT 0
					)
					ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

	$tables = array(
	        'prestatill_drive_creneau' =>
	            array(
	                'pin_code' => 'VARCHAR(10) NOT NULL',
					'manual_slot' => 'INT(1) UNSIGNED NULL DEFAULT 0',
	            ),
	        'prestatill_drive_store_carrier' =>
	            array(
	                'pin_code_active' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                'pin_code_prefix' => 'INT(10) UNSIGNED NULL',
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
	
	// On met à jour la version dans la configuration
	Configuration::updateValue('PRESTATILL_DRIVE_VERSION', '2.2.0');

	$result = true;
	foreach ($sql_requests as $request) {
        if (!empty($request)) {
            $result &= Db::getInstance()->execute(trim($request));
        }
    }

	return $result;
}
