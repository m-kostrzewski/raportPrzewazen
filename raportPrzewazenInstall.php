<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class raportPrzewazenInstall extends ModuleInstall {

    public function install() {
        $ret = true;
        Utils_RecordBrowserCommon::register_processing_callback('custom_agrohandel_purchase_plans', 
                            array($this->get_type () . 'Common', 'purchaseControll'));

        Utils_RecordBrowserCommon::register_processing_callback('custom_agrohandel_transporty', 
                            array($this->get_type () . 'Common', 'transportControll'));

            return $ret;
    }

    public function uninstall() {
        $ret = true;
        return $ret; 
    }

    public function requires($v) {

        return array(); 
    }
    public function info() { // Returns basic information about the module which will be available in the epesi Main Setup
		return array (
				'Author' => 'Mateusz Kostrzewski',
				'License' => 'MIT 1.0',
				'Description' => '' 
		);
	}
    public function version() {

        return array('1.1');
    }
    public function simple_setup() { // Indicates if this module should be visible on the module list in Main Setup's simple view
		return array (
				'package' => __ ( 'Raport z przeważeń' ),
				'version' => '1.1'
		); // - now the module will be visible as "HelloWorld" in simple_view
	}
}