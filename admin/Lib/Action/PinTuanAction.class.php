<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class PinTuanAction extends CommonAction{
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
		$map['is_delete'] = 0;
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		$model = D ("Deal");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
       //输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);

		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);

		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		/*$condition['is_delete'] = 0;*/
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		//查询商品
		$map['is_delete'] = 0;
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		$model = D ("Deal");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}

		$this->assign ( 'vo', $vo );
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);

		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
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
		$data = M(MODULE_NAME)->create ();

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
		$title = $_REQUEST['title'];
		$cate_id = $_REQUEST['cate_id'];
		$city_id = $_REQUEST['city_id'];
		$deal_id = $_REQUEST['deal_id'];
		$one_num = $_REQUEST['one_num'];
		$one_price = $_REQUEST['one_price'];
		$two_num = $_REQUEST['two_num'];
		$two_price = $_REQUEST['two_price'];
		$three_num = $_REQUEST['three_num'];
		$three_price = $_REQUEST['three_price'];
		$buy_count = $_REQUEST['buy_count'];
		$is_effect= $_REQUEST['is_effect'];
		$is_recommend = $_REQUEST['is_recommend'];
		$begin_time = strim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$add_time = NOW_TIME;
		$log_info = $data['title'];



		$data['is_recommend'] = $_REQUEST['is_recommend'];
		$sql ="INSERT INTO `".DB_PREFIX."pin_tuan`( `title`, `cate_id`, `city_id`, `deal_id`, `one_num`,`one_price`, `two_num`,`two_price`,`three_num`,`three_price`,`buy_count`,`is_effect`,`is_recommend`,`begin_time`,`end_time`,`add_time`)
		VALUES ('$title','$cate_id','$city_id','$deal_id','$one_num','$one_price','$two_num','$two_price','$three_num','$three_price','$buy_count','$is_effect','$is_recommend','$begin_time','$end_time','$add_time')" ;

		$list = $GLOBALS['db']->query($sql);
//		$this->error($log_info);
//		file_put_contents('1.txt',var_dump($fields,true));//不能打印，所以写到文本里
//		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
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
		/*$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("title");*/
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
		$title = $_REQUEST['title'];
		$cate_id = $_REQUEST['cate_id'];
		$city_id = $_REQUEST['city_id'];
		$deal_id = $_REQUEST['deal_id'];
		$one_num = $_REQUEST['one_num'];
		$one_price = $_REQUEST['one_price'];
		$two_num = $_REQUEST['two_num'];
		$two_price = $_REQUEST['two_price'];
		$three_num = $_REQUEST['three_num'];
		$three_price = $_REQUEST['three_price'];
		$buy_count = $_REQUEST['buy_count'];
		$is_effect= $_REQUEST['is_effect'];
		$is_recommend = $_REQUEST['is_recommend'];
		$begin_time = strim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$add_time = NOW_TIME;
		$log_info = $title;

		$sql ="UPDATE  ".DB_PREFIX."pin_tuan set `title`= '$title', `cate_id`= '$cate_id', `city_id`= '$city_id', `deal_id`= '$deal_id', `one_num`= '$one_num', `one_price`= '$one_price', `two_num`= '$two_num',`two_price`= '$two_price',`three_num`= '$three_num',`three_price`= '$three_price',`buy_count`= '$buy_count',`is_effect`= '$is_effect',`is_recommend`= '$is_recommend',`begin_time`= '$begin_time',`end_time`= '$end_time',`add_time`= '$add_time' where `id` = $id ";

//		$this->error($sql);
        $list = $GLOBALS['db']->query($sql);


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
				if(M("ArticleCate")->where(array ('pid' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error (l("SUB_ARTICLECATE_EXIST"),$ajax);
				}
				if(M("Article")->where(array ('cate_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error (l("SUB_ARTICLE_EXIST"),$ajax);
				}

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
}
?>