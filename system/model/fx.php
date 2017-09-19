<?php
//分销功能的函数库,分销商品上架后,产生的分销订单，商品一旦下架将不再返还分销佣金，以订单成功为节点

/**
 * 为订单发放分销的佣金
 * @param unknown_type $order_id 订单ID
 */
function send_fx_order_salary($order_id)
{
	require_once APP_ROOT_PATH."system/model/user.php";
	
	//取出全局的分销配置
	$fx_salary = $GLOBALS['db']->getAll("select fx_salary,fx_salary_type from ".DB_PREFIX."fx_salary where level_id = 0 order by fx_level asc limit ".FX_LEVEL);
		
	$deal_order_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = '".$order_id."' and refund_status <> 2");
	foreach($deal_order_items as $k=>$item)
	{
		$deal_is_fx = intval($GLOBALS['db']->getOne("select is_fx from ".DB_PREFIX."deal where id = ".$item['deal_id']));
		if($item['fx_user_id']>0&&$deal_is_fx) //订单为分销单，并且商品为分销商品
		{
			$user_id = $item['fx_user_id'];
			//是分销单
			$fx_user = load_user($user_id);
			if($fx_user['is_fx']==1)
			{
				
				$user_fx_salary = $GLOBALS['db']->getAll("select fx_salary,fx_salary_type from ".DB_PREFIX."fx_salary where level_id = ".$fx_user['fx_level']." order by fx_level asc limit ".FX_LEVEL);
				
				//会员支持分销再查看商品是不是该会员分销的
				//不再强制每个商品必需被会员领取才可以产生分销
				//$user_deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_deal where deal_id = '".$item['deal_id']."' and user_id = '".$user_id."'");
				//if($user_deal||$deal_is_fx==1)
				if($deal_is_fx)//条件永远成立了
				{
					//查看商品是否有分销佣金配置
					$deal_fx_salary = $GLOBALS['db']->getAll("select fx_salary,fx_salary_type from ".DB_PREFIX."deal_fx_salary where deal_id = ".$item['deal_id']." order by fx_level asc limit ".FX_LEVEL);
					//开始发放销售佣金
					$salary_config = null; //分销销售佣金设置
					$salary = 0; //销售佣金
					if(floatval($deal_fx_salary[0]['fx_salary'])>0)
					{
						$salary_config = $deal_fx_salary[0];
					}
					else
					{
						if(floatval($user_fx_salary[0]['fx_salary'])>0)
						{
							$salary_config = $user_fx_salary[0];
						}
						else
						{
							if(floatval($fx_salary[0]['fx_salary'])>0)
							{
								$salary_config = $fx_salary[0];
							}
						}
					}				
					
					if($salary_config) //计算销售佣金
					{
						if($salary_config['fx_salary_type']==0) //定额
						{
							$salary = $salary_config['fx_salary'];
						}
						else
						{
							$salary = $item['total_price']*$salary_config['fx_salary'];
						}
					}
					
					$is_send = send_fx_user_salary($user_id,$item['total_price'],$salary,0,"分销商".$fx_user['user_name']."售出".$item['number']."件".$item['name']."获得佣金");
					if($is_send)
					{
						update_fx_order_item_log($item['id'],$salary,0);
						modify_fx_statements($item['total_price'], 0, "分销商".$fx_user['user_name']."售出".$item['number']."件".$item['name']);
						modify_fx_statements($salary, 1, "分销商".$fx_user['user_name']."售出".$item['number']."件".$item['name']."获得佣金");
					}
					//end 发放销售佣金
					
					//开始发放推广佣金
					$fx_level = 1;
					$tg_user = $fx_user;
					while(true)
					{
						if($tg_user['pid']==0||$fx_level>FX_LEVEL)
						{
							break;
						}
						else
						{
							//开始发放当前级别的推广佣金
							$tg_user = load_user($tg_user['pid']);
							if($tg_user['is_fx']==0)
							{
								break; //当前分销链中的会员被关闭分销身份，退出
							}
							$salary_config = null; //分销推广佣金设置
							$salary = 0; //推广佣金
							if(floatval($deal_fx_salary[$fx_level]['fx_salary'])>0)
							{
								$salary_config = $deal_fx_salary[$fx_level];
							}
							else
							{
								if(floatval($user_fx_salary[$fx_level]['fx_salary'])>0)
								{
									$salary_config = $user_fx_salary[$fx_level];
								}
								else
								{
									if(floatval($fx_salary[$fx_level]['fx_salary'])>0)
									{
										$salary_config = $fx_salary[$fx_level];
									}
								}
							}
								
							if($salary_config) //计算销售佣金
							{
								if($salary_config['fx_salary_type']==0) //定额
								{
									$salary = $salary_config['fx_salary'];
								}
								else
								{
									$salary = $item['total_price']*$salary_config['fx_salary'];
								}
							}
								
							$is_send = send_fx_user_salary($tg_user['id'],$item['total_price'],$salary,$fx_level,"分销商".$fx_user['user_name']."售出".$item['number']."件".$item['name']."获得推广佣金");
							if($is_send)
							{
								//更新每一层级的返佣关系
								$fx_user_reward = array("pid"=>$tg_user['id'],"user_id"=>$fx_user['id'],"money"=>$salary);
								$GLOBALS['db']->autoExecute(DB_PREFIX."fx_user_reward",$fx_user_reward,"INSERT","","SILENT");
								if($GLOBALS['db']->errno())
								{
									$GLOBALS['db']->query("update ".DB_PREFIX."fx_user_reward set money = money + ".$salary." where pid = ".$tg_user['id']." and user_id = ".$fx_user['id']);
								}
								
								update_fx_order_item_log($item['id'],$salary,$fx_level);
								modify_fx_statements($salary, 2, "分销商".$fx_user['user_name']."售出".$item['number']."件".$item['name']."获得推广佣金");
							}
							//end 发放当前级别的推广佣金
							
							$fx_level++;
						}
					}
					//end 发放推广佣金
					
				}
			}// end if($fx_user['is_fx']==1)
		} //end if($item['fx_user_id']>0)
	}
}


/**
 * 将分销佣金发放给会员,并生成会员的分销佣金日志
 * @param unknown_type $user_id 会员ID
 * @param unknown_type $money 佣金金额
 * @param unknown_type $level int 佣金等级 0为销售佣金
 * @param unknown_type $log 日志
 * 
 * 返回 bool
 */
function send_fx_user_salary($user_id,$sale_money,$money,$level,$log)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."user set fx_total_money = fx_total_money+".$sale_money.",fx_total_balance=fx_total_balance+".$money." where id = ".$user_id." and is_fx = 1");
	if($GLOBALS['db']->affected_rows()>0)
	{
		//开始更新分销等级
		$fx_total_money = floatval($GLOBALS['db']->getOne("select fx_total_money from ".DB_PREFIX."user where id = ".$user_id));
		$level_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."fx_level where money<=".$fx_total_money." order by money desc");
		$GLOBALS['db']->query("update ".DB_PREFIX."user set fx_level = ".$level_id." where id = ".$user_id);
		modify_fx_account($money,$user_id,$log);
		return true;
	}
	else
		return false;
}


/**
 * 更新会员的分销帐户
 * @param unknown_type $money
 * @param unknown_type $user_id
 * @param unknown_type $log
 * 

 */
function modify_fx_account($money,$user_id,$log)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."user set fx_money=fx_money+".floatval($money)." where id = ".$user_id);
	require_once APP_ROOT_PATH."system/model/user.php";
	load_user($user_id,true);
	
	//生成分销资金日志
	$log_info['log'] = $log;
	$log_info['create_time'] = NOW_TIME;
	$log_info['money'] = floatval($money);
	$log_info['user_id'] = $user_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."fx_user_money_log",$log_info);
}

/**
 * 生成平台的分销报表
 * @param unknown_type $money
 * @param unknown_type $type 0:营业销 1分销佣金 2推广佣金 3分销提现
 * @param unknown_type $info
 */
function modify_fx_statements($money, $type, $info)
{
	$field_array = array(
			'sale_money',
			'fx_salary',
			'fx_extend_salary',
			'fx_withdraw');
	
	$stat_time = to_date(NOW_TIME,"Y-m-d");
	$stat_month = to_date(NOW_TIME,"Y-m");
	$state_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."fx_statements where stat_time = '".$stat_time."'");
	if($state_data)
	{
		$state_data[$field_array[$type]] = $state_data[$field_array[$type]]+floatval($money);
		$GLOBALS['db']->autoExecute(DB_PREFIX."fx_statements",$state_data, $mode = 'UPDATE', "id=".$state_data['id'], $querymode = 'SILENT');
		$rs = $GLOBALS['db']->affected_rows();
	}
	else
	{
		$state_data[$field_array[$type]] = floatval($money);
		$state_data["stat_time"] = $stat_time;
		$state_data["stat_month"] = $stat_month;
		$GLOBALS['db']->autoExecute(DB_PREFIX."fx_statements",$state_data, $mode = 'INSERT', "", $querymode = 'SILENT');
		$rs = $GLOBALS['db']->insert_id();
	}
	
	if($rs)
	{
		$log_data = array();
		$log_data['log'] = $info;
		$log_data['create_time'] = NOW_TIME;
		$log_data['money'] = floatval($money);
		$log_data['type'] = $type;
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."fx_statements_log",$log_data);
	}
}

/**
 * 更新订单商品的分销佣金
 * @param unknown_type $order_item_id
 * @param unknown_type $salary
 * @param unknown_type $level
 */
function update_fx_order_item_log($order_item_id,$salary,$level)
{
	$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
	$fx_salary_all = unserialize($order_item['fx_salary_all']); 
	if(!$fx_salary_all)
	{
		$fx_salary_all = array();
	}
	$fx_salary_all[$level] = $salary;
	$fx_salary_all = serialize($fx_salary_all);
	if($level==0)
	{
		$sale_salary = $salary;
		$GLOBALS['db']->query("update ".DB_PREFIX."user_deal set sale_count=sale_count+".$order_item['number'].",sale_total=sale_total+".$order_item['total_price'].",sale_balance=sale_balance+".$salary." where deal_id=".$order_item['deal_id']." and user_id = ".$order_item['fx_user_id']);
	}
	else
		$sale_salary = 0;
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set fx_salary=fx_salary+".$sale_salary.",fx_salary_total=fx_salary_total+".$salary.",fx_salary_all='".$fx_salary_all."' where id = ".$order_item_id);
	
	
}


/**
 * 分销提现记录列表

 */
function get_fx_withdraw($limit,$user_id)
{
	$user_id = intval($user_id);
	$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."fx_withdraw where user_id = ".$user_id." order by create_time desc limit ".$limit);
	foreach($list as $k=>$v)
	{
		$bank_account_end = substr($v['bank_account'],-4,4);
		$bank_account_show_length = strlen($v['bank_account']) - 4;
		$bank_account = "";
		for($i=0;$i<$bank_account_show_length;$i++)
		{
			$bank_account.="*";
		}
		$bank_account.=$bank_account_end;
		$list[$k]['bank_account'] =  $bank_account;
		
		$bank_user_end = msubstr($v['bank_user'],-1,1,"utf-8",false);
		$bank_user_show_length = mb_strlen($v['bank_user'],"utf-8")-1;
		$bank_user = "";
		for($i=0;$i<$bank_user_show_length;$i++)
		{
			$bank_user.="*";
		}
		$bank_user.=$bank_user_end;
		$list[$k]['bank_user'] =  $bank_user;
	}
	$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."fx_withdraw where user_id = ".$user_id);

	return array("list"=>$list,'count'=>$count);
}

function add_user_fx_deal($user_id,$deal_id){
    $user_id = intval($user_id);
    $deal = $GLOBALS['db']->getRow('select id,is_fx from '.DB_PREFIX."deal where id=".$deal_id);
    if ($deal['id']>0 && $deal['is_fx']==2){ //允许会员领取的数据
        if($GLOBALS['db']->getOne('select count(*) from '.DB_PREFIX.'user_deal where deal_id='.$deal_id.' and user_id ='.$user_id)){
            return true;
        }else{
            $ins_data['deal_id'] = $deal_id;
            $ins_data['add_time'] = NOW_TIME;
            $ins_data['user_id'] = $user_id;
            $ins_data['is_effect'] = 1; //上架


            $GLOBALS['db']->autoExecute(DB_PREFIX."user_deal",$ins_data);
            return $GLOBALS['db']->insert_id()>0;
        }
    }
    return false;

}
function do_is_effect($user_id,$deal_id){
    $user_id = intval($user_id);
    $deal_id = intval($deal_id);
    
    $u_deal = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'user_deal where user_id='.$user_id.' and deal_id = '.$deal_id.' and type=0 '); 
    if($u_deal){
        $is_effect = $u_deal['is_effect'] == 1?0:1;
        return $GLOBALS['db']->autoExecute(DB_PREFIX.'user_deal',array('is_effect'=>$is_effect),'UPDATE','user_id='.$user_id.' and deal_id = '.$deal_id.' and type=0 ');
    }else{
        return false;
    }
    
}

function del_user_deal($user_id,$deal_id){
    $user_id = intval($user_id);
    $deal_id = intval($deal_id);
    
    $u_deal = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'user_deal where user_id='.$user_id.' and deal_id = '.$deal_id.' and type=0 '); 
    if($u_deal){
        return $GLOBALS['db']->query('delete from '.DB_PREFIX.'user_deal where user_id='.$user_id.' and deal_id = '.$deal_id.' and type=0 ');
    }else{
        return false;
    }
    
}






?>