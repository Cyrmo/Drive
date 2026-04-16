<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

use DigitalFoodSystem\DfsClickCollect\Model\DriveStore;
use DigitalFoodSystem\DfsClickCollect\Model\DriveVacation;
use DigitalFoodSystem\DfsClickCollect\Service\StoreService;

class AdminDfsClickCollectController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        if (!$this->module) {
            $this->module = Module::getInstanceByName('dfs_clickcollect');
        }
        $this->meta_title = $this->module->l('Click & Collect Configuration');
        $this->page_header_toolbar_title = $this->module->l('Click & Collect Configuration');
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->module->l('Click & Collect Configuration');
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_title = $this->module->l('Click & Collect Configuration');
        $this->context->smarty->assign('page_header_toolbar_title', $this->page_header_toolbar_title);
    }

    public function postProcess()
    {
        $id_shop = (int) $this->context->shop->id;

        // Settings save
        if (Tools::isSubmit('submitDfsSettings')) {
            Configuration::updateValue('DFS_DRIVE_DUREE', (int)Tools::getValue('DFS_DRIVE_DUREE'));
            Configuration::updateValue('DFS_DRIVE_CARRIER_ID', (int)Tools::getValue('DFS_DRIVE_CARRIER_ID'));
            $this->confirmations[] = $this->module->l('Paramètres généraux mis à jour.');
        }

        // Store Planning Save
        if (Tools::isSubmit('submitStorePlanning')) {
            $id_store = (int) Tools::getValue('id_store_planning');
            $days = Tools::getValue('days'); // Array format
            
            if ($id_store && is_array($days)) {
                // Clear existing for this store + shop to insert new ones
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_store` WHERE `id_store` = ' . $id_store . ' AND `id_shop` = ' . $id_shop);
                
                foreach ($days as $id_day => $data) {
                    $openning = isset($data['openning']) ? 1 : 0;
                    $nonstop = isset($data['nonstop']) ? 1 : 0;
                    $delay = isset($data['delay']) ? (int)$data['delay'] : 0;
                    
                    require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Model/DriveStore.php';
                    $storeDay = new DriveStore();
                    $storeDay->id_store = $id_store;
                    $storeDay->id_shop = $id_shop;
                    $storeDay->id_day = (int) $id_day;
                    $storeDay->day = $this->getDayName($id_day);
                    $storeDay->openning = $openning;
                    $storeDay->nonstop = $nonstop;
                    $storeDay->hour_open_am = pSQL($data['am_open']);
                    $storeDay->hour_close_am = pSQL($data['am_close']);
                    $storeDay->hour_open_pm = pSQL($data['pm_open']);
                    $storeDay->hour_close_pm = pSQL($data['pm_close']);
                    $storeDay->delay = $delay;
                    
                    $storeDay->add();
                }
                
                $this->confirmations[] = $this->module->l('Planning du magasin enregistré avec succès.');
            }
        }
        
        // Add Vacation Save
        if (Tools::isSubmit('submitAddVacation')) {
            require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Model/DriveVacation.php';
            $vacation = new DriveVacation();
            $vacation->id_store = (int) Tools::getValue('id_store_vacation');
            $vacation->id_shop = $id_shop;
            $vacation->vacation_start = pSQL(Tools::getValue('vacation_start'));
            $vacation->vacation_end = pSQL(Tools::getValue('vacation_end'));
            
            if ($vacation->add()) {
                $this->confirmations[] = $this->module->l('Fermeture exceptionnelle ajoutée avec succès.');
            } else {
                $this->errors[] = $this->module->l('Erreur lors de l\'ajout de la fermeture.');
            }
        }
        
        // Delete Vacation
        if (Tools::isSubmit('deletevacation')) {
            require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Model/DriveVacation.php';
            $id_vacation = (int) Tools::getValue('id_vacation');
            $vacation = new DriveVacation($id_vacation);
            if (Validate::isLoadedObject($vacation) && $vacation->id_shop == $id_shop) {
                $vacation->delete();
                $this->confirmations[] = $this->module->l('Fermeture supprimée avec succès.');
            }
        }

        parent::postProcess();
    }
    
    public function ajaxProcessUpdateSlot()
    {
        $id_order = (int) Tools::getValue('id_order');
        $new_store = (int) Tools::getValue('dfs_new_store');
        $new_date = pSQL(Tools::getValue('dfs_new_date'));
        $new_time = pSQL(Tools::getValue('dfs_new_time'));

        if (!$id_order || !$new_store || !$new_date || !$new_time) {
            die(json_encode([
                'success' => false,
                'message' => $this->module->l('Paramètres invalides.')
            ]));
        }

        $sql = 'SELECT id_creneau FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE id_order = ' . $id_order;
        $id_creneau = (int) Db::getInstance()->getValue($sql);

        if ($id_creneau) {
            $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                          SET id_store = ' . $new_store . ', day = "' . $new_date . '", hour = "' . $new_time . '", date_upd = NOW() 
                          WHERE id_creneau = ' . $id_creneau;
                          
            if (Db::getInstance()->execute($updateSql)) {
                die(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Créneau mis à jour avec succès.')
                ]));
            }
        } else {
            $insertSql = 'INSERT INTO `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                          (id_order, id_store, id_shop, day, hour, manual_slot, date_add, date_upd) 
                          VALUES (' . $id_order . ', ' . $new_store . ', ' . (int)$this->context->shop->id . ', "' . $new_date . '", "' . $new_time . '", 1, NOW(), NOW())';
                          
            if (Db::getInstance()->execute($insertSql)) {
                die(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Nouveau créneau créé et associé avec succès.')
                ]));
            }
        }

        die(json_encode([
            'success' => false,
            'message' => $this->module->l('Erreur lors de la mise à jour.')
        ]));
    }

    public function initContent()
    {
        parent::initContent();

        $id_shop = (int) $this->context->shop->id;
        
        // 1. Get stores data
        require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Service/StoreService.php';
        $storeService = new StoreService();
        // Since we want ALL stores here to configure them (not just active active for FO), we fetch PS stores.
        $storesRaw = Store::getStores($this->context->language->id);
        
        // Filter those active in current shop context
        $stores = [];
        foreach ($storesRaw as $s) {
            $stores[] = [
                'id_store' => $s['id_store'],
                'name' => $s['name'],
                'city' => $s['city']
            ];
        }

        // 2. Determine if a store is selected for planning configuration
        $selected_store_id = (int) Tools::getValue('configure_store');
        $store_planning = [];
        if ($selected_store_id) {
            // Load existing config
            for ($i = 1; $i <= 7; $i++) {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_store` WHERE `id_store` = ' . $selected_store_id . ' AND `id_shop` = ' . $id_shop . ' AND `id_day` = ' . $i;
                $row = Db::getInstance()->getRow($sql);
                if ($row) {
                    $row['name'] = $this->getDayName($i);
                    $store_planning[$i] = $row;
                } else {
                    // Default configuration
                    $store_planning[$i] = [
                        'openning' => 1,
                        'name' => $this->getDayName($i),
                        'hour_open_am' => '08:30',
                        'hour_close_am' => '12:00',
                        'hour_open_pm' => '13:30',
                        'hour_close_pm' => '18:30',
                        'nonstop' => 0
                    ];
                }
            }
        }

        // 3. Vacations
        $vacationsSql = 'SELECT v.*, sl.name as store_name FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_vacation` v 
                         LEFT JOIN `' . _DB_PREFIX_ . 'store_lang` sl ON (v.id_store = sl.id_store AND sl.id_lang = ' . (int)$this->context->language->id . ')
                         WHERE v.id_shop = ' . $id_shop . ' ORDER BY v.vacation_start ASC';
        $vacations = Db::getInstance()->executeS($vacationsSql);

        $this->context->smarty->assign([
            'dfs_stores' => $stores,
            'dfs_selected_store' => $selected_store_id,
            'dfs_store_planning' => $store_planning,
            'dfs_vacations' => $vacations,
            'dfs_duration' => Configuration::get('DFS_DRIVE_DUREE', 60),
            'dfs_carrier_id' => Configuration::get('DFS_DRIVE_CARRIER_ID'),
            'dfs_carriers' => Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS),
            'dfs_post_link' => $this->context->link->getAdminLink('AdminDfsClickCollect', true)
        ]);

        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'dfs_clickcollect/views/templates/admin/configure.tpl');
        
        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }
    
    private function getDayName($id_day)
    {
        $days = [
            1 => $this->module->l('Lundi'),
            2 => $this->module->l('Mardi'),
            3 => $this->module->l('Mercredi'),
            4 => $this->module->l('Jeudi'),
            5 => $this->module->l('Vendredi'),
            6 => $this->module->l('Samedi'),
            7 => $this->module->l('Dimanche'),
        ];
        return isset($days[$id_day]) ? $days[$id_day] : '';
    }
}
