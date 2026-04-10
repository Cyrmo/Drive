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

function upgrade_module_2_0_0($module) {

	$sql_requests = [];
	
	// SINCE 2.0.0					
	$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_drive_store_carrier
                    (
                        id_prestatill_drive_store_carrier int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        id_store INT(10) NOT NULL DEFAULT 1,
                        id_carrier INT(10) NOT NULL,
                        id_shop INT(10) UNSIGNED NULL DEFAULT 1,
	                	id_shop_group INT(10) UNSIGNED NULL DEFAULT 1
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
	
    
	
	$tables = array(
	        'prestatill_drive_creneau' =>
	            array(
	                'date_add' => 'DATETIME NULL',
	                'date_upd' => 'DATETIME NULL',
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

	$result = true;
	foreach ($sql_requests as $request) {
        if (!empty($request)) {
            $result &= Db::getInstance()->execute(trim($request));
        }
    }
	
	if($result == true)
	{
		// Mise à jour des informations par rapport au réglage de base
		if(Configuration::get('PRESTATILL_DRIVE_CARRIER'))
		{
			$id_carrier = (int)Configuration::get('PRESTATILL_DRIVE_CARRIER');
			$carrier = new Carrier((int)$id_carrier);
			if(Validate::isLoadedObject($carrier))
			{
				$pConfig = new PrestatillDriveConfiguration();
        		$stores = $pConfig->getAllStores();
				
				if(!empty($stores))
				{
					foreach($stores as $store)
					{
						$pdc = new PrestatillDriveStoreCarrier();
						$pdc->id_store = (int)$store['id_store'];
						$pdc->id_carrier = (int)$id_carrier;
						$pdc->id_shop = (int)Context::getContext()->shop->id;
						$pdc->id_shop_group = (int)Context::getContext()->shop->id_shop_group;
						$pdc->save();
					}
				}
			}
		}
	}
    			
	return $result;					
}
