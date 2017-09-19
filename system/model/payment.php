<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once APP_ROOT_PATH."system/model/cart.php";

function get_order_type_name($payment_notice){
    if($payment_notice['order_type']==3){
        $sql = "select name ".
            "from ".DB_PREFIX."deal_order_item ".
            "where order_id =". intval($payment_notice['order_id']). " limit 1";
        $title_name = $GLOBALS['db']->getOne($sql);
    }elseif($payment_notice['order_type']==4){
        $title_name='会员买单';
    }elseif ($payment_notice['order_type']==1){
        $title_name='订餐订单';
    }
    
    return $title_name;
}

function get_table_name($payment_notice){
	if($payment_notice['order_type']==3){
		$order_table_name = DB_PREFIX."deal_order";     //普通订单
	}elseif($payment_notice['order_type']==4){
		$order_table_name = DB_PREFIX."store_pay_order"; //会员买单
	}elseif ($payment_notice['order_type']==1){
	    $order_table_name = DB_PREFIX."dc_order"; // 订餐订单
	}
	return $order_table_name;
}


function payment_base($payment_notice){
	require_once APP_ROOT_PATH."system/model/cart.php";
	if($payment_notice['order_type']==3){
		$order_table_name = DB_PREFIX."deal_order";     //用户订单
		$rs = payment_paid($payment_notice['id']);
	}elseif($payment_notice['order_type']==4){
		$order_table_name = DB_PREFIX."store_pay_order"; //商户订单	
		$rs = store_pay_payment_paid($payment_notice['id']);
	}elseif ($payment_notice['order_type']==1){
	    $order_table_name = DB_PREFIX."dc_order"; // 订餐订单
	    $rs = dcpayment_paid($payment_notice['id']);
	}
	$rs_arr['table_name']=$order_table_name;
	$rs_arr['rs']=$rs;
	return $rs_arr;
}

function notify_base($outer_notice_sn,$payment_notice){
        $order_type = intval($payment_notice['order_type']);   
        //定义请求是用户还是商户
        if($order_type==3){
            $order_table_name = DB_PREFIX."deal_order";     //用户订单
            $page_object = "index";
        }elseif($order_type==4){
            $order_table_name = DB_PREFIX."store_pay_order"; //商户订单
            $page_object = "index";
        }elseif ($payment_notice['order_type']==1){
    	    $order_table_name = DB_PREFIX."dc_order"; // 订餐订单
    	    $page_object = "index";
	    }
        $payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id=".$payment_notice['payment_id']);
        $order_info = $GLOBALS['db']->getRow("select * from ".$order_table_name." where id = ".$payment_notice['order_id']);
        require_once APP_ROOT_PATH."system/model/cart.php";
        if($order_type==3){
            $rs = payment_paid($payment_notice['id']);
        }elseif($order_type==4){
            $rs = store_pay_payment_paid($payment_notice['id']);
        }elseif($order_type==1){
        	require_once APP_ROOT_PATH."system/model/dc.php";
            $rs = dcpayment_paid($payment_notice['id']);
        }

        if(!$rs && $payment_notice['is_paid']==1){
        	$rs=1;
        }
        
        
        if($rs)
        {			
                $GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$outer_notice_sn."' where id = ".$payment_notice['id']);	
                if($order_type==3){
                    order_paid($payment_notice['order_id']);

                    
                    if($payment_info['class_name'] == 'AlipayBank' || $payment_info['class_name']=="Alipay"){
                    	require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
                    	$payment_class = $payment_info['class_name']."_payment";
                    	$payment_object = new $payment_class();
                    	$payment_object->auto_do_send_goods($payment_notice,$order_info);
                    }
                    		
                }elseif($order_type==4){
                    store_pay_order_paid($payment_notice['order_id']);	
                }elseif($order_type==1){
                    dcorder_paid($payment_notice['order_id']);
                }
                		
                echo "success";exit;
        }
        else
        {
            if($order_type==3){
            	
            	if($payment_info['class_name'] == 'AlipayBank' || $payment_info['class_name']=="Alipay"){
            		require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
            		$payment_class = $payment_info['class_name']."_payment";
            		$payment_object = new $payment_class();
            		$payment_object->auto_do_send_goods($payment_notice,$order_info);
            	}
            	 	
            }elseif($order_type==4){

            }
                
                echo "fail";exit;
        }
}

function response_base($outer_notice_sn,$payment_notice){
        $order_type = intval($payment_notice['order_type']);   
        //定义请求是用户还是商户
        if($order_type==3){
            $order_table_name = DB_PREFIX."deal_order";     //用户订单
            $page_object = "index";
        }elseif($order_type==4){
            $order_table_name = DB_PREFIX."store_pay_order"; //会员买单
            $page_object = "index";
        }elseif($order_type==1){
            $order_table_name = DB_PREFIX."dc_order"; //订餐订单
            $page_object = "index";
        }
        $payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id=".$payment_notice['payment_id']);
        $order_info = $GLOBALS['db']->getRow("select * from ".$order_table_name." where id = ".$payment_notice['order_id']);
        require_once APP_ROOT_PATH."system/model/cart.php";
        
        if($order_type == 3){
            $rs = payment_paid($payment_notice['id']);
        }elseif($order_type == 4){
            $rs = store_pay_payment_paid($payment_notice['id']);
        }elseif($order_type==1){
        	require_once APP_ROOT_PATH."system/model/dc.php";
            $rs = dcpayment_paid($payment_notice['id']);
        }

        if(!$rs && $payment_notice['is_paid']==1){
        	$rs=1;
        }
        
        if($rs)
        {
                if($order_type == 3){
                    $rs = order_paid($payment_notice['order_id']);	
                }elseif($order_type == 4){
                    $rs = store_pay_order_paid($payment_notice['order_id']);
                }elseif($order_type==1){
                    $rs = dcorder_paid($payment_notice['order_id']);
                }
       		
                if($rs)
                {
                        //开始更新相应的outer_notice_sn					
                        $GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$outer_notice_sn."' where id = ".$payment_notice['id']);
                        
                        if($order_type == 3){
                            //支付宝担保交易
                            if($payment_info['class_name'] == 'AlipayBank' || $payment_info['class_name']=="Alipay"){
                                require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
                                $payment_class = $payment_info['class_name']."_payment";
                                $payment_object = new $payment_class();
                                $payment_object->auto_do_send_goods($payment_notice,$order_info);
                            }	

                            if($order_info['type']==0)
                            	app_redirect(url($page_object,"payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
                            else{
                            	app_redirect(url($page_object,"payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
                            }
                        }elseif($order_type == 4){				
                            //会员买单
                        	$jump=wap_url('index','store_payment#done',array('pay_status'=>1,'order_id'=>$order_info['id'],'payment_notice_id'=>$payment_notice['id']));
                        	app_redirect($jump); //支付成功
                        }elseif($order_type == 1){
                            //订餐买单
                        	$jump=wap_url('index','dc_payment#done',array('pay_status'=>1,'order_id'=>$order_info['id'],'payment_notice_id'=>$payment_notice['id']));
                        	app_redirect($jump); //支付成功
                        }
                        

                }
                else 
                {
                        if($order_info['pay_status'] == 2)
                        {	
                            if($order_type == 3){
                                  //支付宝担保交易
                                if($payment_info['class_name'] == 'AlipayBank' || $payment_info['class_name']=="Alipay"){
                                    require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
                                    $payment_class = $payment_info['class_name']."_payment";
                                    $payment_object = new $payment_class();
                                    $payment_object->auto_do_send_goods($payment_notice,$order_info);
                                }
                                if($order_info['type']==0)
                                	app_redirect(url($page_object,"payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
                                else{
                                	app_redirect(wap_url($page_object,"payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
                                }              
                            }elseif($order_type == 4){	
                                //会员买单
                            	$jump=wap_url('index','store_payment#done',array('pay_status'=>1,'order_id'=>$order_info['id'],'payment_notice_id'=>$payment_notice['id']));
	                        
	                        	app_redirect($jump); //超额支付
                            }elseif($order_type == 1){
                                //订餐买单
                            	$jump=wap_url('index','dc_payment#done',array('pay_status'=>1,'order_id'=>$order_info['id'],'payment_notice_id'=>$payment_notice['id']));
                            	app_redirect($jump); //支付成功
                            }
                            
                        }
                        else{
                        	if($order_type == 3){
                        		app_redirect(url($page_object,"payment#pay",array("id"=>$payment_notice['id'])));
                        	}elseif($order_type == 4){
                        		//会员买单
                        		$jump=wap_url('index','store_pay#check',array('order_id'=>$order_info['id']));
                        		app_redirect($jump); //超额支付
                        	}elseif($order_type == 1){
                                //订餐买单
                            	$jump=wap_url('index','dcorder#order',array('id'=>$order_info['id']));
                            	app_redirect($jump); //支付成功
                            }
                        	
                        }
                       
                }
        }
        else
        {
            if($order_type == 3){
            	if($payment_info['class_name'] == 'AlipayBank' || $payment_info['class_name']=="Alipay"){
            		require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
            		$payment_class = $payment_info['class_name']."_payment";
            		$payment_object = new $payment_class();
            		$payment_object->auto_do_send_goods($payment_notice,$order_info);
            	}
                app_redirect(url($page_object,"payment#pay",array("id"=>$payment_notice['id'])));
            }elseif($order_type == 4){
                //会员买单
            	$jump=wap_url('index','store_pay#check',array('order_id'=>$order_info['id']));
            	app_redirect($jump); //超额支付
            }elseif($order_type == 1){
                //订餐买单
            	$jump=wap_url('index','dcorder#order',array('id'=>$order_info['id']));
            	app_redirect($jump); //支付成功
            }
            
        }
}