<?php
/**
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 */

class AdminDfsClickCollectAjaxController extends ModuleAdminController
{
    public $ajax = true;
    public $content_only = true;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        if (!$this->module) {
            $this->module = Module::getInstanceByName('dfs_clickcollect');
        }
    }

    public function ajaxProcessUpdateSlot()
    {
        $id_order = (int) Tools::getValue('id_order');
        $new_store = (int) Tools::getValue('dfs_new_store');
        $new_date = pSQL(Tools::getValue('dfs_new_date'));
        $new_time = pSQL(Tools::getValue('dfs_new_time'));

        if (!$id_order || !$new_store || !$new_date || !$new_time) {
            $this->ajaxDie(json_encode([
                'success' => false,
                'message' => $this->module->l('Paramètres invalides.')
            ]));
        }

        // Fetch the slot ID for the given order
        $sql = 'SELECT id_creneau FROM `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` WHERE id_order = ' . $id_order;
        $id_creneau = (int) Db::getInstance()->getValue($sql);

        if ($id_creneau) {
            $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                          SET id_store = ' . $new_store . ', day = "' . $new_date . '", hour = "' . $new_time . '", date_upd = NOW() 
                          WHERE id_creneau = ' . $id_creneau;
                          
            if (Db::getInstance()->execute($updateSql)) {
                // Here we could implement an optional email push
                // $this->sendSlotUpdateEmail($id_order, $new_date, $new_time);

                // Add a private message to the order to trace the modification
                $this->addOrderMessage($id_order, sprintf($this->module->l('Créneau modifié manuellement par un administrateur : %s à %s'), $new_date, $new_time));

                $this->ajaxDie(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Créneau mis à jour avec succès.')
                ]));
            }
        } else {
            // It could be that the admin is creating a slot for an order that didn't have one initially
            $insertSql = 'INSERT INTO `' . _DB_PREFIX_ . 'dfs_clickcollect_creneau` 
                          (id_order, id_store, id_shop, day, hour, manual_slot, date_add, date_upd) 
                          VALUES (' . $id_order . ', ' . $new_store . ', ' . (int)$this->context->shop->id . ', "' . $new_date . '", "' . $new_time . '", 1, NOW(), NOW())';
                          
            if (Db::getInstance()->execute($insertSql)) {
                $this->ajaxDie(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Nouveau créneau créé et associé avec succès.')
                ]));
            }
        }

        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => $this->l('Erreur lors de la mise à jour.')
        ]));
    }

    private function addOrderMessage($id_order, $message_text)
    {
        $message = new Message();
        $message->id_order = (int) $id_order;
        $message->id_employee = (int) $this->context->employee->id;
        $message->message = $message_text;
        $message->private = 1;
        $message->add();
    }
}
