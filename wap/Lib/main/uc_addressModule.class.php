<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_addressModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$cart = intval($_REQUEST['cart']);
		$order_id = intval($_REQUEST['order_id']);
		if($cart)
		{
			if($order_id)
				es_session::set("wap_cart_set_address_url",wap_url("index","cart#order",array("id"=>$order_id)));
			else
				es_session::set("wap_cart_set_address_url",wap_url("index","cart#check"));
		}
		else
		{
			es_session::set("wap_cart_set_address_url","");
		}
		
		$param=array();		
		$data = call_api_core("uc_address","index",$param);
		
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		foreach($data['consignee_list'] as $k=>$v){
			$data['consignee_list'][$k]['url']= wap_url("index","uc_address#add",array("id"=>$v['id']));
			$data['consignee_list'][$k]['del_url']=wap_url('index','uc_address#del',array('id'=>$v['id']));
			$data['consignee_list'][$k]['dfurl']=wap_url('index','uc_address#set_default',array('id'=>$v['id']));			
		}

//		print_r($data);exit;
		
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_address_index.html");
	}
	
	public function add()
	{
		global_run();
		init_app_page();
		$cart = intval($_REQUEST['cart']);
		$order_id = intval($_REQUEST['order_id']);
		if($cart)
		{
			if($order_id)
				es_session::set("wap_cart_set_address_url",wap_url("index","cart#order",array("id"=>$order_id)));
			else
				es_session::set("wap_cart_set_address_url",wap_url("index","cart#check"));
		}
		else
		{
			es_session::set("wap_cart_set_address_url","");
		}
		
		$param=array();
		$param['id'] = intval($_REQUEST['id']);		
		$data = call_api_core("uc_address","add",$param);

		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}



		$one_list = $this->getCityList();
		$data['city_list'] = $this->getAreaList($one_list);
//		print_r($data);exit;
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("uc_address_add.html");
	}
	

	public function save()
	{

		global_run();
		$param=array();
		$param['consignee'] = intval($_REQUEST['consignee']);
		$param['region_lv1'] = intval($_REQUEST['region_lv1']);
		$param['region_lv2'] = intval($_REQUEST['region_lv2']);
		$param['region_lv3'] = intval($_REQUEST['region_lv3']);
		$param['region_lv4'] = intval($_REQUEST['region_lv4']);
		$param['address'] = strim($_REQUEST['address']);
		$param['mobile'] = strim($_REQUEST['mobile']);
		$param['consignee'] = strim($_REQUEST['consignee']);
		$param['zip'] = strim($_REQUEST['zip']);
		$data = call_api_core("uc_address","save",$param);
// 		print_r($data);exit;
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$result['info'] = "";
			$result['url'] = wap_url("index","user#login");
			ajax_return($result);
		}else{
			if($data['add_status']==0){
					$result['status'] = 0;
					$result['info']=$data['infos'];
					ajax_return($result);	
			}elseif($data['add_status']==1){
					$result['status'] = 1;
					$wap_cart_set_address_url = es_session::get("wap_cart_set_address_url","");
					if($wap_cart_set_address_url)
						$result['url'] = $wap_cart_set_address_url;
					else
						$result['url'] = wap_url("index","uc_address");
					ajax_return($result);					
			}
		}
		
		

	}
	//ajax提交表单
	public function saves(){

		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];


		$region_lv1 = 1;
		$region_lv2 = intval($_REQUEST['region_lv2']);
		$region_lv3 = intval($_REQUEST['region_lv3']);
		$region_lv4 = intval($_REQUEST['region_lv4']);
		$address = strim($_REQUEST['address']);
		$mobile = strim($_REQUEST['mobile']);
		$consignee = strim($_REQUEST['consignee']);
		$zip = strim($_REQUEST['zip']);
		$is_default = $_REQUEST['checked'] ? strim($_REQUEST['checked']):0;

		//检查此会员是否有其他默认的地址
		if($is_default == 1){
			$sql = "select `id` from ".DB_PREFIX."user_consignee where `user_id` = '$user_id' and `is_default` = 1";
			$ret = $GLOBALS['db']->getOne($sql);
			if($ret){
				$update_sql = "update ".DB_PREFIX."user_consignee set `is_default` = 0 where `id` = $ret";
				$uret = $GLOBALS['db']->query($update_sql);
			}
		}

		$sql = "insert into ".DB_PREFIX."user_consignee ( `user_id`, `region_lv1`, `region_lv2`, `region_lv3`, `region_lv4`, `address`, `mobile`, `zip`, `consignee`, `is_default`) values ('$user_id','$region_lv1','$region_lv2','$region_lv3','$region_lv4','$address','$mobile','$zip','$consignee','$is_default')";
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			$url = SITE_DOMAIN.'/mingming/wap/index.php?ctl=uc_address';
			ajax_return($url);exit;
		}else{
			ajax_return(0);exit;
		}
	}
   public function show(){
	   global_run();

	   $id = intval($_REQUEST['id']);
	   $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where `id` = '$id'");
	   $one_list = $this->getCityList();
	   $data['city_list'] = $this->getAreaList($one_list);
	   //查省市区的名字
	   $data['sheng_name'] =  $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal_city where `id` = '{$data['region_lv2']}'");
	   $data['shi_name'] =  $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal_city where `id` = '{$data['region_lv3']}'");
	   $data['qu_name'] =  $GLOBALS['db']->getOne("select `name`  from ".DB_PREFIX."area where `id` = '{$data['region_lv4']}'");
	   //获取当前省下的所有市
	   $data['shi_list'] = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where `pid` = '{$data['region_lv2']}'order by `sort` desc");
	   $data['qu_list'] = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."area where `city_id` = '{$data['region_lv3']}' and `pid` = 0 order by `sort` asc");
//	   print_r($data);exit;
	   $GLOBALS['tmpl']->assign("data",$data);
	   $GLOBALS['tmpl']->display("modify_add.html");

 }

	/**
	 *执行修改
     */
	public function update(){
		$s_user_info = es_session::get("user_info");
		$user_id = $s_user_info['id'];

		$address_id = $_REQUEST['address_id'];
		$region_lv1 = 1;
		$region_lv2 = intval($_REQUEST['region_lv2']);
		$region_lv3 = intval($_REQUEST['region_lv3']);
		$region_lv4 = intval($_REQUEST['region_lv4']);
		$address = strim($_REQUEST['address']);
		$mobile = strim($_REQUEST['mobile']);
		$consignee = strim($_REQUEST['consignee']);
		$zip = strim($_REQUEST['zip']);
		$is_default = $_REQUEST['checked'] ? strim($_REQUEST['checked']):0;
		$ret = $GLOBALS['db']->query("delete from ".DB_PREFIX."user_consignee where `id` = '$address_id'");
		if($ret){
			$sql = "insert into ".DB_PREFIX."user_consignee ( `user_id`, `region_lv1`, `region_lv2`, `region_lv3`, `region_lv4`, `address`, `mobile`, `zip`, `consignee`, `is_default`) values ('$user_id','$region_lv1','$region_lv2','$region_lv3','$region_lv4','$address','$mobile','$zip','$consignee','$is_default')";
			$ret = $GLOBALS['db']->query($sql);
			if($ret){
				$url = SITE_DOMAIN.'/mingming/wap/index.php?ctl=uc_address';
				ajax_return($url);exit;
			}else{
				ajax_return(0);exit;
			}
		}else{
			ajax_return(0);exit;
		}

	}
	/**
	 *执行修改
	 */
	public function del(){


		$address_id = $_REQUEST['address_id'];

		$ret = $GLOBALS['db']->query("delete from ".DB_PREFIX."user_consignee where `id` = '$address_id'");
		if($ret){
			$url = SITE_DOMAIN.'mingming/wap/index.php?ctl=uc_address';
			ajax_return($url);exit;

		}else{
			ajax_return(0);exit;
		}

	}
/*	public function update(){
		global_run();

		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where `id` = '$id'");
		$one_list = $this->getCityList();
		$data['city_list'] = $this->getAreaList($one_list);
		//查省市区的名字
		$data['sheng_name'] =  $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal_city where `id` = '{$data['region_lv2']}'");
		$data['shi_name'] =  $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal_city where `id` = '{$data['region_lv3']}'");
		$data['qu_name'] =  $GLOBALS['db']->getOne("select `name`  from ".DB_PREFIX."area where `id` = '{$data['region_lv4']}'");
		//获取当前省下的所有市
		$data['shi_list'] = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where `pid` = '{$data['region_lv2']}'order by `sort` desc");
		$data['qu_list'] = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."area where `city_id` = '{$data['region_lv3']}' and `pid` = 0 order by `sort` asc");
//	   print_r($data);exit;
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("modify_add.html");

	}*/
/*	public function del()
	{
			global_run();
			$param=array();
			$param['id'] = intval($_REQUEST['id']);
			$data = call_api_core("uc_address","del",$param);
			
			if($data['del_status']==1){
					$result['status'] = 1;
					$wap_cart_set_address_url = es_session::get("wap_cart_set_address_url","");
					if($wap_cart_set_address_url)
						$result['url'] = $wap_cart_set_address_url;
					else
						$result['url'] = wap_url("index","uc_address");				
					ajax_return($result);			
			}else{
					$result['status'] =0;					
					ajax_return($result);		
			}		
	
	}*/
	
	
	public function set_default()
	{
			global_run();
			$param=array();
			$param['id'] = intval($_REQUEST['id']);
			$data = call_api_core("uc_address","set_default",$param);
			
			if($data['set_status']==1){
					$result['status'] = 1;
					$wap_cart_set_address_url = es_session::get("wap_cart_set_address_url","");
					if($wap_cart_set_address_url)
						$result['url'] = $wap_cart_set_address_url;
					else
						$result['url'] = wap_url("index","uc_address");				
					ajax_return($result);			
			}else{
					$result['status'] =0;					
					ajax_return($result);		
			}		
	
	}


	public function getCityList($id = 0){
		//获取城市列表
		$sql = "select `id`,`name` from ".DB_PREFIX."deal_city where `pid` = '$id'order by `sort` asc";
		$one_list = $GLOBALS['db']->getAll($sql);
		foreach($one_list as $key=>$val){

			$one_list[$key]['sub_list'] = $this->getCityList($val['id']);
		}
		return $one_list;
	}
/*	public function getAreaList($id = 0){
		//获取城市列表
		$sql = "select `id`,`name` from ".DB_PREFIX."deal_city where `is_effect` = 1 and `is_delete` = 0 and `is_open` = '1' and `pid` = '0' order by `sort` asc";
		$one_list = $GLOBALS['db']->getAll($sql);
		foreach($one_list as $key=>$val){
			$sqls = "select `id`,`name` from ".DB_PREFIX."area where `city_id` = '{$val['id']}' order by `sort` desc";
			$one_list[$key]['sub_list'] =  $GLOBALS['db']->getAll($sqls);
		}
		return $one_list;
	}*/
	public function getAreaList($one_list){
		//获取城市列表

		foreach($one_list as $key=>$val){
			foreach($val['sub_list'] as $k=>$v){
				foreach($v as $kk=>$vv){
					$sqls = "select `id`,`name` from ".DB_PREFIX."area where `city_id` = '{$v['id']}' and `pid` = '0' order by `sort` desc";
//					echo $sqls;exit;
					$one_list[$key]['sub_list'][$k]['sub_list'] =  $GLOBALS['db']->getAll($sqls);
				}

			}

		}
		return $one_list;
	}

    //ajax获取市
	public function getShi($id){
		$id = $_REQUEST['id'];

		$sql = "select `id`,`name` from ".DB_PREFIX."deal_city where `pid` = '$id'order by `sort` desc";
		$list = $GLOBALS['db']->getAll($sql);
		$str = '';
		foreach($list as $key=>$val){
			$str .= "<li value='".$val['id']."'>".$val['name']."</li>";
		}
		ajax_return($str);exit;
	}
	//ajax获取区
	public function getQu($id){
		$id = $_REQUEST['id'];

		$sql = "select `id`,`name` from ".DB_PREFIX."area where `city_id` = '$id' and `pid` = 0 order by `sort` asc";
		$list = $GLOBALS['db']->getAll($sql);
		$str = '';
		foreach($list as $key=>$val){
			$str .= "<li value='".$val['id']."'>".$val['name']."</li>";
		}
		ajax_return($str);exit;
	}
}?>