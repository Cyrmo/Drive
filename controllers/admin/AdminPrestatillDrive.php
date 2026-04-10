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

require_once(dirname(_PS_MODULE_DIR_).'/modules/prestatilldrive/prestatilldrive.php');

class AdminPrestatillDriveController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'order';
        $this->className = 'Order';
        $this->bootstrap = true;
        $this->lang  = false;
        $this->addRowAction('view');
		$this->allow_export = true;
		
		parent::__construct();
		
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
		{
        	$this->_select = 'a.id_order, os.color, sl.name, sl.id_store, c.firstname AS firstname, c.lastname AS lastname, pdc.day AS day, pdc.hour AS hour, a.current_state, osl.name AS state,
                CONCAT(LEFT(c.firstname,1), \'. \', c.lastname) customer';
		}
		else 
		{
			$this->_select = 'a.id_order, os.color, s.name, s.id_store, c.firstname AS firstname, c.lastname AS lastname, pdc.day AS day, pdc.hour AS hour, a.current_state, osl.name AS state,
                CONCAT(LEFT(c.firstname,1), \'. \', c.lastname) customer';
		}
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer = a.id_customer)
						LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
                        LEFT JOIN '._DB_PREFIX_.'order_state_lang osl ON (osl.id_order_state = a.current_state) AND osl.id_lang ='.(int)Context::getContext()->language->id.'
                        LEFT JOIN '._DB_PREFIX_.'prestatill_drive_creneau pdc ON (pdc.id_order = a.id_order)
                        LEFT JOIN '._DB_PREFIX_.'store s ON (s.id_store = pdc.id_store)';
						
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
		{
			$this->_join .= ' LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (sl.id_store = pdc.id_store) AND sl.id_lang ='.(int)Context::getContext()->language->id;
		}

        $this->fields_list = array(
        	'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
            ),
        	'name' => array(
                'title' => $this->l('Store'),
                'align' => 'center',
			    'filter_key' => 'sl!name',
            ),
            'day' => array(
                'title' => $this->l('Collect day'),
                'align' => 'center',
                'type' => 'date',
                'filter_key' => 'pdc!day',
            ),
            'hour' => array(
                'title' => $this->l('Collect hour'),
                'align' => 'center',
                'type' => 'hour',
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'align' => 'center',
                'filter_key' => 'c!lastname',
            ),
            'state' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'color' => 'color',
                'filter_key' => 'osl!name',
            ),

        );

        $objetPD = new PrestatillDrive;
        $arrayConfigPD = $objetPD->getConfigFieldsValues();
		
        //afficher seulement les statuts présents dans la conf ($ids)
        if(!empty($arrayConfigPD[0])) 
        {
        	$this->_where .= ' AND osl.id_order_state IN ('.implode(",", $arrayConfigPD).') AND pdc.id_creneau > 0 AND s.active = 1 ';    
        }
		else 
		{
        	$this->_where .= ' AND pdc.id_creneau > 0 AND s.active = 1 ';    
		}
		
		$this->_where .= ' AND a.id_shop = '.(int)Context::getContext()->shop->id.' AND a.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;
		
		$this->_orderBy = 'pdc.day';
		$this->_orderWay = 'ASC';    
    }

    public function initPageHeaderToolbar() 
	{
		if (empty($this->display))
			$this->page_header_toolbar_btn['new_slot'] = array(
				'href' => self::$currentIndex.'&addorder&token='.$this->token,
				'desc' => $this->l('Add a manual slot', null, null, false),
				'icon' => 'process-icon-new'
			);

		parent::initPageHeaderToolbar();
	}


    public function initToolbar()
    {

        if ($this->display == 'view') {
            $id_order = Tools::getValue('id_order');
            $order = new Order((int)$id_order);
            if (Validate::isLoadedObject($order)) {
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.(int)$id_order);
            }
        }
        return parent::initToolbar();
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/admin/'.$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/admin/'.$tpl_name, $this->context->smarty);
        } elseif (file_exists($this->getTemplatePath().$this->override_folder.$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate($this->getTemplatePath().$this->override_folder.$tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }

    /**
     * Get path to back office templates for the module
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/';
    }

    public function renderForm()
    {
        $error = false;

        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/jquery-dateFormat.js');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin-order-hook.js');
        
        // Add CSS
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin-order-hook.css');
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/config.css');
        
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
        
        //$this->setTemplate('add_creneau.tpl');
        $pConfig = new PrestatillDriveConfiguration(true);
        $stores = $pConfig->getAllStores();

        $tpl = $this->context->smarty->createTemplate(
            dirname(__FILE__).'/../../views/templates/admin/add_creneau.tpl'
        );

        $tpl->assign(array(
            'stores' => $stores, 
            'base_dir' => $base_dir,
            'id_store' => 0,
            'creneau' => null,
        ));
        
        parent::renderForm();
        unset($this->toolbar_btn['save']);

        $tpl->assign(array(
            'stores' => $stores, 
            'base_dir' => $base_dir,
            'id_store' => 0,
            'creneau' => null,

        ));

        $this->content .= $tpl->fetch();
    }

    public function postProcess()
    {

        parent::postProcess();

    }
	
}
