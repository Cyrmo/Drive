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

class StoreService
{
    /**
     * Get all active stores for the current shop.
     * Replaces the old nearest location/Nominatim logic with a clean multi-shop query.
     *
     * @return array
     */
    public function getActiveStores()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;

        $sql = 'SELECT s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                FROM `' . _DB_PREFIX_ . 'store` s
                LEFT JOIN `' . _DB_PREFIX_ . 'store_lang` sl ON (s.id_store = sl.id_store AND sl.id_lang = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'store_shop` ss ON (s.id_store = ss.id_store AND ss.id_shop = ' . $id_shop . ')
                WHERE s.active = 1
                ORDER BY sl.name ASC';

        $result = Db::getInstance()->executeS($sql);

        $eligible_stores = [];
        if (!empty($result)) {
            foreach ($result as $res) {
                $eligible_stores[$res['id_store']] = $res;
            }
        }

        return array_values($eligible_stores);
    }
}
