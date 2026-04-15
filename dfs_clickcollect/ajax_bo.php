<?php
/**
 * DFS Click & Collect - Bulletproof AJAX Endpoint for BO
 */

require_once dirname(__FILE__) . '/../../config/config.inc.php';

header('Content-Type: application/json');

// Security Token Check
$token = Tools::getValue('token');
if ($token !== Tools::getAdminTokenLite('AdminDfsClickCollect')) {
    die(json_encode(['success' => false, 'message' => 'Token de sécurité invalide ou expiré. Rafraîchissez la page.']));
}

$id_order = (int) Tools::getValue('id_order');
$new_store = (int) Tools::getValue('dfs_new_store');
$new_date = pSQL(Tools::getValue('dfs_new_date'));
$new_time = pSQL(Tools::getValue('dfs_new_time'));

if (!$id_order || !$new_store || !$new_date || !$new_time) {
    die(json_encode(['success' => false, 'message' => 'Paramètres invalides ou manquants.']));
}

$sql = 'SELECT id_creneau FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE id_order = ' . $id_order;
$id_creneau = (int) Db::getInstance()->getValue($sql);

if ($id_creneau) {
    $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                  SET id_store = ' . $new_store . ', day = "' . $new_date . '", hour = "' . $new_time . '", date_upd = NOW() 
                  WHERE id_creneau = ' . $id_creneau;
                  
    if (Db::getInstance()->execute($updateSql)) {
        die(json_encode(['success' => true, 'message' => 'Créneau mis à jour avec succès.']));
    }
} else {
    // Determine context shop if available, default to 1
    $shopContext = Context::getContext()->shop;
    $id_shop = $shopContext && $shopContext->id ? (int)$shopContext->id : 1;
    
    $insertSql = 'INSERT INTO `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                  (id_order, id_store, id_shop, day, hour, manual_slot, date_add, date_upd) 
                  VALUES (' . $id_order . ', ' . $new_store . ', ' . $id_shop . ', "' . $new_date . '", "' . $new_time . '", 1, NOW(), NOW())';
                  
    if (Db::getInstance()->execute($insertSql)) {
        die(json_encode(['success' => true, 'message' => 'Créneau associé avec succès à la commande.']));
    }
}

die(json_encode(['success' => false, 'message' => Db::getInstance()->getMsgError()]));
