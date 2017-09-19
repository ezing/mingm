<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class pintuanApiModule extends MainBaseApiModule
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
        $cate_id = intval($GLOBALS['request']['cate_id']);//商品分类ID
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        $order_type=strim($GLOBALS['request']['order_type']);
        $city_id = intval($GLOBALS['city']['id']);//城市分类ID
        $data_id = intval($GLOBALS['request']['data_id']);

        /*输出分类*/
        $bcate_list = getShopCateList();
        /*输出商圈*/
        $quan_list=getQuanList($city_id);

        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;

        //获取拼团列表
        $now_time = NOW_TIME;
        $where = " `is_effect` = '1' and `begin_time` <= '$now_time' and `end_time` >= '$now_time' ";
        if(!empty($city_id)){
            $where .= " and `city_id` = '".$city_id."'";
        }
        if(!empty($cate_id)){
            $where .= " and `cate_id` = '".$cate_id."'";
        }
        $order = "";
        /*排序
                 智能排序和 离我最的 是一样的 都以距离来升序来排序，只有这两种情况有传经纬度过来，就没有把 这两种情况写在 下面的判断里，写在上面了。
                default 智能（默认），nearby  离我，avg_point 评价，newest 最新，buy_count 人气，price_asc 价低，price_desc 价高 */
        if($order_type=='newest')/*最新*/
            $order= " add_time desc  ";
        elseif($order_type=='buy_count')/*销量*/
            $order= " buy_count desc  ";
        elseif($order_type=='price_asc')/*价格升*/
            $order= " one_price asc  ";
        elseif($order_type=='price_desc')/*价格降*/
            $order= " one_price desc  ";
        else
            $order= " add_time desc  ";
        $root['city_id']= $city_id;
        $root['cate_id']=$cate_id;
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        if(!empty($data_id)){
            $sql = "select * from ".DB_PREFIX."pin_tuan where deal_id=".$data_id." and is_effect=1 and begin_time<".NOW_TIME." and end_time>".NOW_TIME;
            $pin_info = $GLOBALS['db']->getRow($sql);
            $pin_list = $pin_info;
            $pin_list['one_price'] = round($pin_info['one_price'],2);
            $pin_list['two_price'] = round($pin_info['two_price'],2);
            $pin_list['three_price'] = round($pin_info['three_price'],2);
            $pin_list['now_time'] = NOW_TIME;
            
            require_once APP_ROOT_PATH."system/model/deal.php";
            $deal_data = get_deal($data_id);
            //商品信息
            $pin_list['goods_name'] = $deal_data['name'];
            $pin_list['sub_name'] = $deal_data['sub_name'];
            $pin_list['goods_img'] = $deal_data['img'];
            $pin_list['notes'] = $deal_data['notes'];
            $pin_list['description'] = $deal_data['description'];
            $pin_list['origin_price'] = round($deal_data['origin_price'],2);
            $pin_list['current_price'] = round($deal_data['current_price'],2);
            //拼团信息
            $open_info = "select * from ".DB_PREFIX."open_group where goods_id = ".$data_id." and group_id = ".$pin_info['id']." and order_id >0 group by is_user_id";
            $group_list = $GLOBALS['db']->getAll($open_info);

            $pin_list['yipin_num'] = count($group_list);
            // $pin_list['sqlsql'] = $open_info;

            $g_array = array();
            foreach ($group_list as $key => $value) {

                $group_array = array();
                $g_count = intval($GLOBALS['db']->getOne("select count(is_user_id) from ".DB_PREFIX."open_group where goods_id = ".$data_id." and group_id = ".$pin_info['id']." and order_id > 0"));
                if($g_count<intval($value['group_num'])){

                    $group_array['id'] = $value['id'];
                    $group_array['group_id'] = $value['group_id'];
                    $group_array['goods_id'] = $value['goods_id'];
                    $group_array['user_id'] = $value['user_id'];
                    $group_array['end_time'] = $value['end_time'];
                    $group_array['end1_time'] = date("Y-m-d",$value['end_time']);
                    $group_array['user_name'] = $GLOBALS['db']->getOne("select `user_name` from ".DB_PREFIX."user where `id` = '{$value['user_id']}' ");
                    $group_array['avatar'] = get_abs_img_root(get_muser_avatar($value['user_id'],"big"))?get_abs_img_root(get_muser_avatar($value['user_id'],"small")):"";
                    $group_array['group_num'] = $value['group_num'];
                    $group_array['group_now'] = $value['group_num']-$g_count;
                    $group_array['price'] = $value['price'];
                    
                }
                $g_array[] = $group_array;
            }
            $root['group'] = $g_array;
            $root['detail_url'] = wap_url("index", 'deal_detail', array('data_id'=>$data_id) );//图文详情
            $root['dp_url'] = wap_url("index", 'dp_list', array('data_id'=>$data_id, 'type'=>'deal') );//全部评价


            /*获点评数据*/
            require_once APP_ROOT_PATH."system/model/review.php";
            require_once APP_ROOT_PATH."system/model/user.php";
            $dp_list = get_dp_list(2,$param=array("deal_id"=>$data_id),"","");
            $format_dp_list = array();
            
            foreach($dp_list['list'] as $k=>$v){
            
                $temp_arr = array();
                 
                $temp_arr['id'] = $v['id'];
                $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
                $temp_arr['content'] = $v['content'];
                $temp_arr['reply_content']= $v['reply_content']?$v['reply_content']:'';
                $temp_arr['point'] = $v['point'];
            
                $uinfo = load_user($v['user_id']);
                $temp_arr['user_name'] = $uinfo['user_name'];
            
                $v['images'] = unserialize($v['images_cache']);
            
                $images = array();
                $oimages = array();
            
                if($v['images']){
                    foreach ($v['images'] as $ik=>$iv){
                        $images[] = get_abs_img_root(get_spec_image($iv,60,60,1));
                        $oimages[] = get_abs_img_root($iv);
                    }
                     
                }
                $temp_arr['images'] = $images;
                $temp_arr['oimages'] = $oimages;
            
            
                $format_dp_list[] = $temp_arr;
            }
            $root['dp_list'] = $format_dp_list;

            $root['page_title'].="拼团详情";
        }else{
            $sql = "select * from ".DB_PREFIX."pin_tuan where ".$where." order by ".$order." limit ".$limit;
            $pin_list = $GLOBALS['db']->getAll($sql);
            foreach($pin_list as $k=>$v){
                $pin_list[$k]['one_price'] = round($v['one_price'],2);
                $pin_list[$k]['two_price'] = round($v['two_price'],2);
                $pin_list[$k]['three_price'] = round($v['three_price'],2);
                $pin_list[$k]['goods_name'] = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where `id` = '{$v['deal_id']}' ");
                $pin_list[$k]['goods_sub_name'] = $GLOBALS['db']->getOne("select `sub_name` from ".DB_PREFIX."deal where `id` = '{$v['deal_id']}' ");
                $pin_list[$k]['brief'] = $GLOBALS['db']->getOne("select `brief` from ".DB_PREFIX."deal where `id` = '{$v['deal_id']}' ");

                $goods_img = $GLOBALS['db']->getOne("select `img` from ".DB_PREFIX."deal where `id` = '{$v['deal_id']}' ");
                $pin_list[$k]['goods_img'] =  get_abs_img_root(get_spec_image($goods_img,90, 82,1));
                $count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."pin_tuan where ".$where);
                $page_total = ceil($count/$page_size);
                $root['page_title'].="拼团列表";
                $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
                $root['bcate_list'] = $bcate_list?$bcate_list:array();
                $root['quan_list'] = $quan_list?$quan_list:array();
            }
        } 
        
        $root['item'] = $pin_list?$pin_list:array();
        
        return output($root);

        
	}
	public function add_group(){
        $user_login_status = check_login();
        if($user_login_status==LOGIN_STATUS_NOLOGIN){
            $root['user_login_status'] = $user_login_status;
            $status = -1;
            $info = '请先登录';
        }
    
        $open_num = intval($GLOBALS['request']['open_num']);
        $open_id = intval($GLOBALS['request']['open_id']);
        $open_deal_id = intval($GLOBALS['request']['open_deal_id']);
        $end_time = intval($GLOBALS['request']['end_time']);
        $user_id = intval($GLOBALS['request']['user_id']);
        $status = intval($GLOBALS['request']['status']);
        $is_user_id = intval($GLOBALS['request']['is_user_id']);
        $price = intval($GLOBALS['request']['price']);
        $goods_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$open_deal_id." and is_effect = 1 and is_delete = 0");
        if($goods_info){

            $group_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."open_group where group_id = ".$open_id." and goods_id = ".$open_deal_id." and user_id= ".$user_id." and group_num=".$open_num." and status=".$status." and order_id >0 ");

            $group_count = $GLOBALS['db']->getOne("select count(is_user_id) from ".DB_PREFIX."open_group where group_id = ".$open_id." and goods_id = ".$open_deal_id." and is_user_id = ".$is_user_id." and order_id >0 ");

            if($group_count>=$open_num){
                $info = "该团已经满人啦，快去寻找别的团或者自己开个团吧";
                $status = 0;
            }else{

                if(!$group_info){
                    $sql = "INSERT INTO `".DB_PREFIX."open_group` (`id`,`group_id`, `group_num`, `goods_id`,`user_id`,`end_time`,`status`,`is_user_id`,`price`) values ('','".$open_id."','".$open_num."','".$open_deal_id."','".$user_id."','".$end_time."','".$status."','".$is_user_id."','".$price."')";
                    $info_list = $GLOBALS['db']->query($sql);

                    $id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."open_group order by id desc limit 1 ");
                    if($info_list){
                        $info = "成功，赶快邀请小伙伴们参团吧";
                        $status = 1;
                        $root['data'] = $id;
                    }else{
                        $info = "失败，请稍后重试";
                        $status = 0;
                        $root['data'] = $id;
                    }
                }else{
                    $info = "您已经开过团了，快邀请小伙伴们参团吧";
                    $status = 0;
                }
            }    
        }
        return output($root,$status,$info);
    }
}
?>