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



class PrestatillDriveVacation extends ObjectModel

{

    public $id_vacation;

    

    public $vacation_start;

    

    public $vacation_end;

    

	/* Since 1.2 */

	public $id_store = 0;

    

    /**

     * @see ObjectModel::$definition

     */

    public static $definition = array(

        'table' => 'prestatill_drive_vacation',

        'primary' => 'id_vacation',

        'multilang' => false,

        'fields' => array(

            'vacation_start' =>         array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),

            'vacation_end' =>           array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),

            'id_store' =>               array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),

        ),

    );



    public function __construct($id = null, $id_lang = null)

    {

        parent::__construct($id, $id_lang);

    }



    public static function getAllVacation($id_store = 0)

    {

    	$id_lang = (int)Context::getContext()->language->id;

		

		$request = 'SELECT * FROM '._DB_PREFIX_.'prestatill_drive_vacation v';

		

		if(version_compare(_PS_VERSION_, '1.7.3', '>'))

		{

	        $request .= ' LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (v.id_store = sl.id_store) AND sl.id_lang = '.$id_lang;

	    }

		

        if ((int)$id_store > 0)

            $request .= ' WHERE v.id_store = '.(int)$id_store.' OR v.id_store = 0';

		

        $result = Db::getInstance()->executeS($request);

         // 2.3.0 : Addition of a hook to interact with base carence
		Hook::exec('actionGetUnavailbilitiesAsVacation', array('result' => &$result), null, true);

        return $result;

    }



    public static function deleteVacation($id)

    {

        $vac = new PrestatillDriveVacation((int)$id);

        if (Validate::isLoadedObject($vac)) {

            $vac->delete();

        }

    }

}

