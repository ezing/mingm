<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class user_centerModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$data = call_api_core("user_center","index",$param);
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		//print_r($data);exit;
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("my_center.html");
	}


	/*
	 * 店铺收藏
	 *	
	 */
	public function dc_location_collect(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		require_once APP_ROOT_PATH."system/model/dc.php";
		$page = intval($_REQUEST['p']);
		if($page==0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	
		$location_collect = get_user_location_collect($limit);
		foreach($location_collect['list']  as $k=>$v){

			$location_collect['list'][$k]['del_url']=url('index','uc_collect#del',array('id'=>$v['link_id'],'type'=>'dc_location'));
		}
		$GLOBALS['tmpl']->assign("list",$location_collect['list']);
		$page = new Page($location_collect['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		$GLOBALS['tmpl']->assign("type","dc_location");
		$GLOBALS['tmpl']->display("uc/uc_log.html");
	}

	/**
	 * 积分日志
	 */
	public function score(){
		global_run();
		init_app_page();
		$param=array();
		$data1 = call_api_core("user_center","index",$param);
	    //$user_info = $GLOBALS['user_info'];
	    
	    //业务逻辑部分
	    //取出积分信息
	    $uc_query_data['cur_score'] = $data1['user_score'];
        $cur_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id=".$data1['group_id']);
        $uc_query_data['cur_gourp'] = $cur_group['id'];
        $uc_query_data['cur_gourp_name'] = $cur_group['name'];
        $uc_query_data['cur_discount'] = floatval(sprintf('%.2f', $cur_group['discount']*10));
	     
	    //分页
	    require_once APP_ROOT_PATH."wap/Lib/page.php";
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if($page==0)
	        $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    require_once APP_ROOT_PATH.'system/model/user_center.php';
	    $data = get_user_log_new($limit,$data1['uid'],'score'); //获取积分数据
	    //分页输出
	    $page = new Page($data['count'],$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
	    //$GLOBALS['tmpl']->assign("user_info",$user_info);
	    $GLOBALS['tmpl']->assign("uc_query_data",$uc_query_data);//{$uc_query_data.cur_score}
	    $GLOBALS['tmpl']->assign('data',$data);
	    // var_dump($data);die;
	    $GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	    $GLOBALS['tmpl']->assign("page_title","鸣鸣o2o - 兑换记录"); //title
	    $GLOBALS['tmpl']->display("integral_record.html");
	    
	}
	public function integral_rule(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("page_title","鸣鸣o2o - 积分规则"); //title
	    $GLOBALS['tmpl']->display("integral_rule.html");
	}
	/**
	  *积分数量
	  */
	public function point(){

		$s_user_info = es_session::get("user_info");
		$user_point = $s_user_info['point'];
		$user_id = $s_user_info['id'];
		$sql = "select `id`,`log_time`,`score` from ". DB_PREFIX ."user_log where `user_id` = '$user_id' and `score` != 0";
		$list = $GLOBALS['db']->getAll($sql);
		$zhichu = array();
		$shouru = array();
		foreach($list as $key=>$val){
			$list[$key]['log_time'] = date("Y-m-d",$val['log_time']);
			if($val['score'] < 0){
				$zhichu[] =  $list[$key];
			}else{
				$shouru[] = $list[$key];
			}
		}
		$data['page_title'] = '积分数量';
		$data['score'] = $user_point;
		$data['zhichu']=$zhichu;
		$data['shouru']=$shouru;

		$GLOBALS['tmpl']->assign('data',$data);
		$GLOBALS['tmpl']->display("integral_count.html");
	}

	public function my_bankcard(){
		global_run();
		init_app_page();

		$data = call_api_core("user_center","my_backcard");
		$GLOBALS['tmpl']->assign("page_title","鸣鸣o2o - 我的银行卡"); //title
		$GLOBALS['tmpl']->assign("data",$data); 
		// var_dump($data['']);die;
	    $GLOBALS['tmpl']->display("my_bankcard.html");
	}



	
}
?>