<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

namespace DigitalFoodSystem\DfsClickCollect\Service;

use Db;
use Context;

class SlotAvailabilityService
{
    /**
     * Get available dates and slots for a given store
     * 
     * @param int $id_store
     * @return array
     */
    public function getAvailableSlots($id_store)
    {
        $id_shop = (int) Context::getContext()->shop->id;
        
        // 1. Get Store openings
        $openings = $this->getStoreOpenings($id_store, $id_shop);
        
        // 2. Get Global / Store Vacations
        $vacations = $this->getVacations($id_store, $id_shop);
        
        // 3. Logic to generate slots based on openings & vacations
        // (This would map the previous legacy logic in a cleaner way)
        $slots = [];
        
        // Example logic: Just return openings for now
        return [
            'openings' => $openings,
            'vacations' => $vacations,
            'slots' => $slots
        ];
    }

    private function getStoreOpenings($id_store, $id_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_store` 
                WHERE `id_store` = ' . (int)$id_store . ' 
                AND `id_shop` = ' . (int)$id_shop;
                
        return Db::getInstance()->executeS($sql);
    }
    
    private function getVacations($id_store, $id_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_vacation` 
                WHERE `id_shop` = ' . (int)$id_shop . ' 
                AND (`id_store` = 0 OR `id_store` = ' . (int)$id_store . ')
                AND `vacation_end` >= CURDATE()';
                
        return Db::getInstance()->executeS($sql);
    }

        public function getAvailableSlotsForDate($id_store, $dateString, $id_day, $id_shop)
    {
        error_log("Fetching for date " . $dateString);
        $openings = \Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_store` 
                WHERE `id_store` = ' . (int)$id_store . ' 
                AND `id_day` = ' . (int)$id_day . '
                AND `id_shop` = ' . (int)$id_shop);
                
        if (!$openings || empty($openings['openning'])) {
            return [];
        }
        
        $delay_minutes = isset($openings['delay']) ? (int)$openings['delay'] : 0;
        $tz = \Configuration::get('PS_TIMEZONE');
        if (!$tz) {
            $tz = date_default_timezone_get();
        }
        $timezone = new \DateTimeZone($tz);
        
        $now = new \DateTime('now', $timezone);
        $minTimestamp = 0;
        
        if ($delay_minutes > 0) {
            $now->modify("+{$delay_minutes} minutes");
        }
        $minTimestamp = $now->getTimestamp();
        
        $slots = [];
        
        // Generate AM Slots
        if (!empty($openings['hour_open_am']) && !empty($openings['hour_close_am']) && $openings['hour_open_am'] !== '00:00:00') {
            $current = strtotime($dateString . ' ' . $openings['hour_open_am']);
            $end = strtotime($dateString . ' ' . $openings['hour_close_am']);
            
            $limit = 24;
            while ($current < $end && $limit > 0) {
                $limit--;
                $next = strtotime('+1 hour', $current);
                if (!$next) break;
                if ($next > $end) $next = $end;
                if ($current >= $minTimestamp) {
                    $slots[] = [
                        'hour' => date('H:i', $current) . ' - ' . date('H:i', $next)
                    ];
                }
                $current = $next;
            }
        }
        
        // Generate PM Slots
        if (!empty($openings['hour_open_pm']) && !empty($openings['hour_close_pm']) && $openings['hour_open_pm'] !== '00:00:00') {
            $current = strtotime($dateString . ' ' . $openings['hour_open_pm']);
            $end = strtotime($dateString . ' ' . $openings['hour_close_pm']);
            
            $limit = 24;
            while ($current < $end && $limit > 0) {
                $limit--;
                $next = strtotime('+1 hour', $current);
                if (!$next) break;
                if ($next > $end) $next = $end;
                if ($current >= $minTimestamp) {
                    $slots[] = [
                        'hour' => date('H:i', $current) . ' - ' . date('H:i', $next)
                    ];
                }
                $current = $next;
            }
        }

        // Fallback for nonstop if PM is empty (some configs might just put open_am to close_pm)
        if (!empty($openings['nonstop']) && empty($slots)) {
             $current = strtotime($dateString . ' ' . $openings['hour_open_am']);
             $end = strtotime($dateString . ' ' . $openings['hour_close_pm']);
             $limit = 24;
             while ($current < $end && $limit > 0) {
                $limit--;
                $next = strtotime('+1 hour', $current);
                if (!$next) break;
                if ($next > $end) $next = $end;
                if ($current >= $minTimestamp) {
                    $slots[] = [
                        'hour' => date('H:i', $current) . ' - ' . date('H:i', $next)
                    ];
                }
                $current = $next;
             }
        }
        
        return $slots;
    }
}

