<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_chargeModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();

		
		$param=array();	
		$data = call_api_core("uc_charge","index",$param);
		if(!$GLOBALS['is_weixin'])
		{
		    foreach($data['payment_list'] as $k=>$v)
		    {
		        if($v['code']=="Wwxjspay")
		        {
		            unset($data['payment_list'][$k]);
		        }
		    }
		}
		else
		{
		    foreach($data['payment_list'] as $k=>$v)
		    {
		        if($v['code']=="Upacpwap")
		        {
		            unset($data['payment_list'][$k]);
		        }
		    }
		}
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_charge.html");
	}
	
	public function done()
	{
		global_run();
		init_app_page();		
		$param=array();	
		$param['money'] = floatval($_REQUEST['money']);	
		$param['payment_id'] = intval($_REQUEST['payment_id']);	
		$data = call_api_core("uc_charge","done",$param);
		
		
// 		print_r($data);exit;
		if($data['status']==-1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","user#login");
			ajax_return($ajaxobj);
		}
		elseif($data['status']==1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","payment#done",array("id"=>$data['order_id']));
			ajax_return($ajaxobj);
		}
		elseif($data['status']==2) //sdk
		{
			$ajaxobj['status'] = 2;
			$ajaxobj['sdk_code'] = $data['sdk_code'];
			ajax_return($ajaxobj);
		}
		else
		{
			$ajaxobj['status'] = $data['status'];
			$ajaxobj['info'] = $data['info'];
			ajax_return($ajaxobj);
		}
	}


}
?>