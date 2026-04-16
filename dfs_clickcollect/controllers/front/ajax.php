<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

use DigitalFoodSystem\DfsClickCollect\Service\SlotAvailabilityService;

class Dfs_ClickcollectAjaxModuleFrontController extends ModuleFrontController
{
        public function initContent()
    {
        parent::initContent();

        $action = Tools::getValue('action');
        $id_store = (int) Tools::getValue('id_store');
        $id_shop = (int) $this->context->shop->id;
        
        require_once _PS_MODULE_DIR_ . 'dfs_clickcollect/src/Service/SlotAvailabilityService.php';
        $slotService = new \DigitalFoodSystem\DfsClickCollect\Service\SlotAvailabilityService();

        if ($action == 'getDates') {
            if (!$id_store) {
                die(json_encode(['success' => false, 'message' => 'Magasin Invalide']));
            }
            
            // Generate next 30 days
            $dates = [];
            $currentDate = new \DateTime();
            
            $daysFr = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
            
            for ($i = 0; $i < 30; $i++) {
                $checkDate = clone $currentDate;
                $checkDate->modify("+$i days");
                $dayString = $checkDate->format('Y-m-d');
                $id_day = (int) $checkDate->format('N'); // 1 (Mon) to 7 (Sun)
                
                // Get slots for this day 
                $slots = $slotService->getAvailableSlotsForDate($id_store, $dayString, $id_day, $id_shop);
                if (!empty($slots)) {
                    $dates[] = [
                        'value' => $dayString,
                        'label' => $checkDate->format('d/m/Y'),
                        'dayName' => isset($daysFr[$id_day]) ? $daysFr[$id_day] : ''
                    ];
                }
            }
            
            die(json_encode(['success' => true, 'dates' => $dates]));
            
        } elseif ($action == 'getSlots') {
            $date = Tools::getValue('date');
            
            if (!$id_store || !$date) {
                die(json_encode(['success' => false, 'message' => 'ParamÃ¨tres manquants']));
            }
            
            $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
            $id_day = (int) $dateObj->format('N');
            
            $slots = $slotService->getAvailableSlotsForDate($id_store, $date, $id_day, $id_shop);
            
            die(json_encode(['success' => true, 'slots' => $slots]));
            
        } elseif ($action == 'saveSelection') {
            $id_cart = (int) $this->context->cart->id;
            $id_store = (int) Tools::getValue('id_store');
            $date = pSQL(Tools::getValue('date'));
            $time = pSQL(Tools::getValue('time'));
            
            if ($id_cart && $id_store && $date && $time) {
                // Remove existing selection for this cart
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_cart` WHERE `id_cart` = ' . $id_cart);
                
                // Insert new selection
                $res = Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'dfs_clickcollect_cart` (`id_cart`, `id_store`, `day`, `hour`, `id_shop`)
                    VALUES (' . $id_cart . ', ' . $id_store . ', "' . $date . '", "' . $time . '", ' . $id_shop . ')
                ');
                
                die(json_encode(['success' => $res]));
            }
            die(json_encode(['success' => false, 'message' => 'Champs manquants']));
        }

        die(json_encode(['success' => false, 'message' => 'Action invalide']));
    }
}

