<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class pintuangoodsdetailModule extends MainBaseModule
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
		$param['data_id'] = strim($_REQUEST['data_id']);


		$request = $param;
		$request['catename'] = $shop_cates[$param['cate_id']]['name'];
		$request['quanname'] = $area_data[$param['qid']]['name'];

		$data = call_api_core("pintuan","index",$param);
		$data['pintuan_id'] = $param['data_id'];

//print_r($data);exit;


		//检查会员是否已经收藏此商品
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];

		$ret = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."deal_collect where `user_id` = '$user_id'  and `id_value` = '{$param['data_id']}' and `type` = '1'");
		if($ret){
			$data['is_collect'] = 1;
		}else{
			$data['is_collect'] = 0;
		}
		$data['name'] = $data['item']['goods_name'];
		$data['share_url'] = SITE_DOMAIN.'/index.php?ctl=deal&act='.$param['data_id'];
//		print_r($data);exit;
        $GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("pintuan_goods_detail.html");
	}
	public function add_group(){
		global_run();
	    init_app_page();

	    $param=array();
		$param['open_num'] = $_REQUEST['open_num'];
		$param['open_id'] = $_REQUEST['open_id'];
		$param['open_deal_id'] = $_REQUEST['open_deal_id'];
		$param['end_time'] = $_REQUEST['end_time'];
		$param['user_id'] = $_REQUEST['user_id'];
		$param['status'] = $_REQUEST['status'];
		$param['is_user_id'] = $_REQUEST['is_user_id'];
		$param['price'] = $_REQUEST['price'];

		$data = call_api_core("pintuan","add_group",$param);

		ajax_return($data);
	}
	//添加收藏
	function addcollect(){
		$pin_id = $_REQUEST['activity_id'];
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$type = '2';//收藏类型   1商品  2拼团   3砍价
		//收藏前，检查是否已经收藏
		$is_shou = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where `id_value` = '$pin_id' and `type` = '2' and `user_id` = '$user_id'");
		if($is_shou){
			ajax_return("已被收藏");exit;
		}
		$sql = "insert into ".DB_PREFIX."deal_collect(`id_value`,`type`, `user_id`, `create_time`) values ($pin_id,$type,$user_id,$now_time)";
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

		//收藏前，检查是否已经收藏
		$is_shou_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_collect where `id_value` = '$kan_id' and `type` = '2' and `user_id` = '$user_id'");
		if($is_shou_id){
			//执行取消收藏
			$sql = "delete from ".DB_PREFIX."deal_collect where `id` = '$is_shou_id'";
			$row = $GLOBALS['db']->query($sql);
			if($row){
				ajax_return("取消失败成功");exit;
			}else{
				ajax_return("请稍后重试");exit;
			}
		}else{
			ajax_return("取消失败成功");exit;
		}


	}
}
?>