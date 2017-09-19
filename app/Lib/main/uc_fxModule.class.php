<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


require_once APP_ROOT_PATH.'system/model/user.php';
class uc_fxModule extends MainBaseModule
{
	public function index()
	{
		 app_redirect(url("index","uc_fx#my_fx"));
	}
	
	
	/**
	 * 我的推广
	 */
	public function my_fx(){
	    global_run();
	    if(check_save_login()!=LOGIN_STATUS_LOGINED)
	    {
	        app_redirect(url("index","user#login"));
	    }
	    init_app_page();
	    $user_info = $GLOBALS['user_info'];
	    
	    $u_level = array();
		if($user_info['fx_level']>0){
		    //取出等级信息
		    $u_level = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."fx_level where id = ".$user_info['fx_level']);

		    //独立出用户等级数据
		    $salarys = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where  fx_level=0 and level_id=".$u_level['id']);
		}
		$u_level = $u_level?array_merge($u_level,$salarys):array();


		//默认佣金
		$default_salary = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where level_id = 0 and fx_level=0");
		if (empty($u_level)){  //无等级的情况下取默认
		    $u_level = $default_salary;
		}
            
        //获取我的分销数据
        require_once APP_ROOT_PATH."app/Lib/page.php";

		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		require_once APP_ROOT_PATH.'system/model/deal.php';
		
                
        //获取用户已经分销的数据
        require_once APP_ROOT_PATH."system/model/deal.php";		
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;		
		$page_size = app_conf("DEAL_PAGE_SIZE");
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");

        $join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id ";
        $join .= ' left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id ';
        //$ext_condition = " d.buy_type <> 1  and d.is_fx = 2 and ud.user_id = ".$user_info['id'];
		 $ext_condition = " d.buy_type <> 1  and (d.is_fx=1 or ( d.is_fx = 2 and ud.user_id = ".$user_info['id']."))";
        $sort_field = " ud.is_effect desc,ud.add_time desc ";
        $append_field = " ,ud.sale_count,ud.sale_total,ud.sale_balance,s.name as supplier_name,ud.is_effect as ud_is_effect,ud.type as ud_type ";
        $deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),$join,$ext_condition,$sort_field,$append_field);

        $deal_list = $deal_result['list'];
        $total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);

        	
        $page = new Page($total,$page_size);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        
        
        //获取ID
        foreach ($deal_list as $k=>$v){
            $ids[] = $v['id'];
        }

        //佣金比率
        $deal_salarys = $GLOBALS['db']->getAll('select * from '.DB_PREFIX.'deal_fx_salary where deal_id in('.implode(',', $ids).') and fx_level=0 ');
        foreach ($deal_salarys as $k=>$v){
            if ($v['fx_salary']==0){    //如果商品没设置 分销佣金比率，默认为用户当前的
                $v['fx_salary'] = $u_level['fx_salary'];
                $v['fx_salary_type'] = $u_level['fx_salary_type'];
            }else{
                $v['fx_salary'] = $v['fx_salary'];
            }
            
            $f_deal_salarys[$v['deal_id']] = $v;
        }		
        
		foreach ($deal_list as $K=>$v){
		    $temp_data['id'] = $v['id'];
		    $temp_data['name'] = $v['name'];
		    $temp_data['sub_name'] = $v['sub_name'];
		    $temp_data['icon'] = $v['icon'];
		    $temp_data['current_price'] = round($v['current_price'],2);
		    $temp_data['buy_count'] = $v['buy_count'];
		    
		    $deal_salary = $f_deal_salarys[$v['id']]['fx_salary'];
		    $fx_salary_type = $f_deal_salarys[$v['id']]['fx_salary_type'];
		    
		    $temp_data['fx_salary'] = $fx_salary_type?round($deal_salary*100,2):round($deal_salary,2);
		    $temp_data['fx_salary_type'] = $fx_salary_type;
		    $temp_data['fx_salary_money'] = $fx_salary_type?round($deal_salary*$temp_data['current_price'],2):round($deal_salary,2);
		    
		    //用户获得的佣金信息
		    $temp_data['sale_count'] = $v['sale_count'];
		    $temp_data['sale_total'] = round($v['sale_total'],2);
		    $temp_data['sale_balance'] = round($v['sale_balance'],2);
		    
		    $temp_data['url'] = url("index","deal#".$v['id'],array("r"=>base64_encode($user_info['id'])));
		    $temp_data['share_url'] = SITE_DOMAIN.$temp_data['url'];
            $temp_data['ud_is_effect'] = $v['ud_is_effect'];
            $temp_data['ud_type'] = $v['ud_type'];
            $temp_data['supplier_name'] = $v['supplier_name'];
            $temp_data['stores_url'] = url("index","stores#index",array("supplier_id"=>$v['supplier_id'],"r"=>base64_encode($user_info['id'])));

            if($v['end_time']<= NOW_TIME && $v['end_time']!=0){
                $temp_data['end_status'] = '已过期';
            }elseif(($v['begin_time']<= NOW_TIME || $v['begin_time']==0) && ($v['end_time']> NOW_TIME || $v['end_time']==0)){
                $temp_data['end_status'] = '进行中';
            }elseif($v['begin_time']> NOW_TIME && $v['begin_time']!=0){
                $temp_data['end_status'] = '预告中';
            }
            $list[] = $temp_data;
		}
		if($u_level['fx_salary_type']==1){
			$u_level['fx_salary'] = round($u_level['fx_salary']*100,2);
		}
		$GLOBALS['tmpl']->assign("list",$list);

	    $GLOBALS['tmpl']->assign("user_info",$user_info);
	    $GLOBALS['tmpl']->assign("u_level",$u_level);
	    $GLOBALS['tmpl']->assign("n_level",$n_level);

        $GLOBALS['tmpl']->assign('qrcode_url',url("index","uc_home#mall",array("r"=>base64_encode($user_info['id']))));
	    //通用模版参数定义
	    assign_uc_nav_list();//左侧导航菜单
	    $GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	    $GLOBALS['tmpl']->assign("page_title","我的推广"); //title
	    $GLOBALS['tmpl']->display("uc/uc_fx_my_fx.html"); 
	}
	
	/**
	 * 单品推广
	 */
	public function deal_fx()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		init_app_page();
		$user_info = $GLOBALS['user_info'];
		
		
		
		$u_level = array();
		if($user_info['fx_level']>0){
		    //取出等级信息
		    $u_level = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."fx_level where id = ".$user_info['fx_level']);

		    //独立出用户等级数据
		    $salarys = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where  fx_level=0 and level_id=".$u_level['id']);
		}
		$u_level = $u_level?array_merge($u_level,$salarys):array();


		//默认佣金
		$default_salary = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_salary where level_id = 0 and fx_level=0");
		if (empty($u_level)){  //无等级的情况下取默认
		    $u_level = $default_salary;
		}
		
		
		require_once APP_ROOT_PATH."app/Lib/page.php";

		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		require_once APP_ROOT_PATH.'system/model/deal.php';
		
		$s_deal_name = strim($_REQUEST['fx_deal_search']);
		$condition = '';
		if($s_deal_name !='')
		{
		    $condition = " and d.name like '%".$s_deal_name."%'";
		}
                
        //获取用户已经分销的数据
        $cur_deals = $GLOBALS['db']->getAll('select deal_id from '.DB_PREFIX.'user_deal where user_id = '.$user_info['id']);
        if($cur_deals){
            foreach ($cur_deals as $v){
                $not_deal_ids[] = $v['deal_id'];
            }
            $condition .= " and d.id not in (".  implode(",", $not_deal_ids).") ";
        }
                
        $join = ' left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id ';
        $ext_condition = " d.is_fx=2 and d.buy_type <> 1  ".$condition;
        $field = ' ,s.name as supplier_name';
        $orderby =' d.id desc ';
		$result = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),$join,$ext_condition,$orderby,$field);

		$count = $GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'deal d where '.$result['condition']);

		$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
        //获取ID
        foreach ($result['list'] as $k=>$v){
            $ids[] = $v['id'];
        }

        //佣金比率
        $deal_salarys = $GLOBALS['db']->getAll('select * from '.DB_PREFIX.'deal_fx_salary where deal_id in('.implode(',', $ids).') and fx_level=0 ');
        

        foreach ($deal_salarys as $k=>$v){
            if ($v['fx_salary']==0){    //如果商品没设置 分销佣金比率，默认为用户当前的
                $v['fx_salary'] = $u_level['fx_salary'];
                $v['fx_salary_type'] = $u_level['fx_salary_type'];
            }
            $f_deal_salarys[$v['deal_id']] = $v;
        }		
  
		foreach ($result['list'] as $K=>$v){
		    $deal_salary = '';
		    $fx_salary_type = '';
		    
		    $temp_data['id'] = $v['id'];
		    $temp_data['name'] = $v['name'];
		    $temp_data['sub_name'] = $v['sub_name'];
		    $temp_data['icon'] = $v['icon'];
		    $temp_data['current_price'] = round($v['current_price'],2);
		    $temp_data['supplier_name'] = $v['supplier_name'];
		    $temp_data['buy_count'] = $v['buy_count'];
		  
		    $deal_salary = $f_deal_salarys[$v['id']]['fx_salary'];
		    $fx_salary_type = $f_deal_salarys[$v['id']]['fx_salary_type'];

		    $temp_data['fx_salary'] = $fx_salary_type?round($deal_salary*100,2):round($deal_salary,2);
		    $temp_data['fx_salary_type'] = $fx_salary_type;
		    $temp_data['fx_salary_money'] = $fx_salary_type?round($deal_salary*$temp_data['current_price'],2):round($deal_salary,2);
		    $temp_data['url'] = url("index","deal#".$v['id'],array("r"=>base64_encode($user_info['id'])));
		    $temp_data['share_url'] = SITE_DOMAIN.$temp_data['url'];
		    $temp_data['stores_url'] = url("index","stores#index",array("supplier_id"=>$v['supplier_id'],"r"=>base64_encode($user_info['id'])));
            $list[] = $temp_data;
		}
		
		
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->assign('s_deal_name',$s_deal_name);
		
        $GLOBALS['tmpl']->assign('qrcode_url',url("index","uc_home#mall",array("r"=>base64_encode($user_info['id']))));
		//通用模版参数定义
		assign_uc_nav_list();//左侧导航菜单
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->assign("page_title","单品分销"); //title
		$GLOBALS['tmpl']->display("uc/uc_fx_deal_fx.html"); //title
	}
        
        public function add_fx_deal(){
            global_run();
            if(check_save_login()!=LOGIN_STATUS_LOGINED)
            {
                $result['status']=1000;
                $result['info'] = "用户未登录";
                ajax_return($result);
            }
            require_once APP_ROOT_PATH.'system/model/fx.php';
            $user_id = $GLOBALS['user_info']['id'];
            $deal_id = intval($_REQUEST['deal_id']);
            
            if(add_user_fx_deal($user_id, $deal_id)){
                $result['status']=1;
                $result['info'] = "添加成功";
                $result['jump'] = url('index','uc_fx#deal_fx');
            }else{
                $result['status'] = 0;
                $result['info'] = "添加失败";
            }
            ajax_return($result);
        }
	
        public function do_is_effect(){
            global_run();
            if(check_save_login()!=LOGIN_STATUS_LOGINED)
            {
                $result['status']=1000;
                $result['info'] = "用户未登录";
                ajax_return($result);
            }
            require_once APP_ROOT_PATH.'system/model/fx.php';
            $user_id = $GLOBALS['user_info']['id'];
            $deal_id = intval($_REQUEST['deal_id']);
            if(do_is_effect($user_id, $deal_id)){
                $result['status']=1;
                $result['info'] = "操作成功";
                $result['jump'] = url('index','uc_fx#my_fx');
            }else{
                $result['status'] = 0;
                $result['info'] = "操作失败";
            }
            ajax_return($result);
        }
        
        public function del_user_deal(){
            global_run();
            if(check_save_login()!=LOGIN_STATUS_LOGINED)
            {
                $result['status']=1000;
                $result['info'] = "用户未登录";
                ajax_return($result);
            }
            require_once APP_ROOT_PATH.'system/model/fx.php';
            $user_id = $GLOBALS['user_info']['id'];
            $deal_id = intval($_REQUEST['deal_id']);
            if(del_user_deal($user_id, $deal_id)){
                $result['status']=1;
                $result['info'] = "删除成功";
                $result['jump'] = url('index','uc_fx#my_fx');
            }else{
                $result['status'] = 0;
                $result['info'] = "删除失败";
            }
            ajax_return($result);
        }
	
    
    
}
?>