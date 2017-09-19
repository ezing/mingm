<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class kanjiaModule extends MainBaseModule
{
	public function index()
	{
		/**
		 * 前端全运行函数，生成系统前台使用的全局变量
		 * 1. 定位城市 GLOBALS['city'];
		 * 2. 加载会员 GLOBALS['user_info'];
		 * 4. 加载推荐人与来路
		 * 5. 更新购物车
		 */

		global_run();
        //初始化页面信息，如会员登录状态的显示输出
		init_app_page();


		$param['page'] = intval($_REQUEST['page']); //分页
		$param['order_type'] = strim($_REQUEST['order_type']); //排序方式
		$param['price_asc'] = strim($_REQUEST['price_asc']);
		$request = $param;
		$data = call_api_core("kanjia","index",$param);


		

//		print_r($data);exit;
		$order_type['newest']= 'newest';
		$order_type['click_num']= 'click_num';
		$order_type['price_asc']= 'price_asc';

		$GLOBALS['tmpl']->assign('order_type',$order_type);
		// var_dump($data);die;
		$GLOBALS['tmpl']->assign('data',$data);
		$GLOBALS['tmpl']->display("kanjia_list.html");
	}


}
?>