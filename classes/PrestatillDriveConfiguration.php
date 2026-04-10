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

class PrestatillDriveConfiguration extends ObjectModel
{
    public $id_day;
    public $day;
    public $openning;
    public $hour_open_am;
    public $hour_close_am;
    public $hour_open_pm;
    public $hour_close_pm;
    public $nonstop;
    
    /* Since 1.2 */
    public $id_store = 1;
    public $id_prestatill_drive;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_drive',
        'primary' => 'id_prestatill_drive',
        'multilang' => false,
        'fields' => array(
            'day' =>                array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'openning' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'hour_open_am' =>                   array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_close_am' =>              array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_open_pm' =>               array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_close_pm' =>                  array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'nonstop' =>                array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'id_day' =>                array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_store' =>                array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct((int)$id, (int)$id_lang);
    }

    public static function getAllDays() {
        $request = 'SELECT * 
        	FROM '._DB_PREFIX_.'prestatill_drive pd
        	LEFT JOIN '._DB_PREFIX_.'store s ON (s.id_store = pd.id_store)';
			
		if(Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') != null)
		{
			$request .= ' LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')';
		}
			
        $request .= ' WHERE pd.id_shop = '.(int)Context::getContext()->shop->id.' AND pd.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.'
            AND s.active = 1';
			
		if(Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') != null)
		{
			$request .=' AND ss.id_shop = '.(int)Context::getContext()->shop->id;
		}
			
        $result = Db::getInstance()->executeS($request);
		
        $stores = array();

        foreach($result as $res)
        {
            $stores[$res['id_store']][$res['id_day']] = $res;
        }
		
        return $stores;
	}

    public static function str_to_noaccent($str)
    {
        $lastname = $str;
        $lastname = preg_replace('#Ç#', 'C', $lastname);
        $lastname = preg_replace('#ç#', 'c', $lastname);
        $lastname = preg_replace('#è|é|ê|ë#', 'e', $lastname);
        $lastname = preg_replace('#È|É|Ê|Ë#', 'E', $lastname);
        $lastname = preg_replace('#à|á|â|ã|ä|å#', 'a', $lastname);
        $lastname = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $lastname);
        $lastname = preg_replace('#ì|í|î|ï#', 'i', $lastname);
        $lastname = preg_replace('#Ì|Í|Î|Ï#', 'I', $lastname);
        $lastname = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $lastname);
        $lastname = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $lastname);
        $lastname = preg_replace('#ù|ú|û|ü#', 'u', $lastname);
        $lastname = preg_replace('#Ù|Ú|Û|Ü#', 'U', $lastname);
        $lastname = preg_replace('#ý|ÿ#', 'y', $lastname);
        $lastname = preg_replace('#Ý#', 'Y', $lastname);
         
        return ($lastname);
    }

    public function updateStoresOpening()
    {
    	$pConfig = new PrestatillDriveConfiguration();
        $stores = $pConfig->getAllStores();
        
        $stores_in_bdd = self::getAllDays();

        $pDrive = new PrestatillDrive();
        $days = $pDrive->getWeekDays();
        
        $db = Db::getInstance();
        
        foreach($stores as $store)
        {
            if(!array_key_exists($store['id_store'],$stores_in_bdd))
            {
                foreach ($days as $day => $key) {
                
                    $query = 'INSERT INTO '._DB_PREFIX_.'prestatill_drive
                                     (
                                            id_day,
                                            day,
                                            openning,
                                            hour_open_am,
                                            hour_close_am,
                                            hour_open_pm,
                                            hour_close_pm,
                                            nonstop,
                                            id_store,
                                            id_shop,
                                            id_shop_group
                                        )
                                        VALUES ('.pSQL($day).', "'.pSQL($key).'", "1","8:30:00", "12:00:00", "13:30:00", "18:30:00", "0", "'.(int)$store['id_store'].'", "'.(int)Context::getContext()->shop->id.'", "'.(int)Context::getContext()->shop->id_shop_group.'")
                                        ON DUPLICATE KEY UPDATE
                                            id_store = "'.(int)$store['id_store'].'"';
                                            
                    $db->execute($query);
                }
            }
            
            // 1.2.6 : on créé les adresses associées au magasin pour les afficher sur les BL et FACTURES
            $id_address = null;
            if(Configuration::get('PRESTATILL_ADDR_STORE_'.$store['id_store']))
                $id_address = Configuration::get('PRESTATILL_ADDR_STORE_'.$store['id_store']);
            
            
            if($id_address != null) 
            {
                $address = new Address((int)$id_address);
            }
            else {
                $address = new Address();
            }
            
            $address->alias = 'Drive'; // don't modify this alias
            $lastname = self::str_to_noaccent($store['name']);
			if(version_compare(_PS_VERSION_, '1.7.3', '>'))
			{
				$address->lastname = preg_replace('/[^A-Z a-z]/', '', pSQL($lastname)); // skip problem with numeric characters
			}
			else
			{
				$address->lastname = preg_replace('/[^A-Z a-z]/', '', pSQL(Tools::substr($lastname,0,32))); // skip problem with numeric characters
			}
            
            $address->firstname = 'Drive';  // in warehouse name
            $address->address1 = pSQL($store['address1']);
            $address->address2 = pSQL($store['address2']);
			$address->dni = '0000';
            $address->postcode = pSQL($store['postcode']);
            $address->phone = pSQL($store['phone']);//params['datas']['phone'];
            $address->phone_mobile = pSQL('0000000000');//params['datas']['phone'];
            $address->id_country = pSQL($store['id_country']);
            $address->id_state = pSQL($store['id_state']);
            $address->city = pSQL($store['city']);
            if($address->save()) 
            {
                // On enregistre l'id du store dans la table configuration
                Configuration::updateValue('PRESTATILL_ADDR_STORE_'.$store['id_store'],$address->id);
            }
        }
        
        return true;
    }

    public static function getNbrStatePaid($params)
    {
        $request = 'SELECT COUNT(*) as nbr
                    FROM '._DB_PREFIX_.'order_history oh
                    LEFT JOIN '._DB_PREFIX_.'order_state os ON(os.id_order_state = oh.id_order_state)
                    WHERE oh.id_order = '.pSQL($params['object']->id).' AND os.paid = 1';
        $result = Db::getInstance()->getRow($request);
        return $result;
    }

    public static function getOrderHistory($params, $id_state)
    {
        $request = 'SELECT *
                    FROM '._DB_PREFIX_.'order_history
                    WHERE id_order = '.pSQL($params['object']->id).' AND id_order_state = '.(int)$id_state;
        $result = Db::getInstance()->executeS($request);
        return $result;
    }

    public function getAllStores($all_stores = false)
    {
        if(Configuration::get('PRESTATILL_SEARCH_STORE') == 1 && $all_stores == true)
        {
            // On initialise les variables
            $distance = 100;
            $distance_unit = 'km';
            $address = null;
            $lat = null;
            $long = null;
            $error = null;
            
            // On récupère l'adresse de livraison du customer
            $id_address = (int)Context::getContext()->cart->id_address_delivery;
            $address_delivery = new Address($id_address);
            
            if(Validate::isLoadedObject($address_delivery))
            {
                // On créé l'adresse pour l'api
                //$address = $address_delivery->address1.', '.$address_delivery->postcode.' '.$address_delivery->city;
                $address = $address_delivery->postcode.' '.self::str_to_noaccent($address_delivery->city);
                
                // On prépare la requette
                // $geocoder = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&key='.Configuration::get('PS_API_KEY').'&sensor=false';
                
                if(Configuration::get('PS_API_KEY') != '') {
                	
					$geocoder = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&key='.Configuration::get('PS_API_KEY').'&sensor=false';
					// On exécute
					$query = sprintf($geocoder, urlencode(utf8_encode($address)));
					$result = json_decode(Tools::file_get_contents($query));
					
					// On récupère la lat et long si on a un résultat
					if(!empty($result->results))
					{
						$json = $result->results[0];	
						$lat = $json->geometry->location->lat;
						$long = $json->geometry->location->lng;
					}
					else 
					{
						$error = $result->error_message;	
						return $error;
					}
				}
				else 
				{
					
					$addr = urlencode(utf8_encode($address));
					
					$return = $this->http_get_contents('https://nominatim.openstreetmap.org/search/?addressdetails=1&q='.$addr.'&format=json&addressdetails=1&limit=1');
					
					$json = json_decode($return);
					
					if(!empty($json))
					{
						$lat = $json[0]->lat;
						$long = $json[0]->lon;
					}
					else 
					{
						// On tente juste avec CP + Ville
						$address = $address_delivery->postcode.' '.self::str_to_noaccent($address_delivery->city);
						$addr = urlencode(utf8_encode($address));
						
						$return = $this->http_get_contents('https://nominatim.openstreetmap.org/search/?addressdetails=1&q='.$addr.'&format=json&addressdetails=1&limit=1');
					
						$json = json_decode($return);
						
						if(!empty($json))
						{
							$lat = $json[0]->lat;
							$long = $json[0]->lon;
						}
					}
					
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
                    WHERE s.active = 1 AND cl.id_lang = '.pSQL(Context::getContext()->language->id).'
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
                    WHERE s.active = 1 AND cl.id_lang = '.pSQL(Context::getContext()->language->id).'
                    HAVING distance < '.pSQL($distance).'
                    ORDER BY distance ASC';
            }
        }
        else
        {
            if(version_compare(_PS_VERSION_, '1.7.3', '>'))
            {
                $request = 'SELECT s.id_country, s.id_state, s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                        FROM '._DB_PREFIX_.'store s
                        LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)';
				
				if(Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') != null)
				{
					$request .= ' LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')';					
				}
						
                $request .= ' WHERE s.active = 1
                        	  AND sl.id_lang = '.(int)Context::getContext()->language->id;
						
				if(Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') != null)
				{		
					$request .= ' AND ss.id_shop = '.(int)Context::getContext()->shop->id;
				}
				
				$request .= ' ORDER BY sl.name ASC';
            }
            else 
            {
                $request = 'SELECT s.id_country, s.id_state, s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                        FROM '._DB_PREFIX_.'store s
                        LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                        WHERE s.active = 1';
				
				if(Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') != null)
				{		
					$request .= ' AND ss.id_shop = '.(int)Context::getContext()->shop->id;
				}
				
				$request .= ' ORDER BY s.name ASC';
            }
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

	public function http_get_contents($url, Array $opts = array())
  	{
	    $ch = curl_init();
	    if(!isset($opts[CURLOPT_TIMEOUT])) {
	    	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    if(is_array($opts) && $opts) {
	    	foreach($opts as $key => $val) {
	    		curl_setopt($ch, $key, $val);
	    	}
	    }
	    if(!isset($opts[CURLOPT_USERAGENT])) {
	    	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['SERVER_NAME']);
	    }
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    if(FALSE === ($retval = curl_exec($ch))) {
	    	error_log(curl_error($ch));
	    }
	    return $retval;
  	}

    public static function getStore($id_store)
    {
        if(version_compare(_PS_VERSION_, '1.7.3', '>'))
        {
            $request = 'SELECT s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
					LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1 
                    AND sl.id_lang = '.(int)Context::getContext()->language->id.'
                    AND s.id_store = '.(int)$id_store;
        }
        else 
        {
            $request = 'SELECT s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1 
                    AND s.id_store = '.(int)$id_store;
        }
        
        $result = Db::getInstance()->getRow($request);
        return $result;
    }

    public static function assignStore($id_store)
    {
        $context = Context::getContext();
        $context->cookie->__set('id_store', (int)$id_store);
		$context->cookie->__set('msg', '');
		$context->cookie->__set('id_creneau', '');
        $context->cookie->write();

        $store = self::getStore((int)$id_store);
        if (!empty($store)) {
            return $store;
        }

        return false;
    }
	
	// 2.0.0 : Public date_diff function
    public static function s_datediff( $str_interval, $dt_menor, $dt_maior, $relative=false ){

        if( is_string( $dt_menor)) $dt_menor = date_create( $dt_menor);
        if( is_string( $dt_maior)) $dt_maior = date_create( $dt_maior);

        $diff = date_diff( $dt_menor, $dt_maior, ! $relative);
      
       switch( $str_interval){
            case "y":
               $total = $diff->y + $diff->m / 12 + $diff->d / 365.25; break;
            case "m":
               $total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
               break;
            case "d":
               $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
               break;
            case "h":
               $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
               break;
            case "i":
               $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
               break;
            case "s":
               $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
               break;
            }
        if( $diff->invert)
               return -1 * $total;
		else    return $total;
	}

	// 2.1.0 : Get Additinonal Carence
	public static function getAdditionnalCarence($id_product, $id_product_attribute = 0)
	{
		$request = 'SELECT *
					FROM '._DB_PREFIX_.'prestatill_drive_carence_supp_by_product
                    WHERE id_product = '.(int)$id_product.'
					AND id_product_attribute = '.(int)$id_product_attribute;
					
        $result = Db::getInstance()->getRow($request);
		
        return $result;
	}
	
	// 2.1.0 : Set Additinonal Carence
	public static function setAdditionnalCarence($id_product, $id_product_attribute = 0, $carence_supp)
	{
		$query = 'INSERT INTO '._DB_PREFIX_.'prestatill_drive_carence_supp_by_product
                         (
                                id_product,
                                id_product_attribute,
                                carence_supp
                            )
                            VALUES ('.(int)($id_product).', '.(int)($id_product_attribute).', '.(int)$carence_supp.')
							ON DUPLICATE KEY UPDATE
                                id_product = '.(int)$id_product.',
								carence_supp = '.(int)$carence_supp;
                                
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}

	// 2.2.0 : Get Weedays Availabilities 
	// Si aucun résultat = aucune indisponibilité, on enregistre uniquement les jours d'indisponibilité
	public static function getAdditionnalAvailability($id_product, $id_product_attribute = 0)
	{
		$request = 'SELECT *
					FROM '._DB_PREFIX_.'prestatill_drive_availability_by_product
                    WHERE id_product = '.(int)$id_product.'
					AND id_product_attribute = '.(int)$id_product_attribute;
					
        $result = Db::getInstance()->executeS($request);
		
		$days = array();
		if(!empty($result))
		{
			foreach($result as $res)
			{
				$days[$res['id_day']] = $res;
			}
		}
		
        return $days;
	}
	
	// 2.2.0 : Get Weedays Availabilities 
	// Si aucun résultat = aucune indisponibilité, on enregistre uniquement les jours d'indisponibilité
	public static function setAdditionnalAvailability($id_product, $id_product_attribute = 0, $id_day)
	{
		$query = 'INSERT INTO '._DB_PREFIX_.'prestatill_drive_availability_by_product
                         (
                                id_product,
                                id_product_attribute,
                                id_day
                            )
                            VALUES ('.(int)($id_product).', '.(int)($id_product_attribute).', '.(int)$id_day.')
							ON DUPLICATE KEY UPDATE
                                id_product = '.(int)$id_product.',
                                id_product_attribute = '.(int)$id_product_attribute.',
								id_day = '.(int)$id_day;
                                
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}
	
	// 2.2.0 : Get Weedays Availabilities 
	// On supprime l'indisponibilité si elle existe déjà
	public static function unsetAdditionnalAvailability($id_product, $id_product_attribute = 0, $id_day)
	{
		$query = 'DELETE FROM '._DB_PREFIX_.'prestatill_drive_availability_by_product
                         WHERE id_product='.(int)($id_product).' AND id_product_attribute='.(int)($id_product_attribute).' AND id_day='.(int)$id_day;
						 
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}

}