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

class PrestatillDriveStoreCarrier extends ObjectModel
{
    public $id_prestatill_drive_store_carrier;
    
    public $id_store;
    
    public $id_carrier;
    
	public $id_shop;
	
	public $id_shop_group;
	
	// 2.2.0
	public $pin_code_active = 0;
	
	public $pin_code_prefix;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_drive_store_carrier',
        'primary' => 'id_prestatill_drive_store_carrier',
        'multilang' => false,
        'fields' => array(
            'id_store' =>         array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_carrier' =>       array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_shop' =>          array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop_group' =>    array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            // 2.2.0
            'pin_code_active' =>    array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'pin_code_prefix' =>    array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }
	
	// 2.0.0 : Get stores associated to a carrier
	public function getStoresAssociatedToACarrier($id_carrier = 0)
    {
        if(version_compare(_PS_VERSION_, '1.7.3', '>'))
        {
            $request = 'SELECT pdc.id_carrier, s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
					LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
					LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdc ON (s.id_store = pdc.id_store) AND (pdc.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1 
                    AND sl.id_lang = '.(int)Context::getContext()->language->id;
        }
        else 
        {
            $request = 'SELECT pdc.id_carrier, s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
					LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdc ON (s.id_store = pdc.id_store) AND (pdc.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1';
        }
		
		if($id_carrier > 0)
		{
			$request .= ' AND pdc.id_carrier = '.(int)$id_carrier;
		}
		else 
		{
			//$request .= ' AND pdc.id_carrier > 0';
		}
		
		$result = Db::getInstance()->executeS($request);
		
		$eligible_stores = [];
		if(!empty($result))
		{
			foreach($result as $res)
			{
				$eligible_stores[$res['id_store']] = $res;
			}
		}
        
		return $eligible_stores;
	}

	// 2.0.0 : Get stores near a defined lat and long (from localisation)
	public function getByLatLong($lat, $long, $glue = false)
	{
		$distance = 100;
        $distance_unit = 'km';
		
		// 2.0.1 : On tente une géolocalisation à base du CP ou Ville entré
		if($glue != false)
		{
			$address = $glue.', '.Context::getContext()->language->language_code;
			$addr = urlencode(utf8_encode($address));
			
			$pdc = new PrestatillDriveConfiguration();
			$return = $pdc->http_get_contents('https://nominatim.openstreetmap.org/search/?addressdetails=1&q='.$addr.'&format=json&addressdetails=1&limit=1');
			
			$json = json_decode($return);
			
			if(!empty($json))
			{
				$lat = $json[0]->lat;
				$long = $json[0]->lon;
			}
		}
		
		if(Configuration::get('PRESTATILL_SEARCH_RADIUS'))
                $distance = (int)Configuration::get('PRESTATILL_SEARCH_RADIUS');
		
            $multiplicator = ($distance_unit == 'km' ? 6371 : 3959);
            
            if(version_compare(_PS_VERSION_, '1.7.3', '>'))
            {
                $request = 'SELECT s.*, sl.*, cl.name country, st.iso_code state,
                    ('.(int)$multiplicator.'
                        * acos(
                            cos(radians('.(float)$lat.'))
                            * cos(radians(latitude))
                            * cos(radians(longitude) - radians('.(float)$long.'))
                            + sin(radians('.(float)$lat.'))
                            * sin(radians(latitude))
                        )
                    ) distance,
                    cl.id_country id_country
                    FROM '._DB_PREFIX_.'store s
                    '.Shop::addSqlAssociation('store', 's').'
                    LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store) AND sl.id_lang = '.pSQL(Context::getContext()->language->id).'
                    LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
                    LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
					LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdsc ON (pdsc.id_store = s.id_store)
                    WHERE s.active = 1 AND cl.id_lang = '.pSQL(Context::getContext()->language->id).'
                    AND pdsc.id_store IS NOT NULL
                    HAVING distance < '.pSQL($distance).'
                    ORDER BY distance ASC';
            }
            else
            {
                $request = 'SELECT s.*, cl.name country, st.iso_code state,
                    ('.(int)$multiplicator.'
                        * acos(
                            cos(radians('.(float)$lat.'))
                            * cos(radians(latitude))
                            * cos(radians(longitude) - radians('.(float)$long.'))
                            + sin(radians('.(float)$lat.'))
                            * sin(radians(latitude))
                        )
                    ) distance,
                    cl.id_country id_country
                    FROM '._DB_PREFIX_.'store s
                    '.Shop::addSqlAssociation('store', 's').'
                    LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
                    LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
					LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdsc ON (pdsc.id_store = s.id_store)
                    AND pdsc.id_store IS NOT NULL
                    WHERE s.active = 1 AND cl.id_lang = '.pSQL(Context::getContext()->language->id).'
                    HAVING distance < '.pSQL($distance).'
                    ORDER BY distance ASC';
            }

		$result = Db::getInstance()->executeS($request);
		
    	return $result;
	}

	// 2.0.0 : Get stores near a defined lat and long (from localisation)
	public function getByCPOrCityName($address)
	{
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
        {
            $request = 'SELECT s.id_country, s.id_state, s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
                    LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')						
                    LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdsc ON (pdsc.id_store = s.id_store)						
                    WHERE s.active = 1
                    AND sl.id_lang = '.(int)Context::getContext()->language->id.'
                    AND (s.city LIKE "'.$address.'%" OR s.postcode LIKE "'.$address.'%")
					AND pdsc.id_store IS NOT NULL
                    ORDER BY sl.name ASC';
        }
        else 
        {
            $request = 'SELECT s.id_country, s.id_state, s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                    LEFT JOIN '._DB_PREFIX_.'prestatill_drive_store_carrier pdsc ON (pdsc.id_store = s.id_store)						
                    WHERE s.active = 1
                    AND (s.city LIKE "'.$address.'%" OR s.postcode LIKE "'.$address.'%")
					AND pdsc.id_store IS NOT NULL
                    ORDER BY s.name ASC';
        }

		$result = Db::getInstance()->executeS($request);
    	return $result;
	}

	public static function getByIdStore($id_store, $id_shop = null, $id_shop_group = null)
	{
		if($id_shop == null)
		{
			$id_shop = (int)Context::getContext()->shop->id;
		}
		
		if($id_shop_group == null)
		{
			$id_shop_group = (int)Context::getContext()->shop->id_shop_group;
		}
		
		$request = 'SELECT *
						FROM '._DB_PREFIX_.'prestatill_drive_store_carrier
                    WHERE id_store = '.(int)$id_store.'
						AND (id_shop = '.(int)Context::getContext()->shop->id.') 
						AND (id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.')';

		$result = Db::getInstance()->getRow($request);

		return $result;
	}
    
}
