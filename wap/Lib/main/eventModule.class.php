<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class eventModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();		

		$param['data_id'] = intval($_REQUEST['data_id']); //分类ID

		$request = $param;
		//获取品牌
		$data = call_api_core("event","index",$param);

		if($data['user_login_status']!=LOGIN_STATUS_NOLOGIN){
		    $data['is_login'] = 1;
		}
		
		if(intval($data['id'])==0)
		{
		    app_redirect(wap_url("index"));
		}
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		//检查会员是否已经收藏此商品
		$ret = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."deal_collect where `user_id` = '$user_id'  and `id_value` = '{$param['data_id']}' and `type` = '4'");
		if($ret){
			$data['is_collect'] = 1;
		}else{
			$data['is_collect'] = 0;
		}
//		print_r($data);exit;
		$GLOBALS['tmpl']->assign("event",$data['event_info']);
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","event"));
		$GLOBALS['tmpl']->display("event.html");

	}
	
	/*
	 * 领取优惠券
	 * */
	public function load_event_submit(){
	    global_run();
	    init_app_page();
	    $data_id = intval($_REQUEST['data_id']);
	    $data = call_api_core("event","load_event_submit",array("data_id"=>$data_id));
	    if ($data['user_login_status']!=LOGIN_STATUS_LOGINED){
	    	app_redirect(wap_url("index","user#login"));
	    }
	    
	    if($data['status']==0){
	        showErr($data['info'],0,wap_url("index","event#index",array("data_id"=>$data_id)));
	    }
	    
	    $GLOBALS['tmpl']->assign("event_id",$data_id);
		$GLOBALS['tmpl']->assign("event_fields",$data['event_fields']);	
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","event"));
		$GLOBALS['tmpl']->display("event_submit.html");
	}
	
	public function do_submit(){
	   
	    global_run();
        /*获取参数*/
	    $event_id = intval($_REQUEST['event_id']);
	    $param=array();
	    $param['event_id'] = $event_id;
	    $param['result'] = $_REQUEST['result'];
	    $param['field_id'] = $_REQUEST['field_id'];
	    
	    $data = call_api_core("event","do_submit",$param);
		if ($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['status'] = 0;
			$data['info'] = "";
	        $data['jump'] = wap_url("index","user#login");
	    }
	    else
	    {
	        if ($data['status'] == 1){
	            $data['jump'] = wap_url("index","uc_event#index");
	        }else
	            $data['jump'] = wap_url("index","event#index",array("data_id"=>$event_id));
	    }
	  
	    ajax_return($data);
	    
	}
	
	public function detail()
	{
	    global_run();
	    init_app_page();
	
	    $data_id = intval($_REQUEST['data_id']);
	
	    $data = call_api_core("event","detail",array("data_id"=>$data_id));
	
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("event_info",$data['event_info']);
	    $GLOBALS['tmpl']->display("event_detail.html");
	}

	//添加收藏
	function addcollect(){
		$kan_id = $_REQUEST['activity_id'];

		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$type = '4';//收藏类型   1商品  2拼团   3砍价   4活动
		//收藏前，检查是否已经收藏
		$is_shou = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where `id_value` = '$kan_id' and `type` = '$type' and `user_id` = '$user_id'");
		if($is_shou){
			ajax_return("已被收藏");exit;
		}
		$sql = "insert into ".DB_PREFIX."deal_collect(`id_value`,`type`, `user_id`, `create_time`) values ($kan_id,$type,$user_id,$now_time)";
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			ajax_return("已被收藏");exit;
		}else{
			ajax_return("收藏失败");exit;
		}

	}
	//取消收藏
	function delcollect(){
		$id = $_REQUEST['id_value'];
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];

		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}

		//收藏前，检查是否已经收藏
		$is_shou_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_collect where `id_value` = '$id' and `type` = '4' and `user_id` = '$user_id'");
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