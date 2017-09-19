<?php 
return array(

            "Location"	=>	array(
                "name"	=>	"商户管理",
                "node"	=>	array(
                    "supplier"=>array("name"=>"商户列表","module"=>"supplier","action"=>"index"),
                    "location"=>array("name"=>"门店列表","module"=>"location","action"=>"index"),
                )
            ),
			"Order"	=>	array(
					"name"	=>	"订单管理",
					"node"	=>	array(
							"dealo"=>array("name"=>"团购订单列表","module"=>"dealo","action"=>"index"),
							"goodso"=>array("name"=>"商品订单列表","module"=>"goodso","action"=>"index"),
							"dcorder"=>array("name"=>"外卖订单","module"=>"dcorder","action"=>"index"),
							"dcresorder"=>array("name"=>"预订订单","module"=>"dcresorder","action"=>"index"),
					        "storepayorder"=>array("name"=>"到店付","module"=>"storepayorder","action"=>"index"),
					)
			),
			"Bills"	=>	array(
					"name"	=>	"财务管理",
					"node"	=>	array(
							"balance"=>array("name"=>"财务报表","module"=>"balance","action"=>"detail"),
							"withdrawal"=>array("name"=>"代理提现","module"=>"withdrawal","action"=>"index"),
							"bankinfo"=>array("name"=>"银行账户","module"=>"bankinfo","action"=>"index"),
					)
			)


		);
				
?>