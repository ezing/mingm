<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_accountModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data = call_api_core("uc_account","index");
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}

		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("user_info",$data['user_info']);
		$GLOBALS['tmpl']->display("uc_account.html");
	}
	
	
	public function save()
	{
		global_run();
		init_app_page();
		$param['user_name'] = strim($_REQUEST['user_name']);
		$param['user_email'] = strim($_REQUEST['email']);
		$param['sex'] = strim($_REQUEST['industry']);
		$param['brithday'] = strim($_REQUEST['brithday']);
		// var_dump($param);die;
		$data = call_api_core("uc_account","save_new",$param);
		// var_dump($data);die;
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['jump'] = wap_url("index","user#login");
			ajax_return($data);
		}
		
		if($data['status'])
		{
			// $data['jump'] = wap_url("index","user#personal");
			// ajax_return($data);
			app_redirect(wap_url("index","user#personal"));
		}
		else
		{
			ajax_return($data);
		}
	}
	
	public function phone()
	{
		global_run();
		init_app_page();
		
		$data = call_api_core("uc_account","index");
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("user_info",$data['user_info']);
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		$GLOBALS['tmpl']->display("uc_account_phone.html");
	}
	
	
}
?>