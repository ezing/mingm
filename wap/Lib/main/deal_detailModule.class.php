<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class deal_detailModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data_id = intval($_REQUEST['data_id']);

		
		$data_id = intval($_REQUEST['data_id']);
		
		$data1 = call_api_core("deal","index",array("data_id"=>$data_id,"type"=>1));


		
		$data = call_api_core("deal","detail",array("data_id"=>$data_id));
        
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("data1",$data1);		
		$GLOBALS['tmpl']->display("imgtext_particulars.html");
	}
	
	
}
?>