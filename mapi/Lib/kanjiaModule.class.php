<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class kanjiaApiModule extends MainBaseApiModule
{
	
	/**
	 * 砍价属性接口
	 * 输入：
*/

	public function index()
	{

                $root = array();

                $page = intval($GLOBALS['request']['page']); //分页
                $page=$page==0?1:$page;
                $order_type=strim($GLOBALS['request']['order_type']);
                $price_asc=strim($GLOBALS['request']['price_asc']);

                $page_size = PAGE_SIZE;
                $limit = (($page-1)*$page_size).",".$page_size;

                //获取拼团列表
                $order = "id desc";
                if($order_type=='click_num'){
                    $order = " deal_id desc  ";
                }
                if($price_asc=="price_asc"){
                    $order = " kan_price asc";
                }
                                    
                

                $sql = "select * from ".DB_PREFIX."kan_jia order by ".$order;
        	    $list = $GLOBALS['db']->getAll($sql);
                $list_array = array();
                foreach ($list as $key => $value) {
                    $l_array = array();
                    $l_array['id'] = $value['id'];
                    $l_array['kan_price'] = round($value['kan_price'],2);
                    $goods_info = $GLOBALS['db']->getRow("select `id`,`sub_name`,`icon`,`brief` from ".DB_PREFIX."deal where id = ".$value['deal_id']);
                    $l_array['sub_name'] = $goods_info['sub_name'];
                    $l_array['icon'] = $goods_info['icon'];
                    $l_array['brief'] = $goods_info['brief'];
                    $list_array[] = $l_array;

                }
                $count=count($list);
                $page_total = ceil($count/$page_size);


                $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
                $root['page_title'].="酒店属性列表";
                $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
                $root['item'] = $list_array?$list_array:array();

                return output($root);
	}


	
}
?>