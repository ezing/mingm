<?php 
return array(
//fx 营销菜单位于订单菜单后面
	"marketing"	=>	array(
			"name"	=>	"营销管理",
			"key"	=>	"marketing",
			"groups"	=>	array(
					"fx"	=>	array(
							"name"	=>	"分销管理",
							"key"	=>	"fx",
							"nodes"	=>	array(
									array("name"=>"佣金设置","module"=>"FxSalary","action"=>"index"),
									array("name"=>"分销订单","module"=>"FxOrder","action"=>"index"),
									array("name"=>"分销报表","module"=>"FxStatement","action"=>"index"),
									array("name"=>"分销会员","module"=>"FxUser","action"=>"index"),
									array("name"=>"分销提现","module"=>"FxWithdraw","action"=>"index"),
	
							),
					),
			),
	),
);
?>