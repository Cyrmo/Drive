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



require_once(dirname(__FILE__).'/classes/PrestatillDriveConfiguration.php');

require_once(dirname(__FILE__).'/classes/PrestatillDriveCreneau.php');

require_once(dirname(__FILE__).'/classes/PrestatillDriveVacation.php');

require_once(dirname(__FILE__).'/classes/PrestatillDriveStoreCarrier.php');

require_once(dirname(__FILE__).'/classes/PrestatillDriveCategoryAssociation.php');



//use Symfony\Component\Form\Extension\Core\Type\TextType;



class PrestatillDrive extends Module

{

    public function __construct()

    {

        $this->name = 'prestatilldrive';

        $this->tab = 'administration';

        $this->version = '2.2.0';

        $this->author = 'Prestatill';

        parent::__construct();



        $this->need_instance = 0;

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->bootstrap = true;

        $this->displayName = $this->l('Drive, Click and Collect for Prestashop');

        $this->description = $this->l('Manage and optimize your delivery schedule according to your stores, opening days & hours, on a simple and optimized interface built for smartphones');

        $this->confirmUninstall = $this->l('Confirm Uninstall ?');

        $this->module_key = '737dfada60b06e46e97227664ec53aa6';

    }



    public function install()

    {

        if (!parent::install()

            || !$this->installSql()

            || !$this->registerHook('displayBeforeCarrier')

            || !$this->registerHook('header')

            || !$this->registerHook('displayPaymentTop')

            || !$this->registerHook('displayPaymentReturn')

            || !$this->registerHook('displayPayment')

            || !$this->registerHook('actionValidateOrder')

            || !$this->registerHook('actionGetExtraMailTemplateVars')

            || !$this->registerHook('actionOrderStatusPostUpdate')

            || !$this->registerHook('displayOrderDetail')

			|| !$this->registerHook('displayAdminOrderContentShip')

            || !$this->_installTab('AdminParentOrders', 'AdminPrestatillDrive', $this->l('Drive'))

			// UPDATE 1.2.6 : Drive Picking List Controller

            || !$this->_installTab('AdminParentOrders', 'AdminPrestatillDrivePickingList', $this->l('Drive Picking List'))

			|| !$this->registerHook('displayBackOfficeHeader')

			|| !$this->registerHook('actionCarrierUpdate')

			|| !$this->registerHook('displayInvoiceLegalFreeText')

			// UPDATE 1.2.8 : Webservices ressources

			|| !$this->registerHook('addWebserviceResources')

			// UPDATE 1.3.0 : Join Invoice in store's email

			|| !$this->registerHook('actionOrderHistoryAddAfter')

			// UPDATE 1.4.0 : display available carriers for pick up on product page

			|| !$this->registerHook ('displayProductAdditionalInfo')

			|| !$this->registerHook('displayCartExtraProductActions')

			// UPDATE 2.0.0

			//|| !$this->registerHook('displayNav1')

			|| !$this->registerHook('displayFooter')

			|| !$this->registerHook('displayTop') 

			|| !$this->registerHook('displayRightColumnProduct')

			// NEW HOOK 1.7.7

			|| !$this->registerHook('displayAdminOrderTabContent')  

			// UPDATE 2.1.0

			|| !$this->registerHook('displayAdminProductsExtra')

	        || !$this->registerHook('actionProductSave')

	        || !$this->registerHook('displayProductAdditionalInfo')

			|| !$this->registerHook('displayAfterProofOfDeliveryCustomerDetails')

			// UPDATE 2.3.0 : On override le nouveau controller catégories

			|| !$this->registerHook('actionCategoryFormBuilderModifier')

			|| !$this->registerHook('actionAfterUpdateCategoryFormHandler')

			// UPDATE 2.1.0 BIS

			//|| !$this->registerHook('actionBeforeCartUpdateQty')

			) {

            return false;

        }

		

		Configuration::updateValue('PRESTATILL_DRIVE_VERSION',$this->version);

		if (Configuration::get('PRESTATILL_DRIVE_CARENCE') == '')

		{

			Configuration::updateValue('PRESTATILL_DRIVE_CARENCE', '240');

            Configuration::updateValue('PRESTATILL_DRIVE_DUREE', '60');

            Configuration::updateValue('PRESTATILL_DRIVE_NB_DISPO', '4');

            Configuration::updateValue('PRESTATILL_DRIVE_OPEN', '08:00:00');

            Configuration::updateValue('PRESTATILL_DRIVE_CLOSE', '18:30:00');

            Configuration::updateValue('PRESTATILL_DRIVE_NB_DAY', '14');

            Configuration::updateValue('PRESTATILL_DRIVE_CARRIER', '1');

            Configuration::updateValue('PRESTATILL_DRIVE_STATE_PREPARE', Configuration::get('PS_OS_PAYMENT'));

			//1.3.1

			Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO',0);

			Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES', implode(',', array()));

		}

        return true;

    }



	// Hook de la page catégorie en BO susr 1.7.X

	public function hookActionCategoryFormBuilderModifier($params)

	{

		/** @var FormBuilder $formBuilder */ 

		$formBuilder = $params['form_builder']; 

		

		// On récupère la valeur du délais de carence supp. pour cette catégorie s'il existe déjà

		$id_category = $params['id'];

		$category_exist = false;

		$delay_supp = 0;

		if($id_category)

		{

			// On vérifie si une entrée existe déjà :

			$category_exist = PrestatillDriveCategoryAssociation::getCategoryDelaySupp($id_category);

			if(!empty($category_exist))

			{

				$delay_supp = (int)$category_exist['delay_supp'];

			}

		}

		

		$formBuilder->add('delay_supp', TextType::class, [ 

			'label' => $this->l('Additional Delay for this category'), 

			'required' => false, 

			'data' => (int)$delay_supp, 

			'help' =>$this->l('Enter the additional preparation delay for all products on this category (in minutes)') 

			]);  



	}

	

	public function hookActionAfterUpdateCategoryFormHandler($params)

	{

		// Mise à jour du délais de carence supp.

		$delay_supp = $params['form_data']['delay_supp'];

		$id_category = $params['id'];

		

		// On récupère la valeur du délais de carence supp. du formulaire

		$category_exist = false;



		if($id_category)

		{

			// On vérifie si une entrée existe déjà :

			$category_exist = PrestatillDriveCategoryAssociation::getCategoryDelaySupp($id_category);

			

			if(!empty($category_exist))

			{

				$pdca = new PrestatillDriveCategoryAssociation((int)$category_exist['id_prestatill_drive_category_association']);

				if(Validate::isLoadedObject($pdca))

				{

					$pdca->delay_supp = (int)$delay_supp;

					$pdca->update();

				}

			}

			else 

			{

				$pdca = new PrestatillDriveCategoryAssociation();

				$pdca->id_category = (int)$id_category;

				$pdca->delay_supp = (int)$delay_supp;

				$pdca->save();

			}

		}

		

	}



	// 2.1.0 : Additionnal preparation time

	public function hookDisplayAdminProductsExtra($params)

    {

    	

    	if(isset($params['id_product']) || Tools::getIsset('id_product'))

		{

			if(isset($params['id_product']))

			{

				$id_product = (int)$params['id_product'];

			}

			else 

			{

				$id_product = (int)Tools::getValue('id_product');

			}



			// 2.2.0 : On récupère les jours de la semaine

			$weekdays = $this->getWeekDays();

			

			if($id_product > 0)

			{

				$actual_carence_supp = PrestatillDriveConfiguration::getAdditionnalCarence($id_product);

				

				$product_availability = PrestatillDriveConfiguration::getAdditionnalAvailability($id_product);



				$carence_supp = 0;

				if(!empty($actual_carence_supp))

					$carence_supp = (int)$actual_carence_supp['carence_supp'];

				

				$this->context->smarty->assign(

		            array(

			            'carence_supp' => $carence_supp, 

			            'ps16' => version_compare(_PS_VERSION_, '1.6.1.24', '<='),

			            'weekdays' => $weekdays,

			            'id_product' => $params['id_product'],

			            'product_availability' => $product_availability,

					)

		        );

				

			 	return $this->display(__FILE__, 'views/templates/hook/prestatilldrive_admin_product_extra.tpl');

			}

		}

	}



	public function hookActionProductSave($params)

	{

		if(Tools::getValue('carence_supp'))

		{

			$id_product = $params['id_product'];

			$carence_supp = 0;

			if($id_product > 0)

			{

				$carence_supp = (int)Tools::getValue('carence_supp');

				// On vérifie si le produit a des déclinaisons

				// On récupère la carence actuelle s'il y en a une

				

				$update_carence_supp = PrestatillDriveConfiguration::setAdditionnalCarence($id_product, 0, $carence_supp);

			}

		}

	}

	

	public function hookDisplayAfterPrestatillReceiptCustomerDetails($params)

	{

		if(isset($params['id_order']))

	 	{

	 		

			$order = new Order((int)$params['id_order']);

			if(Validate::isLoadedObject($order))

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

				

	            //$id_cart = (int)$order->id_cart;

				$store = null;

				$msg_creneau = null;

				$day_creneau = null;

				$hour_creneau = null;

				$end_creneau = null;

				

				$js = array(

					$this->_path.'views/js/jquery-dateFormat.js',

		            $this->_path.'views/js/admin-order-hook.js'

		        );

		        $css = array(

		           $this->_path.'views/css/admin-order-hook.css',

		            $this->_path.'views/css/config.css'

		        );

		

		        $this->context->controller->addJS($js);

		        $this->context->controller->addCSS($css);

							

	            $result = PrestatillDriveCreneau::getCreneauByIdOrder((int)$order->id);

				

				$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

					

				// On regarde si le shop est dans un sous dossier ou non

				$shop_url = new ShopUrl(Context::getContext()->shop->id);

				if(Validate::isLoadedObject($shop_url)) 

				{

					if($shop_url != '/')

					{

						$base_dir .= $shop_url->physical_uri;

					}

				}

				

				if($result) {

					$creneau = new PrestatillDriveCreneau((int)$result->id_creneau);

					

		            if (Validate::isLoadedObject($creneau)) {

						if((int)$creneau->id_store > 0 && $creneau->id_order > 0) {

		            	

							if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

							{

								$day_creneau = $creneau->day;

								$day = strftime('%d %B %Y', strtotime($day_creneau));

								$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

								$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

								$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

								

						        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

							}

							else 

							{

								$msg_creneau = null;

							}

							

							

							// On récupère les informations du store

							$store = new Store((int)$creneau->id_store);

							$store_name = '';

							$store_addrress = '';

							$store_city = '';

							$store_postcode = '';

							if(Validate::isLoadedObject($store))

							{

								$store_name = $store->name;

								$store_addrress = $store->address1;

								$store_city = $store->city;

								$store_postcode = $store->postcode;

							}

							

							$pConfig = new PrestatillDriveConfiguration(true);

	        				$stores = $pConfig->getAllStores();

							

							$this->context->smarty->assign(

					            array(

						            'creneau' => $creneau, 

						            'msg_creneau' => $msg_creneau,

						            'stores' => $stores,

						            'store_name' => $store_name,

						            'day_creneau' => $day_creneau,

						            'hour_creneau' => $hour_creneau,

						            'store_address' => $store_addrress,

						            'store_city' => $store_city,

						            'id_store' => $creneau->id_store,

						            'store_postcode' => $store_postcode,

						            'id_lang' => version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0,

						            'base_dir' => $base_dir,

						            'id_creneau' => $creneau->id,

						            'end_creneau' => $end_creneau,

						            'id_order'=> (int)$order->id

								)

					        );

							

						 	return $this->display(__FILE__, 'views/templates/hook/prestatillreceipt_order_creneau.tpl');

						}

		            }

				}

			}

		}

	}



	public function hookDisplayRightColumnProduct($params)

	{

		if(version_compare(_PS_VERSION_, '1.6.1.24', '<='))

		{	

			return $this->hookDisplayProductAdditionalInfo($params);

		}

	}



	public function hookDisplayProductAdditionalInfo($params)

	{

		// Display product's availability for each carriers associated to a Pick-up store

		if(Configuration::get('PRESTATILL_DISPLAY_STORES_ON_PRODUCT') == 1)

		{

			$id_product = null;

			if(isset($params['product']))

			{

				$id_product = (int)$params['product']['id_product'];

			} 

			else if(Tools::getValue('id_product'))

			{

				$id_product = (int)Tools::getValue('id_product');

			}

			

			if($id_product > 0)	

			{

				$cart = $params['cart'];

				$carrier_list = [];

				$id_store = null;

				

				// 2.0.0 : On récupère l'id_store du cookie s'il existe

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

				{

					if(isset($this->context->cookie->id_store) && (int)$this->context->cookie->id_store > 0)

					{

						$id_store = (int)$this->context->cookie->id_store;

					}

				}

					

				$carrier_list = self::_getAvailableCarriersByProducts((int)$id_product);

				

				//d($carrier_list);

				

				// On récupère uniquement les transporteurs liés à un Drive

				$all_store_carriers = self::_checkCarriersByStores();

				

				$selected_store_carriers = [];

				if(!empty($all_store_carriers))

				{

					foreach($all_store_carriers as $id_carrier)

					{

						// On récupère les magasins liés au transporteur

						$pdc = new PrestatillDriveStoreCarrier();

						$stores = $pdc->getStoresAssociatedToACarrier((int)$id_carrier);

						//dump($stores);

						

						if(!empty($stores))

						{

							foreach($stores as $store)

							{

								// On limite au magasin sélectionné

								if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR') && $id_store != null)

								{

									if((int)$id_store == (int)$store['id_store'])

										$selected_store_carriers[$store['id_store']] = $store;

								}

								else

								{

									$selected_store_carriers[$store['id_store']] = $store;

								}

							}

						}

					}

				}

				

				if(!empty($carrier_list))

				{

					foreach($carrier_list as $key => $name)

					{

						if(!in_array($key, $all_store_carriers))

						{

							// On supprime les modes de transports non lié à une boutique

							unset($carrier_list[$key]);

						}

					}

				}

				

				// 2.0.0 : on récupère le nom du point de retrait sélectionné

				$store = [];

				if($id_store > 0)

				{

					$store = PrestatillDriveConfiguration::getStore((int)$id_store);

				}

				

				$this->context->smarty->assign(

		            array(

			            'carriers' => $carrier_list,

			            'stores_carriers' => $selected_store_carriers,

			            'store_limited' => Configuration::get('PRESTATILL_SEARCH_STORE'),

			            'store_selector_active' => Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'),

			            'id_store' => $id_store,

			            'selected_store' => $store,

			            'ps_17' => version_compare(_PS_VERSION_, '1.6.1.24', '>'),

					)

		        );

			

				return $this->display(__FILE__, 'views/templates/hook/product-carriers.tpl');

			}

		}

	}



	// SINCE 2.0.0

	public function hookDisplayFooter($params)

	{

		

		if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

		{

			// On récupère l'ensemble des magasins disponibles

			$pConfig = new PrestatillDriveConfiguration();

			$stores = $pConfig->getAllStores(true);

						

			$drive_enabled = [];

			

			// On récupère les magasins liés au transporteur

			$pdc = new PrestatillDriveStoreCarrier();

			$temp_stores = $pdc->getStoresAssociatedToACarrier();

			

			if(!empty($temp_stores))

			{

				foreach($temp_stores as $store)

				{

					$drive_enabled[$store['id_store']] = $store;	

				}

			}

			

			$id_store = null;

			$selected_store = false;

			$selected_slot = false;

			$slot_reservation_duration = 0;

			

			// On vérifie si le client a déjà sélectionné un sotre

			$context = Context::getContext();

	        if((int)$context->cookie->id_store > 0);

			{

				$id_store = (int)$context->cookie->id_store;

				$my_store = PrestatillDriveConfiguration::getStore($id_store);

				if(!empty($my_store))

				{

					$selected_store = $my_store;

				}

			}

			

			//@TODO: vérifier ici si on a déjà un store sélectionné dans le cookie

			if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION') && Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'))

			{

				if(isset($this->context->cookie->drive_slot_reservation) && $this->context->cookie->msg != '')

				{

					$selected_slot = $this->context->cookie->msg;

					

					// On décrémente la durée de la réservation

					$duration = (int)Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION');

					

					if($duration > 0)

					{

						// SET LOCALES

						$iso_exp = $this->context->language->iso_code;

						$iso_exp_BG = $this->context->language->iso_code;

						

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

						

						// On récupère la différence de timing en minutes

						$date_now = date_create(strftime("%Y-%m-%d %H:%M:%S"));

						$target = date_create($this->context->cookie->drive_slot_reservation);

						// On récupère le nombre de minutes depuis lesquelles le créneau est réservé

						$interval = PrestatillDriveConfiguration::s_datediff( 'i', $date_now, $target);

						$resting_time = $interval;

						

						//$resting_time = strftime("%Y-%m-%d %H:%M:%S", strtotime('- '.$duration.' minutes', strtotime( strftime("%Y-%m-%d %H:%M:%S"))));

						if($resting_time <= $duration && $resting_time > 0) 

						{

							$slot_reservation_duration = round($duration-$resting_time);

						}

						// On libère le créneau

						else 

						{

							// On supprime l'id_creneau s'il existe

							if($this->context->cookie->id_creneau > 0)

							{

								$creneau = new PrestatillDriveCreneau((int)$this->context->cookie->id_creneau);

								if(Validate::isLoadedObject($creneau))

								{

									if($creneau->id_order == 0)

									{

										$creneau->id_day = 0;

										$creneau->day = null;

										$creneau->hour = null;

										$creneau->update();

									}

									//$creneau->delete();

								}

							}

							$this->context->cookie->__set('drive_slot_reservation', '');

							$this->context->cookie->__set('id_creneau', 0);

							$this->context->cookie->__set('msg', '');

					        $this->context->cookie->write();

						}

					}

				}

				// On nettoie les créneaux réservés non libérés (si un client quitte le site)

				PrestatillDriveCreneau::deleteAllUnusedCreneau();

			}

			// Si on a désactivé la réservation de créneau

			else if(isset($this->context->cookie->drive_slot_reservation)) {

				$this->context->cookie->__set('drive_slot_reservation', '');

				$this->context->cookie->write();

			}



			if(isset($this->context->controller->php_self) && $this->context->controller->php_self != 'order' && $this->context->controller->php_self != 'order-opc')

			{

				$this->context->smarty->assign(

		            array(

			            'stores' => $drive_enabled,

			            'search_enabled' => (int)Configuration::get('PRESTATILL_SEARCH_STORE'),

			            'slot_enabled' => (int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'),

			            'slot_choice_enabled' => (int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'),

			            'selected_store' => $selected_store,

			            'selected_slot' => $selected_slot,

			            'slot_reservation_duration' => $slot_reservation_duration,

			            'total_duration' => (int)Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION'),

			            'active' => false,

			            'ps_17' => version_compare(_PS_VERSION_, '1.6.1.24', '>'),

					)

		        );

			

				return $this->display(__FILE__, 'views/templates/hook/front-display-nav1.tpl');

			}

		}

	}

	

	private function _checkCarriersByStores()

	{

		// On check les transporteurs dans la configuration

		$pConfig = new PrestatillDriveConfiguration();

		//@TODO: vérifier ici si on affiche tous les transporteurs ou non

		$stores = $pConfig->getAllStores();

		$carriers = [];

		

		if(is_array($stores))

		{

			foreach($stores as $store)

			{

				$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

				if(!empty($temp))

				{

					$carriers[$temp['id_carrier']] = $temp['id_carrier'];

				}

			}

		}

		

		return $carriers;

	}



	public function hookDisplayCartExtraProductActions($params)

	{

		if(isset($params['product']))

		{

			$cart = $params['cart'];

			$common_carrier = self::_getCommonCarriers($cart);

			$store_carriers = [];

			$carrier_list = [];

			$selected_store_carriers = [];

			$id_store = null;

				

			if(Configuration::get('PRESTATILL_DISPLAY_CARRIERS_ON_CART') || $common_carrier == false)

			{

				// 2.0.0 : On récupère l'id_store du cookie s'il existe

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

				{

					if(isset($this->context->cookie->id_store) && (int)$this->context->cookie->id_store > 0)

					{

						$id_store = (int)$this->context->cookie->id_store;

					}

				}

					

				$carrier_list = self::_getAvailableCarriersByProducts($params['product']['id_product']);

				

				// On récupère uniquement les transporteurs liés à un Drive

				$all_store_carriers = self::_checkCarriersByStores();

				

				$selected_store_carriers = [];

				if(!empty($all_store_carriers))

				{

					foreach($all_store_carriers as $id_carrier)

					{

						// On récupère les magasins liés au transporteur

						$pdc = new PrestatillDriveStoreCarrier();

						$stores = $pdc->getStoresAssociatedToACarrier((int)$id_carrier);

						//dump($stores);

						

						if(!empty($stores))

						{

							foreach($stores as $store)

							{

								// On limite au magasin sélectionné

								if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR') && $id_store != null)

								{

									if((int)$id_store == (int)$store['id_store'])

										$selected_store_carriers[$store['id_store']] = $store;

								}

								else

								{

									$selected_store_carriers[$store['id_store']] = $store;

								}

							}

						}

					}

				}

				

				if(!empty($carrier_list))

				{

					foreach($carrier_list as $key => $name)

					{

						if(!in_array($key, $all_store_carriers))

						{

							// On supprime les modes de transports non lié à une boutique

							unset($carrier_list[$key]);

						}

					}

				}

			}

			

			$this->context->smarty->assign(

	            array(

		            'carriers' => $carrier_list,

		            'stores_carriers' => $selected_store_carriers,

		            'store_limited' => Configuration::get('PRESTATILL_SEARCH_STORE'),

		            'store_selector_active' => Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'),

		            'id_store' => $id_store,

		            'common_carrier' => (int)$common_carrier,

            		'ps_version' => _PS_VERSION_, 

            		'display_common_carriers' => Configuration::get('PRESTATILL_DISPLAY_CARRIERS_ON_CART'),

				)

	        );

		

			return $this->display(__FILE__, 'views/templates/hook/shopping-cart-carriers.tpl');

		}

	}



	public function getFullDateToLocales($date)

    {

        $days = $this->getDays();

        

        foreach ($days as $from => $to) {

            $date = str_replace($from, $to, $date);

        }

        if(is_numeric($date))

        {

            return $date;        

        }

        else 

        {

            $days = $this->getDays(true);

            foreach ($days as $from => $to) {

                $date = str_replace($from, $to, $date);

            }

            

            return $date;

        }

    }



	public function hookDisplayShoppingCartFooter($params)

	{

		if(isset($params['cart']) && false)

		{

			$cart = $params['cart'];

			if(count($cart->getProducts()) > 0)

			{

				$carrier_list = [];

			

				//$common_carrier = self::_getCommonCarriers($cart);

				$common_carrier = true;

				$delai_carence = false;

				

				// 2.1.0 : Carence suppélémentaire par produit si réservation de créneau activée

					

				// On vérifie la carence par rapport à la date / heure actuelle

				$id_cart = (int)Context::getContext()->cart->id;

				$delivery_date = false;

				$date_carence = false;

				if($id_cart > 0)

				{

					$tab_creneau = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

		        	$creneau = new PrestatillDriveCreneau((int)$tab_creneau['id_creneau']);

					

					if(Validate::isLoadedObject($creneau))

					{

						$delivery_date = $creneau->day.' '.$creneau->hour;

					}

					

					$date = date('Y-m-d H:i:s');

	        		$date_carence = date('Y-m-d H:i:s', strtotime('+ '.$delai_carence.' minutes', strtotime($date)));

				}

				

				$this->context->smarty->assign(

		            array(

			            'carriers' => $carrier_list,

			            'common_carrier' => (int)$common_carrier,

			            'delai_carence' => $delai_carence,

			            'date_carence' => $delivery_date <= $date_carence

					)

		        );

	

				return $this->display(__FILE__, 'views/templates/hook/shopping-cart-carriers-footer.tpl');

			}

		}

	}



	private function _getAvailableCarriersByProducts($id_product)

	{

		$carrier_list = [];



		// On récupère la liste des transporteurs possibles pour les produits du panier

		$message = null;



		$carriers = Carrier::getAvailableCarrierList(new Product((int)$id_product), null);

		if(!empty($carriers))

		{

			foreach($carriers as $id_carrier)

			{

				$c = new Carrier((int)$id_carrier);

				//dump($c);

				if(Validate::isLoadedObject($c))

				{

					$carrier_list[$id_carrier] = $c->name;

				}

			}

		}

		

		return $carrier_list;

	}

	

	private function _getAvailableCarriers($cart)

	{

		$carrier_list = [];



		// On récupère la liste des transporteurs possibles pour les produits du panier

		$message = null;

		if(Validate::isLoadedObject($cart))

		{

			$products = $cart->getProducts();

			if(!empty($products))

			{

				foreach($products as $product)

				{

					$carriers = Carrier::getAvailableCarrierList(new Product((int)$product['id_product']), null);

					if(!empty($carriers) && count($carriers) < 2)

					{

						foreach($carriers as $id_carrier)

						{

							$c = new Carrier((int)$id_carrier);

							if(Validate::isLoadedObject($c))

							{

								$carrier_list[$id_carrier]['products'][] = $product['name'];

								$carrier_list[$id_carrier]['carrier_name'] = $c->name;

							}

						}

					}

				}

			}

		}

		

		return $carrier_list;

	}



	public function hookActionCarrierUpdate($params)

	{

		// On met à jour l'id du transporteur dans la configuration du module Drive

		// 1.4.0 pour tous les magasins

		

		$pConfig = new PrestatillDriveConfiguration();

        $stores = $pConfig->getAllStores();

		

		foreach($stores as $key => $store)

		{

			$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);



			if((int)$params['id_carrier'] == (int)$temp['id_carrier']) 

			{

				$pdc = new PrestatillDriveStoreCarrier((int)$temp['id_prestatill_drive_store_carrier']);

				if(Validate::isLoadedObject($pdc))

				{

					$pdc->id_carrier = (int)$params['carrier']->id;

					$pdc->update();

				}

			}

		}

	}

	

	public function hookDisplayInvoiceLegalFreeText($params) 

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

		

		$creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$params['order']->id);

		

		if(Validate::isLoadedObject($creneau)) {

				

			if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

			{	

				$day_creneau = $creneau->day;

				$day = strftime('%a %d %B %Y', strtotime($day_creneau));

				$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

				$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

				$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

				

		        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

				

				$store = new Store((int)$creneau->id_store);

				

				$this->context->smarty->assign(

		            array(

			            'creneau' => $creneau, 

			            'msg_creneau' => $msg_creneau,

			            'drive_store' => $store,

			            'id_lang' => (int)Context::getContext()->language->id,

					)

		        );

		

		        return $this->display(__FILE__, 'views/templates/hook/order-invoice.tpl');

			}

		}

	}



	public function hookDisplayBackOfficeHeader($hookParams)

	{

		$this->context->controller->addjQuery();

		$this->context->controller->addCSS(($this->_path).'views/css/adminprestatilldrive.css', 'all');

		$this->context->controller->addJS(($this->_path).'views/js/adminprestatilldriveproduct.js', 'all');



		if(version_compare(_PS_VERSION_, '1.7.6.8', '>') && $this->context->controller->controller_name == 'AdminOrders') {

		   

			$js = array(

				$this->_path.'views/js/jquery-dateFormat.js',

	            $this->_path.'views/js/admin-order-hook.js'

	        );

	        $css = array(

	           $this->_path.'views/css/admin-order-hook.css',

	           $this->_path.'views/css/config.css',

	           $this->_path.'views/css/config17.css'

	           

	        );

	

	        $this->context->controller->addJS($js);

	        $this->context->controller->addCSS($css);

		}

	}



    private function _installTab($parent, $class_name, $name)

    {

        $tab = new Tab();

        $tab->id_parent = pSQL(Tab::getIdFromClassName($parent));

        $tab->class_name = pSQL($class_name);

        $tab->module = pSQL($this->name);



        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {

            $tab->name[$lang['id_lang']] = pSQL($name);

        }

        return $tab->save();

    }



    private function _uninstallTab($class_name)

    {

        $id_tab = (int)Tab::getIdFromClassName($class_name);

        $tab = new Tab((int)$id_tab);

        return $tab->delete();

    }



    public function uninstall()

    {

        if (!parent::uninstall()

            || !$this->_uninstallTab('AdminPrestatillDrive')

			|| !$this->_uninstallTab('AdminPrestatillDrivePickingList')) {

            return false;

        }

            return true;

    }



	public function uninstallSql()

	{

		$sql_requests = array();

        include(dirname(__FILE__).'/sql/uninstall.php');

        $result = true;

        foreach ($sql_requests as $request) {

            if (!empty($request)) {

                $result &= Db::getInstance()->execute(trim($request));

            }

        }

        return $result;

	}



    public function installSql()

    {

        $sql_requests = array();

        include(dirname(__FILE__).'/sql/install.php');

        $result = true;

        foreach ($sql_requests as $request) {

            if (!empty($request)) {

                $result &= Db::getInstance()->execute(trim($request));

            }

        }

        return $result;

    }



    public function loadAsset()

    {

        // Load JS

        $js = array(

            $this->_path.'views/js/jquery-dateFormat.js',

            $this->_path.'views/js/config.js',

        );

        $css = array(

           $this->_path.'views/css/config.css',

        );



        $this->context->controller->addJS($js);

        $this->context->controller->addCSS($css);

    }



    private function _processConfiguration()

    {

        if (Tools::isSubmit('submitParameters')) {

        	

            Configuration::updateValue('PRESTATILL_DRIVE_CARENCE', Tools::getValue('PRESTATILL_DRIVE_CARENCE'));

            Configuration::updateValue('PRESTATILL_DRIVE_DUREE', Tools::getValue('PRESTATILL_DRIVE_DUREE'));

            Configuration::updateValue('PRESTATILL_DRIVE_NB_DISPO', Tools::getValue('PRESTATILL_DRIVE_NB_DISPO'));

            Configuration::updateValue('PRESTATILL_DRIVE_CARRIER', Tools::getValue('PRESTATILL_DRIVE_CARRIER'));

            Configuration::updateValue('PRESTATILL_DRIVE_OPEN', Tools::getValue('PRESTATILL_DRIVE_OPEN'));

            Configuration::updateValue('PRESTATILL_DRIVE_CLOSE', Tools::getValue('PRESTATILL_DRIVE_CLOSE'));

            Configuration::updateValue('PRESTATILL_DRIVE_NB_DAY', Tools::getValue('PRESTATILL_DRIVE_NB_DAY'));



            $id_lang = (int)$this->context->language->id;

            $states = OrderState::getOrderStates((int)$id_lang);

            $tmp_search=array();

            $tmp_state = array();

            foreach ($states as $state) {

                if (Tools::getIsset('PRESTATILL_DRIVE_STATE_PREPARE'.(int)$state['id_order_state'])) {

                    $tmp_search[] = (int)$state['id_order_state'];

                    $tmp_state[] = $state;

                }

            }

            Configuration::updateValue('PRESTATILL_DRIVE_STATE_PREPARE', implode(';', $tmp_search));

			Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE',Tools::getValue('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'));

			Configuration::updateValue('PRESTATILL_DRIVE_SEND_EMAIL',Tools::getValue('PRESTATILL_DRIVE_SEND_EMAIL'));

			Configuration::updateValue('PRESTATILL_DRIVE_DISPLAY_TABLE',Tools::getValue('PRESTATILL_DRIVE_DISPLAY_TABLE'));

			// 1.2.6

			Configuration::updateValue('PRESTATILL_DRIVE_MODIFY_PDF',Tools::getValue('PRESTATILL_DRIVE_MODIFY_PDF'));

			// 1.2.7

			Configuration::updateValue('PRESTATILL_SEND_MAIL_TO_STORE',Tools::getValue('PRESTATILL_SEND_MAIL_TO_STORE'));

			// 1.2.8

			Configuration::updateValue('PRESTATILL_DRIVE_SEND_REMINDER',Tools::getValue('PRESTATILL_DRIVE_SEND_REMINDER'));

			Configuration::updateValue('PRESTATILL_DRIVE_SEND_REMINDER_TIME',Tools::getValue('PRESTATILL_DRIVE_SEND_REMINDER_TIME'));

			// 1.3.0

			Configuration::updateValue('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL',Tools::getValue('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL'));

			// 1.3.1

			Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO', Tools::getValue('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO'));

			Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES', Tools::isSubmit('categories')?implode(',', Tools::getValue('categories')):'');

			// 2.0.1

			Configuration::updateValue('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION',Tools::getValue('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION'));

			// 2.0.2

			Configuration::updateValue('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS',Tools::getValue('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS'));

			

			

            return true;

        }

    }



    public function getConfigFieldsValues()

    {

        $id_lang = (int)$this->context->language->id;

        $states = OrderState::getOrderStates((int)$id_lang);

        $arrayConfig = array();



        foreach ($states as $state) {

            $arrayConfig['STATES_'.$state['id_order_state']] = (int)Configuration::get('PRESTATILL_DRIVE_STATE_PREPARE'.$state['id_order_state']);

        }

        $arrayState = explode(';', ''.Configuration::get('PRESTATILL_DRIVE_STATE_PREPARE'));

        return $arrayState;

    }



    private function _assignConfiguration()

    {

            $arrayState = array();

            $arrayState = explode(';', ''.Configuration::get('PRESTATILL_DRIVE_STATE_PREPARE'));



            $this->context->smarty->assign('nbdayview', Configuration::get('PRESTATILL_DRIVE_NB_DAY'));

            $this->context->smarty->assign('closedrive', Configuration::get('PRESTATILL_DRIVE_CLOSE'));

            $this->context->smarty->assign('opendrive', Configuration::get('PRESTATILL_DRIVE_OPEN'));

            $this->context->smarty->assign('nb_dispo', Configuration::get('PRESTATILL_DRIVE_NB_DISPO'));

            $this->context->smarty->assign('carence', Configuration::get('PRESTATILL_DRIVE_CARENCE'));

            $this->context->smarty->assign('duree', Configuration::get('PRESTATILL_DRIVE_DUREE'));

            $this->context->smarty->assign('arrayState', $arrayState);

			

			$id_lang = (int)$this->context->language->id;

	        $carriers = Carrier::getCarriers((int)$id_lang, false, false, false, null, ALL_CARRIERS );

	        $states = OrderState::getOrderStates((int)$id_lang);

	        $this->context->smarty->assign(array(

	            'carriers' => $carriers,

	            'states' => $states

	        ));

			

			if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE',1);

				

			if(Configuration::get('PRESTATILL_DRIVE_SEND_EMAIL') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_SEND_EMAIL',1); 

				

			if(Configuration::get('PRESTATILL_DRIVE_DISPLAY_TABLE') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_DISPLAY_TABLE',1);

			

			if(Configuration::get('PRESTATILL_DRIVE_MODIFY_PDF') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_MODIFY_PDF',0);

			

			if(Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_SEND_REMINDER',0);

			

			if(Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER_TIME') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_SEND_REMINDER_TIME',120);

			

			// 1.3.0

			if(Configuration::get('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL') == null)

				Configuration::updateValue('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL',0);

			

			// 1.3.1

			if(Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO',0);

			

			if(Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES',0);

			

			// 1.4.0	

			if(Configuration::get('PRESTATILL_DISPLAY_STORES_ON_PRODUCT') == null)

				Configuration::updateValue('PRESTATILL_DISPLAY_STORES_ON_PRODUCT',0);

			

			if(Configuration::get('PRESTATILL_DISPLAY_CARRIERS_ON_CART') == null)

				Configuration::updateValue('PRESTATILL_DISPLAY_CARRIERS_ON_CART',0);

			

			// 2.0.0	

			if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR',0);

				

			if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION',0);

			

			if(Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION',60);

				

			if(Configuration::get('PRESTATILL_SEARCH_RADIUS') == null)

				Configuration::updateValue('PRESTATILL_SEARCH_RADIUS',100);

			

			// 2.0.1	

			if(Configuration::get('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION',0);

			

			// 2.0.2

			if(Configuration::get('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS') == null)

				Configuration::updateValue('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS',0);

    }



    public function getContent()

    {

    	$tab = 1;

    	// On met à jour la liste des horaires d'ouverture pour les éventuels nouveaux magasins

    	$this->_updateStoresOpenAndClose();

		

        $confirmation = null;

        $this->loadAsset();

        if ($this->_processConfiguration()) {

            $confirmation = true;

			$tab = '2';

        }

        $this->_assignConfiguration();



		// SUBMIT OPENING / CLOSING DRIVE FORM

        if (Tools::isSubmit('submitConfigDrive')) {

            $this->_processDriveConfiguration();

			$tab = '3';

			$confirmation = true;

        }



        $days_by_store = PrestatillDriveConfiguration::getAllDays();



        // SUBMIT VACATION FORM 

        $vacations = $this->_processVacationForm();

		if (Tools::isSubmit('submitVacation')) {

			$tab = '4';

			$confirmation = true;

		}

		

		$pConfig = new PrestatillDriveConfiguration();

        $stores = $pConfig->getAllStores();

		

		// On récupère la configuration des stores actifs / inactifs

		$drive_enabled = array();

		// 1.4.0 : on récupère la configuration des carriers pour chaque store

		$store_carrier = array();

		// 2.2.0 : Pin code

		$pin_code_active = array();

		$pin_code_prefix = array();



		foreach($stores as $key => $store)

		{

			$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

			// 2.0.0 : choose a carrier for each store			

			$store_carrier[$store['id_store']] = empty($temp)?0:(int)$temp['id_carrier'];

			$drive_enabled[$store['id_store']] = Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.(int)$store['id_store']);

			// 2.2.0

			$pin_code_active[$store['id_store']] = empty($temp)?0:(int)$temp['pin_code_active'];

			$pin_code_prefix[$store['id_store']] = empty($temp)?null:$temp['pin_code_prefix'];

		}

		

		// 2.0 : New form "Click & Collect"

		if (Tools::isSubmit('submitCollect')) {

			$this->_processCollect();

			$tab = '1';

			$confirmation = true;

		}



		$link = new Link();

		

		$cron_url = $link->getModuleLink('prestatilldrive', 'generatecron', ['token' => Tools::substr(_COOKIE_KEY_, 34, 8)]);

		

		$stores_link = $link->getAdminLink('AdminStores');

		$carriers_link = $link->getAdminLink('AdminCarriers');



        $this->context->smarty->assign(array(

            'days' => $days_by_store,

            'drive_enabled' => $drive_enabled,

            'vacations' => $vacations,

            'module_version' => $this->version,

            'config_id_carrier' => Configuration::get('PRESTATILL_DRIVE_CARRIER'),

            'stores' => $stores,

            'confirmation' =>$confirmation,

            'p_version_update' => Configuration::get('PRESTATILL_DRIVE_VERSION'),

            'slot_enabled' => Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'),

            'gg_api_key' => Configuration::get('PS_API_KEY'),

            'send_email' => Configuration::get('PRESTATILL_DRIVE_SEND_EMAIL'),

            'display_table' => Configuration::get('PRESTATILL_DRIVE_DISPLAY_TABLE'),

            'modify_pdf' => Configuration::get('PRESTATILL_DRIVE_MODIFY_PDF'),

            'store_search' => Configuration::get('PRESTATILL_SEARCH_STORE'),

            'send_mail_to_store' => Configuration::get('PRESTATILL_SEND_MAIL_TO_STORE'),

            'search_radius' => Configuration::get('PRESTATILL_SEARCH_RADIUS'),

            'cron_url' => $cron_url,

            'send_reminder' => Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER'),

            'send_reminder_time' => Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER_TIME'),

            'stores_link' => $stores_link,

			'carriers_link' => $carriers_link,

			'formatted_days' => $this->getWeekDays(),

			// 1.2.9

			'carence_supp' => Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP')?Tools::jsonDecode(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP'),true):'',

        	'id_lang' => (int)$this->context->language->id,

        	'id_shop_group' => (int)$this->context->shop->id_shop_group,

        	'id_shop' => (int)$this->context->shop->id,

        	// 1.3.0

            'attach_invoice' => Configuration::get('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL'),

            // 1.3.1

            'max_products' => Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO'),

            'categories' => $this->_getRecursiveCategories(),

            // 1.4.0

            'store_carrier' => $store_carrier,

            'store_product_display' => Configuration::get('PRESTATILL_DISPLAY_STORES_ON_PRODUCT'),

            'carrier_product_display' => Configuration::get('PRESTATILL_DISPLAY_CARRIERS_ON_CART'),

            'ps_version_bo' => _PS_VERSION_,

            // 2.0.0

            'tab' => $tab,

            'store_selector' => Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'),

            'slot_reservation' => Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'),

            'slot_reservation_duration' => Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION'),

            'awaiting_delay_no_stock' => Configuration::get('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION'),

            // 2.1.0

            'hide_empty_days' => Configuration::get('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS'),

            // 2.2.0

            'pin_code_active' => $pin_code_active,

            'pin_code_prefix' => $pin_code_prefix,

		));



        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');

    }



	private function _processDriveConfiguration()

	{

		$days_by_store = PrestatillDriveConfiguration::getAllDays();

			

			foreach ($days_by_store as $id_store => $days) 

			{

				array_key_exists('PRESTATILL_DRIVE_ENABLE_STORE_'.(int)$id_store, $_POST)?Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_STORE_'.(int)$id_store, Tools::getValue('PRESTATILL_DRIVE_ENABLE_STORE_'.(int)$id_store)):Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_STORE_'.(int)$id_store, 0);

				

				foreach($days as $d)

				{

					$update_day = new PrestatillDriveConfiguration((int)$d['id_prestatill_drive']);

				

	                if (array_key_exists('PRESTATILL_DRIVE_'.(int)$d['id_prestatill_drive'], $_POST) && array_key_exists('PRESTATILL_DRIVE_OPENING_NONSTOP_'.(int)$d['id_prestatill_drive'], $_POST)) {

	                    $update_day->openning = 1;

	                    $update_day->nonstop = 1;

	                } else if (array_key_exists('PRESTATILL_DRIVE_'.(int)$d['id_prestatill_drive'], $_POST) && array_key_exists('PRESTATILL_DRIVE_OPENING_NONSTOP_'.(int)$d['id_prestatill_drive'], $_POST) == false) {

	                    $update_day->openning = 1;

	                    $update_day->nonstop = 0;

	                } else {

	                    $update_day->openning = 0;

	                    $update_day->nonstop = 0;

	                }

	

	                $update_day->hour_open_am = !empty(Tools::getValue('PRESTATILL_DRIVE_OPENING_AM_'.(int)$d['id_prestatill_drive']))?pSQL(Tools::getValue('PRESTATILL_DRIVE_OPENING_AM_'.(int)$d['id_prestatill_drive'])):'00:00';

	                $update_day->hour_close_am = !empty(Tools::getValue('PRESTATILL_DRIVE_CLOSING_AM_'.(int)$d['id_prestatill_drive']))?pSQL(Tools::getValue('PRESTATILL_DRIVE_CLOSING_AM_'.(int)$d['id_prestatill_drive'])):'00:00';

	                $update_day->hour_open_pm = !empty(Tools::getValue('PRESTATILL_DRIVE_OPENING_PM_'.(int)$d['id_prestatill_drive']))?pSQL(Tools::getValue('PRESTATILL_DRIVE_OPENING_PM_'.(int)$d['id_prestatill_drive'])):'00:00';

	                $update_day->hour_close_pm = !empty(Tools::getValue('PRESTATILL_DRIVE_CLOSING_PM_'.(int)$d['id_prestatill_drive']))?pSQL(Tools::getValue('PRESTATILL_DRIVE_CLOSING_PM_'.(int)$d['id_prestatill_drive'])):'00:00';

	

	                // save

	                if ($update_day->save()) {

	                    $confirmation = true;

	                } else {

	                    $confirmation = false;

	                }

				}	

		

				// 2.0.0 : Update carrier for each store

				if(Tools::getValue('PRESTATILL_DRIVE_CARRIER_'.(int)$id_store) > 0)

				{

					$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$id_store);

					if(!empty($temp))

					{

						$pdc = new PrestatillDriveStoreCarrier((int)$temp['id_prestatill_drive_store_carrier']);

						if(Validate::isLoadedObject($pdc))

						{

							$pdc->id_carrier = (int)Tools::getValue('PRESTATILL_DRIVE_CARRIER_'.(int)$id_store);

							// 2.2.0

							$pdc->pin_code_active = (int)Tools::getValue('PRESTATILL_DRIVE_PIN_CODE_ACTIVE_'.(int)$id_store);

							

							$prefix = Tools::getValue('PRESTATILL_DRIVE_PIN_CODE_PREFIX_'.(int)$id_store);

							$pdc->pin_code_prefix = $prefix != ''?$prefix:null;

							

							$pdc->update();

						}

					}

					else

					{

						$pdc = new PrestatillDriveStoreCarrier();

						$pdc->id_carrier = (int)Tools::getValue('PRESTATILL_DRIVE_CARRIER_'.(int)$id_store);

						$pdc->id_store = (int)$id_store;

						$pdc->id_shop = (int)Context::getContext()->shop->id;

						$pdc->id_shop_group = (int)Context::getContext()->shop->id_shop_group;

						// 2.2.0

						$pdc->pin_code_active = (int)Tools::getValue('PRESTATILL_DRIVE_PIN_CODE_ACTIVE_'.(int)$id_store);

						$pdc->pin_code_prefix = Tools::getValue('PRESTATILL_DRIVE_PIN_CODE_PREFIX_'.(int)$id_store);

						$pdc->save();

					}

					

				}

				else 

				{

					$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$id_store);

					$pdc = new PrestatillDriveStoreCarrier((int)$temp['id_prestatill_drive_store_carrier']);

					if(Validate::isLoadedObject($pdc))

					{

						$pdc->delete();

					}

				}

            }

			$tab = '3';

	}



	private function _processCollect()

	{

		Configuration::updateValue('PS_API_KEY',Tools::getValue('PS_API_KEY'));

		Configuration::updateValue('PRESTATILL_SEARCH_STORE',Tools::getValue('PRESTATILL_SEARCH_STORE'));

		Configuration::updateValue('PRESTATILL_SEARCH_RADIUS',Tools::getValue('PRESTATILL_SEARCH_RADIUS'));

		// 1.4.0

		Configuration::updateValue('PRESTATILL_DISPLAY_STORES_ON_PRODUCT',Tools::getValue('PRESTATILL_DISPLAY_STORES_ON_PRODUCT'));

		Configuration::updateValue('PRESTATILL_DISPLAY_CARRIERS_ON_CART',Tools::getValue('PRESTATILL_DISPLAY_CARRIERS_ON_CART'));

		// 2.0.0

		Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR',Tools::getValue('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'));

		Configuration::updateValue('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION',Tools::getValue('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'));	

		Configuration::updateValue('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION',Tools::getValue('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION'));	

	}

	

	private function _processVacationForm()

	{

		if (Tools::isSubmit('submitVacation')) {

            $vacation = new PrestatillDriveVacation();

            if (!empty(Tools::getValue('PRESTATILL_DRIVE_VACATION_START')) && !empty(Tools::getValue('PRESTATILL_DRIVE_VACATION_END'))) {

                $vacation->vacation_start = pSQL(Tools::getValue('PRESTATILL_DRIVE_VACATION_START'));

                $vacation->vacation_end = pSQL(Tools::getValue('PRESTATILL_DRIVE_VACATION_END'));

				$vacation->id_store = pSQL(Tools::getValue('PRESTATILL_DRIVE_VACATION_ID_STORE'));

				

                if ($vacation->save()) {

                    $confirmation = true;

                } else {

                    $confirmation = false;

                }

            }

        }



        $vacations = PrestatillDriveVacation::getAllVacation();

        foreach ($vacations as $vacation) {

            if (Tools::isSubmit('deleteVacation_'.(int)$vacation['id_vacation'])) {

                PrestatillDriveVacation::deleteVacation((int)$vacation['id_vacation']);

            }

        }



        $vacations = PrestatillDriveVacation::getAllVacation();

		

		return $vacations;

	}



	private function _getRecursiveCategories()

	{	

		$id_lang = $this->context->language->id;

		

		$categories = array();

        $shops = Shop::getShops($id_lang);

		

		if (version_compare(_PS_VERSION_, '1.6', '>=')) {

			$root = Category::getRootCategory();

			

            $tree = new HelperTreeCategories('subtree_associated_categories'); 

            $tree->setUseCheckBox(true)

                ->setAttribute('is_category_filter', $root->id)

                ->setRootCategory($root->id)

                ->setFullTree(true)

                ->setSelectedCategories(explode(',', Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_CATEGORIES')))

                ->setInputName('categories'); 

            $categories = $tree->render();

        }

		

		return $categories;

	}



	private function _updateStoresOpenAndClose() 

	{

		$pdc = new PrestatillDriveConfiguration();

		$update_stores = $pdc->updateStoresOpening();		

		

		return $update_stores;

		

	}

	

	public function d($datas)

	{

		echo('<pre>');

		print_r($datas);

		die();

	}

	

    public function setMedia()

    {

        parent::setMedia();

        $this->context->controller->addJS($this->_path . '/js/configurator.js');

    }

	

	private function _getCommonCarriers($cart)

	{

		$carriers = [];

		$common_carrier = false;

		// On récupère la liste des transporteurs possibles pour les produits du panier

		$carrier_list = array();

		$nb_products = 0;

		if(Validate::isLoadedObject($cart))

		{

			$products = $cart->getProducts();

			if(!empty($products))

			{

				foreach($products as $product)

				{

					$nb_products++;

					$temp_list = Carrier::getAvailableCarrierList(new Product((int)$product['id_product']), null);

					if(!empty($temp_list))

					{

						// On compte le nombre de fois ou chaque transporteur est disponible

						foreach($temp_list as $id_carrier)

						{

							if(!isset($carrier_list[$id_carrier]))

								$carrier_list[$id_carrier] = 0;

							

							$carrier_list[$id_carrier] ++;

						}

					}

				}

				

				// On vérifie si au moins 1 transporteur est disponible pour tous les produits, sinon on renvoie false;

				if(!empty($carrier_list))

				{

					foreach($carrier_list as $nbr_carriers)

					{

						if($nbr_carriers == $nb_products)

							$common_carrier = true;

					}

				}

			}

		}

		

		return $common_carrier;

	}



    public function hookDisplayBeforeCarrier($params)

    {

    	// On récupère les transporteurs commmuns

		$message = null;

    	if(isset($params['cart']))

		{

			$cart = $params['cart'];			

			$common_carrier = self::_getCommonCarriers($cart);

			

			if($common_carrier == false)

			{

				$message = $this->l('Your cart contains products from differents pick up stores, please make separate orders to choose an available time slot for each one.');

			}

		}

    	

        $this->_assignConfiguration();



        $date = date("Y-m-d");

        $nbdayview = Configuration::get('PRESTATILL_DRIVE_NB_DAY');

        $date_fin = date('Y-m-d', (strtotime('+'.$nbdayview.' day')));

        $tableau_creneau = PrestatillDriveCreneau::countCreneau($date, $date_fin);

        $i = 0;

        $duree_creneau = Configuration::get('PRESTATILL_DRIVE_DUREE');

        $hour = Configuration::get('PRESTATILL_DRIVE_OPEN');

        $closedrive = Configuration::get('PRESTATILL_DRIVE_CLOSE');

        $rouge = array();



        for ($date; $date <= $date_fin; $i++) {

            $tmpDate = new DateTime($date.' '.$hour);

            for ($tmpDate; $tmpDate->format('H:i:s') < $closedrive; $tmpDate->modify('+'.(int)$duree_creneau.' minutes')) {

                foreach ($tableau_creneau as $row) {

                    if ($row['day'] == $date && $row['hour'] == $tmpDate->format('H:i:s')) {

                        $rouge[] = $date.' '.$tmpDate->format('H:i:s');

                    }

                }

            }

            $date = date('Y-m-d', (strtotime('+'.$i.' day')));

        }



		$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

		

		// On regarde si le shop est dans un sous dossier ou non

		$shop_url = new ShopUrl(Context::getContext()->shop->id);

		if(Validate::isLoadedObject($shop_url)) 

		{

			if($shop_url != '/')

			{

				$base_dir .= $shop_url->physical_uri;

			}

		}

		

		//dump($params['cart']->id_carrier);

		// 1.4.0 : On check l'id_carrier <> id_carrier du cart



        $this->context->smarty->assign(array(

            'redDate'=>json_encode($rouge),

			'base_url' => $base_dir,

			'slot_enabled' => Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'),

			'store_selector_active' => Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'),

			'message' => $message,

			'common_carriers' => (int)$common_carrier,

        ));



        //self::assignCreneau();

        $this->assignStore();



        return $this->display(__FILE__, 'views/templates/front/carrier.tpl');

    }



    public function assignStore()

    {

        $pConfig = new PrestatillDriveConfiguration();

        $stores = $pConfig->getAllStores();

		

		$drive_enabled = array();

		$ids_carrier = array();

		$id_store = null;

		

		if(is_array($stores) && !empty($stores))

		{

			foreach($stores as $store)

			{

				$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

				// 1.4.0 : On vérifie si le store est rattaché à un carrier

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']) && !empty($temp))

				{

					$drive_enabled[] = $store;

					$ids_carrier[$store['id_store']] = (int)$temp['id_carrier'];

				}

			}

			$nbr_stores = count($drive_enabled);

		}

		else 

		{

			$drive_enabled = $stores;

			$nbr_stores = false;

		} 

		

		// 2.0.0 : On récupère l'id_store du cookie s'il existe

		if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

		{

			if(isset($this->context->cookie->id_store) && (int)$this->context->cookie->id_store > 0)

			{

				$id_store = (int)$this->context->cookie->id_store;

			}

		}

       

        $this->context->smarty->assign(array(

            'stores' => $drive_enabled,

            'nbr_stores' => $nbr_stores,

            'ids_carrier' => $ids_carrier,

            'cart_link_16' => $this->context->link->getPageLink('order'),

            'cart_link_17' => $this->context->link->getPageLink('cart'),

            'ps_version' => _PS_VERSION_,

            'id_store' => $id_store,

            'slot_reservation' => (int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'),

        ));

    }



    public function assignCreneau()

    {

        $context = Context::getContext();

        $id_cart = (int)$context->cart->id;



        $result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

        $creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);

        $id_creneau = (int)$creneau->id;

        PrestatillDriveCreneau::updateOrdersCreneau((int)$id_creneau, 0, (int)$id_cart);

        if (Validate::isLoadedObject($creneau)) {

            $this->context->smarty->assign(array(

                'creneau_day' => $creneau->day,

                'creneau_hour' => $creneau->hour,

                'id' => (int)$id_creneau,



            ));

        }

    }

    

    public function hookHeader($params)

    {

        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'index') {

            $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');

        }



        $this->context->controller->addJquery();

        $this->context->controller->addJS(($this->_path).'views/js/jquery-dateFormat.js', 'all');

		if(version_compare(_PS_VERSION_, '1.7', '>='))

		{

			$this->context->controller->addJS(($this->_path).'views/js/carrier.js', 'all');

			$this->context->controller->addCSS(($this->_path).'views/css/config.css', 'all');

		}

		else

		{

			$this->context->controller->addJS(($this->_path).'views/js/carrier16.js', 'all');

			$this->context->controller->addCSS(($this->_path).'views/css/config16.css', 'all');

		}

		// 2.0.0 : Add store locator and slot reservation actions

		$this->context->controller->addJS($this->_path. 'views/js/storelocator.js');

    }



    public function hookActionValidateOrder(&$params)

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



        $id_order = (int)$params['order']->id;

        $id_cart = (int)$params['order']->id_cart;



        $result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);



		if(!empty($result))

		{

			// MAJ : 1.4.0 : Check si l'id_carrier a un drive

			$pConfig = new PrestatillDriveConfiguration(true);

			$stores = $pConfig->getAllStores();

							

			$drive_enabled = false;

			

			if(is_array($stores))

			{

				foreach($stores as $store)

				{

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']))

					{

						$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

						if(!empty($temp))

						{

							if($params['order']->id_carrier == (int)$temp['id_carrier'])

							{

								$drive_enabled = true;

							}

						}

					}

				}

			}

			

			// 1.4.0 Check OK ?

			if($drive_enabled) {

				$creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);

	

		        if (Validate::isLoadedObject($creneau)) {

		            $id_creneau = (int)$creneau->id_creneau;

		            PrestatillDriveCreneau::updateOrdersCreneau((int)$id_creneau, (int)$id_order, (int)$id_cart);

		

		            // Un fois le créneau assigné on le supprime du cookie pour libérer les prochaines commandes.

		            $context = Context::getContext();

		            //@TODO : donner la possibilité de conserver ou supprimer le magasin sélectionné

		            //2.0.0 : On vide également le créneau et l'évventuelle réservation

		            if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'))

		            	$context->cookie->__set('drive_slot_reservation', '');



					$context->cookie->__set('id_creneau', 0);

		            $context->cookie->__set('msg', '');

		            $context->cookie->write();

		        

					// On propose d'envoyer un mail distinct avec les informations de retrait

					if(Configuration::get('PRESTATILL_DRIVE_SEND_EMAIL') == 1)

					{

						// On envoi un mail si PS < à 1.6.1.5

						$id_lang = (int)Context::getContext()->language->id;

						

						$order = new Order($id_order);

						$customer = new Customer($order->id_customer);

						$email = null;

						$cname = '';

						if(Validate::isLoadedObject($customer))

						{

							$email = $customer->email;

							$cname = $customer->firstname. ' '.$customer->lastname;

						}

						

						// 2.2.0 : On vérifie si le code pin est activé

						$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

						$pin_code_active = null;

						$pin_code = null;

						if(!empty($pdsc))

						{

							$pin_code_active = (int)$pdsc['pin_code_active'];

						}

						

						if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)

						{

							$day_creneau = $creneau->day;

							$day = strftime('%d %B %Y', strtotime($day_creneau));

							$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

							$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

							$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

					        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

							

							

							// On récupère les informations du store

							$store = new Store((int)$creneau->id_store);

							$store_name = '';

							$store_addrress = '';

							$store_city = '';

							$store_postcode = '';

							$store_tel = '';

							if(Validate::isLoadedObject($store))

							{

								if(version_compare(_PS_VERSION_, '1.7.3', '>'))

								{

									$store_name = $store->name[(int)$id_lang];

									$store_addrress = $store->address1[(int)$id_lang];

								}

								else 

								{

									$store_name = $store->name;

									$store_addrress = $store->address1;

								}

								$store_city = $store->city;

								$store_postcode = $store->postcode;

								$store_tel = $store->phone;

							}

							

							$vars = array(

									'{firstname}' => $customer->firstname,

									'{lastname}' => $customer->lastname,

									'{shop_name}' => 'Nom boutique',

									'{msg_creneau}' => $msg_creneau,

									'{order_ref}' => $order->reference,

									'{order_date}' => strftime('%d %B %Y', strtotime($order->date_add)),

									'{store_name}' => $store_name,

									'{store_address}' => $store_addrress,

									'{store_city}' => $store_city,

									'{store_postcode}' => $store_postcode,

									'{store_tel}' => $store_tel,

									'{pin_code}' => '',

									);

								

						

						 	$template = 'creneau';

						}

						else 

						{

							// On récupère les informations du store

							$store = new Store((int)$creneau->id_store);

							$store_name = '';

							$store_addrress = '';

							$store_city = '';

							$store_postcode = '';

							$store_tel = '';

							if(Validate::isLoadedObject($store))

							{

								if(version_compare(_PS_VERSION_, '1.7.3', '>'))

								{

									$store_name = $store->name[(int)$id_lang];

									$store_addrress = $store->address1[(int)$id_lang];

								}

								else 

								{

									$store_name = $store->name;

									$store_addrress = $store->address1;

								}

								$store_city = $store->city;

								$store_postcode = $store->postcode;

								$store_tel = $store->phone;

							}

							

							$vars = array(

									'{firstname}' => $customer->firstname,

									'{lastname}' => $customer->lastname,

									'{shop_name}' => 'Nom boutique',

									'{order_ref}' => $order->reference,

									'{order_date}' => strftime('%d %B %Y', strtotime($order->date_add)),

									'{store_name}' => $store_name,

									'{store_address}' => $store_addrress,

									'{store_city}' => $store_city,

									'{store_postcode}' => $store_postcode,

									'{store_tel}' => $store_tel,

									'{pin_code}' => '',

									);

									

							$template = 'creneau_store'; 

						}



						// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

						if($pin_code_active == 1)

						{

							$pin_code = $creneau->pin_code;

							

							$this->context->smarty->assign('pin_code',$pin_code);

							$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

							$vars['{pin_code}'] = $pin_code_display;

						}



						$template_path = _PS_MODULE_DIR_.'prestatilldrive/mails/';   

						

						@Mail::Send(

							$id_lang, 

							$template, 

							$this->l('Some informations about your order'), 

							$vars, 

							$email,

							$cname,

							null,

							null,

							null,

							null,

							$template_path

						);

					}

				}



				// On met à jour l'adresse sur la commande si l'option est validée dans la configuration

				if(Configuration::get('PRESTATILL_DRIVE_MODIFY_PDF') == 1) 

				{

					

					$id_address = null;

					if(Configuration::get('PRESTATILL_ADDR_STORE_'.$result['id_store'])) 

					{

						$id_address = Configuration::get('PRESTATILL_ADDR_STORE_'.$result['id_store']);

						$params['order']->id_address_delivery = (int)$id_address;

					}

				}

			}

		}



		// 2.0.0 : On ne s'occupe plus du créneau si celui-ci était assigné à un panier

		// et que la commande a été passée avec un autre mode de livraison

		if(isset($this->context->cookie->id_creneau) && $this->context->cookie->id_creneau > 0)

		{

			$this->context->cookie->__set('id_creneau', 0);

            $this->context->cookie->__set('msg', '');

            $this->context->cookie->write();

		}

    }



	public function hookActionOrderHistoryAddAfter($params)

	{

		

		$id_lang = (int)$this->context->language->id;

		

        $creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$params['order_history']->id_order);

		

		if(Validate::isLoadedObject($creneau))	

		{

			if((int)$creneau->store_informed == 0 && Configuration::get('PRESTATILL_SEND_MAIL_TO_STORE') == 1)

			{

				$order = new Order((int)$params['order_history']->id_order);

			

				if(Validate::isLoadedObject($order))

				{

					$msg_creneau = null;

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'))

					{

						$day_creneau = $creneau->day;

						$day = strftime('%d %B %Y', strtotime($day_creneau));

						$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

						$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

						$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

				        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

					}

					else 

					{

						$creneau->day = null;

					}

						

					// On récupère les informations du store

					$store = new Store((int)$creneau->id_store);

					$store_name = '';

					$store_addrress = '';

					$store_city = '';

					$store_postcode = '';

					$store_tel = '';

					if(Validate::isLoadedObject($store))

					{

						if(version_compare(_PS_VERSION_, '1.7.3', '>'))

						{

							$store_name = $store->name[(int)$id_lang];

							$store_addrress = $store->address1[(int)$id_lang];

						}

						else 

						{

							$store_name = $store->name;

							$store_addrress = $store->address1;

						}

						$store_city = $store->city;

						$store_postcode = $store->postcode;

						$store_tel = $store->phone;

					}

					

					

					if(!empty($store->email))

					{

						$template_path = _PS_MODULE_DIR_.'prestatilldrive/mails/';

						

						$template = 'new_creneau_store';  

						

						if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)

						{

							$template = 'new_creneau';

						}

						

						// 1.3.1 : get customer's comment

						$message = $this->getAllMessages($order->id);

						

						// 1.4.0 : get products details 

						$products = $order->getProductsDetail();

						

						// 2.2.0 : On vérifie si le code pin est activé

						$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

						$pin_code_active = null;

						$pin_code = null;

						if(!empty($pdsc))

						{

							$pin_code_active = (int)$pdsc['pin_code_active'];

						}

						

						$customer = new Customer((int)$order->id_customer);

						$customer_infos = null;

						if(Validate::isLoadedObject($customer))

						{

							// 2.0.1 : get address invoice for tel number

							$address = new Address((int)$order->id_address_invoice);

							if(Validate::isLoadedObject($address))

							{

								$this->context->smarty->assign('address',$address);

							}

							

							// 2.0.1 : get customer informations

							$this->context->smarty->assign('customer',$customer);

							$customer_infos = $this->display(__FILE__, 'views/templates/front/customer-infos.tpl');

						}

						

						$this->context->smarty->assign('products',$products);

						$content = $this->display(__FILE__, 'views/templates/front/product-list.tpl');

						

						$vars_admin = array(

							'{msg_creneau}' => $msg_creneau,

							'{order_ref}' => $order->reference,

							'{order_date}' => strftime('%d %B %Y', strtotime($order->date_add)),

							'{store_name}' => $store_name,

							'{store_address}' => $store_addrress,

							'{store_city}' => $store_city,

							'{store_postcode}' => $store_postcode,

							'{store_tel}' => $store_tel,

							// 1.3.1 : comment from customer

							'{message}' => $message,

							// 1.4.0 : products

							'{products}' => $content,

							'{customer_infos}' => $customer_infos,

							'{pin_code}' => '',

						);

						

						// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

						if($pin_code_active == 1)

						{

							$pin_code = $creneau->pin_code;

							

							$this->context->smarty->assign('pin_code',$pin_code);

							$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

							$vars_admin['{pin_code}'] = $pin_code_display;

						}

						

						// Since 1.3.0

						// Join PDF invoice

	                    if ((int)Configuration::get('PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL') == 1 && (int)Configuration::get('PS_INVOICE') && $order->invoice_number) {

	                    	$file_attachement = array();

	                        

							$order_invoice_list = $order->getInvoicesCollection();

	                        //Hook::exec('actionPDFInvoiceRender', array('order_invoice_list' => $order_invoice_list));

	                        $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, $this->context->smarty);

	                        $file_attachement['content'] = $pdf->render(false);

	                        $file_attachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int) $order->id_lang, null, $order->id_shop) . sprintf('%06d', $order->invoice_number) . '.pdf';

	                        $file_attachement['mime'] = 'application/pdf';

	                    } 

	                    else 

	                    {

	                    	$file_attachement = null;	

						}

							

						if(Mail::Send(

							$id_lang, 

							$template, 

							$this->l('New Drive Order'), 

							$vars_admin, 

							$store->email,

							null,

							null,

							null,

							$file_attachement,

							null,

							$template_path

						))

						{

							// Mise à jour de l'information d'envoie

							$creneau->store_informed = 1;

							$creneau->update();

						}

					}

				}

			}

		}

	}



    public static function changeOrderState($id_order_state, $order)

    {

        $order_state = new OrderState((int)$id_order_state);

        $errors = array();

        //d(Validate::isLoadedObject($order_state));

        if (Validate::isLoadedObject($order_state)) {

            $current_order_state = $order->getCurrentOrderState();

            //d($current_order_state);

            if ((int)$current_order_state->id != (int)$order_state->id) {

                // Create new OrderHistory

                $history = new OrderHistory();

                $history->id_order = (int)$order->id;

                $history->id_employee = 0;



                $use_existings_payment = false;

                if (!$order->hasInvoice()) {

                    $use_existings_payment = true;

                }



                $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);



                $carrier = new Carrier((int)$order->id_carrier, (int)$order->id_lang);

                $templateVars = array();

                if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {

                    $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));

                }



                // Save all changes

                if ($history->addWithemail(true, $templateVars)) {

                    // synchronizes quantities if needed..

                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {

                        foreach ($order->getProducts() as $product) {

                            if (StockAvailable::dependsOnStock((int)$product['product_id'])) {

                                StockAvailable::synchronize((int)$product['product_id'], (int)$product['id_shop']);

                            }

                        }

                    }

                }

            }

        } else {

            $errors[] = Tools::displayError('The new order status is invalid.');

        }

    }

	

	// Toujours utile ?

    public function hookActionOrderStatusPostUpdate($params)

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



        $id_order = $params['id_order'];

        $order = new Order((int)$id_order);



        if (Validate::isLoadedObject($order)) {

            $id_cart = (int)$order->id_cart;



            $result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);



            $creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);



            if (Validate::isLoadedObject($creneau)) {

                $order = new Order((int)$id_order);

                if (Validate::isLoadedObject($order)) {

                    $order->delivery_date = pSQL($creneau->day).' '.pSQL($creneau->hour);

                    $order->save();

                }

            }

        }

    }



    public function hookDisplayPaymentTop()

    {

        $context = Context::getContext();

        $msg = $context->cookie->msg;

        $id_creneau = (int)$context->cookie->id_creneau;

		

		if((int)$id_creneau > 0)

		{

            $creneau = new PrestatillDriveCreneau((int)$id_creneau);

		

			if(Validate::isLoadedObject($creneau)) 

			{

				if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

				{

					$this->context->smarty->assign(array(

			                'creneau' => $msg,

			                'id_creneau' => $id_creneau,

			                'slot_enabled' => Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE'),

			            ));

			

			        return $this->display(__FILE__, 'views/templates/front/creneau.tpl');

				}

			}

		}

    }



    public function hookDisplayOrderDetail($params)

    {

        $creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$params['order']->id);

		

		if(Validate::isLoadedObject($creneau)) {

			

            	if((int)$creneau->id_store > 0 && $creneau->id_order > 0) 

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

					

					// 2.2.0 : On vérifie si le code pin est activé

					$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

					$pin_code_active = null;

					$pin_code = null;

					if(!empty($pdsc))

					{

						$pin_code_active = (int)$pdsc['pin_code_active'];

					}

					

					// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

					if($pin_code_active == 1)

					{

						$pin_code = $creneau->pin_code;

					}

						

					if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

					{

						$day_creneau = $creneau->day;

						$day = strftime('%d %B %Y', strtotime($day_creneau));

						$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

						$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

						$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

						

				        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

					}

					else 

					{

						$msg_creneau = null;

					}

				

					$store = new Store((int)$creneau->id_store);

					

					$this->context->smarty->assign(

			            array(

				            'creneau' => $creneau, 

				            'msg_creneau' => $msg_creneau,

				            'drive_store' => $store,

				            'pin_code' => $pin_code,

				            'id_lang' => version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0,

						)

			        );

		

		        return $this->display(__FILE__, 'views/templates/front/order-detail.tpl');

			}

		}

    }



	// New HOOK on 1.7.7

	public function hookDisplayAdminOrderTabContent($hookParams)

	{

		$order = new Order((int)$hookParams['id_order']);

		

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

		

        if (Validate::isLoadedObject($order)) {

            //$id_cart = (int)$order->id_cart;

			$store = null;

			$msg_creneau = null;

			$day_creneau = null;

			$hour_creneau = null;

			$end_creneau = null;

			

			$js = array(

				$this->_path.'views/js/jquery-dateFormat.js',

	            $this->_path.'views/js/admin-order-hook.js'

	        );

	        $css = array(

	           $this->_path.'views/css/admin-order-hook.css',

	            $this->_path.'views/css/config.css'

	        );

	

	        $this->context->controller->addJS($js);

	        $this->context->controller->addCSS($css);

						

            $result = PrestatillDriveCreneau::getCreneauByIdOrder((int)$order->id);

			

			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

				

			// On regarde si le shop est dans un sous dossier ou non

			$shop_url = new ShopUrl(Context::getContext()->shop->id);

			if(Validate::isLoadedObject($shop_url)) 

			{

				if($shop_url != '/')

				{

					$base_dir .= $shop_url->physical_uri;

				}

			}

			

			if($result) {

				$creneau = new PrestatillDriveCreneau((int)$result->id_creneau);

				

	            if (Validate::isLoadedObject($creneau)) {

					if((int)$creneau->id_store > 0 && $creneau->id_order > 0) {

	            	

						if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

						{

							$day_creneau = $creneau->day;

							$day = strftime('%d %B %Y', strtotime($day_creneau));

							$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

							$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

							$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

							

					        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

						}

						else 

						{

							$msg_creneau = null;

						}

						

						

						// On récupère les informations du store

						$store = new Store((int)$creneau->id_store);

						$store_name = '';

						$store_addrress = '';

						$store_city = '';

						$store_postcode = '';

						if(Validate::isLoadedObject($store))

						{

							$store_name = $store->name;

							$store_addrress = $store->address1;

							$store_city = $store->city;

							$store_postcode = $store->postcode;

						}

						

						$pConfig = new PrestatillDriveConfiguration(true);

        				$stores = $pConfig->getAllStores();

						

						$this->context->smarty->assign(

				            array(

					            'creneau' => $creneau, 

					            'msg_creneau' => $msg_creneau,

					            'stores' => $stores,

					            'store_name' => $store_name,

					            'day_creneau' => $day_creneau,

					            'hour_creneau' => $hour_creneau,

					            'store_address' => $store_addrress,

					            'store_city' => $store_city,

					            'id_store' => $creneau->id_store,

					            'store_postcode' => $store_postcode,

					            'id_lang' => version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0,

					            'base_dir' => $base_dir,

					            'id_creneau' => $creneau->id,

					            'end_creneau' => $end_creneau,

					            'id_order'=> (int)$hookParams['id_order']

							)

				        );

						

					 	return $this->display(__FILE__, 'views/templates/hook/order_creneau.tpl');

					}

	            }

			}

			// On propose la création d'un créneau

			else 

			{

				$pConfig = new PrestatillDriveConfiguration(true);

        		$stores = $pConfig->getAllStores();

				

				$this->context->smarty->assign(

			        array(

			            'stores' => $stores, 

			            'base_dir' => $base_dir,

			            'id_store' => 0,

			            'creneau' => null,

			            'id_order'=> (int)$hookParams['id_order']

					)

		        );

				

				return $this->display(__FILE__, 'views/templates/hook/order_creneau_new.tpl');

			}

        }

	}

	

	/*

	 * SINCE 1.2.4

	 */

	 public function hookDisplayAdminOrderContentShip($params) 

	 {

        $order = new Order((int)$params['order']->id);

		

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

		

        if (Validate::isLoadedObject($order)) {

            //$id_cart = (int)$order->id_cart;

			$store = null;

			$msg_creneau = null;

			$day_creneau = null;

			$hour_creneau = null;

			$end_creneau = null;

			

			$js = array(

				$this->_path.'views/js/jquery-dateFormat.js',

	            $this->_path.'views/js/admin-order-hook.js'

	        );

	        $css = array(

	           $this->_path.'views/css/admin-order-hook.css',

	            $this->_path.'views/css/config.css'

	        );

	

	        $this->context->controller->addJS($js);

	        $this->context->controller->addCSS($css);

						

            $result = PrestatillDriveCreneau::getCreneauByIdOrder((int)$order->id);

			

			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

				

			// On regarde si le shop est dans un sous dossier ou non

			$shop_url = new ShopUrl(Context::getContext()->shop->id);

			if(Validate::isLoadedObject($shop_url)) 

			{

				if($shop_url != '/')

				{

					$base_dir .= $shop_url->physical_uri;

				}

			}

			

			if($result) {

				$creneau = new PrestatillDriveCreneau((int)$result->id_creneau);

				

	            if (Validate::isLoadedObject($creneau)) {

					if((int)$creneau->id_store > 0 && $creneau->id_order > 0) {

	            	

						if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 

						{

							$day_creneau = $creneau->day;

							$day = strftime('%d %B %Y', strtotime($day_creneau));

							$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

							$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

							$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

							

					        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

						}

						else 

						{

							$msg_creneau = null;

						}

						

						

						// On récupère les informations du store

						$store = new Store((int)$creneau->id_store);

						$store_name = '';

						$store_addrress = '';

						$store_city = '';

						$store_postcode = '';

						if(Validate::isLoadedObject($store))

						{

							$store_name = $store->name;

							$store_addrress = $store->address1;

							$store_city = $store->city;

							$store_postcode = $store->postcode;

						}

						

						$pConfig = new PrestatillDriveConfiguration(true);

        				$stores = $pConfig->getAllStores();

						

						$this->context->smarty->assign(

				            array(

					            'creneau' => $creneau, 

					            'msg_creneau' => $msg_creneau,

					            'stores' => $stores,

					            'store_name' => $store_name,

					            'day_creneau' => $day_creneau,

					            'hour_creneau' => $hour_creneau,

					            'store_address' => $store_addrress,

					            'store_city' => $store_city,

					            'id_store' => $creneau->id_store,

					            'store_postcode' => $store_postcode,

					            'id_lang' => version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0,

					            'base_dir' => $base_dir,

					            'id_creneau' => $creneau->id,

					            'end_creneau' => $end_creneau,

					            'id_order'=> (int)$params['order']->id

							)

				        );

						

					 	return $this->display(__FILE__, 'views/templates/hook/order_creneau.tpl');

					}

	            }

			}

			// On propose la création d'un créneau

			else 

			{

				$pConfig = new PrestatillDriveConfiguration(true);

        		$stores = $pConfig->getAllStores();

				

				$this->context->smarty->assign(

			        array(

			            'stores' => $stores, 

			            'base_dir' => $base_dir,

			            'id_store' => 0,

			            'creneau' => null,

			            'id_order'=> (int)$params['order']->id

					)

		        );

				

				return $this->display(__FILE__, 'views/templates/hook/order_creneau_new.tpl');

			}

        }

	}

	

	/**

	 * On envoie une mail de rappel si activé

	 */

	public function sendMailReminderForDriveOrders($time = 120)

	{

		// On récupère la liste des créneaux pour lesquels envoyer un rappel

		$slots = PrestatillDriveCreneau::getOrdersForReminder($time);

		

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

		

		if(!empty($slots))

		{

			$id_lang = (int)Context::getContext()->language->id;



			foreach($slots as $slot)

			{

				$creneau = new PrestatillDriveCreneau($slot['id_creneau']);

				if(Validate::isLoadedObject($creneau))

				{



					if($creneau->reminded == 0)

					{

						$order = new Order($creneau->id_order);

						if(Validate::isLoadedObject($order))

						{



							if((int)$creneau->id_store > 0 && $creneau->id_order > 0) 

							{



								$customer = new Customer($order->id_customer);

								$email = null;

								if(Validate::isLoadedObject($customer))

								{

									$email = $customer->email;

								}

								

								$day_creneau = $creneau->day;

								$day = strftime('%d %B %Y', strtotime($day_creneau));

								$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

								$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

								$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

						        $msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

								$cname = $customer->firstname. ' '.$customer->lastname;

								

								// On récupère les informations du store

								$store = new Store((int)$creneau->id_store);

								$store_name = '';

								$store_addrress = '';

								$store_city = '';

								$store_postcode = '';

								$store_tel = '';

								if(Validate::isLoadedObject($store))

								{

									if(version_compare(_PS_VERSION_, '1.7.3', '>'))

									{

										$store_name = $store->name[(int)$id_lang];

										$store_addrress = $store->address1[(int)$id_lang];

									}

									else 

									{

										$store_name = $store->name;

										$store_addrress = $store->address1;

									}

									$store_city = $store->city;

									$store_postcode = $store->postcode;

									$store_tel = $store->phone;

								}

								

								// 2.2.0 : On vérifie si le code pin est activé

								$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

								$pin_code_active = null;

								$pin_code = null;

								if(!empty($pdsc))

								{

									$pin_code_active = (int)$pdsc['pin_code_active'];

								}

								

								$vars = array(

										'{firstname}' => $customer->firstname,

										'{lastname}' => $customer->lastname,

										'{shop_name}' => 'Nom boutique',

										'{msg_creneau}' => $msg_creneau,

										'{order_ref}' => $order->reference,

										'{order_date}' => strftime('%d %B %Y', strtotime($order->date_add)),

										'{store_name}' => $store_name,

										'{store_address}' => $store_addrress,

										'{store_city}' => $store_city,

										'{store_postcode}' => $store_postcode,

										'{store_tel}' => $store_tel,

										'{pin_code}' => '',

									);

									

								// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

								if($pin_code_active == 1)

								{

									$pin_code = $creneau->pin_code;

									

									$this->context->smarty->assign('pin_code',$pin_code);

									$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

									$vars['{pin_code}'] = $pin_code_display;

								}

										

								$template_path = _PS_MODULE_DIR_.'prestatilldrive/mails/';   

						

								$template = 'creneau_store';  

								

								if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)

								{

									$template = 'creneau';

								}

								

								if(Mail::Send(

									$id_lang, 

									$template, 

									$this->l('Reminder : Your Pick-up informations'), 

									$vars, 

									$email,

									$cname,

									null,

									null,

									null,

									null,

									$template_path

								)){

									$creneau->reminded = true;

									$creneau->update();

								}

							}

						}

					}

				}

			}

		}		

	}



	// 1.2.9

	public function sendEmailModification($creneau, $creation = false)

	{

		$id_lang = (int)$this->context->language->id;

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

		

		if($creneau->id_creneau > 0)

		{

			$msg_creneau = '';

						

			$day = strftime('%d %B %Y', strtotime($creneau->day));

			$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

			$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

			$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes'));

			

			$msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

			

			// 2.2.0 : On vérifie si le code pin est activé

			$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

			$pin_code_active = null;

			$pin_code = null;

			if(!empty($pdsc))

			{

				$pin_code_active = (int)$pdsc['pin_code_active'];

			}



			$order = new Order((int)$creneau->id_order);

			if(Validate::isLoadedObject($order))

			{

				$customer = new Customer((int)$order->id_customer);

				if(Validate::isLoadedObject($customer))

				{

					$email = $customer->email;

					$cname = $customer->firstname. ' '.$customer->lastname;

					

					// On récupère les informations du store

					$store = new Store((int)$creneau->id_store);

					$store_name = '';

					$store_addrress = '';

					$store_city = '';

					$store_postcode = '';

					$store_tel = '';

					if(Validate::isLoadedObject($store))

					{

						if(version_compare(_PS_VERSION_, '1.7.3', '>'))

						{

							$store_name = $store->name[(int)$id_lang];

							$store_addrress = $store->address1[(int)$id_lang];

						}

						else 

						{

							$store_name = $store->name;

							$store_addrress = $store->address1;

						}

						$store_city = $store->city;

						$store_postcode = $store->postcode;

						$store_tel = $store->phone;

					}

					

					$vars = array(

							'{firstname}' => $customer->firstname,

							'{lastname}' => $customer->lastname,

							'{shop_name}' => 'Nom boutique',

							'{msg_creneau}' => $msg_creneau,

							'{order_ref}' => $order->reference,

							'{order_date}' => strftime('%d %B %Y', strtotime($order->date_add)),

							'{store_name}' => $store_name,

							'{store_address}' => $store_addrress,

							'{store_city}' => $store_city,

							'{store_postcode}' => $store_postcode,

							'{store_tel}' => $store_tel,

							'{pin_code}' => '',

							);

							

					// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

					if($pin_code_active == 1)

					{

						$pin_code = $creneau->pin_code;

						

						$this->context->smarty->assign('pin_code',$pin_code);

						$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

						$vars['{pin_code}'] = $pin_code_display;

					}

				}

			}



			$template = 'creneau';

			$template_path = _PS_MODULE_DIR_.'prestatilldrive/mails/';

			

			$mail_object = $this->l('Modification of your Pick Up Slot');

			

			if($creation == true)

				$mail_object = $this->l('Informations about your Pick Up Slot');

			

			if(

				Mail::Send(

						$id_lang, 

						$template, 

						$mail_object, 

						$vars, 

						$email,

						$cname,

						null,

						null,

						null,

						null,

						$template_path

					)

				)

			{

				return true;

			}

		}

	}

	

	public function getDays($force = false)

	{

		if($force == false)

		{

			$days = array($this->l('Mon') => 1,$this->l('Tue') => 2,$this->l('Wed') => 3,$this->l('Thu') => 4,$this->l('Fri') => 5,$this->l('Sat') => 6,$this->l('Sun') => 7);

		}

		else 

		{

			// On identifie la locale courante si celle souhaitée par l'utilisateur n'est pas installée

			$currentLocale = setlocale(LC_CTYPE, 0);



			switch ($currentLocale) {

				case 'fr_FR.UTF-8':

					$days = array('lun.' => 1,'mar.' => 2,'mer.' => 3,'jeu.' => 4,'ven.' => 5,'sam.' => 6,'dim.' => 7);

					break;

				

				default:

					$days = array('Mon' => 1,'Tue' => 2,'Wed' => 3,'Thu' => 4,'Fri' => 5,'Sat' => 6,'Sun' => 7);

					break;

			}

		}

		

		return $days;

	}

	

	public function getWeekDays($all_days = false) 

	{

		if($all_days == true)

			return $this->l('All days'); 

			

		$days = array(

	        '1' => $this->l('Monday'),

	        '2' => $this->l('Tuesday'),

	        '3' => $this->l('Wednesday'),

	        '4' => $this->l('Thursday'),

	        '5' => $this->l('Friday'),

	        '6' => $this->l('Saturday'),

	        '7' => $this->l('Sunday'),

	    );

		

		return $days;

	}



	public function hookAddWebserviceResources($params) 

	{

		if(!empty($params['resources'])) 

		{

			$params['resources']['prestatill_drive_creneau'] = array('description' => 'Drive Slots', 'class' => 'PrestatillDriveCreneau');

		}

		return $params['resources'];

		

	}



	public function getCarrenceSupp($params)

    {

    	$context = Context::getContext();

		

		if(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))

			$carence_supp = Tools::jsonDecode(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id),true);

		

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['hour_limit'] = $params['hour_limit'];

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['id_day'] = $params['id_day'];

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['hour_limit_end'] = $params['hour_limit_end'];

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['waiting_time'] = $params['waiting_time'];

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['id_day_end'] = $params['id_day_end'];

		$carence_supp[$params['id_store'].'_'.$params['id_day']]['id_store'] = $params['id_store'];

		

		if(Configuration::updateValue('PRESTATILL_DRIVE_CARENCE_SUPP', Tools::jsonEncode($carence_supp),false,(int)$context->shop->id_shop_group,(int)$context->shop->id))

		{

			$this->context->smarty->assign(

	            array(

		            'carence_supp' => $carence_supp,

		            'formatted_days' => $this->getWeekDays(),

				)

	        );

			

	        return $this->display(__FILE__, 'views/templates/admin/tabs/carence_supp.tpl');

		}

    }



	// 1.4.0

	public function getStoresList($stores, $template = null, $active = false)

	{

		$this->context->smarty->assign(

            array(

	            'stores' => $stores,

	            'active' => $active,

	            'ps_17' => version_compare(_PS_VERSION_, '1.6.1.24', '>'),

			)

        );

		

		return $this->display(__FILE__, 'views/templates/front/stores'.$template.'.tpl');

	}



	public function deleteCarrenceSupp($params)

    {

    	$carence_supp = array();

		$context = Context::getContext();



		if(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))

			$carence_supp = Tools::jsonDecode(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id),true);

		

		if(!empty($carence_supp))

		{

			foreach($carence_supp as $key => $carence)

			{

				if($key == $params['id_store'].'_'.$params['id_day'])

				{

					unset($carence_supp[$key]);

				}

			}



			if(Configuration::updateValue('PRESTATILL_DRIVE_CARENCE_SUPP', Tools::jsonEncode($carence_supp),false,(int)$context->shop->id_shop_group,(int)$context->shop->id))

			{

				$this->context->smarty->assign(

		            array(

			            'carence_supp' => $carence_supp,

			            'formatted_days' => $this->getWeekDays(),

					)

		        );

				

		        return $this->display(__FILE__, 'views/templates/admin/tabs/carence_supp.tpl');

			}

		}

    }

	

	/*

	 * FOR PS 1.6.X

	 */

	public function hookActionGetExtraMailTemplateVars($params)

    {

		if(isset($params['cart']->id))

		{

			if(version_compare(_PS_VERSION_, '1.6.1.30', '<') && (Tools::getValue('controller') != 'validateordercarrier' && Tools::getValue('controller') != 'generatecron' || $params['template'] == 'new_order'))

			{

				$result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$params['cart']->id);

				

				$creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);

				

				if (Validate::isLoadedObject($creneau)) {

					

					$id_store = (int)$creneau->id_store;

					

					if(((int)$creneau->id_store > 0 && $creneau->id_order > 0 && Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 0) || (Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1) && ($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00')) 

					{

						$store = new Store((int)$id_store);

						$day_creneau = $creneau->day;

						$day = strftime('%d %B %Y', strtotime($day_creneau));

						$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

						$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

						$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

						$msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

						

						// 2.2.0 : On vérifie si le code pin est activé

						$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

						$pin_code_active = null;

						$pin_code = null;

						if(!empty($pdsc))

						{

							$pin_code_active = (int)$pdsc['pin_code_active'];

						}

						

						if(isset($params['template_vars']['{payment}']))

						{

							$extra_vars_payment = $params['template_vars']['{payment}'];

							if((int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)

							{

								$extra_vars_payment .= '<p><span size="2" face="Open-sans, sans-serif" color="#555454" style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"><span style="color: #333;"><strong>'.$this->l('Pick-up slot for your order: ').'</strong>'.$msg_creneau.'</p>';

							}

							$extra_vars_payment .= '<p><span size="2" face="Open-sans, sans-serif" color="#555454" style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"><span style="color: #333;"><strong>'.$this->l('Pick-up store: ').'</strong>'.$store->name.' '.$store->address1.' '.$store->postcode.' '.$store->city.'</p>';

							

							// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

							if($pin_code_active == 1)

							{

								$pin_code = $creneau->pin_code;

								

								$this->context->smarty->assign('pin_code',$pin_code);

								$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

								$extra_vars_payment .= $pin_code_display;

							}

							

							

							$params['extra_template_vars']['{payment}'] = $extra_vars_payment;

						}

					

					}

				}



			}

			else if(version_compare(_PS_VERSION_, '1.7', '>') && (Tools::getValue('controller') != 'validateordercarrier' || $params['template'] == 'order_conf'))

			{

				$result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$params['cart']->id);

				

				$creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);

				

				if (Validate::isLoadedObject($creneau)) {

					

					$id_store = (int)$creneau->id_store;

					

					if(((int)$creneau->id_store > 0 && $creneau->id_order > 0 && Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 0) || (Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1) && ($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00')) 

					{

						$store = new Store((int)$id_store);

						$day_creneau = $creneau->day;

						$day = strftime('%d %B %Y', strtotime($day_creneau));

						$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

						$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

						$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

						$msg_creneau = $day.' '.$this->l('beetween').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;

						

						// 2.2.0 : On vérifie si le code pin est activé

						$pdsc = PrestatillDriveStoreCarrier::getByIdStore((int)$creneau->id_store);

						$pin_code_active = null;

						$pin_code = null;

						if(!empty($pdsc))

						{

							$pin_code_active = (int)$pdsc['pin_code_active'];

						}

						

						if(isset($params['template_vars']['{payment}']))

						{

							$extra_vars_payment = $params['template_vars']['{payment}'];

							

							if((int)Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE') == 1)

							{

								$extra_vars_payment .= '<p><span size="2" face="Open-sans, sans-serif" color="#555454" style="font-family:Open sans,arial,sans-serif;font-size:16px;line-height:25px;text-align:left;color:#363a41;"><span style="color: #333;"><strong>'.$this->l('Pick-up slot: ').'</strong>'.$msg_creneau.'</p>';

							}

							

							if(version_compare(_PS_VERSION_, '1.7.3', '>'))

							{

								$id_lang = (int)Context::getContext()->language->id;

								$extra_vars_payment .= '<p><span size="2" face="Open-sans, sans-serif" color="#555454" style="font-family:Open sans,arial,sans-serif;font-size:16px;line-height:25px;text-align:left;color:#363a41;"><span style="color: #333;"><strong>'.$this->l('Pick-up store: ').'</strong>'.$store->name[$id_lang].' '.$store->address1[$id_lang].' '.$store->postcode.' '.$store->city.'</p>';

							}

							else 

							{

								$extra_vars_payment .= '<p><span size="2" face="Open-sans, sans-serif" color="#555454" style="font-family:Open sans,arial,sans-serif;font-size:16px;line-height:25px;text-align:left;color:#363a41;"><span style="color: #333;"><strong>'.$this->l('Pick-up store: ').'</strong>'.$store->name.' '.$store->address1.' '.$store->postcode.' '.$store->city.'</p>';

							}



							// 2.2.0 : Si le code pin est actif, on récupère le CODE PIN de la bdd

							if($pin_code_active == 1)

							{

								$pin_code = $creneau->pin_code;

								

								$this->context->smarty->assign('pin_code',$pin_code);

								$pin_code_display = $this->display(__FILE__, 'views/templates/front/pin-code.tpl');

								$extra_vars_payment .= $pin_code_display;

							}



							$params['extra_template_vars']['{payment}'] = $extra_vars_payment;

						}

					}

				}

			}

		}

    }



	// 1.3.1 : get customer's comment

	public function getAllMessages($id_order)

    {

        $messages = Db::getInstance()->executeS('

			SELECT `message`

			FROM `'._DB_PREFIX_.'message`

			WHERE `id_order` = '.(int)$id_order.'

			AND private = 0

			ORDER BY `id_message` ASC');

			

        $result = '';

		

		if(!empty($messages))

		{

			foreach ($messages as $message) {

	            $result .= $message['message'];

	        }

		}

        

		return $result;

	}

}