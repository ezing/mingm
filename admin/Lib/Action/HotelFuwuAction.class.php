<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class HotelFuwuAction extends CommonAction{
	public function index()
	{
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel_fuwu");

//print_r($data);exit;
		foreach($list as $key=>$val){
			$list[$key]['img'] = "<img src='{$val['img']}'>";
		}
        $this->assign('list',$list);
		$this->display ();
		return;
	}
	
	public function add()
	{

		$model = M ("HotelFuwu");
		if (! empty ( $model )) {
			$this->_list ( $model );
		}

		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		/*$condition['is_delete'] = 0;*/
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$this->display ();
	}

	public function insert() {
		B('FilterString');
//		$ajax = intval($_REQUEST['ajax']);
//		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u("HotelFuwu/add"));

		// 更新数据
		$name = $_REQUEST['name'];
		$img = $_REQUEST['icon'];

		$log_info = '商家添加酒店服务';

		//统计是否超过8条
		 $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."hotel_fuwu");
		if($count >= 8){
			save_log($log_info.L("服务最多填8条"),0);
			$this->error(L("INSERT_FAILED"));
		}

		$sql ="INSERT INTO `".DB_PREFIX."hotel_fuwu`(`shangjia_id`, `mendian_id`,`name`, `img`) VALUES ('1','1','$name','$img')" ;
//		$this->error($sql);
        $list = $GLOBALS['db']->query($sql);
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
		$img = $_REQUEST['icon'];
	    $log_info = '修改酒店服务';

//		$this->error($log_info);
//		file_put_contents('1.txt',var_dump($fields,true));//不能打印，所以写到文本里
//		$list=M(MODULE_NAME)->add($data);
		if(empty($img)){
			$sql ="UPDATE `".DB_PREFIX."hotel_fuwu` set `name`= '$name' where `id` = $id ";
		}else{
			$sql ="UPDATE `".DB_PREFIX."hotel_fuwu` set `name`= '$name', `img`= '$img' where `id` = $id ";
		}
//		$this->error($sql);
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
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();			
				foreach($rel_data as $data)
				{
					$info[] = $data['title'];	
				}
				if($info) $info = implode(",",$info);
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

	public function foreverdel(){
		$this->error ('fdsafasf');
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST['id'];
		$sql = "delete from ".DB_PREFIX."hotel_fuwu where `id` = '$id'";
		$this->error ($sql);
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			save_log(l("DELETE_SUCCESS"),1);
			$this->success (l("DELETE_SUCCESS"),$ajax);
		}else{
			$this->error (l("删除失败"),$ajax);
		}
	}

}
?>