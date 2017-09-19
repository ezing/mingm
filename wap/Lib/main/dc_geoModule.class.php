<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dc_geoModule extends MainBaseModule
{
	public function index()
	{
		/*global_run();		
		if($GLOBALS['geo']['xpoint']==0&&$GLOBALS['geo']['ypoint']==0)
		{
			call_api_core("userxypoint","index");
		}		
				
		$xpoint = strim($_REQUEST['m_longitude']);
		$ypoint = strim($_REQUEST['m_latitude']);
		if($xpoint&&$ypoint)
		{
			$url = "http://api.map.baidu.com/geocoder/v2/?ak=FANWE_MAP_KEY&location=FANWE_MAP_YPOINT,FANWE_MAP_XPOINT&output=json";
			$url = str_replace("FANWE_MAP_KEY", app_conf("BAIDU_MAP_APPKEY"), $url);
			$url = str_replace("FANWE_MAP_YPOINT", $ypoint, $url);
			$url = str_replace("FANWE_MAP_XPOINT", $xpoint, $url);
				
			require_once APP_ROOT_PATH."system/utils/transport.php";
			$trans = new transport();
			$trans->use_curl = true;
			$request_data = $trans->request($url);
			$data = $request_data['body'];
			$data = json_decode($data,1);
			$data = $data['result']['addressComponent'];
			$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_effect = 1 and LOCATE(name,'".$data['district']."')");
			if(empty($current_city))
				$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_effect = 1 and LOCATE(name,'".$data['city']."')");
			
			if($current_city&&$current_city['id']!=$GLOBALS['city']['id'])
			{
				ajax_return(array("status"=>0,"city"=>$current_city));
			}
		}
		
		ajax_return(array("status"=>1));*/

		global_run();		
		/*if($GLOBALS['geo']['xpoint']==0&&$GLOBALS['geo']['ypoint']==0)
		{
			call_api_core("userxypoint","index");
		}	*/	
				
		$xpoint = strim($_REQUEST['m_longitude']);
		$ypoint = strim($_REQUEST['m_latitude']);

	
        $current_geo = array('status'=>0);
		if($xpoint&&$ypoint)
		{
			$url = "http://api.map.baidu.com/geocoder/v2/?ak=FANWE_MAP_KEY&location=FANWE_MAP_YPOINT,FANWE_MAP_XPOINT&output=json&coordtype=wgs84ll";
			$url = str_replace("FANWE_MAP_KEY", app_conf("BAIDU_MAP_APPKEY"), $url);
			$url = str_replace("FANWE_MAP_YPOINT", $ypoint, $url);
			$url = str_replace("FANWE_MAP_XPOINT", $xpoint, $url);
				
			require_once APP_ROOT_PATH."system/utils/transport.php";
			$trans = new transport();
			$trans->use_curl = true;
			$request_data = $trans->request($url);


			$data = $request_data['body'];
			$data = json_decode($data,1);
			logger::write(print_r($data,1));
			//$address = $data['result']['sematic_description']?$data['result']['sematic_description']:$data['result']['formatted_address'];
			//$address = $data['result']['formatted_address'];
			$address = $data['result']['addressComponent']['district'].$data['result']['addressComponent']['street'].$data['result']['addressComponent']['street_number'];
            
            if($address)
				$current_geo['address'] = $address;
			else
			{
				es_session::delete("current_geo");
			}

			$data = $data['result']['addressComponent'];
			$current_geo['xpoint'] = $xpoint;
			$current_geo['ypoint'] = $ypoint;

            //$GLOBALS['geo'] = $current_geo;

					//定位当前城市

			$city=$data['city'];
			$city=str_replace('市', '', $city);
			$city=str_replace('县', '', $city);
			$city1=$city.'市';
			$city2=$city.'县';
			$city_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_city where name='".$city."'");

			if(intval($city_id)==0){
				$city_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_city where name='".$city1."'");
			}
			if(intval($city_id)==0){
				$city_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_city where name='".$city2."'");
			}
			
			$result['geo'] = $current_geo;
			
            require_once APP_ROOT_PATH."system/model/dc.php";
			$dc_search['dc_xpoint'] = $xpoint;
			$dc_search['dc_ypoint'] = $ypoint;
			$dc_search['dc_title'] = $address;
			$dc_search['dc_content'] = $data['result']['formatted_address'];
			$dc_search['dc_num'] = '';			
			$dc_search['city_name'] = $data['result']['addressComponent']['city'];

			if(intval($city_id)>0){
				global $city;
				require_once APP_ROOT_PATH."system/model/city.php";
				require_once APP_ROOT_PATH."system/model/dc.php";
				City::locate_city(intval($city_id));
					
				$dc_search_history_str=dc_stripslashes(es_cookie::get('dc_search_history'));
				$dc_search_history=array();
				$dc_search_history=json_decode($dc_search_history_str,true);
				$search_key=md5($dc_search['dc_title']);
				$dc_search_history_new[$search_key]=$dc_search;
				foreach($dc_search_history as $k=>$v){
					if($k!=$search_key){
						$dc_search_history_new[$k]=$v;
					}
				}
				logger::write(print_r($dc_search_history_new,1));
				es_cookie::set('dc_search_history', json_encode($dc_search_history_new),3600*24*7);
				$result['status']= 1;
				$result['info']=$address;
				
			}else{
				$result['status']= 2;
				$result['info']= '抱歉：您所在城市:'+$data['result']['addressComponent']['city']+',暂未开通';
				
			}
		}else{
			$result['status']= 0;
			$result['info']='定位失败';
			
		}
		
		ajax_return($result);

	}
	
	
}
?>