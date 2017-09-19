<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class pinTuanModule extends MainBaseModule
{

	public function index()
	{
		global_run();
		init_app_page();

		$area_data = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id'])); //当前城市的所有地区配置
		$shop_cates = load_auto_cache("cache_shop_cate");

		$param['qid'] = intval($_REQUEST['qid']);
		$param['cate_id'] = intval($_REQUEST['cate_id']); //分类ID
		$param['page'] = intval($_REQUEST['page']); //分页
		$param['keyword'] = strim($_REQUEST['keyword']); //关键词
		$param['order_type'] = strim($_REQUEST['order_type']); //排序方式


		$request = $param;
		$request['catename'] = $shop_cates[$param['cate_id']]['name'];
		$request['quanname'] = $area_data[$param['qid']]['name'];

		$data = call_api_core("pintuan","index",$param);
		//格式化 quan_list
//		$quan_list = $data['quan_list'];
	/*	foreach($quan_list as $k=>$v)
		{
			$tmp_url_param = $param;
			$tmp_url_param['qid']=$v['id'];
			$quan_list[$k]["url"] = wap_url("index","pintuan",$tmp_url_param);

			foreach($v['quan_sub'] as $kk=>$vv)
			{
				$tmp_url_param = $param;
				$tmp_url_param['qid']=$vv['id'];
				$quan_list[$k]["quan_sub"][$kk]["url"] = wap_url("index","pintuan",$tmp_url_param);
			}
		}*/
//		$data['quan_list'] = $quan_list;

		// print_r($data['item']);exit;

        $GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("pintuan_list.html");
	}

}
?>