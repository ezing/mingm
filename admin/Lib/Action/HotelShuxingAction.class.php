<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class HotelShuxingAction extends CommonAction{
	public function index()
	{
       $data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel_shuxing");
        $this->assign('list',$data);

		$this->display ('');
	}
	
	public function add()
	{

	/*	$map['is_delete'] = 0;
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		$model = D ("Deal");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}*/
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		/*$condition['is_delete'] = 0;*/
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();


		$model = D ("HotelShuxing");
		if (! empty ( $model )) {
			$this->_list ( $model);
		}

		$this->assign ( 'vo', $vo );

//		$ids = D(MODULE_NAME)->getChildIds($id);
		//$ids[] = $id;
		
	/*	$condition=array();
		$condition['is_delete'] = 0;
		$condition['pid']=0;
		$condition['type_id'] = array('not in',array(1,3));		
		//$condition['id'] = array('not in',$ids);
		
		$cate_tree = M(MODULE_NAME)->where($condition)->findAll();
		$cate_tree = D(MODULE_NAME)->toFormatTree($cate_tree);
		$this->assign("cate_tree",$cate_tree);*/
		
		$this->display ();
	}

	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
//		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u("HotelShuxing/add"));

		// 更新数据
		$name = $_REQUEST['name'];
		$value = $_REQUEST['value'];

		$log_info = '商家添加属性';

		//统计是否超过8条
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."hotel_shuxing");

		if($count >=6){
			save_log($log_info.L("属性最多填6条"),0);
			$this->error(L("INSERT_FAILED"));
		}
		$sql ="INSERT INTO ".DB_PREFIX."hotel_shuxing ( `shangjia_id`, `mendian_id`, `name`, `value`) VALUES ('1','1','$name','$value')";
        $list = $GLOBALS['db']->query($sql);

//		$list=M(MODULE_NAME)->add($data);
		if ($list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("title");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		 
		 
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
    public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("title");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
	}
	
	public function update() {
		B('FilterString');
//		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($_REQUEST['id']))->getField("id");

		// 更新数据
		$id = $_REQUEST['id'];
		$name = $_REQUEST['name'];
		$value = $_REQUEST['value'];
	    $log_info = '修改酒店属性';

		$sql ="UPDATE `".DB_PREFIX."hotel_shuxing` set `name`= '$name', `value`= '$value' where `id` = $id ";

		$list = $GLOBALS['db']->query($sql);


//		$list= M(MODULE_NAME)->save($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}

	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		$sql = "delete from ".DB_PREFIX."hotel_shuxing where `id` = '$id'";
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			save_log(l("FOREVER_DELETE_SUCCESS"),1);
			$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
		}else{
			save_log(l("FOREVER_DELETE_FAILED"),0);
			$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
		}
	}
		public function restore() {
			//删除指定记录
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
					$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
					if ($list!==false) {
						save_log($info.l("RESTORE_SUCCESS"),1);
						$this->success (l("RESTORE_SUCCESS"),$ajax);
					} else {
						save_log($info.l("RESTORE_FAILED"),0);
						$this->error (l("RESTORE_FAILED"),$ajax);
					}
				} else {
					$this->error (l("INVALID_OPERATION"),$ajax);
			}
 	}
}
?>