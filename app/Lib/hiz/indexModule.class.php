<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class indexModule extends HizBaseModule
{
    
	public function index()
	{	
	    //获取权限
	    global_run();
		$account_info = $GLOBALS['hiz_account_info'];
	
		if($account_info){
			app_redirect(url("hiz","supplier"));
		}else{
	   		app_redirect(url("hiz","user#login"));
		}
	}
	
	
}
?>