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

class AdminPrestatillDrivePickingListController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'order_detail';
        $this->className = 'OrderDetail';
        $this->bootstrap = true;
        $this->lang  = false;
		$this->allow_export = true;
		$this->list_no_link = true;
		parent::__construct();
		
		$id_shop = (int)Context::getContext()->shop->id;
		
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
		{
			$this->_select = 'o.id_order, 
        					IF(image_shop.id_image IS NOT NULL, image_shop.id_image,"0") AS id_image, 
        					o.reference, a.product_reference, 
        					a.product_supplier_reference, 
        					SUM(a.product_quantity) as product_quantity, 
        					a.total_price_tax_excl, 
        					a.total_price_tax_incl,
        					a.unit_price_tax_excl, 
        					a.unit_price_tax_incl,
        					cl.name as cat_name, 
        					a.product_id, 
        					a.product_attribute_id, 
        					a.product_name, 
        					sl.name, 
        					sl.id_store,  
        					pdc.day AS day, 
        					pdc.hour AS hour, 
        					o.current_state, 
        					os.name AS state';
		}
		else 
		{
			$this->_select = 'o.id_order, 
		        					IF(image_shop.id_image IS NOT NULL, image_shop.id_image,"0") AS id_image, 
		        					o.reference, a.product_reference, 
		        					a.product_supplier_reference, 
		        					SUM(a.product_quantity) as product_quantity, 
		        					a.total_price_tax_excl, 
		        					a.total_price_tax_incl,
		        					a.unit_price_tax_excl, 
		        					a.unit_price_tax_incl,
		        					cl.name as cat_name, 
		        					a.product_id, 
		        					a.product_attribute_id, 
		        					a.product_name, 
		        					s.name, 
		        					s.id_store,  
		        					pdc.day AS day, 
		        					pdc.hour AS hour, 
		        					o.current_state, 
		        					os.name AS state';
		}
        
							
							
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order) AND a.id_shop = '.(int)$id_shop.'
        				LEFT JOIN '._DB_PREFIX_.'shop shop ON (shop.id_shop = '.(int)$id_shop.' AND shop.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.')
                        LEFT JOIN '._DB_PREFIX_.'order_state_lang os ON (os.id_order_state = o.current_state) AND os.id_lang ='.(int)Context::getContext()->language->id.'
                        LEFT JOIN '._DB_PREFIX_.'prestatill_drive_creneau pdc ON (pdc.id_order = o.id_order)
                        LEFT JOIN '._DB_PREFIX_.'store s ON (s.id_store = pdc.id_store)
                        LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = a.product_id)
						LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = p.id_category_default AND cl.id_lang ='.(int)Context::getContext()->language->id.' AND cl.id_shop = '.(int)$id_shop.')
                        LEFT JOIN '._DB_PREFIX_.'image_shop image_shop ON (image_shop.id_product = p.id_product AND image_shop.cover = 1 AND image_shop.id_shop = '.(int)$id_shop.')
						LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_image = image_shop.id_image)';
		
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
		{
			$this->_join .= ' LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (sl.id_store = pdc.id_store) AND sl.id_lang ='.(int)Context::getContext()->language->id;
		}
		
		$objetPD = new PrestatillDrive;
        $arrayConfigPD = $objetPD->getConfigFieldsValues();

        //afficher seulement les statuts présents dans la conf ($ids)
        if(!empty($arrayConfigPD[0])) 
        {
        	$this->_where .= ' AND os.id_order_state IN ('.implode(",", $arrayConfigPD).') AND pdc.id_creneau > 0 AND s.active = 1 ';    
        }
		else 
		{
        	$this->_where .= ' AND pdc.id_creneau > 0 AND s.active = 1 ';    
		}

		$this->_where .= ' AND o.id_shop = '.(int)Context::getContext()->shop->id.' AND o.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;

		$this->_group = ' GROUP BY pdc.day,a.product_attribute_id,a.product_id';
		
		$this->_orderBy = 'pdc.day';
		$this->_orderWay = 'ASC';
		
        $this->fields_list = array(
            'product_id' => array(
                'title' => $this->l('ID Product'),
                'align' => 'center',
            ),
            'id_image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                //'callback' => 'addProductImg',
                //'callback_object' => $this,
      		),
            'product_reference' => array(
                'title' => $this->l('Product Ref.'),
                'align' => 'center',
            ),
            'product_supplier_reference' => array(
                'title' => $this->l('Supplier Ref.'),
                'align' => 'center',
            ),
        	'product_name' => array(
                'title' => $this->l('Product Name'),
                'align' => 'left',
			    'filter_key' => 'od!product_name',
            ),
            'product_quantity' => array(
                'title' => $this->l('Qty'),
                'align' => 'center',
			    'filter_key' => 'product_quantity',
			    'search' => false
            ),
            'cat_name' => array(
                'title' => $this->l('Category'),
                'align' => 'center',
			    'filter_key' => 'cl!name'
            ),
            /*'product_quantity_in_stock' => array(
                'title' => $this->l('Qty in stock'),
                'align' => 'center',
			    'filter_key' => 'od!product_quantity_in_stock',
            ),
            'total_price_tax_excl' => array(
                'title' => $this->l('Total HT'),
                'align' => 'center',
                'type' => 'price',
            ),*/
            'unit_price_tax_incl' => array(
                'title' => $this->l('PU TTC'),
                'align' => 'center',
                'type' => 'price',  
            ),
            'day' => array(
                'title' => $this->l('Collect day'),
                'align' => 'center',
                'type' => 'date',
                'filter_key' => 'pdc!day',
            ),
            /*
            'hour' => array(
                'title' => $this->l('Collect hour'),
                'align' => 'center',
                'type' => 'hour',
            ),*/
        );   
    }

	public function initPageHeaderToolbar()
    {
    	/*
        $this->page_header_toolbar_btn['generate_pdf'] = array(
            'href' => self::$currentIndex.'&token='.$this->token,
            'desc' => $this->l('Generate PDF', null, null, false),
            'icon' => 'process-icon-save-date'
        );*/
		
		$this->page_header_toolbar_btn['print'] = array(
            'href' => 'javascript:window.print()',
            'desc' => $this->l('Print'),
            'icon' => 'process-icon-hey icon-print'
        );
		
		$this->page_header_toolbar_btn['exportorder'] = array(
            'href' => self::$currentIndex.'&token='.$this->token.'&exportorder',
            'desc' => $this->l('Export CSV'),
            'icon' => 'process-icon-export'
        );

        parent::initPageHeaderToolbar();
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

	public function addProductImg($value)
    {
    	/*
    	if($value > 0)
		{
			$img = new Image((int)$value);
			$prod = new Product($img->id_product);
			$link = new Link();
			$img_link = $link->getImageLink($prod->link_rewrite[Context::getContext()->language->id],$img->id_product.'-'.$img->id_image, ImageType::getFormatedName('small'));
	    	return '<img src="//'.$img_link.'" width="50">';
		}
		else 
		{
			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
	    	return '<img src="'.$base_dir.'/img/p/'.Context::getContext()->language->iso_code.'-default-'.ImageType::getFormatedName('small').'.jpg" width="50">';
		}
    	*/
    }
}
