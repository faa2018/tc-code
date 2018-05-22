<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\BannerModel;
class IndexController extends UserController {
	public function _initialize(){
		parent::_initialize();
	}
	//轮播
	public function getBannerList(){
		$BANNER = new BannerModel();
		$banner_where['status'] = 1 ;
		$banner_where['type'] = 1 ; 		
		$banner_list = $BANNER->selectBanner(8,$banner_where);
		 return $this->ajaxReturn($banner_list,'jsonp');
	}
	//合作商家
	public function getCooperation(){
		$BANNER = new BannerModel();
		$banner_where['status'] = 1 ;
		$banner_where['type'] = 2 ; 		
		$banner_list = M('Banner')->where($banner_where)->find();
		 return $this->ajaxReturn($banner_list,'jsonp');
	}
	//获取公告列表
	public function getGonggaoList(){
	    $list = M('Art')->where('type = 2 and status = 1')->select();
	    $data_return['status'] = 1;
	    $data_return['info']['list'] = $list;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取公告详情
	public function getGonggaoDetails(){
		$id = I('id');			   
	    $list = M('Art')->where(['id'=>$id,'status'=>1])->find();
	    return $this->ajaxReturn($list,'jsonp');
	}
	//获取用户协议
	public function getXieYiDetails(){
	    $list = M('Art')->where(['type'=>3])->find();
	    return $this->ajaxReturn($list,'jsonp');
	}
	//获取排行
	public function getRankingList(){
		$last = M('Project_user_auto')->order('id desc')->find();
		$where['uid'] = $this->uid;
		$zidingyi = M('User_ranking')->where($where)->select();
	    $log = M('Project_user_auto')->where($where)->order('ranking')->select();
	    foreach ($log as $k => $v) {
	    	$log[$k]['username'] = M('User')->where(['uid'=>$v['uid']])->find()['username'];
	    }
	    foreach ($zidingyi as $kk => $vv) {
	    	$zidingyi[$kk]['username'] = M('User')->where(['uid'=>$vv['uid']])->find()['username'];
	    }
	    $r = array_merge($zidingyi,$log);
		$list['list'] = $r;
		$list['zongpaiming'] = $last['ranking']?$last['ranking']:'0';
	    return $this->ajaxReturn($list,'jsonp');
	}
	
}