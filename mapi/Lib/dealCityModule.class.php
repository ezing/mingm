<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class dealCityApiModule extends MainBaseApiModule
{

	//城市一级分类列表
	public function getOneCityList(){
		$where = " `is_effect` = '1' and `is_delete` = '0' and `is_open` = '1' and `pid` = '0'";
		$yicityList = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where ".$where." order by `sort` asc");
		return $yicityList;
	}
	//城市二级分类列表
	public function getTwoCityList($city_id = ''){
		$where = " `is_effect` = '1' and `is_delete` = '0' and `is_open` = '1' and `pid` = '$city_id'";
		$ercityList = $GLOBALS['db']->getAll("select `id`,`name` from ".DB_PREFIX."deal_city where ".$where." order by `sort` asc");
		return $ercityList;
	}
	
	
	
	
	
}
?>