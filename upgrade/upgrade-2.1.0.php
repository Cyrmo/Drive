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

function upgrade_module_2_1_0($module) {

	$sql_requests = [];

	// SINCE 2.1.0					
	$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_carence_supp_by_product
                    (
                        id_product INT(10) NOT NULL,
                        id_product_attribute INT(10) NOT NULL DEFAULT 0,
                        carence_supp INT(10) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id_product`)
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
					
	$result = true;
	foreach ($sql_requests as $request) {
        if (!empty($request)) {
            $result &= Db::getInstance()->execute(trim($request));
        }
    }

	return 
		$module->registerHook('displayAdminProductsExtra')
        && $module->registerHook('actionProductSave')
        && $module->registerHook('displayProductAdditionalInfo')
		//&& $module->registerHook('displayShoppingCartFooter')
		&& $result;
}
