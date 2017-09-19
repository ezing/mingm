<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class SupplierAction extends CommonAction{
	public function index()
	{
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		
		$id = intval($_REQUEST['id']);
		if($id)
			$ex_condition = " and id = ".$id." ";
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
	    if(isset($order))
	    {
	    	$orderby = "order by ".$order." ".$sort;
	    }else 
	    {
	    	 $orderby = "";
	    }

	    	
		
		if(strim($_REQUEST['name'])!='')
		{
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier");
			if($total<50000)
			{
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition  $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition");			
			}
			else
			{
				$kws_div = div_str(trim($_REQUEST['name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE) $ex_condition $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE) $ex_condition");
				
			}
		}
		else
		{
			$list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where 1=1 $ex_condition  $orderby limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where 1=1 $ex_condition");
		}
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		if(OPEN_WEIXIN)
		{
			foreach($list as $k=>$v)
			{
				if($v['platform_status']==1)
				{
					$list[$k]['is_bind'] = M("WeixinAccount")->where("user_id = ".$v['id'])->count();
				}
			}	
		}
		
		foreach($list as $k=>$v)
		{

			$list[$k]['agency_name'] = M("Agency")->where("id = ".$v['agency_id'])->getField('name');
			
		}
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);
			
		$this->display ();
		return;
	}
	public function add()
	{	
		$this->assign("new_sort", M(MODULE_NAME)->max("sort")+1);
		
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['publish_verify_balance'] = round($vo['publish_verify_balance']*100,2);
		$vo['store_payment_rate'] = round($vo['store_payment_rate']*100, 2);
		if($vo['agency_id']>0){
			$vo['share_code']=base64_encode($vo['agency_id']);
		}
		$agency_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."agency where is_effect = 1 and is_delete = 0");
		$this->assign ("agency_list",$agency_list);
		//商户账户信息
		$account_info = M("SupplierAccount")->where("supplier_id=".$id." and is_main=1")->find();
		
		// 遍历目录下的文件，读取所有对应商户的优惠
		$dir_url = APP_ROOT_PATH."system/promote/";
		$dir = opendir($dir_url);
		$i=0;
		while($filename = readdir($dir)) {
		  if($filename!="." && $filename !="..") {
		      $filename_url = $dir_url."/".$filename;
		      if(is_file($filename_url)) {
		          require_once $filename_url;
		          if ($config['is_supplier']) {
		              $promote_list[$i]['lang']   = $lang;
		              $promote_list[$i]['config'] = $config;
		              $promote_list[$i]['url'] = $filename;
		              
		              // 保存所有config和lang，用于显示
		              $class_name = substr($filename, 0, stripos($filename, '_'));
		              $all_config[$class_name] = $config;
		              $all_lang[$class_name]   = $lang;
		              $i++;
		          }
		          unset($lang);
		          unset($config);
		      }  
		    }
		}
		 
		// 获取已经设置的促销
		$promote_model = M('Promote');
		$promote_where['supplier_id'] = $id;
		$promote = $promote_model->where($promote_where)->order('id asc')->select();
		 
		$promote_html = '';
		foreach ($promote as $key=>$value){
		  $value['config'] = unserialize($value['config']);
		  $promote_html .= $this->get_edit_promote_html($value, $all_config, $all_lang);
        }
        
        // 获取所有门店
        $supplier_location_model  = M("SupplierLocation");
        $supplier_location_where['supplier_id'] = $id;
        $supplier_location_where['is_effect']   = 1;
        $supplier_location_result = $supplier_location_model->where($supplier_location_where)->field('id, name, open_store_payment')->order('sort desc')->select();
        
        $supplier_location_html = '';
        foreach ($supplier_location_result as $key=>$value){
            $checked = '';
            if($value['open_store_payment'] == 1){
                $checked = 'checked="checked"';
            }
            $supplier_location_html .= '<label><input type="checkbox" name="location_id[]" value="'.$value['id'].'" '.$checked.'>'.$value['name'].'&nbsp;&nbsp;</label>';
        }
        
        $this->assign('supplier_location_html', $supplier_location_html);
		$this->assign('promote_html', $promote_html);
		$this->assign('promote', $promote);
		$this->assign('promote_list', $promote_list);
		$this->assign("account_info",$account_info);
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	public function delete_promote(){
	    $data['status'] = 0;
	    $model = M('Promote');
	    $id    = intval($_REQUEST['promote_id']);
	    $status = $model->delete($id);
	    if($status){
	        $data['status'] = 1;
	    }
	    echo json_encode($data);
	    exit;
	}
	
	public function get_edit_promote_html($promote, $all_config, $all_lang){
	    $filename = $promote['class_name'].'_promote.php';
	    $calss_name = substr($filename, 0, strripos($filename, '_'));
	    
	    $html = '<div class='.$filename.' >';
	    $html .= '<div><b>'.$all_lang[$calss_name]['name'].'</b></div>&nbsp;';
	     
	    foreach ($all_config[$calss_name] as $key=>$value){
	        if (isset($all_lang[$calss_name][$key])) {
	            
	            if ($key=='discount_type') {
	                $html .= '<input type="hidden" class="textbox" style="width:50px;" name="parameter_'.$calss_name.'_'.$key.'" value="'.$promote['config'][$key].'"> ';
	            }else{
	                $html .= $all_lang[$calss_name][$key].'：<input type="text" class="textbox" style="width:50px;" name="parameter_'.$calss_name.'_'.$key.'" value="'.$promote['config'][$key].'"> ';
	            }
	            
	            
	        }
	    }
	    $html .= ' 描述：<input type="text" class="textbox" style="width:200px;" name="parameter_'.$calss_name.'_description" value="'.$promote['description'].'">';
	    if ($promote['supplier_or_platform'] == 1) {
	        $html .= ' 补贴者：<select name="parameter_'.$calss_name.'_supplier_or_platform"><option value="0">平台</option><option selected="selected" value="1">商户</option></select>';
	    }else{
	        $html .= ' 补贴者：<select name="parameter_'.$calss_name.'_supplier_or_platform"><option selected="selected" value="0">平台</option><option value="1">商户</option></select>';
	    }
	   
	    $html .= ' <a promote_id="'.$promote['id'].'" href="javascript:void(0);" onclick="delRow(this);" style="text-decoration:none;">[-]</a>';
	    $html .= '<div class="blank10"></div></div>';
	    
	    
	    return $html;
	}
	

	/**
	 * ajax请求促销参数的input
	 * @author hhcycj
	 */
	public function get_promote_html(){
	    $filename = strim($_REQUEST['promote_name']);
	    $calss_name = substr($filename, 0, strripos($filename, '_'));
	    $dir_url = APP_ROOT_PATH."system/promote/".$filename;
	    require_once $dir_url;
	    $html = '<div class='.$filename.' >';
	    $html .= '<div><b>'.$lang['name'].'</b></div>&nbsp;';
	    
	    /**
	     * 组合的name用下划线分割，第一个下划线前面（parameter） 用于识别是活动的参数，第二个下划线前面是活动的类名，第三个下划线前面是活动对应的参数名
	     * 如：parameter_Discountamount_discount_limit;
	     *     parameter 要添加进promote 的参数； Discountamount 活动的类名； 活动中$config 数组的 discount_limit
	     */
	    foreach ($config as $key=>$value){
	        if (isset($lang[$key])) {
	            if ($key=='discount_type') {
	                $html .= '<input type="hidden" class="textbox" style="width:50px;" name="parameter_'.$calss_name.'_'.$key.'" value="'.$value.'"> ';
	            }else{
	                $html .= $lang[$key].'：<input type="text" class="textbox" style="width:50px;" name="parameter_'.$calss_name.'_'.$key.'" value="'.$value.'"> ';
	            }
	            
	        }
	    }
	    $html .= ' 描述：<input type="text" class="textbox" style="width:200px;" name="parameter_'.$calss_name.'_description" value="">';
	    $html .= ' 补贴者：<select name="parameter_'.$calss_name.'_supplier_or_platform"><option selected="selected" value="0">平台</option><option value="1">商户</option></select>';
	    $html .= ' <a href="javascript:void(0);" onclick="delRow(this);" style="text-decoration:none;">[-]</a>';
	    $html .= '<div class="blank10"></div></div>';
	    
        if ($html != '') {
            $data['status'] = 1;
            $data['info']   = $html;
            echo json_encode($data);
        }else{
            $data['status'] = 0;
        }
	     
	    
	    
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];

		
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				
				
				if(M("deal")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error (l("该商户下还有商品"),$ajax);
				}
				
				if(M("SupplierLocation")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error ("请先清空所有的分店数据",$ajax);
				}
				//查询子账户
				$sub_accounts = M("SupplierAccount")->field("id,account_name")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->select();
				foreach ($sub_accounts as $k=>$v){
				    $f_sub_accounts[] = $v['id'];
				}
				
				M("SupplierAccount")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				M("SupplierAccountAuth")->where(array ('supplier_account_id' => array ('in', $f_sub_accounts )))->delete();
				
				M("SupplierMoneyLog")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				M("SupplierMoneySubmit")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				
				
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					 
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}					
		
		// 更新数据
		$log_info = $data['name'];
		if(M(MODULE_NAME)->where("name='".$data['name']."'")->find()){
			$this->error("商户名重复");
		}else{
			$list=M(MODULE_NAME)->add($data);	
		}
		if (false !== $list) {
			syn_supplier_match($list);
			//成功提示
			
			$supplier_location['name'] = $data['name'];
			$supplier_location['is_main'] = 1;
			$supplier_location['supplier_id'] = $list;
			M("SupplierLocation")->add($supplier_location);
			
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$list)));
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success("新增成功，请完善资料");
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}
		
		//处理商户帐号信息部分
		$account_id = intval($_REQUEST['account_id']);

		//更新商户账户信息
		$account_ins['supplier_id'] = $data['id'];
		$account_ins['id'] = $account_id;
		$account_ins['account_name'] = strim($_REQUEST['account_name']);
		$account_ins['mobile'] = strim($_REQUEST['mobile']);
		$account_ins['is_effect'] = 1;
		//$account_ins['is_store_payment'] = $data['is_store_payment'];
		 
		if(!$account_ins['id'])
		{
			if(!$account_ins['account_name'])
			{
				$this->error("请输入管理员账户");
			}
			if(!$account_ins['mobile'])
			{
				$this->error("请输入商户手机");
			}
		}
		if (strim($_REQUEST['account_password'])){
		    $account_ins['account_password'] = md5(strim($_REQUEST['account_password']));
		}
		else
		{
			if(!$account_ins['id'])
			{
				$this->error("请输入密码");
			}
		}
		//商户帐号验证
		if(M("SupplierAccount")->where("account_name='".$account_ins['account_name']."' and id<>".$account_id)->count()){
		    
		    $this->error("账户名已被使用！");
		}

		if(M("SupplierAccount")->where("mobile='".$account_ins['mobile']."' and id<>".$account_id)->count()){
		    $this->error("手机号已被使用！");
		}

		
		$data['publish_verify_balance'] = $data['publish_verify_balance']/100;
		$data['store_payment_rate'] = $data['is_store_payment'] == 1 ? $data['store_payment_rate']/100 : 0;
		
		if($_REQUEST['share_code']){
			$data['agency_id'] = base64_decode($_REQUEST['share_code']);
		}
		// 开启到店支付则添加或者更新promote
		if ($data['is_store_payment'] == 1) {
    		// 把支持的 优惠添加到 promote
    		$promote_data = array();
    		foreach ($_REQUEST as $key=>$value){
    		   $value = strim($value);
    	       $is_parameter = substr($key, 0, stripos($key, '_')) == 'parameter';
    	       if ($is_parameter) {
    	           if ($value == '' && substr( $key, strripos($key, '_')+1 ) != 'description' ) {
    	               $this->error('促销活动参数不能为空',0);
    	           }
    	           $remove_parameter   = str_replace('parameter_', '', $key);
    	           $class_name         = substr( $remove_parameter,   0,  stripos($remove_parameter, '_') );
    	           $parameter          = str_replace('parameter_'.$class_name.'_', '', $key);
    	           
    	           if (substr( $key, strripos($key, '_')+1 ) != 'description' ) {
    	               $value = intval($value);
    	           }
    	           $promote_data[$class_name][$parameter] = $value;
    	       }
    		}
    		 
    		if ($promote_data) {
    		    $value_array = array();
    		    foreach ($promote_data as $key=>$value){
    		        require_once APP_ROOT_PATH."system/promote/".$key.'_promote.php';
    		        $serialize = array();
    		        foreach ($value as $k=>$v){
    		            if ( in_array($k, array_keys($config)) ){
    		                $serialize[$k] = $v;
    		            }
    		        }
    		        $serialize     = serialize($serialize );
    		        $value_array[] = "('{$lang['name']}', '{$key}', '0', '{$serialize}', '{$value['description']}', '1', '{$data['id']}', '{$value['supplier_or_platform']}')";
    		        unset($lang);
    		        unset($config);
    		    }
    		    
    		    
    		    $value_array = join(',', $value_array);
    		    $promote_sql = "INSERT INTO `".DB_PREFIX."promote`(`name`, `class_name`, `sort`, `config`, `description`, `type`, `supplier_id`, `supplier_or_platform`) VALUES ".$value_array.
    		    " ON DUPLICATE KEY UPDATE  name=VALUES(name), class_name=VALUES(class_name),  sort=VALUES(sort), config=VALUES(config), description=VALUES(description), type=VALUES(type), supplier_id=VALUES(supplier_id), supplier_or_platform=VALUES(supplier_or_platform)";
    		    $promote_status = M()->query( $promote_sql );
    		    if($promote_status === false){
    		        //错误提示
    		        save_log($log_info.L("UPDATE_FAILED"),0);
    		        $this->error('活动添加失败',0);
    		    }
    		}
		}else{
		      // 如果关闭到店支付，删除所有跟商户有关的promote
		      $promote_model = M("Promote");
		      $promote_model->where("supplier_id=".$data['id'])->delete();
		}
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		if (false !== $list) {
		    
		    // 更新门店表
		    $location_id = $_REQUEST['location_id'];
		    $supplier_location_model = M('SupplierLocation');
		    // 先全部设置为0，把所有门店的到店支付关闭
		    $supplier_location_where['supplier_id'] = $data['id'];
		    $supplier_location_model->where($supplier_location_where)->save(array('open_store_payment'=>0));
		    // 把要开启到店支付的门店开启
		    if( is_array($location_id) && $data['is_store_payment'] == 1){
		        $supplier_location_data = '';
		        foreach ($location_id as $key=>$value){
		            $supplier_location_data['id'] = $value;
		            $supplier_location_data['open_store_payment'] = 1;
		            $supplier_location_model->save($supplier_location_data);
		        }
		        
		    } 
		    
		    
			syn_supplier_match($data['id']);
			 
			$account_ins['is_main'] = 1;
			if($account_ins['id'])
				M("SupplierAccount")->save ($account_ins);
			else
				M("SupplierAccount")->add ($account_ins);
			
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	
	
	/*
	 * 商户提现
	 */	
	public function charge_index()
	{
		if(isset($_REQUEST['status']))
		{
			$map['status'] = intval($_REQUEST['status']);
		}		
		$model = D ("SupplierMoneySubmit");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	/*
	 * 商户提现编辑
	 */	
	public function charge_edit()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		if($charge_id>0){
				$charge_info = M("SupplierMoneySubmit")->getById($charge_id);
				$supplier_info= M("Supplier")->where("id=".$charge_info['supplier_id'])->find();
				$charge_info['supplier_name']=$supplier_info['name'];
				$charge_info['supplier_money']= $supplier_info['money'];
				$this->assign("type",1);				
				$this->assign("charge_info",$charge_info);
		}
		if($supplier_id>0){
				$supplier_info= M("Supplier")->where("id=".$supplier_id)->find();
				$this->assign("supplier_info",$supplier_info);
				$this->assign("type",2);
		}

		$this->display();
	}	
	
	public function refuse_edit()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		if($charge_id>0){
			$charge_info = M("SupplierMoneySubmit")->getById($charge_id);
			$this->assign("charge_info",$charge_info);
		}
		if($supplier_id>0){
			$supplier_info= M("Supplier")->where("id=".$supplier_id)->find();
			$this->assign("supplier_info",$supplier_info);
		}
	
		$this->display();
	}
	
	public function dorefuse()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$reason = strim($_REQUEST['reason']);
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_money_submit set status = 2,reason = '".$reason."' where id = ".$charge_id." and status = 0 ");
		if($GLOBALS['db']->affected_rows())
			$this->success("操作成功");
		else
			$this->error("操作失败");
	}
	
	/*
	 * 商户提现审核
	 */	
	public function docharge()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		$log=strim($_REQUEST['log']);
		require_once APP_ROOT_PATH."system/model/supplier.php";
		if($charge_id>0){
				$charge = M("SupplierMoneySubmit")->getById($charge_id);
				$supplier_info=M("Supplier")->getById($charge['supplier_id']);
				$charge['money']=floatval($_REQUEST['money']);
				if($charge['money']<=0)$this->error("提现金额必须大于0");
				

				if($charge['money']>$supplier_info['money'])$this->error("提现超额");				
				
				if($charge['status']==0)
				{
					M("SupplierMoneySubmit")->where("id=".$charge['id'])->setField("status",1);
					M("SupplierMoneySubmit")->where("id=".$charge['id'])->setField("money",$charge['money']);					
					modify_supplier_account($charge['money'],$charge['supplier_id'],5,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);//.提现增加
					modify_supplier_account("-".$charge['money'],$charge['supplier_id'],3,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);//已结算减少
					modify_statements($charge['money'],3,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);
					modify_statements($charge['money'],5,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);
					
					send_supplier_withdraw_sms($supplier_info['id'],$charge['money']);
					save_log($supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log,1);					
					$this->success("确认提现成功");
				}
				else
				{
					$this->error("已提现过，无需再次提现");
				}
	
		}
		if($supplier_id>0){
			$supplier_info=M("Supplier")->getById($supplier_id);
			$remittance_num=floatval($_REQUEST['money']);
			if($remittance_num<=0)$this->error("打款金额必须大于0");
			

			if($remittance_num>$supplier_info['money']) $this->error("打款超额");	
								
			modify_supplier_account($remittance_num,$supplier_id,5,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);//.提现增加
			modify_supplier_account("-".$remittance_num,$supplier_id,3,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);//已结算减少
			modify_statements($remittance_num,3,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);
			modify_statements($remittance_num,5,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);
			
			send_supplier_withdraw_sms($supplier_info['id'],$remittance_num);
			save_log("成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log,1);					
			$this->success("打款成功");			
			
		}
	}
	
	public function del_charge()
	{
		$id = intval($_REQUEST['id']);
		$charge = M("SupplierMoneySubmit")->getById($id);
		
		$list = M("SupplierMoneySubmit")->where ("id=".$id )->delete();		
		if ($list!==false) {					 
				save_log($charge['supplier_id']."号商户提现".$charge['money']."元记录".l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),1);
		} else {
				save_log($charge['supplier_id']."号商户提现".$charge['money']."元记录".l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),1);
		}

	}	
	
	public function view_wx()
	{
		$id = intval($_REQUEST['id']);
		
		
		$config = M("WeixinAccount")->where("user_id=".$id)->find();
		$this->assign("config",$config);
			
		$this->assign("unbind_url",U("Supplier/unbind",array("id"=>$id)));
		
		
		$verify_type_array=array(-1=>'未认证',0=>'微信认证',1=>'新浪微博认证',2=>'腾讯微博认证',3=>'已资质认证通过但还未通过名称认证',4=>'已资质认证通过、还未通过名称认证，但通过了新浪微博认证',5=>'已资质认证通过、还未通过名称认证，但通过了腾讯微博认证');
		$service_type_array=array(0=>'订阅号',1=>'由历史老帐号升级后的订阅号',2=>'服务号');
		$this->assign("verify_type",$verify_type_array[$config['verify_type_info']]);
		$this->assign("service_type",$service_type_array[$config['service_type_info']]);
		
		$this->display();
	}
	
	public function unbind()
	{
		$id = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_account where user_id = ".$id);
		app_redirect(U("Supplier/view_wx",array("id"=>$id)));
	}
	
	
}
?>