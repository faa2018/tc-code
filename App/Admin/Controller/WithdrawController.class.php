<?php
namespace Admin\Controller;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
use Common\Model\UserModel;
class WithdrawController extends AdminBaseController {
	/**
	 * 自动加载方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	//提现记录
	public function getWithdrawList(){
		$status = 999;
		$USER = new  UserModel();
		$USERMONEY = new UserMoneyModel();
		if(IS_POST){
			$url = I('url');if($url){
				$where['url'] = array('like','%'.$url.'%');
			}
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username)['uid'];
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
		
		$list = $USERMONEY->getWithdrawLog($where,20);
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
		$this->display();
	}
	/**
	 * 提现通过
	 */
	public function withdraw_adopt(){
		$where['id'] = I('id');
		$where['status'] = 0;
		$withdraw = M('Withdraw')->where($where)->find();
		if(!$withdraw){
			$this->error('记录不存在');
		}
		$status = M('Withdraw')->where($where)->setField('status','1');
		if($status){
			$this->success('操作成功',U('UserMoney/getUserTixian'));
		}else{
			$this->error('操作失败');
		}
	
	}
	/**
	 * 提现拒绝
	 */
	public function withdraw_refuse(){
		$where['id'] = I('id');
		$where['status'] = 0;
		$withdraw = M('Withdraw')->where($where)->find();
		if(!$withdraw){
			$this->error('记录不存在');
		}
		$status = M('Withdraw')->where($where)->setField('status','2');
		if($status){
			$USERMONEY = new UserMoneyApi($withdraw['uid']);
			$adopt = $USERMONEY->setUserMoneyLogic($withdraw['money'],1,2,'提现失败返还'.$withdraw['money'],2,1);
			if(!$adopt){
				$this->error('修改资金失败');
			}
			$this->success('操作成功',U('UserMoney/getUserTixian'));
		}else{
			$this->error('操作失败');
		}
	}
}