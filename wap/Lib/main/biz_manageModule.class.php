<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_manageModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
        
		if(!$GLOBALS['account_info']){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		
		$data['page_title'] = "经营管理";
		if(defined("DC")){
			$is_dc=1;
		}else{
			$is_dc=0;
		}
		$GLOBALS['tmpl']->assign("is_dc",$is_dc);
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("biz_manage.html");
	}
	
	
	
	
}
?>