<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class goodsApiModule extends MainBaseApiModule
{
	
	/**
	 * 商品列表接口
	 * 输入：
	 * cate_id: int 商品分类ID
	 * bid: int 品牌ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 * order_type: string 排序类型(default(默认)/nearby(离我)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
	 * 
	 * 
	 * 
	 * 输出：
	 * cate_id:int 当前分类ID
	 * bid:int 当前品牌ID
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * item:array:array 团购列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 商品ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 商品名称
                    [sub_name] => 镜片代金券 [string] 商品短名称
                    [brief] => 【36店通用】明视眼镜 [string] 商品简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [is_refund] => [int] 随时退 0:否 1:是
                )
         )
	 * bcate_list:array 大类列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int]分类ID
	            [name] => 全部分类 [string] 分类名
	            [iconfont]=> [string] wap端使用的iconfont代码
	            [iconcolor]=> #f0f0f0 [string] 颜色配置 16进度
	            [bcate_type] => Array
	                (
	                    [0] => Array
	                        (
	                            [id] => 0 [int]小分类ID
	                            [cate_id] => 0 [int]父分类ID
	                            [name] => 全部分类 [string] 分类名称
	                        )
	
	                )
	
	        )
	 )
	 * brand_list:array 品牌列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int] 品牌ID
	            [name] => xx [string] 品牌名称
	        )
	 * )
	 * navs:array 排序菜单 
	 * 固定数据如下
	 * array(
			array("name"=>"默认","code"=>"default"),
			array("name"=>"好评","code"=>"avg_point"),
			array("name"=>"最新","code"=>"newest"),
			array("name"=>"销量","code"=>"buy_count"),
			array("name"=>"价格最低","code"=>"price_asc"),
			array("name"=>"价格最高","code"=>"price_desc"),
		);
	 * 
	 */

	public function index()
	{

		$root = array();
		$catalog_id = intval($GLOBALS['request']['cate_id']);//商品分类ID		
		$page = intval($GLOBALS['request']['page']) ? intval($GLOBALS['request']['page']) : 1; //分页
		$keyword = strim($GLOBALS['request']['keyword']);

		$brand_id = intval($GLOBALS['request']['bid']); //品牌id	
		$order_type=strim($GLOBALS['request']['order_type']);
		//$city_id = intval($GLOBALS['city']['id']);//不会是省的城市ID
		 $city_id = intval($GLOBALS['request']['qid']);//不会是省的城市ID
/*		echo $GLOBALS['city']['id']."<br>";
		echo $city_id;*/

		/*输出分类*/
//		$bcate_list = getShopCateList();
		/*输出分类*/
		$bcate_list = getCateList();

		/*输出品牌*/
		$brand_list = getBrandList($catalog_id);

		/*输出商圈*/
		$quan_list=getQuanList($city_id);


		$page_size = PAGE_SIZE;

		$limit = (($page-1)*$page_size).",".$page_size;
//	echo $limit;exit;
//		$ext_condition = " d.buy_type <> 1 and d.is_shop = 1 ";
		$ext_condition = " d.is_delete = 0 and d.is_effect = 1 ";
		if($keyword)
		{
			$ext_condition.=" and d.name like '%".$keyword."%' ";
		}
		if($catalog_id)
		{
			$ext_condition.=" and d.cate_id = '".$catalog_id."' ";
		}
        //检查当前等位是否为区级的
		if(empty($city_id)){

			$cid = $GLOBALS['city']['id'];
			//只能用定位到的市级id去找到区的id   再根据区的id找到街道的id，最后in（区，街道）查商品
			//area表中的city_id代表市的id，pid 为街道的父类，只需匹配city_id，就能找到当前市下面的所有区和街道的id
			$sql = "select `id` from ".DB_PREFIX."area where `city_id` = '$cid'";
			$qu_id = $GLOBALS['db']->getAll($sql);
			$ids = $cid.',';
			foreach($qu_id as $key=>$val){
				$ids .= $val['id'].',';

			}
			$ids = substr($ids,0,-1);
			$ext_condition.=" and d.city_id in (".$ids.") ";
			$shang_sql = "  and city_id in (".$ids.") ";
		}else{

			$sql = "select `pid` from ".DB_PREFIX."deal_city where `id` = '$city_id'";
			$pid = $GLOBALS['db']->getOne($sql);
			$ids = $city_id .'';
			if($pid){
				//等于0说明为区
				$sqls = "select `id` from ".DB_PREFIX."area where `city_id` = '$pid' and `pid` != 0";
				$id_ary = $GLOBALS['db']->getAll($sqls);
				foreach($id_ary as $key=>$val){
					$ids .= $val['id'].',';
				}
				$ids = substr($ids,0,-1);
				$ext_condition.=" and d.city_id in (".$ids.") ";
				$shang_sql =" and city_id in (".$ids.")";
			}else{
				//为假说明街道地址
			/*	$sqls = "select `id` from ".DB_PREFIX."area where `id` = '$city_id'";
				$id = $GLOBALS['db']->getOne($sqls);*/
				$ext_condition.=" and d.city_id = '".$city_id."' ";
				$shang_sql = " and city_id = '".$city_id."'";
			}
		}


		//echo $ext_condition;exit;
		/*if($city_id == $GLOBALS['city']['id'])//选中全城
		{
			//查找当前城市下所有的市deal_city，所有区crea
			$ext_condition.=" and d.city_id = '".$GLOBALS['city']['id']."' ";
		}else{
			$ext_condition.=" and d.city_id = '".$city_id."' ";
		}*/
		if($brand_id)
		{
			$ext_condition.=" and d.brand_id = '".$brand_id."' ";
		}

		$order = "";

		/*排序  
		 智能排序和 离我最的 是一样的 都以距离来升序来排序，只有这两种情况有传经纬度过来，就没有把 这两种情况写在 下面的判断里，写在上面了。
		default 智能（默认），nearby  离我，avg_point 评价，newest 最新，buy_count 人气，price_asc 价低，price_desc 价高 */
		if($order_type=='avg_point')/*评价*/
			$order= " d.avg_point desc  ";
		elseif($order_type=='newest')/*最新*/
			$order= " d.id desc  ";
		elseif($order_type=='buy_count')/*销量*/
			$order= " d.buy_count desc  ";
		else
			$order= " d.id desc  ";


		/*
		 * 流程：找到所有推荐到首页的门店，找到门店下面推荐到首页的商品，商品有可能为多个，显示最近推荐的
		 * */
		$sqls = "select `id` from ".DB_PREFIX."supplier_location as d  where d.`is_effect` = '1' and d.`is_main` = '1' and d.`is_close` = '0'  ".$shang_sql." order by  ".$order."  limit ".$limit;
//echo $sqls;exit;
		$local_id = $GLOBALS['db']->getAll($sqls);
        $deal_id = array();
        foreach($local_id as $key=>$val){
			//找到这些门店下的推荐的商品
			 $sql = "select `deal_id` from ".DB_PREFIX."deal_location_link where `location_id` = '{$val['id']}'";
			 $deal_id[] = $GLOBALS['db']->getAll($sql);
		}



		$deal_ids = array();

        foreach($deal_id as $kk=>$vv){
			$ids = '';
			foreach($vv as $k1=>$v1){

				$ids .= $v1['deal_id'].',';

			}
			$ids = substr($ids,0,-1);
			$deal_ids[$kk] = $ids;
		}


		foreach($deal_ids as $k=>$v){
			$sql = "select `id`,`name`,`sub_name`,`img`,`buy_count`,`icon`,`current_price`,`origin_price`,`brief`,`supplier_id` from ".DB_PREFIX."deal as d  where ".$ext_condition."  and  `id` in (".$v.") and `is_recommend`=1 order by  `id` desc limit 1";
			$row = $GLOBALS['db']->getRow($sql);
			if($row){
				$list[] = $row;
			}
		}

		$count= count($list);

		
		$page_total = ceil($count/$page_size);
		
		$root = array();
		

		$goodses = array();
		foreach($list as $k=>$v)
		{
			$goodses[$k] = format_deal_list_item($v);
			$goodses[$k]['supplier_location'] = $GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$v['supplier_id']);
		}
//print_r($goodses);exit;
		//价格排序
		/*
		 * 	elseif($order_type=='price_asc')价格升
		$order= " d.current_price asc  ";
	    *elseif($order_type=='price_desc')价格降
		*$order= " d.current_price desc  ";
		 *
		 *
		 * */

		if($order_type){
			$order_type = array();
			foreach($goodses as $key=>$val){
				$order_type = $val['current_price'];
			}
			if($order_type =='price_asc' ){
				array_multisort($order_type,SORT_NUMERIC,SORT_DESC,$goodses);
			}
		}

		$root['city_id']= $city_id;
		$root['bid']= $brand_id;
		$root['cate_id']=$catalog_id;
	
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="商品列表";
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		$root['item'] = $goodses?$goodses:array();
		$root['bcate_list'] = $bcate_list?$bcate_list:array();
		$root['brand_list'] = $brand_list?$brand_list:array();
		$root['quan_list'] = $quan_list?$quan_list:array();
		//排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
		$root['navs'] = array(
			array("name"=>"默认","code"=>"default"),
			array("name"=>"好评","code"=>"avg_point"),
			array("name"=>"最新","code"=>"newest"),
			array("name"=>"销量","code"=>"buy_count"),
			array("name"=>"价格最低","code"=>"price_asc"),
			array("name"=>"价格最高","code"=>"price_desc"),
		);
		
		return output($root);
	}
	
}
?>