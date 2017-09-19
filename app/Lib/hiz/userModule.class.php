<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class userModule extends HizBaseModule{
	public function login(){
	

		
		init_app_page();
		$GLOBALS['tmpl']->display("login.html");
	}

	
	function do_login(){
		$account_name = strim($_POST['account_name']);
		$account_password = strim($_POST['account_password']);
		
		$data = array();
		//验证
		
		//验证码
		$verify = md5(strim($_POST['verify_code']));
		$session_verify = es_session::get('verify');
		
		if($verify!=$session_verify)
		{
			$data['status'] = false;
			$data['info']	=	"图片验证码错误";
			$data['field'] = "verify_code";
			ajax_return($data);
		}
		if($account_name == ''){
			$data['status'] = false;
			$data['info'] = "请输入用户名";
			$data['field'] = "account_user";
			ajax_return($data);
		}
		if($account_password == ''){
			$data['status'] = false;
			$data['info'] = "请输入密码";
			$data['field'] = "account_password";
			ajax_return($data);
		}
		$account_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."agency WHERE account_name='".$account_name."' AND is_delete=0");
		
		require_once APP_ROOT_PATH."system/libs/hiz_user.php";

		$result = do_login_hiz($account_name,$account_password);

		if($result['status'])
		{

			//获取权限

			$jump_url = url("hiz","supplier");
			
			$return['status'] = true;
			$return['info'] = "登录成功";
			$return['data'] = $result['msg'];
			$return['jump'] = $jump_url;
			$return['tip'] = $tip;

			ajax_return($return);
		}
		else
		{
			if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
			{
				$field = "account_name";
				$err = $GLOBALS['lang']['USER_NOT_EXIST'];
			}
			if($result['data'] == ACCOUNT_PASSWORD_ERROR)
			{
				$field = "account_password";
				$err = $GLOBALS['lang']['PASSWORD_ERROR'];
			}
			if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
			{
				$field = "account_name";
				$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
			}
			$data['status'] = false;
			$data['info']	=	$err;
			$data['field'] = $field;
			ajax_return($data);
		}
		
	}
	
	
	/**
	 * 验证会员字段的有效性
	 * @param array $data  字段名称/值
	 * @return array
	 */
	function check_register_field($data)
	{
		$data = array();
		$data['status'] = true;
		$data['info'] = "";
		
		if(strim($data['account_name']))
		{
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account where account_name = '".$data['account_name']."'");
			if(intval($rs)>0)
			{
				$data['status'] = false;
				$data['info'] = "账户已被注册";
				$data['field'] = "account_name";
				return $data;
			}
		}
		
		if(strim($data['account_mobile']))
		{
			if(!check_mobile($data['account_mobile']))
			{
				$data['status'] = false;
				$data['info'] = "手机号格式不正确";
				$data['field'] = "account_mobile";
				return $data;
			}
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account where account_mobile = '".$data['account_mobile']."'");
			if(intval($rs)>0)
			{
				$data['status'] = false;
				$data['info'] = "手机号已被注册";
				$data['field'] = "account_mobile";
				return $data;
			}
		}

		if(strim($data['verify_code']) && app_conf("SMS_ON") == 1)
		{
	
			$verify = md5($data['verify_code']);
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{
				$data['status'] = false;
				$data['info']	=	"图片验证码错误";
				$data['field'] = "verify_code";
				return $data;
			}
		}
		
		return $data;
	}
	
	public function logout()
	{
		require_once APP_ROOT_PATH."system/libs/hiz_user.php";
		loginout_hiz();
		es_session::delete("hiz_nav_list");
		$jump = url("hiz","user#login");
		app_redirect($jump);
	}
	
	public function edit_password(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->display("edit_password.html");
	}
	
	public function do_edit_password(){
		global_run();
		$data = array();
		$data['status'] = 1;
		
		$account_password = strim($_POST['account_password']);
		$new_account_password = strim($_POST['new_account_password']);
		$rnew_account_password = strim($_POST['rnew_account_password']);
		if($account_password == ''){
			$data['status'] = 0;
			$data['info'] = "原密码不能为空";
			ajax_return($data);
		}
		if($new_account_password == ''){
			$data['status'] = 0;
			$data['info'] = "新密码不能为空";
			ajax_return($data);
		}
		if(strlen($new_account_password)<6){
			$data['status'] = 0;
			$data['info'] = "新密码长度不能小于6位";
			ajax_return($data);
		}
		if($rnew_account_password == ''){
			$data['status'] = 0;
			$data['info'] = "请确认新密码";
			ajax_return($data);
		}
		if($new_account_password != $rnew_account_password){
			$data['status'] = 0;
			$data['info'] = "请确认两次输入的新密码";
			ajax_return($data);
		}
		$account_info = $GLOBALS['hiz_account_info'];

		if($account_info){//用户必须登录存在

			if(md5($account_password) != $account_info['account_password']){
				$data['status'] = 0;
				$data['info'] = "原密码错误";
				ajax_return($data);
			}else{
				$GLOBALS['db']->query("update ".DB_PREFIX."agency set account_password = '".md5($new_account_password)."' where id = ".intval($account_info['id']));
				$data['jump'] = url("hiz","user#logout");
			}
		}else{
			$data['status'] = 0;
			$data['info'] = "请登录后修改！";
			ajax_return($data);
			
		}
		ajax_return($data);
		
	}
	
	
	public function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['id']);
		$type_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$cate_id);
		$html = "";
		foreach($type_list as $item)
		{
			$html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$item['id'].'" />'.$item['name'].'</label>';
		}
	
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
	public function load_city_area()
	{
		$city_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = 0 order by sort desc");
		$html = "";
		if($area_list)
		{
			$html = "<select name='area_id[]'  class='ui-select'>";
			foreach($area_list as $item)
			{
				$html .= "<option value='".$item['id']."'>".$item['name']."</option>";
			}
			$html.="</select>";
		}
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	
	}
	
	public function load_quan_list()
	{
		$area_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where pid = ".$area_id." order by sort desc");
		$html = "";
		foreach($area_list as $item)
		{
			$html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="area_id[]" value="'.$item['id'].'" />'.$item['name'].'</label>';
		}
	
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
}
function check_issupplier()
{
	$account_name = $GLOBALS['user_info']['merchant_name'];
	$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and is_effect = 1 and is_delete = 0");
	if($account)
	{
		$s_account_info = es_session::get("account_info");
		if(intval($s_account_info['id'])==0)
		{
			showErr("您已经是商家会员，请登录",0,url("biz"));
		}
		else
			app_redirect(url("biz"));
	}

}
?>