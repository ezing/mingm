<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class supplierModule extends HizBaseModule
{
    public function __construct()
    {
        parent::__construct();
        global_run();

    }
	public function index()
	{		
	      /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['hiz_account_info'];
        $account_id = $account_info['id'];
        
        /* 获取参数 */
        $begin_time = strim($_REQUEST['begin_time']);
        $end_time = strim($_REQUEST['end_time']);
        
        $begin_time_s = to_date(to_timespan($begin_time,"Y-m-d"),"Y-m-d");
        $end_time_s =  to_date(to_timespan($end_time,"Y-m-d"),"Y-m-d");
        
       
        $condition='';
        if($begin_time_s)
        	$condition .=" and stat_time >= '".$begin_time_s."' ";
        if($end_time_s)
        	$condition .=" and stat_time <= '".$end_time_s."' ";
        
        /* 业务逻辑部分 */
        $conditions .= " where agency_id = ".$account_id; // 查询条件
        
        $sql_count = " select count(distinct(id)) from " . DB_PREFIX . "supplier";
        $sql = " select * from " . DB_PREFIX . "supplier";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count.$conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);

        $list = $GLOBALS['db']->getAll($sql.$conditions . " order by id desc limit " . $limit);
        
        foreach ($list as $k => $v) {
            $list[$k]['edit_url'] = url("hiz", "supplier#edit", array(
                "id" => $v['id'],
            ));
            $list[$k]['preview_url'] = url("index","preview#store",array("id"=>$v['id'],"type"=>0));
            $list[$k]['update_url'] = url("hiz","supplier#update",array("id"=>$v['id']));
            $list[$k]['deal_sale_money']=floatval($GLOBALS['db']->getOne("select sum(deal_sale_money) as deal_sale_money from ".DB_PREFIX."supplier_statements where supplier_id=".$v['id'].$condition));
            $list[$k]['store_pay_money']=floatval($GLOBALS['db']->getOne("select sum(store_pay_money) as store_pay_money from ".DB_PREFIX."supplier_statements where supplier_id=".$v['id'].$condition));
            $list[$k]['dc_sale_money']=floatval($GLOBALS['db']->getOne("select sum(confirm_money) as confirm_money from ".DB_PREFIX."dc_supplier_statements where supplier_id=".$v['id'].$condition));

        }
        
        /* 数据 */
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("hiz", "supplier#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("hiz", "supplier#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("hiz", "supplier"));
	    $GLOBALS['tmpl']->assign("index_url", url("hiz", "supplier#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("hiz", "supplier#no_online_index"));

	    $GLOBALS['tmpl']->assign("begin_time",$begin_time_s);
	    $GLOBALS['tmpl']->assign("end_time",$end_time_s);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "商户列表管理");
        $GLOBALS['tmpl']->display("pages/supplier/index.html");
	}
	
	public function no_online_index()
	{
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['hiz_account_info'];
	    $account_id = $account_info['id'];
	
	    /* 业务逻辑部分 */
	    $conditions .= " where is_publish = 0 and agency_id= ".$account_id; // 查询条件

	    $sql_count = " select count(*) from " . DB_PREFIX . "supplier_submit ";
	    $sql = " select * from " . DB_PREFIX . "supplier_submit ";
	
	    /* 分页 */
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if ($page == 0)
	        $page = 1;
	    $limit = (($page - 1) * $page_size) . "," . $page_size;
	
	    $total = $GLOBALS['db']->getOne($sql_count . $conditions);
	    $page = new Page($total, $page_size); // 初始化分页对象
	    $p = $page->show();
	    $GLOBALS['tmpl']->assign('pages', $p);
	    
	    $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
	
	    foreach ($list as $k => $v) {
	        $list[$k]['edit_url'] = url("hiz","supplier#publish",array("id"=>$v['id']));
	        $list[$k]['del_url'] = url("hiz","supplier#del",array("id"=>$v['id']));
	    }

	    /* 数据 */
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("hiz", "supplier#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("hiz", "supplier#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("hiz", "supplier"));
	    $GLOBALS['tmpl']->assign("index_url", url("hiz", "supplier#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("hiz", "supplier#no_online_index"));
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "待审核商户");
	    $GLOBALS['tmpl']->display("pages/supplier/index.html");
	}
	
	public function publish(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['hiz_account_info'];
	 
	    $account_id = $account_info['id'];
	
	    $id = intval($_REQUEST['id']);

	
	    $supplier_info=$GLOBALS['db']->getRow("select ss.*,dc.name as city_name from ".DB_PREFIX."supplier_submit as ss left join ".DB_PREFIX."deal_city as dc on ss.city_id=dc.id where ss.id=".$id);

	
	    $go_list_url=url("hiz", "supplier#no_online_index");

	    /* 数据 */

	    $GLOBALS['tmpl']->assign("vo", $supplier_info); // 门店所有数据
	    $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
	
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("hiz", "supplier"));
	    $GLOBALS['tmpl']->assign("page_title", "商户审核");
	    $GLOBALS['tmpl']->display("pages/supplier/publish.html");
	}
	
	public function do_save_publish(){
		
			$id=intval($_REQUEST['id']);
			$data=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_submit where is_publish=0 and id=".$id);
			if(!$data)
			{
					$result['status'] = 0;
					$result['info'] = "非法数据";
					ajax_return($result);
			}
		
			
			$info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name='".$data['account_name']."' or mobile='".$data['account_mobile']."'");
			if($info)
			{
				$result['status'] = 0;
				$result['info'] = "帐号名或手机号已被注册";
				ajax_return($result);
			}
			
			
			$cate_config = unserialize($data['cate_config']);
			$data['deal_cate_id'] = $cate_config['deal_cate_id'];
			$data['deal_cate_type_list'] = $cate_config['deal_cate_type_id'];
			$data['area_list'] = unserialize($data['location_config']);
		
		
			if($data['location_id']==0)
			{

		
					//先创建商户
					$supplier_info['name'] = $data['name'];
					$supplier_info['bank_info'] = $data['h_bank_info'];
					$supplier_info['bank_user'] = $data['h_bank_user'];
					$supplier_info['bank_name'] = $data['h_bank_name'];
					$supplier_info['preview'] = $data['h_supplier_logo'];
					//公司信息
					$supplier_info['h_name'] = $data['h_name'];
					$supplier_info['h_faren'] = $data['h_faren'];
					$supplier_info['h_tel'] = $data['h_tel'];
					$supplier_info['is_effect'] =1;
					$supplier_info['city_id'] = $data['city_id'];
					$supplier_info['agency_id'] = $data['agency_id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$supplier_info);
					$location_info['is_main'] = 1;
					$supplier_id =$GLOBALS['db']->insert_id();
					
				$location_info['name'] = $data['name'];
				$location_info['address'] = $data['address'];
				$location_info['tel'] = $data['tel'];
				$location_info['xpoint'] = $data['xpoint'];
				$location_info['ypoint'] = $data['ypoint'];
				$location_info['supplier_id'] = $supplier_id;
				$location_info['open_time'] = $data['open_time'];
				$location_info['city_id'] = $data['city_id'];
				$location_info['deal_cate_id'] = $data['deal_cate_id'];
				$location_info['preview'] = $data['h_supplier_image'];
				$location_info['biz_license'] = $data['h_license'];
				$location_info['biz_other_license'] = $data['h_other_license'];
				$location_info['is_effect'] = 1;
					
				 $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$location_info);
				 $data['location_id'] =$GLOBALS['db']->insert_id();
				foreach($data['deal_cate_type_list'] as $deal_cate_type_id)
				{
					$link = array();
					$link['location_id'] = $data['location_id'];
					$link['deal_cate_type_id'] = $deal_cate_type_id;
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cate_type_location_link",$link);
				}
					
				foreach($data['area_list'] as $area_id)
				{
					$link = array();
					$link['location_id'] = $data['location_id'];
					$link['area_id'] = $area_id;
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_area_link",$link);
				}
				syn_supplier_location_match($data['location_id']);
				// 插入关键字
				require_once APP_ROOT_PATH."system/model/search_key_words.php";
				insertKeyWordsApi($data['location_id'], 4);
			}
		
			if($data['location_id']>0)
			{
		
				//会员未绑定商户，或绑定的不是同名商户管理员，创建一个商户管理员
				$account['account_name'] = $data['account_name'];
				$account['account_password'] = $data['account_password'];
				$account['supplier_id'] = $location_info['supplier_id'];
				$account['is_effect'] = 1;
				$account['description'] = $data['h_name']." 法人：".$data['h_faren']." 电话：".$data['h_tel'];
				$account['update_time'] = NOW_TIME;
				$account['mobile'] = $data['account_mobile'];
				$account['is_main'] = 1;
				$account['allow_delivery'] = 1;
				$account['allow_charge'] = 1;
		

				
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account",$account);
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					//添加成功
					$link = array();
					$link['account_id'] = $id;
					$link['location_id'] = $data['location_id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account_location_link",$link);

					
					//认领成功
					$location_info['biz_license'] = $data['h_license'];
					$location_info['biz_other_license'] = $data['h_other_license'];
					$location_info['id']=$data['location_id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$location_info,$mode='UPDATE','',$querymode = 'SILENT');
						
	
					$GLOBALS['db']->query("update ".DB_PREFIX."supplier_submit set is_publish=1 where id=".intval($_REQUEST['id']));
					
					
					
					if($data['user_id']>0){
	
						$user_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$data['user_id']);
						$user_info['is_merchant'] = 1;
						$user_info['merchant_name'] = $account['account_name'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info,$mode='UPDATE','',$querymode = 'SILENT');
					}
						
					$result['status'] = 1;
					$result['info'] = "审核成功";
					$result['jump'] = url("hiz","supplier#index");
					 
					ajax_return($result);

				}
				else
				{
					$result['status'] = 0;
					$result['info'] = "审核失败，请联系系统管理员";
					$result['jump'] = url("hiz","supplier#no_online_index");
					ajax_return($result);
				}
			}
		
	}
	
	public function edit()
	{
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['hiz_account_info'];
	 
	    $account_id = $account_info['id'];
	
	    $id = intval($_REQUEST['id']);

	
	    $supplier_info=$GLOBALS['db']->getRow("select s.*, sa.account_name as account_name , sa.mobile as mobile from ".DB_PREFIX."supplier as s left join ".DB_PREFIX."supplier_account as sa on s.id=sa.supplier_id where s.id=".$id);

	
	    $go_list_url=url("hiz", "supplier");

	    /* 数据 */

	    $GLOBALS['tmpl']->assign("vo", $supplier_info); // 门店所有数据
	    $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
	
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("hiz", "supplier"));
	    $GLOBALS['tmpl']->assign("page_title", "商户编辑");
	    $GLOBALS['tmpl']->display("pages/supplier/edit.html");
	}
	/**
	 * 时时更新的商户数据
	 */
	public function update(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['hiz_account_info'];
	    $account_id = $account_info['id'];
	    
	    $id = intval($_REQUEST['id']);
        
	    $supplier_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier  where id=".$id." and agency_id = ".$account_id);
	    
	    if (empty($supplier_info)) {
	        showHizErr("数据不存在或没有操作权限！",0,url("hiz","supplier#index"));
	        exit();
	    }
	    $data=array();
	    $preview_img = strim($_REQUEST['preview']); // 缩略图
	    $data['preview'] = replace_domain_to_public($preview_img);
	    $data['name'] = strim($_REQUEST['name']); 
 
	    $data['h_name'] = strim($_REQUEST['h_name']); 
	    $data['h_faren'] = strim($_REQUEST['h_faren']);
	    $data['h_tel'] = strim($_REQUEST['h_tel']); 
	    
	    $data_account=array();
	    $data_account['mobile'] = strim($_REQUEST['mobile']);
	    
	   
	    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier", $data, "UPDATE", " id=" . $id." and agency_id=".$account_id);
	    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_account", $data_account, "UPDATE", " supplier_id=" . $id);

	   
    	$result['status'] = 1;
    	$result['info'] = "修改成功"; 
	    $result['jump'] = url("hiz","supplier#index");
	    
	    ajax_return($result);

	}
	
	/**
	 * 保存时时更新的商户数据
	 * */
	public function do_update(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    // 白名单过滤
	    require_once APP_ROOT_PATH . 'system/model/no_xss.php';
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    
	    $location_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$id." and supplier_id=".$supplier_id);
	    if(empty($location_data)){
	        $result['status'] = 0;
	        $result['info'] = "数据不存在或没有权限操作该数据";
	        ajax_return($result);
	        exit;
	    }
	    
	    //供应商标志图片
	    $preview_img = strim($_REQUEST['preview']); // 缩略图
	    $data['preview'] = replace_domain_to_public($preview_img);
	    
	    //图库
	    $location_images = $_REQUEST['location_images'];
	    foreach ($location_images as $k=>$v){
	        $f_location_images[] = replace_domain_to_public($v);
	    }
	    $data['address'] = strim($_REQUEST['address']); // 地址
	    $data['route'] = strim($_REQUEST['route']); // 交通路线
	    $data['tel'] = strim($_REQUEST['tel']); // 地址
	    $data['contact'] = strim($_REQUEST['contact']); // 联系人
	    $data['open_time'] = strim($_REQUEST['open_time']); // 营业时间
	    $data['api_address'] = strim($_REQUEST['api_address']); // 地图定位的地址
	    $data['xpoint'] = strim($_REQUEST['xpoint']); // 经度
	    $data['ypoint'] = strim($_REQUEST['ypoint']); // 纬度
	    $data['brief'] = btrim(no_xss($_REQUEST['brief'])); // 部门简介
	    $data['adv_img_1'] = btrim(no_xss($_REQUEST['adv_img_1'])); // 门店顶部广告位1
	    $data['adv_img_2'] = btrim(no_xss($_REQUEST['adv_img_2'])); // 门店侧边广告位2
	    $data['location_qq'] = strim($_REQUEST['location_qq']); // 门店客服QQ

	    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location", $data, "UPDATE", " id=" . $id." and supplier_id=".$supplier_id);
        if($GLOBALS['db']->affected_rows()){
            //更新图库
            if(count($f_location_images)>0){
                $GLOBALS['db']->query("delete from ".DB_PREFIX."supplier_location_images where supplier_location_id=".$supplier_id);
              
                foreach($f_location_images as $k=>$v){
                    $imgdata = array();
                    $imgdata['image'] = $v;
                    $imgdata['sort'] = 100;
                    $imgdata['create_time'] = NOW_TIME;
                    $imgdata['supplier_location_id'] = $id;
                    $imgdata['status'] = 1;
                    
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location_images", $imgdata);
                }
            }
            $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location", array("image_count"=>count($f_location_images)), "UPDATE", " id=" . $id." and supplier_id=".$supplier_id);
            
            //更新后更新商户缓存
            rm_auto_cache("store",array("id"=>$id));
            $result['status'] = 1;
            $result['info'] = "修改成功";
        }else{
            $result['status'] = 0;
            $result['info'] = "修改失败，请稍后再试";
        }
	    
        $result['jump'] = url("biz","location#index");
		
		ajax_return($result);
	    
	    
	}
	
	public function del()
	{
	    /* 基本参数初始化 */
	    $id = intval($_REQUEST['id']);
	    $account_info = $GLOBALS['hiz_account_info'];
	    $account_id = $account_info['id'];
	
	    
	    $supplier_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_submit  where id=".$id." and agency_id = ".$account_id);
	     
	    if (empty($supplier_info)) {
	    	showHizErr("数据不存在或没有操作权限！");
	    	exit();
	    }
	    

	    $GLOBALS['db']->query("delete from " . DB_PREFIX . "supplier_submit where id=" . $id . " and agency_id =" . $account_id);
	    $data['status'] = 1;
	    $data['info'] = "删除成功";

	    ajax_return($data);
	}


	

	
}
?>