<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class KtvShuxingAction extends CommonAction{
	public function index()
	{
		$name=$this->getActionName();
		$model = D($name);
		$this->_list ( $model);



		$this->display ();
		return;
	}
	
	public function add()
	{
		/*$map['is_delete'] = 0;
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		$model = M ("Deal");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}*/

		//查找商品
		$sql = "select `id`,`name` from ".DB_PREFIX."deal where `is_effect` = '1' and `is_delete` = '0'";
		$list = $GLOBALS['db']->getAll($sql);
		$this->assign('list',$list);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		/*$condition['is_delete'] = 0;*/
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();

		$vo['begin_time'] = $vo['begin_time']!=0?date("H",($vo['begin_time'])):'';
		$vo['end_time'] = $vo['end_time']!=0?date("H",($vo['end_time'])):'';
		//查询商品
		/*$map['is_delete'] = 0;
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		$model = D ("Deal");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}*/

		$this->assign ( 'vo', $vo );
		//查找商品
		$sql = "select `id`,`name` from ".DB_PREFIX."deal where `is_effect` = '1' and `is_delete` = '0'";
		$list = $GLOBALS['db']->getAll($sql);
		$this->assign('list',$list);
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
//		$ajax = intval($_REQUEST['ajax']);
//		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		/*if(!check_empty($data['title']))
		{
			$this->error(L("ARTICLECATE_TITLE_EMPTY_TIP"));
		}	*/
		//开始同步type_id
		/*if($data['pid']>0)
		{
			$data['type_id'] = M("ArticleCate")->where("id=".$data['pid'])->getField("type_id");
		}
		if($data['type_id']!=1)
		{
			$data['iconfont'] = "";
		}*/
		// 更新数据
		$time = $_REQUEST['time'];
		$deal_id = $_REQUEST['deal_id'];
		$hours = $_REQUEST['hours'];
		$price = $_REQUEST['price'];
		$log_info = '添加KTV购买属性';

//		$this->error($log_info);
//		file_put_contents('1.txt',var_dump($fields,true));//不能打印，所以写到文本里
//		$list=M(MODULE_NAME)->add($data);
		$sql ="INSERT INTO `".DB_PREFIX."ktv_shuxing`( `price`, `time`, `deal_id`,`hours`) VALUES ('$price','$time','$deal_id','$hours')" ;

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
//		$log_info = M(MODULE_NAME)->where("id=".intval($_REQUEST['id']))->getField("id");
		//开始验证有效性
		/*$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['title']))
		{
			$this->error(L("ARTICLECATE_TITLE_EMPTY_TIP"));
		}			
		if($data['pid']>0)
		{
			$data['type_id'] = M("ArticleCate")->where("id=".$data['pid'])->getField("type_id");
		}
		if($data['type_id']!=1)
		{
			$data['iconfont'] = "";
		}*/
		//开始同步type_id
		/*$ids = D("ArticleCate")->getChildIds($data['id']);
		M("ArticleCate")->where(array("id"=>array("in",$ids)))->setField("type_id",$data['type_id']);*/


		// 更新数据
		$id = $_REQUEST['id'];
		$price = $_REQUEST['price'];
		$time = $_REQUEST['time'];
		$hours = $_REQUEST['hours'];
		$deal_id = $_REQUEST['deal_id'];
		$log_info = '修改ktv购买属性'.$id.'';

//		$this->error($log_info);
//		file_put_contents('1.txt',var_dump($fields,true));//不能打印，所以写到文本里
//		$list=M(MODULE_NAME)->add($data);
		$sql ="UPDATE `".DB_PREFIX."ktv_shuxing` set `price`= '$price', `time`= '$time', `deal_id`= '$deal_id',`hours`='$hours' where `id` = '$id' ";
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
		$sql = "delete from ".DB_PREFIX."ktv_shuxing where `id` = '$id'";
		$ret = $GLOBALS['db']->query($sql);
		if($ret){
			save_log(l("FOREVER_DELETE_SUCCESS"),1);
			$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
		}else{
			save_log(l("FOREVER_DELETE_FAILED"),0);
			$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
		}

	}
}
?>