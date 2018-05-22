<?php
namespace Admin\Controller;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
use Common\Model\MessageModel;
use Common\Model\UserModel;
use Common\Model\CoinModel;
use Common\Model\ConfigModel;
class UserMoneyController extends AdminBaseController {
	/**
	 * 自动加载方法
	 */
	private $USER;//实例化userModel
	public function _initialize(){
		$this->USER = new UserModel();
		parent::_initialize();
	}
/***********************************以下为展示页面方法*************************************/
//查看/修改用户金额
	public function updateUserMoney(){
			$uid  = I('uid');
			$where['uid'] = $uid;
			$USERMONEY = new UserMoneyModel($uid);
			$list = $USERMONEY->getUserMoneyByUid();
			$this->assign("list",$list);
			$where['uid'] = $uid;
    		$where['status'] = 0;
			$username = $this->USER->getUserByUid($uid)['username'];
			$this->assign("username",$username);
			$money_type = C('USER_MONEY_TYPE');
			$this->assign('money_type',$money_type);
			$this->display();
	}
	public function editUserMoney(){
		$data['uid'] = I('uid');
		if(empty($data['uid'])){
			$this->error('该用户不存在');
		}
		$data['admin_id'] = $_SESSION['user']['id'];//管理员id
		$data['type'] = 1;
		$data['money_type'] = I('money_type');
		$data['inc_type'] = I('inc_type');
		$data['num'] = I('num');
		$data['desc'] = I('desc');
		$data['add_time'] = time();
		foreach ($data as $v){
			if($v ==''){
				$this->error('信息填写不完整');
			}
		}
		$MONEY = new UserMoneyModel($data['uid']);
		//消费积分剩余的钱
		$xiaofei = $MONEY->getUserMoneyByUid($data['money_type'])['num'];
		if($data['inc_type']==2){
			if($xiaofei<$data['num']){
				$this->error('余额不足');
			}
		}
		$list = M('Admin_recharge')->add($data);
		$MONEY = new UserMoneyModel($data['uid']);
		if($list){
			$addMoney = $MONEY->updateUserMoney($data['num'],$data['inc_type'],$data['money_type']);
			if($addMoney){
				$this->success('操作成功',U('Finance/adminMoneyLog'));
			}else{
				$this->error('系统繁忙');
			}
		}
	}
	//查看/修改用户币种
	public function updateUserCoin(){
		$uid  = I('uid');
		$COIN = new CoinModel();
		$list = $COIN->getUserCoinByUid($uid);
		$this->assign("list",$list);
		$username = $this->USER->getUserByUid($uid)['username'];
		$this->assign("username",$username);
		$COIN = new CoinModel();
		$where['switch'] = 1;
		$coin_type = $COIN->getCoinList($where);
		$this->assign("coin_type",$coin_type);
		$this->display();
	}
	public function editUserCoin(){
		$data['uid'] = I('uid');
		if(empty($data['uid'])){
			$this->error('该用户不存在');
		}
		$data['admin_id'] = $_SESSION['user']['id'];//管理员id
		$data['type'] = 2;
		$data['money_type'] = I('money_type');
		$data['inc_type'] = I('inc_type');
		$data['num'] = I('num');
		$data['desc'] = I('desc');
		$data['add_time'] = time();
		foreach ($data as $v){
			if($v ==''){
				$this->error('信息填写不完整');
			}
		}
		//dump($data);die;
		$list = M('Admin_recharge')->add($data);
		$MONEY = new UserMoneyModel();
		if($list){
			$addMoney = $MONEY->updateUserCoin($data['uid'],$data['num'],$data['inc_type'],$data['money_type']);
			if($addMoney){
				$this->success('操作成功',U('Finance/adminMoneyLog'));
			}else{
				$this->error('系统繁忙');
			}
		}
	}
	//充值
	public function chongzhi(){
		if(IS_POST){
			$chongzhi_name  = I('username')?I('username'):null;
			$chongzhi_money = I('money')?floatval(I('money')):null;
			$chongzhi_money_type = I('money_type')?floatval(I('money_type')):1;
			if($chongzhi_money<=0){
				$this->error('请输入正确数值');
			}
			$where['username'] = $chongzhi_name;
			$uid = $this->USER->getUserByUsername($chongzhi_name)['id'];
			if(empty($uid)){
				$this->error('请输入正确的用户名');
			}
			$UserMoneyApi = new UserMoneyApi();
			$chongzhi = $UserMoneyApi->setUserMoneyLogic($uid, $chongzhi_money, 1, 3, '后台充值成功', 3,$chongzhi_money_type);
			if($chongzhi){
				$this->success('操作成功',U('UserMoney/chongzhi'));
			}else{
				$this->error('操作失败');
			}
		}else{
			//币种
			$money_type = C('USER_MONEY_TYPE');
			$this->assign('money_type',$money_type);
			$this->display();
		}
	}
	//充值记录
	public function getUserPayList(){
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
	//提现记录
	public function getUserTixian(){
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
	 * 充值通过
	 */
	public function chongzhi_adopt(){
		$where['id'] = I('id');
		$where['status'] = 0;
		$recharge = M('Recharge')->where($where)->find();
		if(!$recharge){
			$this->error('记录不存在');
		}
		$status = M('Recharge')->where($where)->setField('status','1');
		if($status){
			$USERMONEY = new UserMoneyApi();
			$adopt = $USERMONEY->setUserMoneyLogic($recharge['uid'],$recharge['num'],1,1,'充值'.$recharge['num'],1,1);
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
	public function chongzhi_refuse(){
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
			$USERMONEY = new UserMoneyApi();
			$adopt = $USERMONEY->setUserMoneyLogic($withdraw['uid'],$withdraw['money'],1,2,'提现失败返还'.$withdraw['money'],2,1);
			if(!$adopt){
				$this->error('修改资金失败');
			}
			$this->success('操作成功',U('UserMoney/getUserTixian'));
		}else{
			$this->error('操作失败');
		}
	}
	//释放待解冻积分
	public function releaseFrozen(){
		if(IS_POST){
			set_time_limit(0);
			$USERMONEY = new UserMoneyApi();
			$num = I('num');
			$bili = $num/100;
			$where['status'] 		= 1;
			$where['shifang_num']	= $this->config['shifang_num']+1;
			$count  = M('User_jiedong')->where($where)->count();
			$s = (int)($count/200);
			for($i=0;$i<$s;$i++){
				$list = M('User_jiedong')->where($where)->limit($i*500,500+$i*500)->select();
				$this->sendMoney($list, $bili);
			}
			if(($count-$s*200)>0){
				$list = M('User_jiedong')->limit($s*200,$count)->select();
				$this->sendMoney($list, $bili);
			}
			M('Config')->where(array('key'=>'shifang_num'))->setInc('value',1);
			$this->success('操作成功');
		}else{
			$this->display();
		}
	}
	private function sendMoney($list,$bili){
		$USERMONEY = new UserMoneyApi();
		foreach($list as $k=>$v){
			$where['uid'] = $v['uid'];
			$where['type'] = 8;
			$where['num'] = array('gt',0);
			$money = M('User_money'.cuttable($v['uid']))->where($where)->find();
			if(!money){
				continue;
			}
			//减待解冻积分
			$decmoney = $USERMONEY->setUserMoneyLogic($v['uid'],$bili*$v['jifen'],2,6001,'释放待解冻积分扣除',6001,8);
			//加七乐积分
			$incmoney = $USERMONEY->setUserMoneyLogic($v['uid'],$bili*$v['jifen'],1,6002,'释放待解冻积分获取',6002,2);
			//修改表数据
			M('User_jiedong')->where(array('uid'=>$v['uid']))->setInc('shifang_num',1);
			M('User_jiedong')->where(array('uid'=>$v['uid']))->setInc('shifang_money',$bili*$v['jifen']);
		}
		return ;
	}
}