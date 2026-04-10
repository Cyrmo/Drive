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



class PrestatillDriveValidateOrderCarrierModuleFrontController extends ModuleFrontController

{

    public function initContent()

    {

        parent::initContent();

		

		$action = Tools::getValue('action');

		

		$context = Context::getContext();

		$message = array();

        $response = array();

		$status = 'success';

		

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

		

		

		switch ($action) {

			

			case 'initTable':

				$id_store = Tools::getValue('id_store');

				$init_bo = Tools::getValue('init_bo');

		        $message = array();

				// A voir si on fait ici le test sur PRESTATILL_DRIVE_DISPLAY_TABLE

				$message['display_table'] = Configuration::get('PRESTATILL_DRIVE_DISPLAY_TABLE',null,(int)$context->shop->id_shop_group,(int)$context->shop->id);

		        $message['table_days'] = $this->initTable($id_store, $init_bo);

		        $message['id_store'] = 0;

		        $message['creneau'] = null;

				

				

				// On réinitialise le créneau au chargement

				if($init_bo == 0)

				{

					$context = Context::getContext();

					$context->cookie->__set('msg', '');

					$context->cookie->write();

				}

				

				// 2.0.1 : Masquage des jours off

				$message['hide_empty_days'] = (int)Configuration::get('PRESTATILL_DRIVE_HIDE_EMPTY_DAYS');

				

		        $infos = $context = Context::getContext()->cookie;

		        if (!empty($infos->id_store)) {

		            $message['id_store'] = $infos->id_store;

		        }

		        if (!empty($infos->msg)) {

		            $message['creneau'] = $infos->msg;

		        }

	        break;

				

			case 'assignStore':

		

				$id_store = (int)Tools::getValue('store');

				$id_store_original = $id_store;

				

				// 2.0.0

				$id_carrier = (int)Tools::getValue('id_carrier');

				

				$drive_enabled = [];

				

				// 2.0.0 : On récupère l'id_store du cookie s'il existe

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

				{

					if(isset($context->cookie->id_store) && (int)$context->cookie->id_store > 0)

					{

						// On récupère les magasins liés au transporteur

						$pdc = new PrestatillDriveStoreCarrier();

						$stores = $pdc->getStoresAssociatedToACarrier((int)$id_carrier);

						

						if(!empty($stores))

						{

							foreach($stores as $store)

							{

								$drive_enabled[$store['id_store']] = $store;	

							}

						}

						

						// Uniquement si le magasin fait partie du transporteur sélectionné

						if(array_key_exists((int)$context->cookie->id_store, $drive_enabled))

						{

							$message['id_store'] = (int)$context->cookie->id_store;

						

							$store = new Store((int)$context->cookie->id_store);

							if(Validate::isLoadedObject($store))

							{

								$id_store_original = (int)$context->cookie->id_store;

							}

						}

					}

				}

				

		        $id_cart = (int)Context::getContext()->cart->id;

				$id_creneau = 0;

				$msg = null;

				$reserved = false;

				$crenau_valid = true;

				

		        if ((int)$id_cart > 0) {

		        		

		        	// On vérifie si un créneau n'a pas déjà été réservé (cookie)

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION') && (int)$context->cookie->id_creneau > 0)

					{

						$id_creneau = (int)$context->cookie->id_creneau;

						$creneau = PrestatillDriveCreneau::getCreneauByIdCreneau((int)$id_creneau);

					}

					else

					{

						$creneau = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

					}

					

		            if (!empty($creneau)) {

		            	

						$id_store_bdd = PrestatillDriveCreneau::getIdStoreByIdCreneau((int)$creneau['id_creneau']);

						

						if(!empty($id_store_bdd))

						{

							$id_store_original = (int)$id_store_bdd['id_store'];

						}

						

						// On vérifie si on est dans le cas d'une réservation lié au même id_store

						if(isset($context->cookie->msg) && $context->cookie->msg != '')

						{

							if($id_store_original == $id_store)

							{

								$msg = $context->cookie->msg;

								$reserved = true;

							}

							

							// 2.1.0 : On vérifie si le panier ne contient pas de produits avec un délai de préparation > au créneau choisit

							$check_creneau = new PrestatillDriveCreneau((int)$creneau['id_creneau']);

							if(Validate::isLoadedObject($check_creneau))

							{

								$delai_carence = $this->checkAllCarences($id_store);

								$delivery_date = $check_creneau->day.' '.$check_creneau->hour;

								

								$date = date('Y-m-d H:i:s');

		        				$date_carence = date('Y-m-d H:i:s', strtotime('+ '.$delai_carence.' minutes', strtotime($date)));

								

								// Si le créneau n'est plus valide

								if($delivery_date <= $date_carence)

								{

									$crenau_valid = false;

								}

							}

						}

						

		                PrestatillDriveCreneau::updateStoreByIdcreneau((int)$id_store, (int)$creneau['id_creneau'], 0, $reserved);

		            } else {

		                $creneau = new PrestatillDriveCreneau();

		                PrestatillDriveCreneau::updateStoreByIdcreneau((int)$id_store, 0);

		            }

		        }

				

				$slot_enabled = Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE');

				

				// On enregistre uniquement le magasin lorsqu'il correspond au choix du client

				if((int)$id_store != (int)$context->cookie->id_store)

				{

					$msg = null;

				}

				$context->cookie->__set('id_store', (int)$id_store);

	        	$context->cookie->write();				

		

		        $message['msg'] = $msg;

		        $message['store'] = new Store((int)$id_store);

		        $message['id_lang'] = version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0;

				$message['slot_enabled'] = $slot_enabled;

				$message['crenau_valid'] = $crenau_valid;

				

	        break;

			

			case 'assignSlot':

				

				$id_cart = (int)$context->cart->id;

				

				$slot = Tools::getValue('slot');

		        $id_day = (int)$slot['idDay'];

		        $hour = $slot['hour'];

		        //$date_msg = Tools::getValue('date');

		        $date = $slot['datetime'];



		        $id_week = strftime(strftime("%V"), strtotime($date));

		        if ($id_week < 10) {

		            $id_week = Tools::substr($id_week, -1);

		        }

				

				if($id_cart > 0)

				{

					// On vérifie si un créneau n'a pas déjà été réservé (cookie)

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION') && (int)$context->cookie->id_creneau > 0)

					{

						$id_creneau = (int)$context->cookie->id_creneau;

						$creneau = new PrestatillDriveCreneau((int)$id_creneau);

					}

					else

					{

						$result = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

						$creneau = new PrestatillDriveCreneau((int)$result['id_creneau']);

					}

					

				}

				// On est dans le cas d'une réservation ?

				else if(isset($context->cookie->id_creneau) && (int)$context->cookie->id_creneau > 0)

				{

					$id_creneau = (int)$context->cookie->id_creneau;

					if($id_creneau > 0)

					{

						$creneau = new PrestatillDriveCreneau((int)$id_creneau);

					}

				}

				else

				{

					$creneau = new PrestatillDriveCreneau();

				}



		        $response = array();

				

				// 2.0.0 : On récupère l'id_store du cookie s'il existe

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR') && $creneau->id_store == 0)

				{

					if(isset($context->cookie->id_store) && (int)$context->cookie->id_store > 0)

					{

						$id_store = (int)$context->cookie->id_store;

						if($creneau->id_store == 0)

							$creneau->id_store = (int)$id_store;

					}

				}

		

		        $creneau->id_day = (int)$id_day;

		        $creneau->hour = pSQL($hour);

		        $creneau->day = pSQL($date);

		        $creneau->id_week = (int)$id_week;

		        $creneau->id_cart = (int)$id_cart;				

				$creneau->save();

				

		        $id_creneau = (int)$creneau->id;

				

				$day_creneau = $creneau->day;

				$day = strftime('%d %B %Y', strtotime($day_creneau));

				$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

				$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

				$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

				

		        $msg_creneau = $day.' '.$this->module->l('beetween').' '.$hour_creneau.' '.$this->module->l('and').' '.$end_creneau;

				

		        $this->context->cookie->__set('msg', $msg_creneau);

				

				// 2.0.0 : On réserve le créneau pour X minutes s'il s'agit d'un nouveau ?

				

				

		        $this->context->cookie->__set('id_creneau', (int)$id_creneau);

		        $this->context->cookie->write();

		

		        $response = array(

		            'success' => true,

		            'msg' => $msg_creneau,

		            'id' => (int)$id_creneau,

		        );

				

			break;

			

			case 'assignSlotFromBO':

				$id_order = Tools::getValue('id_order');
				$id_store = Tools::getValue('id_store');
				$slot = Tools::getValue('slot');
		        $id_day = (int)$slot['idDay'];
		        $hour = $slot['hour'];

		        //$date_msg = Tools::getValue('date');

		        $date = $slot['datetime'];

				

		        $id_week = strftime(strftime("%V"), strtotime($date));

		        if ($id_week < 10) {

		            $id_week = Tools::substr($id_week, -1);

		        }

				

		        $creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$id_order);

				$response = array();

				if(!Validate::isLoadedObject($creneau))
				{
					$creneau = new PrestatillDriveCreneau();
					$creneau->id_order = (int)$id_order;
					$creneau->id_store = (int)$id_store;
				}

				

					$creneau->id_day = (int)$id_day;

			        $creneau->hour = pSQL($hour);

			        $creneau->day = pSQL($date);

			        $creneau->id_week = (int)$id_week;

			        $creneau->id_cart = 0;

			        $id_creneau = (int)$creneau->id_creneau;

					

					$day_creneau = $creneau->day;

					

					$day = strftime('%d %B %Y', strtotime($day_creneau));

					$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

					$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE');

					$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

					

			        $msg_creneau = $day.' '.$this->module->l('beetween').' '.$hour_creneau.' '.$this->module->l('and').' '.$end_creneau;

					

			        /* $this->context->cookie->__set('msg', $msg_creneau);

			        $this->context->cookie->__set('id_creneau', (int)$id_creneau);

			        $this->context->cookie->write();

					*/

			

			        $response = array(

			            'success' => true,

			            'msg' => $msg_creneau,

			            'id' => (int)$id_creneau,

			        );

					$creneau->save();

				
			break;

			

			case 'processCarrier':

				

				$id_cart = (int)$context->cart->id;

				

		        $tab_creneau = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

		        $creneau = new PrestatillDriveCreneau((int)$tab_creneau['id_creneau']);

		        

		        $delivery_date = $creneau->day.' '.$creneau->hour;

				if(Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO'))

				{

		        	$tableau_nb_orders = PrestatillDriveCreneau::countOrder($delivery_date, $creneau->id_store, true);

				}

				else 

				{

		        	$tableau_nb_orders = PrestatillDriveCreneau::countOrder($delivery_date, $creneau->id_store);

				}

				

		        $nb_orders = $tableau_nb_orders[0]['nb'];

		        $nb_orders_conf = Configuration::get('PRESTATILL_DRIVE_NB_DISPO');

				

				// 2.1.0 : check toutes les carences

		        $carence = $this->checkAllCarences($creneau->id_store);



				$slot_enabled = Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE');

		        $date = date('Y-m-d H:i:s');

		        $date_carence = date('Y-m-d H:i:s', strtotime('+ '.$carence.' minutes', strtotime($date)));

		        //d(array($delivery_date, $date_carence, $delivery_date<$date_carence?'inf':'sup'));	

		        $error = false;

				

		        if($slot_enabled == 1)	

			    {    

			        $response = array();

			        if ($nb_orders >= $nb_orders_conf) {

			            $error = true;			

			        } else if ($delivery_date <= $date_carence) {

			            $error = true;	

			        }

		        }

				

		        if ($error !=false) {

		            $message = array(

		                    'success' => false,

		                    'alert' => $error,

		                    'slot_enabled' => $slot_enabled,

		                    'nb_orders' => $nb_orders,

							'delivery_date' => $delivery_date,

							'date_carence' => $date_carence,

		                );			

		        } else {

		            $message = array(

		                    'success' => true,

		                    'slot_enabled' => $slot_enabled,

		                    'nb_orders' => $nb_orders,  

		                    );

				}        

				

			break;

			

			case 'addCarenceSupp':

		

				$params = array(

					'id_store' => Tools::getValue('id_store')?(int)Tools::getValue('id_store'):0,

					'id_day' => Tools::getValue('id_day')?(int)Tools::getValue('id_day'):0,

					'id_day_end' => Tools::getValue('id_day_end')?(int)Tools::getValue('id_day_end'):0,

					'hour_limit' => Tools::getValue('hour_limit')?Tools::getValue('hour_limit'):'00:00:00',

					'hour_limit_end' => Tools::getValue('hour_limit_end')?Tools::getValue('hour_limit_end'):'00:00:00',

					'waiting_time' => Tools::getValue('waiting_time')?Tools::getValue('waiting_time'):0,

					'id_lang' => Tools::getValue('id_lang'),

					'id_shop_group' => $context->shop->id_shop_group,

					'id_shop' => $context->shop->id, 

				);

				

				$context->cookie->__set('id_lang', Tools::getValue('id_lang'));

		        $context->cookie->write();

				

				$pdrive = new PrestatillDrive();

				$message['tpl'] = $pdrive->getCarrenceSupp($params);

				

				//@TODO: Si all_days : on supprime les autres entrées du store

		

				break;

				

			case 'deleteCarenceSupp':

				

				$params = array(

					'id_store' => Tools::getValue('id_store')?Tools::getValue('id_store'):0,

					'id_day' => Tools::getValue('id_day')?Tools::getValue('id_day'):0,

					'id_shop_group' => $context->shop->id_shop_group,

					'id_shop' => $context->shop->id,

					);

				

				$pdrive = new PrestatillDrive();

				$message['tpl'] = $pdrive->deleteCarrenceSupp($params);

				

			break;

				

			// 1.2.9

			case 'assignStoreFromBO':

		        $id_store = (int)Tools::getValue('store');
		        $id_order = (int)Tools::getValue('id_order');
		        if ((int)$id_order > 0) {
		            $creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$id_order);
		            if (!empty($creneau)) {
		                PrestatillDriveCreneau::updateStoreByIdcreneau((int)$id_store, (int)$creneau->id_creneau, $id_order);
		            } 
		        } else {
					PrestatillDriveCreneau::updateStoreByIdcreneau((int)$id_store, 0, 0, false, 1);
				}
				
				$slot_enabled = Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE',null,(int)$context->shop->id_shop_group,(int)$context->shop->id);
		
		        $message = array();
		        $message['store'] = new Store((int)$id_store);
		        $message['id_lang'] = version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0;
				$message['slot_enabled'] = $slot_enabled;

				

		    break;

			

			// Since 2.2.0

			case 'adminSetProductAvailability':

			

			$id_product = (int)Tools::getValue('id_product');

			$id_product_attribute = (int)Tools::getValue('id_product_attribute');

			$id_day = (int)Tools::getValue('id_day');

			$availability = (int)Tools::getValue('availability');

			

			// On met à jour la valeur dans la bdd

			if($id_product > 0 && $id_day > 0)

			{

				if($availability == 0)

				{

					$setAvailability = PrestatillDriveConfiguration::setAdditionnalAvailability($id_product, $id_product_attribute, $id_day);

				}

				else

				{

					$setAvailability = PrestatillDriveConfiguration::unsetAdditionnalAvailability($id_product, $id_product_attribute, $id_day);

				}

			}

			

			$message = array();

		    $message['update_ok'] = $setAvailability;

		    $message['availability'] = $availability;

			

			break;

		

			// New since 1.2.9

			case 'adminCreateSlot':

				

				$id_order = (int)Tools::getValue('id_order');

				

				$creneau = PrestatillDriveCreneau::getCreneauByIdOrder((int)$id_order);

				

				$send_email = (int)Tools::getValue('send_email');

				if(Validate::isLoadedObject($creneau)) {

			

					$msg_creneau = '';

							

					$day = strftime('%d %B %Y', strtotime($creneau->day));

					$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

					$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE',null,(int)$context->shop->id_shop_group,(int)$context->shop->id);

					$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes'));

					

			        $msg_creneau = $day.' '.$this->module->l('beetween').' '.$hour_creneau.' '.$this->module->l('and').' '.$end_creneau;

					

					$message = array(

		                'success' => true,

		                'message_creneau' => $msg_creneau,                   

		            );

					

					if($send_email == 1)

					{

						$pdDrive = new PrestatillDrive();

						$send_success = $pdDrive->sendEmailModification($creneau, (int)Tools::getValue('type'));

						

						if($send_success)

						{

							$message['email_sended'] = true;

						}

					}

				}

				

			break;

				

			case 'reInitStore':

				

				// 2.0.0 : On vérifie si un magasin est déjà sélectionné par le client (cookie)

				if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR'))

				{

					if(isset($context->cookie->id_store) && $context->cookie->id_store > 0)

						$message['store'] = (int)$context->cookie->id_store;

				}

				else 

				{

					$id_store = 0;

			        $id_cart = (int)Context::getContext()->cart->id;

			        if ((int)$id_cart > 0) {

			            $creneau = PrestatillDriveCreneau::getAllCreneauByIdCart((int)$id_cart);

			            if (!empty($creneau)) {

			                PrestatillDriveCreneau::updateStoreByIdcreneau(0, (int)$creneau['id_creneau']);

			            }

			        }

					

					$message['store'] = 0;

					

			        $context = Context::getContext();

			        $context->cookie->__set('id_store', 0);

					$context->cookie->__set('msg', '');

			        $context->cookie->write();

				}

		

			break;

			

			//1.4.0 : On affiche uniquement les stores rattachés au carrier

			case 'filterStoresByCarrier':

				

				$id_carrier = (int)Tools::getValue('id_carrier');

				$eligibles_stores = [];

				$drive_enabled = [];

				

				// on récupère les magasins en fonction de l'affichage où non du rayon		

				$pConfig = new PrestatillDriveConfiguration();

				$stores = $pConfig->getAllStores(true);

				

				// On conserve uniquement ceux associés au transporteur

				$pdc = new PrestatillDriveStoreCarrier();

				$eligibles_stores = $pdc->getStoresAssociatedToACarrier((int)$id_carrier);

				

				if(!empty($eligibles_stores))

				{

					$drive_enabled = array_intersect_key($stores, $eligibles_stores);

				}

				

				// On vérifie qu'ils sont élibles au drive

				if(!empty($drive_enabled))

				{

					foreach($drive_enabled as $store)

					{

						$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

						if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']) == null || empty($temp))

						{

							unset($drive_enabled[$store['id_store']]);

						}

					}

				}

				

				$nbr_stores = count($drive_enabled);

				

				$message['id_store'] = null;

				$message['id_creneau'] = null;

				$message['store'] = null;

				$message['nbr_stores'] = $nbr_stores;

				

				$force_reload = (int)Tools::getValue('force');

			

		        if (!empty($drive_enabled)) {

		        	

		            $pdrive = new PrestatillDrive();

					$message['tpl'] = $pdrive->getStoresList($drive_enabled);

					

					// 2.0.0 : On vérifie si un magasin est déjà sélectionné par le client (cookie)

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR') && $force_reload == 0)

					{

						if(isset($context->cookie->id_store) && $context->cookie->id_store > 0)

						{

							$id_store = (int)$context->cookie->id_store;

							

							// Uniquement si le magasin fait partie du transporteur sélectionné

							if(array_key_exists($id_store, $drive_enabled))

							{

								$message['id_store'] = (int)$context->cookie->id_store;

							

								$store = new Store((int)$context->cookie->id_store);

								if(Validate::isLoadedObject($store))

								{

									$message['store'] = $store;

								}

							}

						}

					}

					

					if(Configuration::get('PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION'))

					{

						if(isset($context->cookie->id_creneau) && $context->cookie->id_creneau > 0)

							$message['id_creneau'] = (int)$context->cookie->id_creneau;

					}

		        }

				else 

				{

					// Afficher un message si aucun store ne correspond

					$pdrive = new PrestatillDrive();

					$message['tpl'] = $pdrive->getStoresList($drive_enabled);				}

				

			break;

			

			// 2.0.0 

			case 'geolocCustomer':

				

				if(Tools::getValue('lat') && Tools::getValue('long'))

				{

					$lat = Tools::getValue('lat');

					$long = Tools::getValue('long');

					$pdc = new PrestatillDriveStoreCarrier();

		        	$stores = $pdc->getByLatLong($lat, $long);

					$drive_enabled = [];

					

					if(is_array($stores) && !empty($stores))

					{

						foreach($stores as $store)

						{

							$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

							// 1.4.0 : On vérifie si le store est rattaché à un carrier

							if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']) && !empty($temp))

							{

								$drive_enabled[] = $store;

							}

						}

					}

					else 

					{

						$drive_enabled = $stores;

					}

								

					$pdrive = new PrestatillDrive();

					$message['tpl'] = $pdrive->getStoresList($drive_enabled, '-modal');

				}

				else

				{

					$status = 'error';

					$message['tpl'] = $this->l('The localisation didn\'t work. Please enter a postcode or city name to show stores near you.', 'validateordercarrier');

				}

				

			break;

			

			case 'searchCP':

				

				$address = Tools::getValue('address');

				if(Validate::isPostCode($address) || Validate::isCityName($address))

				{

					// On recherche le/les boutiques avec le CP ou la ville

					$pdc = new PrestatillDriveStoreCarrier();

					$stores = $pdc->getByCPOrCityName($address);

					$drive_enabled = [];

					

					if(is_array($stores) && !empty($stores))

					{

						foreach($stores as $store)

						{

							$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

							// 1.4.0 : On vérifie si le store est rattaché à un carrier

							if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']) && !empty($temp))

							{

								$drive_enabled[] = $store;

							}

						}

					}

					else 

					{

						$drive_enabled = $stores;

					}

					

					if(!empty($drive_enabled))

					{				

						$pdrive = new PrestatillDrive();

						$message['tpl'] = $pdrive->getStoresList($drive_enabled, '-modal');

						$message['button'] = $this->module->l('Change store', 'validateordercarrier');

					}

					else 

					{

						// On essaye de rechercher avec la localisation ?

						$stores = $pdc->getByLatLong(null,null,$address);

						

						$drive_enabled = [];

						if(is_array($stores) && !empty($stores))

						{

							foreach($stores as $store)

							{

								$temp = PrestatillDriveStoreCarrier::getByIdStore((int)$store['id_store']);

								// 1.4.0 : On vérifie si le store est rattaché à un carrier

								if(Configuration::get('PRESTATILL_DRIVE_ENABLE_STORE_'.$store['id_store']) && !empty($temp))

								{

									$drive_enabled[] = $store;

								}

							}

						}

						else 

						{

							$drive_enabled = $stores;

						}

						

						if(!empty($drive_enabled))

						{

							$pdrive = new PrestatillDrive();

							$message['tpl'] = $pdrive->getStoresList($drive_enabled, '-modal');

							$message['button'] = $this->module->l('Change store', 'validateordercarrier');

						}

						else 

						{

							$status = 'error';

							$message['tpl'] = $this->module->l('There\'s no Store corresponding with your search items. Please try another.', 'validateordercarrier');

						}

					}

				}

				else

				{

					$status = 'error';

					$message['tpl'] = $this->l('There\'s no Store corresponding with your search items. Please try another.', 'validateordercarrier');

				}

				

			break;

			

			case 'selectStoreFroModal':

				

				$id_store = Tools::getValue('id_store');

				$selected_store = false;

				

				if($id_store > 0)

				{

					$my_store = PrestatillDriveConfiguration::getStore($id_store);

					if(!empty($my_store))

					{

						// Enregistrement de l'information dans le cookie

						$context->cookie->__set('id_store', (int)$id_store);

						$context->cookie->write();

					

						// On créé un nouveau créneau

						if(!isset($context->cookie->id_creneau) || $context->cookie->id_creneau == 0)

						{

							$creneau = new PrestatillDriveCreneau();

							$creneau->id_store = (int)$id_store;

				            $creneau->day = null;

							$creneau->hour = null;

							$creneau->save();

							

							$context->cookie->__set('id_creneau', (int)$creneau->id);

							$context->cookie->write();

						}

						else 

						{

							// On récupère s'il existe déjà

							$id_creneau = (int)$context->cookie->id_creneau;

							// @TODO : Faire une vérification sur l'id_cart et l'id_order pour voir si le créneau n'est pas déjà attribué

				            PrestatillDriveCreneau::updateStoreByIdcreneau((int)$id_store, (int)$id_creneau);

						}

						

						// On envoie l'information au client

						$pdrive = new PrestatillDrive();

						$message['tpl'] = $pdrive->getStoresList([$my_store], '-modal', true);

						$message['button'] = $this->module->l('Change store', 'validateordercarrier');

						$message['infos'] = $my_store['name'];

						

					}

				}

				

			break;

			

			case 'validateSlotReservation':

				

				$id_creneau = (int)$context->cookie->id_creneau;

				

				// On récupère l'id_cart :

				//$id_cart = (int)$context->cart->id;

				if($id_creneau > 0)

				{

					$creneau = new PrestatillDriveCreneau((int)$id_creneau);

				

					if(Validate::isLoadedObject($creneau)) {

				

						$msg_creneau = '';

								

						$day = strftime('%d %B %Y', strtotime($creneau->day));

						$hour_creneau = strftime("%H:%M", strtotime($creneau->hour));

						$creneau_duration = Configuration::get('PRESTATILL_DRIVE_DUREE',null,(int)$context->shop->id_shop_group,(int)$context->shop->id);

						$end_creneau = strftime("%H:%M", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes'));

						

				        $msg_creneau = $day.' '.$this->module->l('beetween').' '.$hour_creneau.' '.$this->module->l('and').' '.$end_creneau;

						

						// On détermine l'heure de réservation dans le cookie

						$date_now = strftime("%Y-%m-%d %H:%M:%S");

						$context->cookie->__set('drive_slot_reservation', $date_now);

						$context->cookie->__set('msg', $msg_creneau);

				        $context->cookie->write();

						

						$message = array(

			                'success' => true,

			                'message_creneau' => $msg_creneau,  

			                'id_creneau' => $id_creneau,                 

			                'id_store' => $context->cookie->id_store,                   

			                'duration' => (int)Configuration::get('PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION'),                   

			            );

						

					}

				}

				//dump([$context->cookie,$message]);

				//die();

				

			break;

			

			default:

		        $status = 'error';

		        $message = 'Unknown parameters!';

		    

		    exit;

			

		}

		

		$response['status'] = $status;

		$response['message'] = $message;



        header('Content-Type: application/json');

        echo(json_encode($response));

        die();

    }



	public static function getAllDaysOpen($id_store)

    {

        $request = 'SELECT * 

        	FROM '._DB_PREFIX_.'prestatill_drive 

        	WHERE openning = 1 

        	AND id_store = '.(int)$id_store.' AND id_shop = '.(int)Context::getContext()->shop->id.'

        	AND id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;

			

        $result = Db::getInstance()->executeS($request);

        return $result;

    }

	

	public function initTable($id_store, $init_bo = 0)

    {

        $table_days = array();

        $result = self::getAllDaysOpen($id_store);

		

		$nb_days_view = (int)Configuration::get('PRESTATILL_DRIVE_NB_DAY'); //14

		

		// 2.2.0 : On récupère les jours d'indisponibilité pour les produits du panier

		$id_cart = (int)Context::getContext()->cart->id;

		$cart = new Cart((int)$id_cart);

		$days_unavailable = [];

		

		if(Validate::isLoadedObject($cart))

		{

			// On récupère la liste des produits

			$products = $cart->getProducts();

			foreach($products as $product)

			{

				$product_availability = PrestatillDriveConfiguration::getAdditionnalAvailability($product['id_product']);	

				if(!empty($product_availability))

				{

					foreach($product_availability as $pa)

					{

						if(!in_array($pa['id_day'], $days_unavailable))

							$days_unavailable[] = $pa['id_day'];

					}

				}	

			}

		}



        foreach ($result as $res) {

            $table_days['open'][$res['id_day']] = $res;

        }

		

		// On désactive les jours d'indisponibilité

		if(!empty($days_unavailable))

		{

			foreach($days_unavailable as $day)

			{

				if(isset($table_days['open'][$day]))

					unset($table_days['open'][$day]);

			}

			

		}

		

        $date = strftime("%d %B %G");

        $dateen = date("Y-m-d");

        $dateday = strftime("%a");



        //loop on days

        $i = 1;

		

        while ($i <= $nb_days_view) {

            $table_days['days'][$i]['date'] = $date;

            $table_days['days'][$i]['dateen'] = $dateen;

            $table_days['days'][$i]['day'] = $dateday;

            $table_days['days'][$i]['id_day']= $this->getFullDateToLocales($dateday);

            $today = date("Y-m-d", strtotime('+'.$i.' days'));

            $today_day = date("D", strtotime('+'.$i.' days'));

            $date = strftime("%d %B %G", strtotime($today)); ////

            $dateen = date("Y-m-d", strtotime('+'.$i.' days'));

            $dateday = strftime("%a", strtotime($today_day));//date("D", strtotime('+'.$i.' days'));////

            $i++;

        }



        $days_dispo = array();

        $increment = Configuration::get('PRESTATILL_DRIVE_DUREE')*60;

        $nb_orders_conf = Configuration::get('PRESTATILL_DRIVE_NB_DISPO');

        $table_days['increment'] = Configuration::get('PRESTATILL_DRIVE_DUREE');

        $table_days['nb_days_view'] = $nb_days_view;

        foreach ($table_days['days'] as $id) {

            $date = strftime(strftime("%F"), strtotime($id['date']));



            $h = strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_OPEN'));



            $i = 1;

            while ($i <= $nb_days_view) {

                if (!isset($table_days['open'][$i]['id_day'])) {

                    $table_days['open'][$i]['id_day'] = array();

                }

                if ($id['id_day'] == $table_days['open'][$i]['id_day']) {

                    while ($h <= strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_CLOSE'))) {

                        if ($table_days['open'][$i]['nonstop'] == true) {

                            if ($h >= $table_days['open'][$i]['hour_open_am'] && $h < $table_days['open'][$i]['hour_close_pm']) {

                                $days_dispo[$id['date']][] = $h;

                            }

                        } else {

                            if ((($h >= $table_days['open'][$i]['hour_open_am']) && ($h < $table_days['open'][$i]['hour_close_am']))

                            || (($h >= $table_days['open'][$i]['hour_open_pm']) && ($h < $table_days['open'][$i]['hour_close_pm']))) {

                                $days_dispo[$id['date']][] = $h;

                            }

                        }

                        $h = $h+$increment;

                    }

                }

                $i++;

            }

        }



        //loop for hours

        $h = strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_OPEN'));

        $increment = Configuration::get('PRESTATILL_DRIVE_DUREE')*60;



        $h2 = strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_OPEN'));

        while ($h <= strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_CLOSE'))) {

            $table_days['hours'][] = date('H:i:s', $h);

            while ($h2 <= strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_CLOSE'))) {

                $table_days['creneau'][] = date('H:i:s', $h2);

                $h2 = $h2+$increment;

            }

            $h = $h+3600;

			/*

			if($h > strtotime($date.' '.Configuration::get('PRESTATILL_DRIVE_CLOSE')))

			{

				$table_days['hours'][] = date('H:i:s', $h2);

				$table_days['creneau'][] = date('H:i:s', $h2);

			}*/

        }



		



        $delai_carence = $this->checkAllCarences($id_store);

		

        // Calcul number of waiting time slots

        $creneau_carence = $delai_carence;//ceil($delai_carence/$duree_creneau);

        //Add waiting time slots to $table_days

        $table_days['creneau_carence'] = $creneau_carence ;

        $table_days['vacations'] = PrestatillDriveVacation::getAllVacation($id_store);

        

        // loop for reserved

        if(Configuration::get('PRESTATILL_DRIVE_NB_PRODUCTS_DISPO'))

		{

			$id_cart = (int)Context::getContext()->cart->id;

			$creneau_reserved = PrestatillDriveCreneau::getReservedCreneau((int)$id_store,$id_cart, true);

		}

		else 

		{

			$creneau_reserved = PrestatillDriveCreneau::getReservedCreneau((int)$id_store);

		}

        

        $table_days['reserved'] = null;

        if(!empty($creneau_reserved))

        {

            $table_days['reserved'] = $creneau_reserved;

        }

        $table_days['creneau_limit'] = (int)$nb_orders_conf;

        

        return $table_days;

    }



	private function checkAllCarences($id_store)

	{

		$delai_carence = Configuration::get('PRESTATILL_DRIVE_CARENCE');

		

		//1.3.0 : Carence supplémentaire en fonction du store, du jour et de l'heure

		if(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP'))

			$carence_supp = Tools::jsonDecode(Configuration::get('PRESTATILL_DRIVE_CARENCE_SUPP'),true);



		if(!empty($carence_supp))

		{

			$date_now = strftime("%a");

			$id_day_now = $this->getFullDateToLocales($date_now);

			$id_day_now==1?$id_day_prev=7:$id_day_prev=$id_day_now-1;

			$hour_now = date('H:i:s');



			foreach($carence_supp as $key => $carence)

			{

				// Carence AllDays

				if ($key == $id_store.'_0')

				{

					//@TODO : A tester : if(($hour_now >  $carence['hour_limit'] && $carence['hour_limit'] > $carence['hour_limit_end']) || ($hour_now > $carence['hour_limit'] && $hour_now < $carence['hour_limit_end'])) 

					if($hour_now > $carence['hour_limit'] || ($hour_now > $carence['hour_limit'] && $hour_now < $carence['hour_limit_end'])) 

					{

						$delai_carence += $carence['waiting_time'];

					}

				}

				

				// Carence Day by day

				if ($key == $id_store.'_'.$id_day_now && $key != $id_store.'_0')

				{

					if($carence['id_day'] == $carence['id_day_end'])

					{

						if($hour_now > $carence['hour_limit'] && $hour_now < $carence['hour_limit_end'])

						{

							$delai_carence += $carence['waiting_time'];

						}

					}

					else 

					{

						if($hour_now > $carence['hour_limit'])

						{

							$delai_carence += $carence['waiting_time'];

						}

					}

					

					// Check if it was carence day before

					if (isset($carence_supp[$id_store.'_'.$id_day_prev]))

					{

						if($hour_now > 0 && $hour_now < $carence['hour_limit_end'])

						{

							$delai_carence += $carence['waiting_time'];

						}

					}	

				}

			}

		}

		// UPDATE 2.0.1 : On récupère le panier pour voir si des produits sont en rupture

		if(Configuration::get('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION') > 0)

		{

			$id_cart = (int)Context::getContext()->cart->id;

			$cart = new Cart((int)$id_cart);

			if(Validate::isLoadedObject($cart))

			{

				// On récupère la liste des produits

				$products = $cart->getProducts();

				$carrence_add = 0;

				foreach($products as $product)

				{

					$qty = $product['quantity_available'];

					

					if($qty <= 0)

					{

						$carrence_temp = (int)Configuration::get('PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION');

						// On rallonge le délais

						if($carrence_temp > $carrence_add)

							$carrence_add = (int)$carrence_temp; 

					}

					

					$delai_carence += $carrence_add;

				}

			}

		}	



		// UPDATE 2.1.0 : On récupère le panier pour voir si des produits ont une carence supplémentaire

		$id_cart = (int)Context::getContext()->cart->id;

		$cart = new Cart((int)$id_cart);

		if(Validate::isLoadedObject($cart))

		{

			// On récupère la liste des produits

			$products = $cart->getProducts();

			// On initialise le délai de carence avec le délai actuel

			$carrence_add = $delai_carence;

			foreach($products as $product)

			{

				$temp_carence_supp = 0;

				$actual_carence_supp = PrestatillDriveConfiguration::getAdditionnalCarence($product['id_product']);	

				if(!empty($actual_carence_supp))

					$temp_carence_supp = (int)$actual_carence_supp['carence_supp']*60; // Conversion en min	

						

				if($temp_carence_supp > 0)

				{

					// On rallonge le délais

					if($temp_carence_supp > $carrence_add)

						$delai_carence = (int)$temp_carence_supp; 

				}

			}

		}



		// UPDATE 2.2.0 : Ajout d'une carence supplémentaire basé sur les catégories d'un produit

		if(Validate::isLoadedObject($cart))

		{

			// On récupère la liste des produits

			$products = $cart->getProducts();

			

			foreach($products as $product)

			{

				$p = new Product((int)$product['id_product']);

				if(Validate::isLoadedObject($p))

				{

					$categories = $p->getCategories();

					if(!empty($categories))

					{

						// On vérifie pour chaque catégorie s'il y a un délais de carence

						foreach($categories as $category)

						{

							// On vérifie si une entrée existe déjà :

							$category_exist = PrestatillDriveCategoryAssociation::getCategoryDelaySupp((int)$category);

							

							if(!empty($category_exist))

							{

								// On retient le délais de carence le plus élevé

								if($category_exist['delay_supp'] > $delai_carence)

									$delai_carence = $category_exist['delay_supp'];

							}

						}

					}

					

				}

			}

		}

		

		return $delai_carence;

	}



	public function getFullDateToLocales($date)

    {

        $pDrive = new PrestatillDrive();

        $days = $pDrive->getDays();

        

        foreach ($days as $from => $to) {

            $date = str_replace($from, $to, $date);

        }

        if(is_numeric($date))

        {

            return $date;        

        }

        else 

        {

            $days = $pDrive->getDays(true);

            foreach ($days as $from => $to) {

                $date = str_replace($from, $to, $date);

            }

            

            return $date;

        }

    }

}