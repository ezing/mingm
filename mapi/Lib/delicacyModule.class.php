<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class delicacyApiModule extends MainBaseApiModule
{
	
	/**
	 * 美食列表接口
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

        $cate_id = intval($GLOBALS['request']['cate_id'])?intval($GLOBALS['request']['cate_id']):8;//商品分类ID
        //缓存下来的地区配置
        $area_data = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));
        $quan_id = intval($GLOBALS['request']['qid']); //商圈id

        $city_id = intval($GLOBALS['city']['id']);//城市分类ID
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        $order_type=strim($GLOBALS['request']['order_type']);


        $ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
        $ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
        $xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
        $xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
        $ypoint =  $m_latitude = $GLOBALS['geo']['ypoint'];  //ypoint
        $xpoint = $m_longitude = $GLOBALS['geo']['xpoint'];  //xpoint


        /*输出分类*/
        $sql = "select * from ".DB_PREFIX."deal_cate where `is_delete` = 0";
        $bcate_list = $GLOBALS['db']->getAll($sql);


        /*输出商圈*/
        $quan_list=getQuanList($city_id);

//print_r($quan_list);exit;

        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;

        //获取拼团列表
        $now_time = NOW_TIME;
        $where = " `is_effect` = '1' and `begin_time` <= '$now_time' and `end_time` >= '$now_time' and `cate_id` = '$cate_id'";
        if(!empty($city_id)){
            $where .= " and `city_id` = '".$city_id."'";
        }

        $order = "";
        /*排序
                 智能排序和 离我最的 是一样的 都以距离来升序来排序，只有这两种情况有传经纬度过来，就没有把 这两种情况写在 下面的判断里，写在上面了。
                default 智能（默认），nearby  离我，avg_point 评价，newest 最新，buy_count 人气，price_asc 价低，price_desc 价高 */
        if($order_type=='newest')/*最新*/
            $order= " id desc  ";
        elseif($order_type=='click_num')/*点击量*/
            $order= " buy_count desc  ";
        elseif($order_type=='price_asc')/*价格升*/
            $order= " one_price asc  ";
        else
            $order= " id desc  ";

        $sql = "select `id`,`name`,`sub_name`,`img`,`buy_count`,`icon`,`current_price`,`origin_price`,`supplier_id` from ".DB_PREFIX."deal where ".$where." order by ".$order." limit ".$limit;
        $list = $GLOBALS['db']->getAll($sql);
//echo $sql;exit;
//        print_r($list);exit;
        foreach($list as $k=>$v){
            $list[$k]['current_price'] = round($v['current_price'],2);
            $list[$k]['origin_price'] = round($v['origin_price'],2);
            $list[$k]['supplier_location'] = $GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$v['supplier_id']);
            $list[$k]['icon'] =  get_abs_img_root(get_spec_image($v['img'],90, 82,1));
        }

        $count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where ".$where);
        $page_total = ceil($count/$page_size);

        $root['city_id']= $city_id;

        $root['quan_id']= $quan_id;
        $root['cate_id']=$cate_id;
        $root['bcate_list'] = $bcate_list?$bcate_list:array();
        $root['quan_list'] = $quan_list?$quan_list:array();
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        $root['page_title'].="美食列表";
        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        $root['item'] = $list?$list:array();
       //排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
        $root['navs'] = array(
            array("name"=>"默认","code"=>"default"),
            array("name"=>"好评","code"=>"avg_point"),
            array("name"=>"最新","code"=>"newest"),
            array("name"=>"报名量","code"=>"buy_count")
        );
		return output($root);
	}


	
}
?>