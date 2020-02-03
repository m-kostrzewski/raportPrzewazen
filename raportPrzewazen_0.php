<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class raportPrzewazen extends Module { 


    public function settings(){
        if(file_exists((dirname(__DIR__).'/../data/raportPrzewazenConfig.json'))){
            $cfg = raportPrzewazenCommon::getConfig();
        }else{
            $cfg = [];
        }
        $form = &$this->init_module('Libs/QuickForm');
        $form->addElement("text", 'limit','Limit', array('value'=> $cfg['limit']) );
        $form->addElement("submit", 'submit', 'Zapisz');
        $form->display();
        if($form->validate()){
            $values = $form->exportValues();
            $cfg['limit'] = $values['limit'];
            file_put_contents(dirname(__DIR__).'/../data/raportPrzewazenConfig.json', json_encode($cfg));
        }
    }

    public function body(){

        if(isset($_REQUEST['__jump_to_RB_table'])){    
            $rs = new RBO_RecordsetAccessor($_REQUEST['__jump_to_RB_table']);
            $rb = $rs->create_rb_module ( $this );
            $this->display_module ( $rb);
        }    

        Base_ThemeCommon::install_default_theme($this->get_type());
        $form = $this->init_module('Libs/QuickForm');

        $crits = array();
        $fcallback = array('raportPrzewazenCommon','transport_format');
        $form->addElement('autoselect', 'transportSelect', 'Szukaj po numerze transportu', array(),
            array(array('raportPrzewazenCommon','autoselect_transport'), array($crits, $fcallback)), $fcallback);
        
        $form->addElement('automulti','automul','Wybierz transporty', 
            array($this->get_type().'Common', 'automulti_search'), array(),
            array($this->get_type().'Common', 'automulti_format'));

        $fcallback = array('raportPrzewazenCommon','driver_format');
        $form->addElement('autoselect', 'driverSelect', 'Szukaj po kierowcy', array(),
                array(array('raportPrzewazenCommon','autoselect_driver'), array($crits, $fcallback)), $fcallback);

        $fcallback = array('raportPrzewazenCommon','vehicle_format');
        $form->addElement('autoselect', 'vehicleSelect', 'Szukaj po samochodzie', array(),
                array(array('raportPrzewazenCommon','autoselect_vehicle'), array($crits, $fcallback)), $fcallback);

        $fcallback = array('raportPrzewazenCommon','company_format');
        $form->addElement('autoselect', 'farmerSelect', 'Szukaj po rolniku', array(),
                array(array('raportPrzewazenCommon','autoselect_farmer'), array($crits, $fcallback)), $fcallback);

        $fcallback = array('raportPrzewazenCommon','company_format');
        $form->addElement('autoselect', 'ubojniaSelect', 'Szukaj po ubojni', array(),
                    array(array('raportPrzewazenCommon','autoselect_ubojnia'), array($crits, $fcallback)), $fcallback);

        $form->addElement("datepicker","from","Od");
        $form->addElement("datepicker","to","Do");
            
        $form->addElement("select","type","Wyświetl jako", array("graph"=>"Graficznie", "table"=>"Tabela"));
        $form->addElement("submit","graph","Pokaż");

        $form->display_as_row();
        Epesi::js("
            var form = jq('form');
            jq(form[2]).css('margin-left','45px');
            jq(form[2]).addClass('formBox');
        ");
        $theme = $this->init_module('Base/Theme');
        if($form->validate()){
            $values = $form->exportValues();
            $cits = array('type' => 'tucznik', '!zakupy' => null);
            if($values['from'] && $values['to']){
                $crits['>=date'] = $values['from'];
                $crits['<=date'] = $values['to'];
            }
            else if($values['from']){
                $crits['date'] = $values['from'];
                $crits['date'] = $values['to'];
               /* $transports = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty",array("date" => $values['from'], 
                    'type' => 'tucznik', '!zakupy' => null ),array(),array());*/
            }
            if($values['vehicleSelect']){
                $crits['vehicle'] = $values['vehicleSelect'];
            }
            if($values['driverSelect']){
                $crits['driver_1'] = $values['driverSelect'];
            }
            if($values['ubojniaSelect']){
                $crits['company'] = $values['ubojniaSelect'];
            }
            if($values['transportSelect']){
                 $transports = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty",array('id' => $values['transportSelect'], 
                    'type' => 'tucznik', '!zakupy' => null),array(),array());
            }
            else if($values['automul']){
                $transports = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty",array('id' => $values['automul'] , 
                    'type' => 'tucznik', '!zakupy' => null),array(),array());
            }else if($values['farmerSelect']){
                $purchases = Utils_RecordBrowserCommon::get_records("custom_agrohandel_purchase_plans", array('company' => $values['farmerSelect'],
                    '>=planed_purchase_date' => $values['from'], '<=planed_purchase_date'=> $values['to']),array(),array());
                $ids = array();
                foreach($purchases as $purchase){
                    $ids[] = $purchase['id'];
                }
                $crits['zakupy'] = $ids;
                if($crits['date'] || ($crits['>=date'] && $crits['<=date'])){
                    $transports = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty", $crits ,array(),array());
                }
            }
            else{
                if($crits['date'] || ($crits['>=date'] && $crits['<=date'])){
                    $transports = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty", $crits ,array(),array());
                }
            }
            $list = array();
            foreach($transports as $transport){
                $l = array();
                $results = raportPrzewazenCommon::checkTransport($transport);
                if(count($results) > 0){
                    $l['trans'] = $results['weights'];
                    $l['name'] =  $results['name'];
                    $l['driver'] =  $results['driver'];
                    $l['truck'] =  $results['truck'];
                    $l['method'] = $results['method'];
                    $list[] = $l;
                }
            }
            $colors = [];
            $colors[] =  '#30cc13';
            $colors[] = '#ffcf33';
            $colors[] = '#ff8c4a';
            $colors[] =  '#ff5f4a';
            $colors[] = '#4affe7'; 
            $colors[] = '#6a9eff';
            $colors[] = '#e8ff82';

            $theme->assign("colors",$colors);
            $theme->assign("transporty",$list);
            if($values['type'] == 'graph'){
                $theme->display();
            }
            else if($values['type'] == 'table'){
                if($values['farmerSelect']){
                    $farmer = Utils_RecordBrowserCommon::get_val('company', 'Company Name', Utils_RecordBrowserCommon::get_record("company",$values['farmerSelect']));
                    $theme->assign("farmer",$farmer);
                }
                if($values['ubojniaSelect']){
                    $ubojnia = Utils_RecordBrowserCommon::get_val('company', 'Company Name', Utils_RecordBrowserCommon::get_record("company",$values['ubojniaSelect']));
                    $theme->assign("ubojnia",$ubojnia);
                }

                $theme->display('table');
            }
        }
        
    }
}