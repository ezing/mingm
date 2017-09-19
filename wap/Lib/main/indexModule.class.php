<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class indexModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		
		$data = call_api_core("index","wap");
		

		
		
		foreach ($data['supplier_list'] as $k=>$v){
		    $data['supplier_list'][$k]['url'] = wap_url("index", 'store', array('data_id'=>$v['id']));
		    $price = $GLOBALS['db']->getOne("select min(current_price) as price from ".DB_PREFIX."deal where supplier_id = ".$v['supplier_id']);
		    $data['supplier_list'][$k]['price'] = round($price,2);
		}
		
		foreach ($data['cate_list'] as $k=>$v){
		    $data['cate_list'][$k]['url'] = wap_url("index", 'tuan', array('cate_id'=>$v['id']));
		    if( ($v['is_new'] == 1 && $v['recommend']) or $v['is_new'] == 1){
		        $data['cate_list'][$k]['show_hot_new'] = 'NEW';
		        $data['cate_list'][$k]['show_hot_new_low'] = 'new';
		    }elseif ($v['recommend'] == 1){
		        $data['cate_list'][$k]['show_hot_new'] = 'HOT';
		        $data['cate_list'][$k]['show_hot_new_low'] = 'hot';
		    }
		}
		 
		
		foreach($data['advs'] as $k=>$v)
		{
			
			$data['advs'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		foreach($data['advs2'] as $k=>$v)
		{
		    	
		    $data['advs2'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		foreach ($data['deal_list'] as $k=>$v){
		    $deal_param['data_id'] = $v['id']; 
		    $data['deal_list'][$k]['url'] = wap_url("index", 'deal', $deal_param);
		    
		    
		        $distance = $v['distance'];
		        $distance_str = "";
		        if($distance>0)
		        {
		            if($distance>1500)
		            {
		                $distance_str =  round($distance/1000)."km";
		            }
		            else
		            {
		                $distance_str = round($distance)."米";
		            }
		        }
		        $data['deal_list'][$k]['distance'] = $distance_str;
		     
		}
		
		foreach($data['indexs'] as $k=>$v)
		{
			$data['indexs'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		// 计算首页导航隐藏页数
		$data_nav_row = intval(ceil(count($data['indexs']) / 8));
		$data_nav_row_str = '';
		for($i=0; $i<$data_nav_row; $i++){
		    $data_nav_row_str .= '<li class=""></li>';
		}
		$GLOBALS['tmpl']->assign("data_nav_row_str",$data_nav_row_str);
	  
	  	// var_dump($data['huodong_info']);die;
		$GLOBALS['tmpl']->assign("data",$data);
		
		if($GLOBALS['geo']['xpoint']>0||$GLOBALS['geo']['ypoint']>0)
		{
			$GLOBALS['tmpl']->assign('has_location',1);
		}
		else
		{
			$GLOBALS['tmpl']->assign('has_location',0);
		}
		
		if (es_cookie::get('is_app_down')){
			$GLOBALS['tmpl']->assign('is_show_down',0);//用户已下载
		}else{
			$GLOBALS['tmpl']->assign('is_show_down',1);//用户未下载
		}		
		
		
		//输出友情链接
		$links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1  order by sort desc");
			
		foreach($links as $kk=>$vv)
		{
			if(substr($vv['url'],0,7)=='http://')
			{
				$links[$kk]['url'] = str_replace("http://","",$vv['url']);
			}
		}			
		
		$GLOBALS['tmpl']->assign("links",$links);


		$shop_cates = load_auto_cache("cache_shop_cate");
		
		$param['cate_id'] = intval($_REQUEST['cate_id']); //分类ID
		$param['bid'] = intval($_REQUEST['bid']);  //品牌ID
		$param['page'] = intval($_REQUEST['page']); //分页
		$param['keyword'] = strim($_REQUEST['keyword']); //关键词
		$param['order_type'] = strim($_REQUEST['order_type']); //排序方式
		

		$request = $param;
		$request['catename'] = $shop_cates[$param['cate_id']]['name'];
		$request['brandname'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = '".$param['bid']."'");
		//获取品牌
		$data1 = call_api_core("scores","index",$param);
		
		foreach($data1['navs'] as $k=>$v)
		{
			if($param['order_type']==$v['code'])
			{
				$request['ordername'] = $v['name'];
			}
		}		
		$GLOBALS['tmpl']->assign("request",$request);
		
		//格式化bcate_list的url
		$bcate_list = $data1['bcate_list'];
		foreach($bcate_list as $k=>$v)
		{		
			$tmp_url_param = $param;
			$tmp_url_param['cate_id']=$v['id'];			
			
			$bcate_list[$k]["url"] = wap_url("index","scores",$tmp_url_param);
			
			foreach($v['bcate_type'] as $kk=>$vv)
			{				
				$tmp_url_param = $param;
				$tmp_url_param['cate_id']=$vv['id'];

				$bcate_list[$k]["bcate_type"][$kk]["url"]= wap_url("index","scores",$tmp_url_param);
			}
		}
		$data1['bcate_list'] = $bcate_list;
		//end bcate_list
		
		//格式化 brand_list
		$brand_list = $data1['brand_list'];
		foreach($brand_list as $k=>$v)
		{		
			$tmp_url_param = $param;
			$tmp_url_param['bid']=$v['id'];					
			$brand_list[$k]["url"] = wap_url("index","scores",$tmp_url_param);
				
		}
		$data1['brand_list'] = $brand_list;
		//end quan_list
		
		//重写navs 排序的url
		$navs = $data1['navs'];
		
		foreach($navs as $k=>$v)
		{
			$tmp_url_param = $param;
			$tmp_url_param['order_type'] = $v['code'];			
			$navs[$k]['url'] = wap_url("index","scores",$tmp_url_param);
		}
		$data1['navs'] = $navs;
		//end navs
		if(isset($data1['page']) && is_array($data1['page'])){

			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data1['page']['data_total'],$data1['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		$GLOBALS['tmpl']->assign("data1",$data1);	


		
		$GLOBALS['tmpl']->display("index.html");
	}
	
	
}
?>