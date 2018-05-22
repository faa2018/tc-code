<?php
namespace Home\Controller;
use Home\Controller\UserController;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
use Common\Model\ConfigModel;
use Common\Model\UserRelationModel;
use Common\Model\UserModel;
use Common\Model\FinanceModel;
use Common\Model\CoinModel;
use Common\Model\OrderModel;
class OrderController extends UserController {
	public $ORDER;
	public function _initialize(){
		parent::_initialize();
		$this->ORDER = new OrderModel();
	}
	//我的订单
	public function index(){
		$where['user_id'] = $this->uid;
		$order = $this->ORDER->getOrder($where);
		$this->assign('order',$order);
		$this->display();
	}
}