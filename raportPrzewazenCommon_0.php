<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class raportPrzewazenCommon extends ModuleCommon {

    public static function menu() {
		return array(_M('Reports') => array('__submenu__' => 1, __('Raport z przeważeń') => array(
	    'view'
			)));
	}

    public static function user_settings() {
		return array(__("Raport przeważeń")=> 'settings');
	 }

	 public static function getConfig(){
        $fileCfg = file_get_contents(dirname(__DIR__)."/../data/raportPrzewazenConfig.json");
        $cfg = json_decode($fileCfg, true);
        return $cfg;

    }
 
    public static function purchaseControll($record, $mode){
		//custom_agrohandel_purchase_plans
		//waga pusto = wagazala
		//waga pelno = wagazalak
		$recordOld = Utils_RecordBrowserCommon::get_record("custom_agrohandel_purchase_plans",$record['id']);
		if($mode == "edit"){
			if($record['wagazala'] != $recordOld['wagazala'] || $record['wagazalak'] != $recordOld['wagazalak']){
				$tr = raportPrzewazenCommon::getTransportByPurchase($record);
				$results = raportPrzewazenCommon::checkTransport($tr);
				if($results['report']){
					//mail
				}
			}       
		}
	}

	public static function transportControll($record, $mode){
		//custom_agrohandel_transporty
		//waga pusto = wagarozpo
		//Waga przed rozłądunkiem = wagarozprzed

		$recordOld = Utils_RecordBrowserCommon::get_record("custom_agrohandel_transporty",$record['id']);
		if($mode == "edit"){
			if($record['wagarozpo'] != $recordOld['wagarozpo'] || $record['wagarozprzed'] != $recordOld['wagarozprzed']){
				$results = raportPrzewazenCommon::checkTransport($record);
				if($results['report']){
					//mail
				}
			}
		}
	}

	public static function checkTransport($transport){
		//$transport = raportPrzewazenCommon::getTransportByPurchase($tr);
		//$prevTransport = raportPrzewazenCommon::getPreviusTransport($transport);
		$purchases = raportPrzewazenCommon::getPurchases($transport);
		$weightStart = 0;
		$weightEnd = 0;
		$limit = raportPrzewazenCommon::getConfig()['limit'];
		$method = 0;
		$report = 0;
		if($transport['wagarozprzed'] != null && $transport['wagarozpo'] != null && $transport['wagarozpo'] != 0 && $transport['wagarozprzed'] != 0 ){
			$method = 1;
		}
		else if($transport['wagarozprzed'] != null && ($transport['wagarozpo'] == '' || $transport['wagarozpo'] == 0) && raportPrzewazenCommon::fullWeights($transport) == false){
			$method = 3;
		}
		else if($transport['wagarozprzed'] != null && ($transport['wagarozpo'] == '' || $transport['wagarozpo'] == 0)){
			$method = 2;
		}
		$ubojnia = "&nbsp;";
		if($transport['company']){
			$ubojnia = Utils_RecordBrowserCommon::get_val('custom_agrohandel_transporty', 'company', $transport);
		}else{
			$ubojnia = "&nbsp;";
		}
		switch ($method) {
			case 0:
			//	$weights[] = array("weightEmpty" => "Brakuje danych", "weightFull" => "Brakuje danych");
				break;
			case 1:
				$weights = array();
				foreach($purchases as $purchase){
					if($purchase['company']){
						$farmer = Utils_RecordBrowserCommon::get_val('custom_agrohandel_purchase_plans', 'company', $purchase);
					}else{
						$farmer = "&nbsp;";
					}
					if($purchase['wagazala'] != null && $purchase['wagazalak'] != null){
						$weights[] = array("weightEmpty" => $purchase['wagazala'], "weightFull" => $purchase['wagazalak'], 'farmer'=> $farmer, 'ubojnia' => '');
					}
				}
				$count = count($weights);
				$emptyZal = 0;
				//zaladnunki
				for($i = 0; $i<$count;$i++){
					$weightEmpty = $weights[$i]['weightEmpty'];
					$weightFull =  $weights[$i]['weightFull'];
					if($i != 0){
						$diffrence = $weightEmpty - $weights[$i-1]['weightFull'];
					}
					else{
						$emptyZal = $weights[$i]['weightEmpty'];
						$diffrence = '&nbsp;';
					}
					if(($i+1) == $count){
						$diffrence = $transport['wagarozprzed'] - $weights[$i]['weightFull'];
					}
					$weights[$i]['weight'] = $weightFull - $weightEmpty;
					$weights[$i]['amount'] = $purchase['amount'];
					$weights[$i]['diff'] = $diffrence;
					$weights[$i]['perPig'] = round($diffrence / $purchase['amount'],3);
					$weights[$i]['perPig'] = str_replace(".",",",$weights[$i]['perPig']);
					$weights[$i]['type'] = "Załadunek";
					$weights[$i]['place'] = $weights[$i]['farmer'];
					if($diffrence > $limit){
						$report = true;
					}
				}

				//rozladunki
				$weightEmpty = $transport['wagarozpo'];
				$weightFull  = $transport['wagarozprzed'];
				$diffrence = $weightEmpty - $emptyZal;

				$weights[$count]['diff'] = $diffrence;
				$weights[$count]['type'] = "Rozładunek";
				$weights[$count]['place'] = $ubojnia;
				$weights[$count]['weightEmpty'] = $transport['wagarozpo'];
				$weights[$count]['weightFull'] = $transport['wagarozprzed'];
				if($diffrence > $limit){
					$report = true;
				}
				$name = Utils_RecordBrowserCommon::get_val('custom_agrohandel_transporty', 'number', $transport);
				$truck = Utils_RecordBrowserCommon::get_val('custom_agrohandel_transporty', 'vehicle', $transport);
				$driver = Utils_RecordBrowserCommon::get_val('custom_agrohandel_transporty', 'driver_1', $transport);
				break;
			case 2:
			/*	$weights = array();
				foreach($purchases as $purchase){
					$weights[] = array("weightEmpty" => $purchase['wagazala'], "weightFull" => $purchase['wagazalak']);
				}
				$count = count($weights);
				$weightStart = $weights[0]['weightEmpty'];
				for($i = 0; $i<$count;$i++){
					$weightEmpty = $weights[$i]['weightEmpty'];
					$weightFull =  $weights[$i]['weightFull'];
					if($i != 0)
						$diffrence =  $weightEmpty - $weights[$i-1]['weightFull'];
					else
						$diffrence =  '&nbsp;';
					$weights[$i]['diff'] = $diffrence;
					$weights[$i]['type'] = "Załadunek";
					$weights[$i]['zalPlace'] = " <BR>".$weights[$i]['farmer'];
					if($diffrence > $limit){
						$report = true;
					}
				}
				$weightEnd = $weights[$count-1]['weightFull'];
				$diffrence = $weightEnd - $transport['wagarozprzed'];
				$diffrence = $diffrence - $weightStart;
				$weights[$count]['weightEmpty'] = $weightEnd;
				$weights[$count]['weightFull'] = $transport['wagarozprzed'];
				$weights[$count]['diff'] = $diffrence;
				$weights[$i]['type'] = "Rozładunek";
				$weights[$i]['rozPlace'] = "<BR>".$ubojnia;
				if($diffrence > $limit){
					$report = true;
				}*/
			break;
			case 3:
				/*$weights = array();
				foreach($purchases as $purchase){
					$weights[] = array("weightEmpty" => $purchase['wagazala'], "weightFull" => $purchase['wagazalak']);
				}
				$count = count($weights);
				$weightsTotal = 0;
				for($i = 0; $i<$count;$i++){
					$weightsTotal += $weights[$i]['weightFull'];
					$weights[$i]['weightEmpty'] = "&nbsp;";
					$weights[$i]['type'] = "Załadunek";
					$weights[$i]['zalPlace'] =  "<BR>".$weights[$i]['farmer'];
					$weights[$i]['diff'] = $weights[$i]['weightFull'] - $weights[$i-1]['weightFull'];
				}
				$diffrence = $weightsTotal - $transport['wagarozprzed'];
				$weights[$i]['weightFull'] = $weightsTotal;
				$weights[$i]['weightEmpty'] = $transport['wagarozprzed'];
				$weights[$i]['diff'] = $diffrence;
				$weights[$i]['type'] = "Rozładunek";
				$weights[$i]['rozPlace'] = " <BR>".$ubojnia;
				if($diffrence > $limit){
					$report = true;
				}*/
				break;
		}
		if($report){
				$weights['report'] = true;
		}
		$returned = [];
		$returned['weights'] = $weights;
		$returned['name'] = $name;
		$returned['driver'] = $driver;
		$returned['truck'] = $truck;
		$returned['method'] = $method;
		return $returned;
	}

	public static function getPurchases($transport){
		return Utils_RecordBrowserCommon::get_records('custom_agrohandel_purchase_plans', array("id" => $transport['zakupy'] ),array(),
			array("wagazalak" => "ASC"));//"Numer Pozycji Transportu" => "ASC"
	}

	public static function getTransportByPurchase($purchase){
		$transport = Utils_RecordBrowserCommon::get_records('custom_agrohandel_transporty', array("zakupy" => $purchase['id'] ),array(),array());
		list($firstItem) = $transport;
		return $firstItem;
	}
	
	public static function fullWeights($transport){
		$purchases = raportPrzewazenCommon::getPurchases($transport);
		$isFullData = false;
		foreach($purchases as $purchase){
			if($purchase['wagazala'] != '' || $purchase['wagazala'] != null ){
				$isFullData = true;
			}
		}
		return $isFullData;
	}

	public static function autoselect_transport($str, $crits, $format_callback) {
        $str = explode(' ', trim($str));
        foreach ($str as $k=>$v)
            if ($v) {
                $v = "%$v%";
                $crits = Utils_RecordBrowserCommon::merge_crits($crits, array('~number'=>$v));
            }
        $recs = Utils_RecordBrowserCommon::get_records('custom_agrohandel_transporty', $crits, array(), array('number'=>'ASC'), 12);
        $ret = array();
        foreach($recs as $v) {
            $ret[$v['id']."__".$v['number']] = call_user_func($format_callback, $v, true);
        }
        return $ret;
	}

	public static function autoselect_vehicle($str, $crits, $format_callback) {
        $str = explode(' ', trim($str));
        foreach ($str as $k=>$v)
            if ($v) {
                $v = "%$v%";
                $crits = Utils_RecordBrowserCommon::merge_crits($crits, array('~name'=>$v));
            }
        $recs = Utils_RecordBrowserCommon::get_records('custom_agrohandel_vehicle', $crits, array(), array('name'=>'ASC'), 12);
        $ret = array();
        foreach($recs as $v) {
            $ret[$v['id']."__".$v['name']] = call_user_func($format_callback, $v, true);
        }
        return $ret;
	}

	public static function autoselect_driver($str, $crits, $format_callback) {
        $str = explode(' ', trim($str));
        foreach ($str as $k=>$v)
            if ($v) {
                $v = "$v%";
                $crits = Utils_RecordBrowserCommon::merge_crits($crits, array('(~last_name'=>$v, '|~first_name'=>$v, 'group' => array('u_driver')));
            }
        $recs = Utils_RecordBrowserCommon::get_records('contact', $crits, array(), array('last_name'=>'ASC'), 12);
        $ret = array();
        foreach($recs as $v) {
            $ret[$v['id']."__".$v['last_name']." ".$v['first_name']] = call_user_func($format_callback, $v, true);
        }
        return $ret;
	}

	public static function autoselect_farmer($str, $crits, $format_callback) {
        $str = explode(' ', trim($str));
        foreach ($str as $k=>$v)
            if ($v) {
                $v = "$v%";
                $crits = Utils_RecordBrowserCommon::merge_crits($crits, array('~company_name'=>$v, 'group' => array('farmer')));
            }
        $recs = Utils_RecordBrowserCommon::get_records('company', $crits, array(), array('company_name'=>'ASC'), 12);
        $ret = array();
        foreach($recs as $v) {
            $ret[$v['id']."__".$v['company_name']] = call_user_func($format_callback, $v, true);
        }
        return $ret;
	}
	public static function autoselect_ubojnia($str, $crits, $format_callback) {
		$str = explode(' ', trim($str));
		foreach ($str as $k=>$v)
			if ($v) {
				$v = "$v%";
				$crits = Utils_RecordBrowserCommon::merge_crits($crits, array('~company_name'=>$v, 'group' => array('ubojnia')));
			}
		$recs = Utils_RecordBrowserCommon::get_records('company', $crits, array(), array('company_name'=>'ASC'), 12);
		$ret = array();
		foreach($recs as $v) {
			$ret[$v['id']."__".$v['company_name']] = call_user_func($format_callback, $v, true);
		}
		return $ret;
	}
	public static function company_format($record){
		$ret = $record['company_name'];
		return $ret;
	}

	
	public static function driver_format($record){
		$ret = $record['last_name']." ".$record['first_name'];
		return $ret;
	}

	public static function vehicle_format($record){
		$ret = $record['name'];
		return $ret;
	}

	public static function transport_format($record){
		$ret = $record['number'];
		return $ret;
	}

	public static function automulti_format($record) {
		$ret = $record['number'];
		return $ret;
	}
	
	public static function automulti_search($arg) {
		$records = Utils_RecordBrowserCommon::get_records("custom_agrohandel_transporty", 
		array("~number" => "%$arg%"),array(),array());
		$arrayReturned = array();
		foreach($records as $record){
			$arrayReturned[$record['id']."__".$record['number']] = $record['number'];
		}
		return $arrayReturned;
	}

	public static function getPreviusTransport($tr){
		$transport = Utils_RecordBrowserCommon::get_records('custom_agrohandel_transporty', array( 'id<' => $tr['id'] , 'vehicle' => $tr['vehicle']  ),
			array(),array('id' => 'DESC'), $limit = 1);
		foreach($transport as $trans){
			$firstItem = $trans;
			break;
		}
		return $firstItem;
	}

}