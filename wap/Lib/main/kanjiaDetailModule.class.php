<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class kanjiaDetailModule extends MainBaseModule
{
	public function index()
	{
		$data = array();
		$id = $_REQUEST['data_id'];//活动id
//获取分享的链接
//		$url = call_api_core("kanjiaDetail","index",array('data_id'=>$id));
        $data['share_url'] = SITE_DOMAIN.'/wap/index.php?ctl=kanjiadetail&data_id='.$id;

		$now_time = NOW_TIME;
		//砍价详情
		$sql = "select  * from ".DB_PREFIX."kan_jia where `id` ='$id' ";
        $row = $GLOBALS['db']->getRow($sql);
		//砍价商品信息
		$goods_sql = "select `id`,`name`,`sub_name`,`img`,`description`,`origin_price` from ".DB_PREFIX."deal where `id` = '{$row['deal_id']}'";
		$goods_info = $GLOBALS['db']->getRow($goods_sql);
//		print_r($goods_info);exit;
		//商品评论
		$goods_ping_sql = "select s.`id`,s.`title`,s.`content`,s.`create_time`,s.`is_img`,s.`images_cache`,s.`point`,u.`user_name` from ".DB_PREFIX."supplier_location_dp as s left join ".DB_PREFIX."user as u on s.user_id=u.id where s.deal_id = '{$row['deal_id']}' and s.`status` = '1' order by s.`create_time` desc limit 1";
		$dp_list = $GLOBALS['db']->getRow($goods_ping_sql);
		$dp_list['create_time'] = $dp_list['create_time']?to_date($dp_list['create_time']):'';
		if($dp_list['is_img']==1){
			$dp_list['images'] = unserialize($dp_list['images_cache']);
		}
		$xingxing = '';
		if($dp_list['point'] != 0){
			for($i=0;$i<5;$i++){
				if($i < $dp_list['point']){
					$xingxing .= "<li></li>";
				}else{
					$xingxing .= "<li class='kong'></li>";
				}
			}
		}
		$dp_list['xingxing'] = $xingxing?$xingxing:'';
		//被砍次数
		$beikan_detail_sql = "select count(*) from " . DB_PREFIX . "kan_jia_detail where `kan_id` = '$id'";
		$beikan_num = $GLOBALS['db']->getOne($beikan_detail_sql);

		//检查此会员是否已砍过
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		$user_beikan_detail_sql = "select count(*) from ".DB_PREFIX."kan_jia_detail where `kan_id` = '$id' and `user_id` = '$user_id'";
		$user_beikan_num = $GLOBALS['db']->getOne($user_beikan_detail_sql);//检查会员是否已经砍价

		if ($user_beikan_num && $user_beikan_num > 0) {
			$data['is_kan'] = 1;
		}else{
			$data['is_kan'] = 0;
		}
//		print_r($dp_list);exit;
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];

		//检查会员是否已经收藏此商品
		$ret = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."deal_collect where `user_id` = '$user_id'  and `id_value` = '$id' and `type` = '3'");
		if($ret){
			$data['is_collect'] = 1;
		}else{
			$data['is_collect'] = 0;
		}

		$data['dp_url'] = wap_url("index", 'dp_list', array('data_id'=>$id, 'type'=>'kanjia') );//全部评价
		$data['id'] = $row['id'];
		$data['deal_id'] = $row['deal_id'];
		$data['kan_num'] = $beikan_num;
		$data['kan_price'] = round($row['kan_price'],2);
		$data['name'] = $goods_info['name'];
		$data['sub_name'] = $goods_info['sub_name'];
		$data['sub_name'] = $goods_info['sub_name'];
		$data['img'] = $goods_info['img'];
		$data['description'] = $goods_info['description'];
		$data['origin_price'] = round($goods_info['origin_price'],2);
		$data['youhui_price'] = round($goods_info['origin_price']-$row['kan_price'],2);
		$data['dp_list'] = $dp_list;
		$data['page_title'] = "砍价详情";
// print_r($data);exit;
		$GLOBALS['tmpl']->assign('data',$data);
		$GLOBALS['tmpl']->display("kanjia_goods_detail.html");
	}


   //砍一刀

	public function kanyidao()
	{
		//1.判断此活动是否在生存周期,有效  2.判断此会员是否已砍过价    3检查是否达到砍价的次数  4检查是否达到最低砍价
		//检查用户,用户密码
		$activity_id = $_REQUEST['activity_id'];
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$sql = "select `kan_price`,`kan_num`,`min_price` from ".DB_PREFIX."kan_jia where `id` = '$activity_id' and `begin_time` <= '$now_time' and `end_time` >= '$now_time' and `is_effect` = '1'";
		$row = $GLOBALS['db']->getRow($sql);
//		 ajax_return($sql);
		$beikan_detail_sql = "select count(*） from " . DB_PREFIX . "kan_jia_detail where `kan_id` = '$activity_id'";
		$beikan_num = $GLOBALS['db']->getOne($beikan_detail_sql);//总共被砍次数

		$user_beikan_detail_sql = "select count(*) from ".DB_PREFIX."kan_jia_detail where `kan_id` = '$activity_id' and `user_id` = '$user_id'";
		$user_beikan_num = $GLOBALS['db']->getOne($user_beikan_detail_sql);//检查会员是否已经砍价
//		ajax_return($user_beikan_detail_sql);exit;
//		ajax_return ($user_beikan_num);exit;
		if ($user_beikan_num && $user_beikan_num > 0) {
			ajax_return('您已砍过价，不能再进行砍价！');
			exit;
		}
		if ($row['kan_price']){
			  //是否已达到最高砍价次数
				if($beikan_num <$row['kan_num']){

					//检查是否达到最低砍价  当前砍得价钱要高于最低砍价价钱，才可以进行砍价
					$cha_price = round($row['kan_price'] - $row['min_price'],2);//如果砍价与最低金额相差一元则不允许再砍价
					if($row['kan_price'] > $row['min_price'] &&  $cha_price  >= '1'){

						$youhui_price = round($this->randFloat(),2);//被砍掉的金额
						$prices = round($row['kan_price'] - $youhui_price,2);
						//修改数据库的金额
						$sql1 = "update ".DB_PREFIX."kan_jia SET `kan_price`='$prices' WHERE `id` = '$activity_id'";
						$result = $GLOBALS['db']->query($sql1);
						if($result){//添加砍价记录

							$sql2 = "insert into ".DB_PREFIX."kan_jia_detail(`kan_id`, `user_id`, `add_time`) values ('$activity_id','$user_id','$now_time')";
							$ret = $GLOBALS['db']->query($sql2);
							if($ret){
								ajax_return($youhui_price);exit;
								$data['youhui_price'] = $youhui_price;
								$data['kan_price'] = $prices;
								$data['beikan_num'] = $beikan_num+1;
								$data['status'] = 1;
								ajax_return($data);exit;
							}
						}else{
							ajax_return('请稍后再砍价！');//已达到最低砍价金额
							exit;
						}


					}else{
						ajax_return('不能再进行砍价！');//已达到最低砍价金额
						exit;
					}

				}else{
					ajax_return('此活动已达到最高砍价次数，不能再进行砍价！');
					exit;
				}

		}else{
			ajax_return('此活动已失效！');
			exit;
		}

	}

	//生成随机金额,带有小数点 eg： 1.2    0.9
	function randFloat($min=0, $max=2){
		return $min + mt_rand()/mt_getrandmax() * ($max-$min);
	}

    //添加收藏
	function addcollect(){
		$kan_id = $_REQUEST['activity_id'];
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];
		if(empty($user_id)){
			ajax_return("请先登录");exit;
		}
		$now_time = NOW_TIME;
		$type = '3';//收藏类型   1商品  2拼团   3砍价
		//收藏前，检查是否已经收藏
		$is_shou = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where `id_value` = '$kan_id' and `type` = '3' and `user_id` = '$user_id'");
		if($is_shou){
			ajax_return("已被收藏");exit;
		}
		$sql = "insert into ".DB_PREFIX."deal_collect(`id_value`,`type`, `user_id`, `create_time`) values ($kan_id,$type,$user_id,$now_time)";
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
		$is_shou_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_collect where `id_value` = '$kan_id' and `type` = '3' and `user_id` = '$user_id'");
		if($is_shou_id){
			//执行取消收藏
			$sql = "delete from ".DB_PREFIX."deal_collect where `id` = '$is_shou_id'";
			$row = $GLOBALS['db']->query($sql);
			if($row){
				ajax_return("取消收藏成功");exit;
			}else{
				ajax_return("请稍后重试");exit;
			}
		}else{
			ajax_return("取消收藏成功");exit;
		}


	}


}
?>