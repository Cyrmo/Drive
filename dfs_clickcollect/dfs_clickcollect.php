<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Dfs_Clickcollect extends Module
{
    public function __construct()
    {
        $this->name = 'dfs_clickcollect';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Cyrille Mohr - Digital Food System';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('DFS Click & Collect');
        $this->description = $this->l('Manage and optimize your delivery schedule according to your stores, opening days & hours.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayCarrierExtraContent')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('displayAdminOrderTabContent')
            && $this->registerHook('displayAdminOrderContentShip')
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('displayInvoiceLegalFreeText')
            && $this->registerHook('displayPDFInvoice')
            && $this->registerHook('actionOrderGridDefinitionModifier')
            && $this->registerHook('actionOrderGridQueryBuilderModifier')
            && $this->registerHook('sendMailAlterTemplateVars')
            && $this->_installTab('ShopParameters', 'AdminDfsClickCollect', $this->l('Click & Collect'));
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall()
            && $this->_uninstallTab('AdminDfsClickCollect');
    }

    private function _installTab($parent, $className, $name)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->module = $this->name;
        return $tab->add();
    }

    private function _uninstallTab($className)
    {
        $idTab = (int)Tab::getIdFromClassName($className);
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayHeader()
    {
        $carrier_id = (int) Configuration::get('DFS_DRIVE_CARRIER_ID');
        if (!$carrier_id) return;
        
        $this->context->controller->registerJavascript(
            'modules-dfsclickcollect-front',
            'modules/'.$this->name.'/views/js/front.js',
            ['position' => 'bottom', 'priority' => 150]
        );
        $this->context->controller->registerStylesheet(
            'modules-dfsclickcollect-front-css',
            'modules/'.$this->name.'/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );
        
        $active_carrier = Carrier::getCarrierByReference($carrier_id);
        $real_carrier_id = $active_carrier ? (int) $active_carrier->id : $carrier_id;

        Media::addJsDef([
            'dfs_ajax_link' => $this->context->link->getModuleLink($this->name, 'ajax', array(), true),
            'dfs_trigger_carrier' => $real_carrier_id . ','
        ]);
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        $dfs_carrier_id = (int) Configuration::get('DFS_DRIVE_CARRIER_ID');

        if (!isset($params['carrier'])) {
            return '';
        }

        $id_ref = is_array($params['carrier']) ? (int) $params['carrier']['id_reference'] : (int) $params['carrier']->id_reference;
        if ($id_ref !== $dfs_carrier_id) {
            return '';
        }

        return $this->getClickCollectHtml();
    }

    public function getClickCollectHtml()
    {
        $dfs_carrier_id = (int) Configuration::get('DFS_DRIVE_CARRIER_ID');
        if (!$dfs_carrier_id) return '';

        $cart = $this->context->cart;
        $current_selection = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_cart` WHERE `id_cart` = ' . (int) $cart->id);

        require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Service/StoreService.php';
        $storeService = new \DigitalFoodSystem\DfsClickCollect\Service\StoreService();
        $stores = $storeService->getActiveStores();

        $this->context->smarty->assign([
            'dfs_stores' => $stores,
            'dfs_selected_store' => $current_selection ? $current_selection['id_store'] : '',
            'dfs_selected_date' => $current_selection ? $current_selection['day'] : '',
            'dfs_selected_time' => $current_selection ? $current_selection['hour'] : '',
            'dfs_ajax_link' => $this->context->link->getModuleLink('dfs_clickcollect', 'ajax', array(), true),
            'dfs_trigger_carrier_id' => $dfs_carrier_id
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayCarrierExtraContent.tpl');
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart  = $params['cart'];

         // Important PS1.7 fix: get actual reference match if active ID differs
        $active_carrier = Carrier::getCarrierByReference((int) Configuration::get('DFS_DRIVE_CARRIER_ID'));
        $match = $active_carrier ? ((int)$order->id_carrier === (int)$active_carrier->id) : ((int)$order->id_carrier === (int) Configuration::get('DFS_DRIVE_CARRIER_ID'));
        
        if (!$match) {
            return; // Not click and collect logic
        }

        $selection = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_cart` WHERE `id_cart` = ' . (int) $cart->id);
        if ($selection) {
            Db::getInstance()->insert('dfs_clickcollect_creneau', [
                'id_order' => (int) $order->id,
                'id_store' => (int) $selection['id_store'],
                'day'      => pSQL($selection['day']),
                'hour'     => pSQL($selection['hour']),
            ]);
        }
    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition->getColumns()->addAfter(
            'payment',
            (new \PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('dfs_pickup_date'))
                ->setName($this->l('Click & Collect'))
                ->setOptions([
                    'field' => 'dfs_pickup_date',
                ])
        );

        $definition->getFilters()->add(
            (new \PrestaShop\PrestaShop\Core\Grid\Filter\Filter('dfs_pickup_date', \PrestaShopBundle\Form\Admin\Type\DateRangeType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('dfs_pickup_date')
        );
    }

    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        $searchQueryBuilder->leftJoin(
            'o',
            _DB_PREFIX_ . 'dfs_clickcollect_creneau',
            'dcc',
            'o.id_order = dcc.id_order'
        );

        $searchQueryBuilder->addSelect('CONCAT(DATE_FORMAT(dcc.day, "%d-%m-%Y"), " ", dcc.hour) AS dfs_pickup_date');

        if (!empty($params['search_criteria']->getFilters()['dfs_pickup_date'])) {
            $dateFilter = $params['search_criteria']->getFilters()['dfs_pickup_date'];
            if (isset($dateFilter['from']) && $dateFilter['from']) {
                $from_date = $dateFilter['from'];
                if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $from_date, $matches)) {
                    $from_date = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
                $searchQueryBuilder->andWhere('dcc.day >= :date_from');
                $searchQueryBuilder->setParameter('date_from', $from_date);
            }
            if (isset($dateFilter['to']) && $dateFilter['to']) {
                $to_date = $dateFilter['to'];
                if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $to_date, $matches)) {
                    $to_date = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
                $searchQueryBuilder->andWhere('dcc.day <= :date_to');
                $searchQueryBuilder->setParameter('date_to', $to_date);
            }
        }
    }

    public function hookSendMailAlterTemplateVars(&$params)
    {
        $id_order = 0;
        if (isset($params['template_vars']['{id_order}'])) {
            $id_order = (int) $params['template_vars']['{id_order}'];
        } elseif (isset($params['template_vars']['{order_name}'])) {
            $id_order = (int) Order::getByReference($params['template_vars']['{order_name}'])->id;
        }

        if (!$id_order) {
            return;
        }

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $active_carrier = Carrier::getCarrierByReference((int) Configuration::get('DFS_DRIVE_CARRIER_ID'));
        $match = $active_carrier ? ((int)$order->id_carrier === (int)$active_carrier->id) : ((int)$order->id_carrier === (int) Configuration::get('DFS_DRIVE_CARRIER_ID'));
        if (!$match) {
             return;
        }

        $selection = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE `id_order` = ' . (int) $id_order);
        if (!$selection) {
             return;
        }

        $store = new Store($selection['id_store']);
        if (!Validate::isLoadedObject($store)) {
            return;
        }

        $date_formatted = date('d/m/Y', strtotime($selection['day']));
        $time_formatted = $selection['hour'];

        $drive_html = '<div style="margin-bottom: 20px; border: 1px solid #e6e6e6; padding: 15px;">';
        $drive_html .= '<h3 style="margin-top: 0; color: #333333;">Détails de votre <span style="color:#2cb1c1;">Click & Collect</span></h3>';
        $drive_html .= '<strong>Magasin: </strong> ' . $store->name . '<br />';
        $drive_html .= '<strong>Adresse: </strong> ' . $store->address1 . ', ' . $store->city . '<br />';
        $drive_html .= '<strong>Date de retrait: </strong> <span style="color:#2cb1c1; font-weight:bold;">' . $date_formatted . '</span><br />';
        $drive_html .= '<strong>Heure: </strong> <span style="color:#2cb1c1; font-weight:bold;">' . $time_formatted . '</span>';
        $drive_html .= '</div>';

        if (isset($params['template_vars']['{delivery_block_html}'])) {
            $params['template_vars']['{delivery_block_html}'] .= $drive_html;
        }
        if (isset($params['template_vars']['{bankwire_owner}'])) {
            $params['template_vars']['{bankwire_details}'] .= $drive_html;
        }
        if (isset($params['template_vars']['{check_name}'])) {
            $params['template_vars']['{check_address_html}'] .= $drive_html;
        }
    }

    public function hookDisplayAdminOrderMain($params)
    {
        $id_order = (int) $params['id_order'];
        $selection = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE `id_order` = ' . $id_order);
        if (!$selection) return '';

        require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Service/StoreService.php';
        $storeService = new \DigitalFoodSystem\DfsClickCollect\Service\StoreService();
        $stores = $storeService->getActiveStores();

        $store_name = '';
        foreach ($stores as $s) {
            if ($s['id_store'] == $selection['id_store']) {
                $store_name = $s['name'];
            }
        }
        $selection['store_name'] = $store_name;

        $this->context->smarty->assign([
            'dfs_slot' => $selection,
            'id_order' => $id_order,
            'dfs_all_stores' => $stores,
            'dfs_ajax_url' => $this->context->link->getAdminLink('AdminDfsClickCollectAjax')
        ]);
        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrderMainBottom.tpl');
    }

    public function hookDisplayAdminOrderContentShip($params)
    {
        // Retro-compat PS 1.7.6+
        return $this->hookDisplayAdminOrderMain($params);
    }

    public function hookDisplayInvoiceLegalFreeText($params) 
    {
        $id_order = isset($params['order']->id) ? (int)$params['order']->id : 0;
        if (!$id_order) {
            // Alternatively if passed directly
            if (isset($params['object']) && isset($params['object']->id_order)) {
                $id_order = (int)$params['object']->id_order;
            }
        }
        if (!$id_order) return '';
        
        $selection = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE `id_order` = ' . $id_order);
        if (!$selection) return '';
        
        $store = new Store((int)$selection['id_store']);
        $day_formatted = date('d/m/Y', strtotime($selection['day']));

        $this->context->smarty->assign([
            'dfs_store_name' => $store->name,
            'dfs_store_address' => $store->address1 . ', ' . $store->city,
            'dfs_date' => $day_formatted,
            'dfs_hour' => $selection['hour']
        ]);
        return $this->display(__FILE__, 'views/templates/hook/displayPDFInvoice.tpl');
    }
    
    public function hookDisplayPDFInvoice($params)
    {
        return $this->hookDisplayInvoiceLegalFreeText($params);
    }
}
