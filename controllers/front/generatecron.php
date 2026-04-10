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

class PrestatillDriveGenerateCronModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
    {
        parent::initContent();
		
		if (!defined('_PS_ADMIN_DIR_')) {
		    define('_PS_ADMIN_DIR_', getcwd());
		}
		
		if (Tools::substr(_COOKIE_KEY_, 34, 8) != Tools::getValue('token') || !Module::isInstalled('prestatilldrive')) {
		    die;
		}
		
		if(Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER'))
		{
			$time = Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER_TIME')?Configuration::get('PRESTATILL_DRIVE_SEND_REMINDER_TIME'):60;
			$drive = new PrestatillDrive();
			$drive->sendMailReminderForDriveOrders($time);
		}
		
		if (Tools::getValue('redirect') && isset($_SERVER['HTTP_REFERER'])) {
		    Tools::redirectAdmin($_SERVER['HTTP_REFERER'].'&conf=4');
		}
		die();
	
	}

}
