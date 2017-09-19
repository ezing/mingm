<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class uc_moneyModule extends MainBaseModule
{
        /**
         * 资金记录
         */
        public function index(){
            global_run();
            init_app_page();
            $param['page'] = intval($_REQUEST['page']);			
			$data = call_api_core("uc_money","index",$param);	
				
			if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
				app_redirect(wap_url("index","user#login"));
			}	
	  		if(isset($data['page']) && is_array($data['page'])){			
				$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象			
				$p  =  $page->show();					
				$GLOBALS['tmpl']->assign('pages',$p);
			}				
		

			$GLOBALS['tmpl']->assign("data",$data);	            
            
            $GLOBALS['tmpl']->display("uc_money_index.html");
        }

			
		
		  public function withdraw_bank_list(){
			    global_run();
			    init_app_page();
				$param=array();

				$data = call_api_core("uc_money","withdraw_bank_list",$param);				
				if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
					app_redirect(wap_url("index","user#login"));
				}

		   		$GLOBALS['tmpl']->assign("data",$data);	      
			    $GLOBALS['tmpl']->display("uc_money_withdraw.html");
		  }

		  public function add_card(){
			    global_run();
			    init_app_page();
				$param=array();

				$data = call_api_core("uc_money","add_card",$param);				
				if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
					app_redirect(wap_url("index","user#login"));
				}
				$data['step']=2;
				$data['page_title']="添加银行卡";
				$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
				$GLOBALS['tmpl']->assign("data",$data);		      
			    $GLOBALS['tmpl']->display("uc_money_withdraw.html");
		  }		  

		 public function do_bind_bank(){
			    global_run();
				$param=array();
                $param['bank_name'] = strim($_REQUEST['bank_name']);
                $param['bank_account']= strim($_REQUEST['bank_account']);
                $param['bank_user'] = strim($_REQUEST['bank_user']);
                $param['sms_verify'] = strim($_REQUEST['sms_verify']);
				$data = call_api_core("uc_money","do_bind_bank",$param);				
				if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
					app_redirect(wap_url("index","user#login"));
				}
		 		if($data['status']==1){
					$result['status'] = 1;
					$result['url'] = wap_url("index","uc_money#withdraw_bank_list");
					ajax_return($result);			
				}else{
					$result['status'] =0;
					$result['info'] =$data['info'];					
					ajax_return($result);		
				}
		 }		  

		 public function do_withdraw(){
		 		global_run();
				$param=array();
                $param['user_bank_id'] = intval($_REQUEST['bank_id']);
                $param['money']= floatval($_REQUEST['money']);
                $param['check_pwd'] = strim($_REQUEST['pwd']);
   
				$data = call_api_core("uc_money","do_withdraw",$param);				
				if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
					app_redirect(wap_url("index","user#login"));
				}
		 		if($data['status']==1){
					$result['status'] = 1;
					$result['url'] = wap_url("index","uc_money#withdraw_log");
					ajax_return($result);			
				}else{
					$result['status'] =0;
					$result['info'] =$data['info'];					
					ajax_return($result);		
				}		 	
		 }		 
		 public function withdraw_log(){
		 		global_run();
		 		init_app_page();
				$param=array();
				$param['page'] = intval($_REQUEST['page']);	
				$data = call_api_core("uc_money","withdraw_log",$param);				
				if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
					app_redirect(wap_url("index","user#login"));
				}
		 		if(isset($data['page']) && is_array($data['page'])){			
					$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象			
					$p  =  $page->show();
					
					$GLOBALS['tmpl']->assign('pages',$p);
				}
				$GLOBALS['tmpl']->assign("data",$data);	
				$GLOBALS['tmpl']->display("uc_withdraw_log.html");	 	
		 }

	    //余额充值
	public function chongzhi(){

		global_run();
		init_app_page();
		//获取到银行卡
		//输出支付方式
		$payment_list = load_auto_cache("cache_payment");
		$icon_paylist = array(); //用图标展示的支付方式
		//$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
		$bank_paylist = array(); //网银直连
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay"||$v['class_name']=="tenpayc2c")
			{
				//$disp_paylist[] = $v;
			}
			else
			{
				if($v['class_name']=="Alipay")
				{
					$cfg = unserialize($v['config']);
					if($cfg['alipay_service']==2)
					{
						if($v['is_bank']==1)
							$bank_paylist[] = $v;
						else
							$icon_paylist[] = $v;
					}
				}
				else
				{
					if($v['is_bank']==1)
						$bank_paylist[] = $v;
					else
						$icon_paylist[] = $v;
				}
			}
		}

		$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
		//$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
		$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);

		$GLOBALS['tmpl']->display("recharge.html");
	}
	public function tixian(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->display("withdraw.html");
	}
	//提现
	public function withdraw_done()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}

		$is_bind = intval($_REQUEST['is_bind']);
		$money = floatval($_REQUEST['money']);
		$mobile = $GLOBALS['user_info']['mobile'];
		$user_bank_id = intval($_REQUEST['user_bank_id']);

		$sms_verify = strim($_REQUEST['sms_verify']);

		if($user_bank_id){//数据库中查询银行信息
			$bank_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where id=".$user_bank_id." and user_id =".$GLOBALS['user_info']['id']);
			if($bank_info){
				$bank_name = $bank_info['bank_name'];
				$bank_account = $bank_info['bank_account'];;
				$bank_user = $bank_info['bank_user'];
			}else{
				$data['status'] = 0;
				$data['info'] = "银行数据错误，请选择其他银行或新卡提现";
				ajax_return($data);
			}

		}else{//表单数据
			$bank_name = strim($_REQUEST['bank_name']);
			$bank_account = strim($_REQUEST['bank_account']);
			$bank_user = strim($_REQUEST['bank_user']);

			if($bank_name=="")
			{
				$data['status'] = 0;
				$data['info'] = "请输入开户行名称";
				ajax_return($data);
			}
			if($bank_account=="")
			{
				$data['status'] = 0;
				$data['info'] = "请输入开户行账号";
				ajax_return($data);
			}
			if($bank_user=="")
			{
				$data['status'] = 0;
				$data['info'] = "请输入个人真实姓名";
				ajax_return($data);
			}
		}

		if($money<=0)
		{
			$data['status'] = 0;
			$data['info'] = "请输入正确的提现金额";
			ajax_return($data);
		}

		if(app_conf("SMS_ON")==1)
		{
			if($mobile=="")
			{
				$data['status'] = 0;
				$data['info'] = "请先完善会员的手机号码";
				$data['jump'] = url("index","uc_account");
				ajax_return($data);
			}




			if($sms_verify=="")
			{
				$data['status'] = 0;
				$data['info']	=	"请输入收到的验证码";
				ajax_return($data);
			}

			//短信码验证
			$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
			$GLOBALS['db']->query($sql);

			$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");

			if($mobile_data['code']!=$sms_verify)
			{
				$data['status'] = 1;
				$data['info']	=  "验证码错误";
				ajax_return($data);
			}


		}

		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."withdraw where user_id = ".$GLOBALS['user_info']['id']." and is_delete = 0 and is_paid = 0"));
		if($submitted_money+$money>$GLOBALS['user_info']['money'])
		{
			$data['status'] = 0;
			$data['info'] = "提现超额";
			ajax_return($data);
		}

		$withdraw_data = array();
		$withdraw_data['user_id'] = $GLOBALS['user_info']['id'];
		$withdraw_data['money'] = $money;
		$withdraw_data['create_time'] = NOW_TIME;
		$withdraw_data['bank_name'] = $bank_name;
		$withdraw_data['bank_account'] = $bank_account;
		$withdraw_data['bank_user'] = $bank_user;
		$withdraw_data['is_bind'] = $is_bind;
		$withdraw_data['bank_mobile'] = $mobile;
		$GLOBALS['db']->autoExecute(DB_PREFIX."withdraw",$withdraw_data);

		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");
		$data['status'] = 1;
		$data['info'] = "提现申请提交成功，请等待审核";
		ajax_return($data);
	}


}
?>
