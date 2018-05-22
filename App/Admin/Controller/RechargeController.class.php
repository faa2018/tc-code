<?php
namespace Admin\Controller;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
use Common\Model\MessageModel;
use Common\Model\UserModel;
use Common\Model\CoinModel;
use Common\Model\ConfigModel;
class RechargeController extends AdminBaseController {
	/**
	 * 自动加载方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	//充值记录
	public function getRechargeList(){
		$USER = new  UserModel();
		$status = 999;
		if(IS_POST){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
			$status = I('status');
			if($status === ''){
				$status = 999;
			}else{
				$where['status'] = $status;
			}
		}
		$this->assign('status_old',intval($status));
		$USERMONEY = new UserMoneyModel();
		$list = $USERMONEY->getChongZhilog($where,20);
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
		$this->display();
	}
	/**
	 * 充值通过
	 */
	public function recharge_adopt(){
		$where['id'] = I('id');
		$where['status'] = 0;
		$recharge = M('Recharge')->where($where)->find();
		if(!$recharge){
			$this->error('记录不存在');
		}
		$status = M('Recharge')->where($where)->setField('status','1');
		if($status){
			$USERMONEY = new UserMoneyApi($recharge['uid']);
			$adopt = $USERMONEY->setUserMoneyLogic($recharge['num'],1,1,'充值'.$recharge['num'],1,1);
			if($adopt){
				$this->success('操作成功',U('UserMoney/getUserPayList'));
			}else{
			$this->error('操作失败');
		}
	}
	}
	/**
	 * 充值拒绝
	 */
	public function recharge_refuse(){
		$where['id'] = I('id');
		$where['status'] = 0;
		$recharge = M('Recharge')->where($where)->find();
		if(!$recharge){
			$this->error('记录不存在');
		}
		$status = M('Recharge')->where($where)->setField('status','2');
		if($status){
			$this->success('操作成功',U('UserMoney/getUserPayList'));
		}else{
			$this->error('操作失败');
		}
	}
}