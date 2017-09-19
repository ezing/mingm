<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class uc_orderModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
//		$param['pay_status'] = intval($_REQUEST['pay_status']);
		$param['id'] = intval($_REQUEST['id']);
		$data = call_api_core("uc_order","index",$param);
// 		print_r($data);exit;
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
		    app_redirect(wap_url("index","user#login"));
		}
		
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
        $all_list = $data['item'];//全部订单列表
		$paied_list = array();//已支付页面
		$pay_list = array();//未支付页面
		foreach($all_list as $key=>$val){
			if($val['pay_status'] == 2){
				$paied_list[] = $all_list[$key];
			}else{
				$pay_list[] = $all_list[$key];
			}
		}
        $data['paied_list'] = $paied_list;
        $data['pay_list'] = $pay_list;
		unset($data['item']);
//		print_r($data);exit;
		$GLOBALS['tmpl']->assign("pay_status",$param['pay_status']);
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_order.html");
	}
	
	public function cancel()
	{
		global_run();
		$param=array();
		$param['id'] = intval($_REQUEST['id']);
		
		$data = call_api_core("uc_order","cancel",$param);
// 		print_r($data);exit;
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		if($data['status']==0)
		{
			showErr($data['info']);
		}
		else
		{
			showErr($data['info'],0,get_gopreview());
	
		}		
	}
	
	/**
	 * 退单（实体商品）页面
	 */
	public function refund()
	{
		global_run();
		init_app_page();
		$item_id = intval($_REQUEST['item_id']);
		
		$data = call_api_core("uc_order","refund",array("item_id"=>$item_id));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		$data['placeholder'] = "请输入退款理由";
		$data['action'] = wap_url("index","uc_order#do_refund");
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("uc_order_message.html");
	}
	
	
	public function do_refund()
	{
		global_run();
		$item_id = intval($_REQUEST['item_id']);		
		$content =  strim($_REQUEST['content']);
		$data = call_api_core("uc_order","do_refund",array("item_id"=>$item_id,"content"=>$content));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['jump'] = wap_url("index","user#login");
		}
		else
		{
			if($data['status'])
			$data['jump'] = get_gopreview();
		}
		
		ajax_return($data);
	}
	
	
	/**
	 * 退券（团购商品）页面
	 */
	public function refund_coupon()
	{
		global_run();
		init_app_page();
		$item_id = intval($_REQUEST['item_id']);
		
		$data = call_api_core("uc_order","refund_coupon",array("item_id"=>$item_id));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		$data['placeholder'] = "请输入退款理由";
		$data['action'] = wap_url("index","uc_order#do_refund_coupon");
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("uc_order_coupon_message.html");
	}
	
	
	public function do_refund_coupon()
	{
		global_run();
		
		$item_id = array();
		if($_REQUEST['item_id'])
		{
			foreach($_REQUEST['item_id'] as $k=>$v)
			{
				$item_id[$k] = intval($v);
			}
		}		
		$content =  strim($_REQUEST['content']);
		$data = call_api_core("uc_order","do_refund_coupon",array("item_id"=>$item_id,"content"=>$content));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['jump'] = wap_url("index","user#login");
		}
		else
		{
			if($data['status'])
				$data['jump'] = get_gopreview();
		}
	
		ajax_return($data);
	}
	
	
	/**
	 * 查看物流
	 */
	public function check_delivery()
	{
		$item_id = intval($_REQUEST['item_id']);
		$data = call_api_core("uc_order","check_delivery",array("item_id"=>$item_id));
		
		if($data['status']==0)
		{
			showErr($data['info']);
		}
		else
		{
			app_redirect($data['url']);
		}
	}
	
	
	/**
	 * 确认收货
	 */
	public function verify_delivery()
	{
		global_run();
		$item_id = intval($_REQUEST['item_id']);
				
		$data = call_api_core("uc_order","verify_delivery",array("item_id"=>$item_id));

		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		if($data['status']==0)
		{
			showErr($data['info']);
		}
		else
		{
			showErr($data['info'],0,get_gopreview());		
		}
	}
	
	
	/**
	 * 拒绝收货
	 */
	public function refuse_delivery()
	{
		global_run();
		init_app_page();
		$item_id = intval($_REQUEST['item_id']);
		
		$data = call_api_core("uc_order","refuse_delivery",array("item_id"=>$item_id));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		$data['placeholder'] = "请输入详细原因";
		$data['action'] = wap_url("index","uc_order#do_refuse_delivery");
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("uc_order_message.html");
	}
	
	
	public function do_refuse_delivery()
	{
		global_run();
		$item_id = intval($_REQUEST['item_id']);
		$content =  strim($_REQUEST['content']);
		$data = call_api_core("uc_order","do_refuse_delivery",array("item_id"=>$item_id,"content"=>$content));
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['jump'] = wap_url("index","user#login");
		}
		else
		{
			if($data['status'])
			$data['jump'] = get_gopreview();
		}
		
		ajax_return($data);
	}
	
}
?>