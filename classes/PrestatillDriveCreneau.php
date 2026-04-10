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

class PrestatillDriveCreneau extends ObjectModel
{
    public $id_creneau;
    public $id_store;
    public $id_week;
    public $id_day;
    public $id_order;
    public $id_cart;
    public $cause;
    public $hour;
    public $day;
	// 1.2.8
	public $reminded = 0;
	// 1.3.0
	public $store_informed = 0;
	// 2.0.0
	public $date_add;
	public $date_upd;
	// 2.2.0
	public $pin_code;
	public $manual_slot = 0;

    public static $definition = array(
        'table' => 'prestatill_drive_creneau',
        'primary' => 'id_creneau',
        'multilang' => false,
        'fields' => array(
            'id_week' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_day' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_store' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'cause' =>    array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 128),
            'hour' =>     array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'day' =>      array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'reminded' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'store_informed' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'pin_code' =>  array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'manual_slot' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),

    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public static function getAllCreneauByIdCart($id_cart)
    {
        $request = 'SELECT id_creneau, id_store FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_cart = '.(int)$id_cart;

        $result = Db::getInstance()->getRow($request);
        return $result;
    }
	
	public static function getCreneauByIdCreneau($id_creneau)
    {
        $request = 'SELECT id_creneau, id_store FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_creneau = '.(int)$id_creneau;

        $result = Db::getInstance()->getRow($request);
        return $result;
    }

    /*
     *  Récupérer un id_creneau à partir de la commande (id_order)
     */
    public static function getCreneauByIdOrder($id_order = 0)
    {
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_order = '.(int)$id_order;

		// SINCE 2.2.0
		if($id_order == 0)
		{
			$request = 'SELECT MAX(id_creneau) as id_creneau FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_order = 0 AND manual_slot = 1';
		}
		else
		{
			$request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_order = '.(int)$id_order;
		}

        $result = Db::getInstance()->getRow($request);
        if (!empty($result)) {
            return new PrestatillDriveCreneau((int)$result['id_creneau']);
        }

        return false;
    }

    public static function updateCartCreneau($id_creneau, $id_cart)
    {
        $cart = new Cart((int)$id_cart);
        if (Validate::isLoadedObject((int)$id_cart)) {
            $cart->id_creneau = (int)$id_creneau;
            $cart->update();
        }
    }

    public static function updateOrdersCreneau($id_creneau, $id_order, $id_cart)
    {
        $creneau = new PrestatillDriveCreneau((int)$id_creneau);
        if (Validate::isLoadedObject($creneau)) {
            if ($creneau->id_cart == (int)$id_cart) {
                $creneau->id_order = (int)$id_order;
				if((int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 0)
				{
					$creneau->day = null;
					$creneau->hour = null;
				}
                $creneau->update();
            }
			if((int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)
			{
	            $order = new Order((int)$id_order);
	            if (Validate::isLoadedObject($order)) {
	                $order->delivery_date = pSQL($creneau->day).' '.pSQL($creneau->hour);
	                $order->update();
	            }
			}
        }
    }

    public static function updateStoreByIdcreneau($id_store, $id_creneau, $id_order = 0, $reserved = false, $manual_slot = 0)
    {
		// SET LOCALES
		$iso_exp = Context::getContext()->language->iso_code;
		$iso_exp_BG = Context::getContext()->language->iso_code;
		
		switch ($iso_exp) {
			case 'ca':
				$iso_exp_BG = 'es';
				break;
			
			case 'en':
				$iso_exp_BG = 'us';
				break;
		}
			
        $iso_lang = $iso_exp.'_'.Tools::strtoupper($iso_exp_BG);

        setlocale(LC_TIME, $iso_lang.'.utf8');
		// END SET LOCALES 

        $date = date("Y-m-d");
		
        if ((int)$id_creneau == 0) {
            $creneau = new PrestatillDriveCreneau();
            $creneau->day = pSQL($date);
			$creneau->id_cart = (int)Context::getContext()->cart->id;
			
			if($id_order > 0)
				$creneau->id_order = (int)$id_order;
			
            $creneau->save();
        } else {
            $creneau = new PrestatillDriveCreneau((int)$id_creneau);
        }
		
		// 2.2.0 : On vérifie si le code pin est activé
		$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$id_store);
		$pin_code_active = null;
		$pin_code = null;

		if(!empty($pdsc))
		{
			$pin_code_active = (int)$pdsc['pin_code_active'];
		}
		
		// Si le code pin est actif, on génère un code aléatoire en récupérant l'éventuel préfixe
		if($pin_code_active == 1)
		{
			$pin_code = $pdsc['pin_code_prefix'].Tools::passwdGen(4,'NUMERIC');
		}

		if (Validate::isLoadedObject($creneau)) {
			
			$creneau->id_store = (int)$id_store;
			
			// 2.0.0 : on met à jour l'id_cart
			$creneau->id_cart = (int)Context::getContext()->cart->id;
			
			if($reserved == false)
			{
				$creneau->id_day = 0;
				$creneau->day = null;
				$creneau->hour = null;
			}
			
			// 2.2.0
			if($pin_code != null)
			{
				$creneau->pin_code = $pin_code;
			}
			else
			{
				$creneau->pin_code = null;
			}
			
			// FIX BDD STRICT
			if($creneau->day == '0000-00-00')
				$creneau->day = null;
			
			if($id_order > 0)
				$creneau->id_order = (int)$id_order;

			// 2.2.0
			$creneau->manual_slot = (int)$manual_slot;
			
            $creneau->update();
            return $creneau;
        }
    }

    public static function updateOrdersState($id_order, $id_state)
    {
        $order = new Order((int)$id_order);
        if (Validate::isLoadedObject($order)) {
            $order->id_state = (int)$id_state;
            $order->update();
        }
    }
	
    public static function countOrder($delivery_date, $id_store, $max_products = false)
    {
    	// Nombre de produits || de commandes
    	//@TODO : dynamiser les catégories : WHERE p.id_category_default IN (...)
    	//@TODO : faire en sorte de vérifier si un produit est dans une catégorie mais pas forcément en catégorie par défaut
    	$categories = Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES');
    	    	
    	if($max_products == true)
		{
			$request = 'SELECT SUM(cp.quantity) as nb FROM '._DB_PREFIX_.'prestatill_drive_creneau pdc
				LEFT JOIN '._DB_PREFIX_.'cart_product cp ON (pdc.id_cart = cp.id_cart)
				LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product)
				WHERE 1=1';
				
				if(!empty($categories))
				{
					$request .= ' AND p.id_category_default IN ('.$categories.')';
				}
				
				$request .= ' AND day = LEFT("'.pSQL($delivery_date).'",10)
						 AND hour = RIGHT("'.pSQL($delivery_date).'",8)
						 AND id_order > 0
						 AND id_store = '.(int)$id_store;
		}
		else 
		{
			$request = 'SELECT count(id_order) as nb FROM '._DB_PREFIX_.'prestatill_drive_creneau 
				WHERE day = LEFT("'.pSQL($delivery_date).'",10)
				AND hour = RIGHT("'.pSQL($delivery_date).'",8)
				AND id_order > 0
				AND id_store = '.(int)$id_store.'
				AND id_store > 0 AND id_day > 0';
		}

        $result = Db::getInstance()->executeS($request);
        return $result;
    }
	
	public static function getReservedCreneau($id_store, $id_cart = false, $max_products = false)
    {
		$delivery_day = date('Y-m-d');
		
		if($max_products == true)
		{
			$request = 'SELECT pdc.id_week, pdc.id_day, pdc.day, pdc.hour, od.product_quantity 
							FROM '._DB_PREFIX_.'prestatill_drive_creneau pdc
							LEFT JOIN '._DB_PREFIX_.'order_detail od ON (pdc.id_order = od.id_order)
							LEFT JOIN '._DB_PREFIX_.'orders o ON (pdc.id_order = o.id_order)
							LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
							WHERE day >= "'.pSQL($delivery_day).'" 
							AND pdc.id_order > 0
							AND o.id_shop = '.(int)Context::getContext()->shop->id.' 
							AND o.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.' 
							AND pdc.id_store ='.(int)$id_store;
		}
		else 
		{
			$request = 'SELECT pdc.id_week, pdc.id_day, pdc.day, pdc.hour 
							FROM '._DB_PREFIX_.'prestatill_drive_creneau pdc
							LEFT JOIN '._DB_PREFIX_.'orders o ON (pdc.id_order = o.id_order)
							WHERE pdc.id_store ='.(int)$id_store.'
							AND o.id_shop = '.(int)Context::getContext()->shop->id.' 
						    AND o.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;
							
			// 2.0.0 : add the reserved slot 
			if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'))
			{
				$request .= ' AND (pdc.id_day > 0 AND pdc.hour != "00:00:00" AND pdc.day != "0000-00-00" ) AND pdc.day >= "'.pSQL($delivery_day).'" ';
			}
			
			// + les créneaux déjà réservés
			$request .= ' OR (pdc.id_order > 0 AND pdc.day >= "'.pSQL($delivery_day).'" )';
			
			// 2.2.0 : Ajout des créneaux manuels
			$request .= ' OR (pdc.id_order = 0 AND pdc.day >= "'.pSQL($delivery_day).'" AND pdc.manual_slot = 1 )';
		}
		
        $result = Db::getInstance()->executeS($request);
		
		// On ajout les produits du panier si on est en mode nb_produits
		$nb_products = 0;
		
		$categories = Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES');
		
		if($max_products == true && $id_cart != null)
		{
			$nb = 'SELECT sum(cp.quantity) as nb 
					FROM '._DB_PREFIX_.'cart_product cp
					LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product)
					WHERE 1=1';
					
			if(!empty($categories))
			{
				$nb .= ' AND p.id_category_default IN ('.$categories.')';
			}
					
			$nb .= ' AND cp.id_cart = '.(int)$id_cart;
				
			$nb_prod = Db::getInstance()->getRow($nb);	
			
			if(!empty($nb_prod))
			{
				$nb_products = $nb_prod['nb'];
			}
		}

        $table = array();
		if(!empty($result))
		{
			foreach($result as $res)
			{
				if(!isset($table[$res['day']." ".$res['hour']]))
					$table[$res['day']." ".$res['hour']] = 0;
				
				// nb_produits || nb_comandes
				if($max_products == true)
				{
					// we reduce 1 if nb_products max
					$table[$res['day']." ".$res['hour']] += (int)$res['product_quantity']+$nb_products-1;
				}
				else 
				{
					$table[$res['day']." ".$res['hour']] += 1;
				}
			}
			return $table;
		}
        return $result;
    }

    public static function countCreneau($date, $date_fin)
    {
        $request = 'SELECT COUNT(hour), id_week, id_day, day, hour FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE day >= "'.pSQL($date).'" AND day <= "'.pSQL($date_fin).'" GROUP BY id_week, id_day, day, hour HAVING COUNT(hour) >= 3;';
        $result = Db::getInstance()->executeS($request);
        return $result;
    }
	
	/*
     *  Récupérer les créneaux pour lequels envoyer un rappel
     */
    public static function getOrdersForReminder($time = 120)
    {
    	$now = strftime("%H:%M"); 
    	$max_time = strftime("%H:%M", strtotime(date("Y-m-d H:i:s").' +'.$time.' Minutes')); 
		
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE day = "'.date('Y-m-d').'" AND hour >= "'.pSQL($now).'" and hour <= "'.pSQL($max_time).'" AND reminded = 0';

        $result = Db::getInstance()->executeS($request);
        
		return $result;
    }
	
	/*
	 * Récupérer l'id_store en BDD d'un creneau avant mise à jour 
	 */
	public static function getIdStoreByIdCreneau($id_creneau)
	{
		$request = 'SELECT id_store FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE id_creneau = '.(int)$id_creneau;

        $result = Db::getInstance()->getRow($request);
        
		return $result;
	}
	
	/*
	 * Suppression des créneaux expirés
	 */
	 public static function deleteAllUnusedCreneau()
	 {
	 	$duration = Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION');
    	$date_now = strftime("%Y-%m-%d %H:%M:%S", strtotime(date("Y-m-d H:i:s").' -'.$duration.' Minutes')); 
		
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_drive_creneau WHERE date_upd < "'.$date_now.'"
						AND id_order = 0
						AND id_day > 0 
						AND hour != "00:00:00" 
						AND day != "0000-00-00"';
        $result = Db::getInstance()->executeS($request);
		
		if(!empty($result))
		{
			foreach($result as $res)
			{
				$creneau = new PrestatillDriveCreneau((int)$res['id_creneau']);
				if(Validate::isLoadedObject($creneau))
				{
					$creneau->id_day = 0;
					$creneau->day = null;
					$creneau->hour = '00:00:00';
					$creneau->update();
				}
			}
		}
	 }
}
