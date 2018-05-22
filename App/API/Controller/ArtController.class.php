<?php
namespace API\Controller;
class ArtController extends HomeController {
	public function _initialize(){
		parent::_initialize();
	}
	//获取资讯列表
	public function getInformation(){
	    $list = M('Art')->where('type = 1')->order('add_time desc')->select();
	    return $this->ajaxReturn($list,'jsonp');
	}
	//获取资讯详情
	public function informationDetails(){
		$id = I('id');
	    $list = M('Art')->where(['id'=>$id,'status'=>1])->find();
	    M('Art')->where(['id'=>$id,'status'=>1])->setInc('dianjishu',1);
	    return $this->ajaxReturn($list,'jsonp');
	}
	 
}