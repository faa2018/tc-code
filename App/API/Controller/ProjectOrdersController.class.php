<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\UserMoneyModel;
use Org\Nx\Page;
use Common\Api\UserMoneyApi;
use Common\Model\UserModel;
use Common\Model\UserRelationModel;
//会员消费专区商品控制器
class ProjectOrdersController extends UserController {
	public function _initialize(){
		//检测是否登录
		parent::_initialize();
	}
	//获取订单列表
	public function getProjectOrder(){
		$where['uid'] = $this->uid;
		$order_list = M('Project_orders')->where($where)->select();
		foreach($order_list as $k=>$v){
		    $good_id_list = M('Project_og')->where('order_id = '.$v['order_id'])->select();
		    foreach ($good_id_list as $kk=>$vv){
		        $good_list[$kk]['good'] = M('Project_goods')->where('project_goods_id = '.$vv['goods_id'])->find();
		        $good_list[$kk]['num'] = $vv['num'];
		    }
		    $order_list[$k]['good_list'] = $good_list;
		}
		//dump($order_list);die;
		$data_return['info']['order'] = $order_list;
		$data_return['status'] = 1;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
}


	