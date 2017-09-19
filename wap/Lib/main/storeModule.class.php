<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class storeModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();		

		$param['data_id'] = intval($_REQUEST['data_id']); //分类ID
		$param['c'] = intval($_REQUEST['c']); //分类ID

		$request = $param;
		//获取品牌
		$data = call_api_core("store","index",$param);

//		print_r($data);exit;
		// 优惠券
		foreach ($data['youhui_list'] as $k=>$v){
		    $data['youhui_list'][$k]['youhui_url'] = wap_url("index", 'youhui', array('data_id'=>$v['id']) );
		}
		
		// 活动
		foreach ($data['event_list'] as $k=>$v){
		    $data['event_list'][$k]['event_url'] = wap_url("index", 'event', array('data_id'=>$v['id']) );
		}
	 
		// 优惠信息
		foreach ($data['store_info']['promotes'] as $k=>$v){
		    $promote[] = $v['description'];
		}
		
		// 团购
		foreach ($data['tuan_list'] as $k=>$v){
		    $data['tuan_list'][$k]['tuan_url'] = wap_url("index", 'deal', array('data_id'=>$v['id']) );
			//根据商品id找到门店id，再找到门店地址
			$sql = "select `address` from ".DB_PREFIX."supplier_location where `id` = (select `location_id` from ".DB_PREFIX."deal_location_link where `deal_id` = '{$v['id']}')";
			$data['tuan_list'][$k]['address'] = $GLOBALS['db']->getOne($sql);
		}

		// 商品
		foreach ($data['deal_list'] as $k=>$v){
		    $data['deal_list'][$k]['deal_url'] = wap_url("index", 'deal', array('data_id'=>$v['id']) );
		}
		
		// 推荐商家
		foreach ($data['location_list'] as $k=>$v){
		    $data['location_list'][$k]['location_url'] = wap_url("index", 'store', array('data_id'=>$v['id']) );
		    $data['location_list'][$k]['avg_point'] = round($v['avg_point'], 1);
		    
		    $distance = $v['distance'];
		    $distance_str = "";
		    if($distance>0)
		    {
		        if($distance>1500)
		        {
		            $distance_str =  round($distance/1000)."km";
		        }
		        else
		        {
		            $distance_str = round($distance)."米";
		        }
		    }
		    $data['location_list'][$k]['distance'] = $distance_str;
		    
		}
		
		
		foreach ($data['other_supplier_location'] as $k=>$v){
		    $data['other_supplier_location'][$k]['location_url'] = wap_url("index", 'store', array('data_id'=>$v['id']) );
		    
		    
		    $distance = $v['distance'];
		    $distance_str = "";
		    if($distance>0)
		    {
		        if($distance>1500)
		        {
		            $distance_str =  round($distance/1000)."km";
		        }
		        else
		        {
		            $distance_str = round($distance)."米";
		        }
		    }
		    $data['other_supplier_location'][$k]['distance'] = $distance_str;
		}
		
		// 分店数
		$data['other_supplier_location_count'] = count($data['other_supplier_location']);
		
		// 评价链接
		$data['dp_url'] = wap_url("index", 'dp_list', array( 'data_id'=>$param['data_id'], 'type'=>'store') );
		
		// 优惠买单地址
		$data['store_pay_url'] = wap_url("index", 'store_pay', array('id'=>$param['data_id']) );
		
		$data['store_info']['promote_str'] = join('，', $promote);
		
		$data['store_info']['ref_avg_price'] = round($data['store_info']['ref_avg_price']);

		if(intval($data['id'])==0)
		{
		    app_redirect(wap_url("index"));
		}


		$GLOBALS['tmpl']->assign("request",$request);
		$GLOBALS['tmpl']->assign("store_info",$data['store_info']);
		$GLOBALS['tmpl']->assign("data",$data);		

		// 计算时间
		$now_day = time();
		$now_week = date("D",$now_day);

		$one_day = strtotime("+1 day");
		$one_week = date("D",$one_day);

		$two_day = strtotime("+2 day");
		$two_week = date("D",$two_day);

		$therr_day = strtotime("+3 day");
		$therr_week = date("D",$therr_day);

		$four_day = strtotime("+4 day");
		$four_week = date("D",$four_day);

		$five_day = strtotime("+5 day");
		$five_week = date("D",$five_day);

		$six_day = strtotime("+6 day");
		$six_week = date("D",$six_day);

		// $seven_day = strtotime("+1 week");
		// $seven_week = date("D",$seven_day);

		// echo date("D",$now_day);die;		
		
		// $data_info = array('data_time'=>$now_time);
		// $data_info = array($now_day,$one_day,$two_day,$therr_day,$four_day,$five_day,$six_day);
		
		$data_week = array(array('sc'=>$now_day,'week'=>$now_week),array('sc'=>$one_day,'week'=>$one_week),array('sc'=>$two_day,'week'=>$two_week),array('sc'=>$therr_day,'week'=>$therr_week),array('sc'=>$four_day,'week'=>$four_week),array('sc'=>$five_day,'week'=>$five_week),array('sc'=>$six_day,'week'=>$six_week));


		// $data_week = array($now_week,$one_week,$two_week,$therr_week,$four_week,$five_week,$six_week);


		$GLOBALS['tmpl']->assign("data_info",$data_info);
		$GLOBALS['tmpl']->assign("data_week",$data_week);
		$GLOBALS['tmpl']->assign('c',$param['c']);
		if($param['c']==1){
			
			$GLOBALS['tmpl']->display("hotel_details.html");
		}elseif($param['c']==2){
			$GLOBALS['tmpl']->display("hair_details.html");
		}elseif($param['c']==3){
			// var_dump($data);die;
			$GLOBALS['tmpl']->display("ktv_details.html");
		}else{
			// var_dump($data['']);die;
			$GLOBALS['tmpl']->display("store.html");
		}
		

	}
	public function get_hotel_shuxing(){
		
		global_run();		
		init_app_page();		
		$param['id'] = intval($_REQUEST['id']);
		$data = call_api_core("store","get_hotel_shuxing",$param);
		ajax_return($data);
	}

	public function lingqu_youhui(){
		global_run();		
		init_app_page();		
		$param['id'] = intval($_REQUEST['id']);
		$param['time_info'] = strtotime($_REQUEST['time_info']);
		$data = call_api_core("store","lingqu_youhui",$param);
		if($data['status']==-1){
	    	$data['jump'] = wap_url("index","user#login");
	    }
		ajax_return($data);
	}
	public function addcollect(){

		$deal_id = intval($_REQUEST['activity_id']);
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$type = $_REQUEST['type'] ? $_REQUEST['type'] :'5';//收藏类型   1商品  2拼团   3砍价   4活动 5店铺  6ltv  7酒店  8丽人 9 团购  10购物
		//收藏前，检查是否已经收藏
		$is_shou = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where `id_value` = '$deal_id' and `type` = '$type' and `user_id` = '$user_id'");
		if($is_shou){
			ajax_return("已被收藏");exit;
		}
		$sql = "insert into ".DB_PREFIX."deal_collect(`id_value`,`type`, `user_id`, `create_time`) values ('$deal_id','$type','$user_id','$now_time')";
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			ajax_return("已被收藏");exit;
		}else{
			ajax_return("收藏失败");exit;
		}

	}
	//取消收藏
	function delcollect(){
		$kan_id = $_REQUEST['activity_id'];
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$type = $_REQUEST['type'] ? $_REQUEST['type'] :'5';
		//收藏前，检查是否已经收藏
		$is_shou_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_collect where `id_value` = '$kan_id' and `type` = '$type' and `user_id` = '$user_id'");
		if($is_shou_id){
			//执行取消收藏
			$sql = "delete from ".DB_PREFIX."deal_collect where `id` = '$is_shou_id'";
			$row = $GLOBALS['db']->query($sql);
			if($row){
				ajax_return("取消收藏成功");exit;
			}else{
				ajax_return("请稍后重试");exit;
			}
		}else{
			ajax_return("取消收藏成功");exit;
		}


	}
	
}
?>