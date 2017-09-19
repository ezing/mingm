<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dealModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();


		$data_id = intval($_REQUEST['data_id']);
		$c = intval($_REQUEST['c']);//酒店标识
		if($data_id==0)
			$data_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".strim($_REQUEST['data_id'])."'"));
		
		$data = call_api_core("deal","index",array("data_id"=>$data_id,"type"=>1,"c"=>$c));
//print_r($data);exit;
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];

		//检查会员是否已经收藏此商品
		$ret = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."deal_collect where `user_id` = '$user_id'  and `id_value` = '$data_id' and `type` = '1'");
 		if($ret){
			$data['is_collect'] = 1;
		}else{
			$data['is_collect'] = 0;
		}
//echo $data['is_collect'];exit;
		if(intval($data['id'])==0)
		{
			app_redirect(wap_url("index"));
		}
		
		$data['detail_url'] = wap_url("index", 'deal_detail', array('data_id'=>$data['id']) );
		$data['dp_url'] = wap_url("index", 'dp_list', array('data_id'=>$data['id'], 'type'=>'deal') );
		
		 
		// 优惠互动
	    $data['promotes_list'] = join('，',  $data['promotes_list']);
		
		// 商家其它团购商品
		foreach ($data['other_location_deal'] as $k=>$v){
		    $data['other_location_deal'][$k]['old_url'] =  wap_url("index", 'deal', array('data_id'=>$v['id']) );
		    
		}

		$gg_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$data['supplier_id']);

		$data['supplier_img'] = $gg_info['preview'];
		//商家信息
		//先找到该商品的门店id
		$sql = "select `location_id` from ".DB_PREFIX."deal_location_link where `deal_id` = '{$data['id']}'";
		$location_id  = $GLOBALS['db']->getOne($sql);
//		echo $location_id;exit;
		$sql = "select `supplier_id`,`name`,`address`,`preview` from ".DB_PREFIX."supplier_location where `id` = '$location_id'";
		// echo $sql;exit;
		$row = $GLOBALS['db']->getRow($sql);

//		print_r($row);exit;
		$data['supplier_info'] = $row;
		$data['location_id'] = $location_id;


//print_r($data);exit;

		// 商家其它门店
		foreach ($data['supplier_location_list'] as $k=>$v){
		    $data['supplier_location_list'][$k]['location_url'] =  wap_url("index", 'store', array('data_id'=>$v['id']) );
		    
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
		    $data['supplier_location_list'][$k]['distance'] = $distance_str;
		    
		}
		//查找改商品的门店地址
		//$sql = "select `id`,`address`,`tel`,`name` from ".DB_PREFIX.""
		// 推荐商品
		foreach ($data['recommend_data'] as $k=>$v){
		    $data['recommend_data'][$k]['rd_url'] =  wap_url("index", 'deal', array('data_id'=>$v['id']) );
		
		}
		 
		//是否存在关联商品
		$relate_data = $data['relate_data'];
		if($relate_data){
			//把主产品加入 relate_data
			$newGoodsList = array();
			foreach( $relate_data['goodsList'] as $k=>$item ){
				if( intval($item['id'])!=$data_id ){
					$newGoodsList[] = $item;
				}
			}
			//goodsList wap展示为两个商品一组，需要改造一下
			$rsGoodsList = array();
			for( $k=0;$k<ceil(count($newGoodsList)/2);$k++ ){
				$item1 = $newGoodsList[$k*2];
				$item2 = $newGoodsList[$k*2+1];
				if(!$item2){
					$item1['widthP'] = '50%';
				}else{
					$item1['widthP'] = '100%';
				}
				$rsGoodsList[$k][] = $item1;
				if($item2){
					$item2['widthP'] = '100%';
					$rsGoodsList[$k][] = $item2;
				}			
			}
			$GLOBALS['tmpl']->assign("goodsList",$rsGoodsList);
			$GLOBALS['tmpl']->assign("jsonDeal",json_encode($relate_data['dealArray']));
			$GLOBALS['tmpl']->assign("jsonAttr",json_encode($relate_data['attrArray']));
			$GLOBALS['tmpl']->assign("jsonStock",json_encode($relate_data['stockArray']));
		}
		$data['now_time']  = NOW_TIME;
		$hasRelateGoods = !empty($relate_data)?1:0;

		//z找到该商品的分类id，如果分类id为19（ktv分类），则显示ktv的详情页
		$cate_id = $GLOBALS['db']->getOne("select `cate_id` from ".DB_PREFIX."deal where id = '".strim($_REQUEST['data_id'])."'");

		// print_r($data);exit;
		$GLOBALS['tmpl']->assign("hasRelateGoods",$hasRelateGoods);
		$GLOBALS['tmpl']->assign("download",url("index","app_download"));
		// print_r($data);exit;
		$GLOBALS['tmpl']->assign("data",$data);
		if($cate_id == '19'){
			$GLOBALS['tmpl']->display("ktv_details.html");
		}else{
			// var_dump($data[]);die;
			$GLOBALS['tmpl']->display("bulk_goods.html");
		}



	}
	
	public function addcollect(){

	/*
	
	    $param=array();
	    $param['id'] = intval($_REQUEST['id']);
	    $data = call_api_core("deal","add_collect",$param);
	    ajax_return($data);*/
		/*$user_login_status = check_login();
		if($user_login_status==LOGIN_STATUS_NOLOGIN){
			ajax_return("请先登录");exit;
		}*/

		$deal_id = intval($_REQUEST['activity_id']);
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$type = $_REQUEST['type'] ? $_REQUEST['type'] :'1';//收藏类型   1商品  2拼团   3砍价   4活动 5店铺  6ltv  7酒店  8丽人 9 团购  10购物
		//收藏前，检查是否已经收藏
		$is_shou = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where `id_value` = '$deal_id' and `type` = '$type' and `user_id` = '$user_id'");
		if($is_shou){
			ajax_return("已被收藏");exit;
		}
		$sql = "insert into ".DB_PREFIX."deal_collect(`id_value`,`type`, `user_id`, `create_time`) values ($deal_id,$type,$user_id,$now_time)";
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
		$type = $_REQUEST['type'] ? $_REQUEST['type'] :'1';
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