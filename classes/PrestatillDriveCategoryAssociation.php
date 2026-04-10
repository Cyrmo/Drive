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

class PrestatillDriveCategoryAssociation extends ObjectModel 
{
	/* Since 1.2 */
    public $id_category;
    public $delay_supp = 0;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_drive_category_association',
        'primary' => 'id_prestatill_drive_category_association',
        'multilang' => false,
        'fields' => array(
            'id_category' =>               array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'delay_supp' =>                array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
        ),
    );
	
	
	public static function getCategoryDelaySupp($id_category)
	{
		$request = 'SELECT *
        	FROM '._DB_PREFIX_.'prestatill_drive_category_association
        	WHERE id_category = '.(int)$id_category;

        $result = Db::getInstance()->getRow($request);
		
		return $result;
	}
	
}
