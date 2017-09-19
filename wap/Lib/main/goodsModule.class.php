<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class goodsModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();



		$area_data = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id'])); //当前城市的所有地区配置
		$shop_cates = load_auto_cache("cache_shop_cate");
        $city_id =  $GLOBALS['city']['id'];
		$param['qid'] = intval($_REQUEST['qid']);
		$param['cate_id'] = intval($_REQUEST['cate_id']); //分类ID
		$param['bid'] = intval($_REQUEST['bid']);  //品牌ID
		$param['page'] = intval($_REQUEST['page']); //分页
		$param['keyword'] = strim($_REQUEST['keyword']); //关键词
		$param['order_type'] = strim($_REQUEST['order_type']); //排序方式


		$request = $param;
		$request['catename'] = $shop_cates[$param['cate_id']]['name'];
		$request['quanname'] = $area_data[$param['qid']]['name'];
		$request['brandname'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = '".$param['bid']."'");

		//获取品牌
		$data = call_api_core("goods","index",$param);
//print_r($data);exit;
		//格式化 quan_list
		$quan_list = $data['quan_list'];

		foreach($quan_list as $k=>$v)
		{
			$tmp_url_param = $param;
			$tmp_url_param['qid']=$v['id'];
			$quan_list[$k]["url"] = wap_url("index","goods",$tmp_url_param);


			foreach($v['quan_sub'] as $kk=>$vv)
			{
				$tmp_url_param = $param;
				$tmp_url_param['qid']=$vv['id'];
				$quan_list[$k]["quan_sub"][$kk]["url"] = wap_url("index","goods",$tmp_url_param);
			}
		}
		$data['quan_list'] = $quan_list;

		foreach($data['navs'] as $k=>$v)
		{
			if($param['order_type']==$v['code'])
			{
				$request['ordername'] = $v['name'];
			}
		}		
		$GLOBALS['tmpl']->assign("request",$request);
		
		//格式化bcate_list的url
		$bcate_list = $data['bcate_list'];
		foreach($bcate_list as $k=>$v)
		{
			if($v['id'] == $param['cate_id']){
				$request['catename'] = $v['name'];
			}
			$tmp_url_param = $param;
			$tmp_url_param['cate_id']=$v['id'];			
			
			$bcate_list[$k]["url"] = wap_url("index","goods",$tmp_url_param);
			
			foreach($v['bcate_type'] as $kk=>$vv)
			{				
				$tmp_url_param = $param;
				$tmp_url_param['cate_id']=$vv['id'];

				$bcate_list[$k]["bcate_type"][$kk]["url"]= wap_url("index","goods",$tmp_url_param);
			}
		}
		$data['bcate_list'] = $bcate_list;
//	print_r($data['bcate_list']);exit;
		
		//格式化 brand_list
		$brand_list = $data['brand_list'];
		foreach($brand_list as $k=>$v)
		{		
			$tmp_url_param = $param;
			$tmp_url_param['bid']=$v['id'];					
			$brand_list[$k]["url"] = wap_url("index","goods",$tmp_url_param);
				
		}
		$data['brand_list'] = $brand_list;
		//end quan_list


		//重写navs 排序的url
		$navs = $data['navs'];
		
		foreach($navs as $k=>$v)
		{
			$tmp_url_param = $param;
			$tmp_url_param['order_type'] = $v['code'];			
			$navs[$k]['url'] = wap_url("index","goods",$tmp_url_param);
		}
		$data['navs'] = $navs;
		//end navs
		if(isset($data['page']) && is_array($data['page'])){

			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}

//    print_r($data);exit;
		//获取城市的名字

		$request['brandname'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city where `id` = '$city_id'");

		$GLOBALS['tmpl']->assign("request",$request);
		$GLOBALS['tmpl']->assign("cate_id",$param['cate_id']);
		$GLOBALS['tmpl']->assign("data",$data);
		if($_REQUEST['cate_id']==18||$_REQUEST['cate_id']==20){//18休闲娱乐   20丽人美发
			$GLOBALS['tmpl']->display("entertainment_list.html");
		}else{
			$GLOBALS['tmpl']->display("goods.html");
		}
	}
	


	public function getAllCityList(){
		$where = " `is_effect` = '1' and `is_delete` = '0' and `is_open` = '1' and `pid` = '0'";
		$city_list = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where ".$where." order by `sort` asc");
		foreach($city_list as $key=>$val){
			$rows = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where `is_effect` = '1' and `is_delete` = '0' and `is_open` = '1' and `pid`= '".$val['id']."' order by `sort` asc");
			$city_list[$key]['city_type'] = $rows;
		}
		return $city_list;
	}

}
?>