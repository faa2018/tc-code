<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\UserMoneyModel;
use Common\Model\ConfigModel;
use Common\Model\CoinModel;
use Common\Api\UserCoinApi;
use Common\Model\UserModel;
use Common\Api\UserMoneyApi;
class WithdrawController extends UserController {
	protected $USERMONEY;
	public function _initialize(){
		parent::_initialize();
		$this->USERMONEY = new UserMoneyModel($this->uid);
	}
	//提现配置项
	public function withdrawConfig(){
		$M_CONFIG = new ConfigModel();
		$config['tixian_fee'] = $M_CONFIG->getConfig()['tixian_fee'];
		$config['tixian_min'] = $M_CONFIG->getConfig()['tixian_min'];
		$config['ketixian'] =  $this->USERMONEY->getUserMoneyByUid(2)['num'];
		$this->ajaxReturn($config,'JSONP');
		
	}
	public function withdrawList(){
		$WITHDRAW = M('Withdraw'); // 实例化User对象
		$count      = $WITHDRAW->count();// 查询满足要求的总记录数
		$Page       = new \Org\Nx\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $WITHDRAW->order('add_time')->limit($Page->firstRow.','.$Page->listRows)->select();
		$re['list'] = $list;
		$re['page'] = $show;
		$this->ajaxReturn($re,'JSONP');
	}
	//提现
	public function doWithdraw(){
		//$type = I('type');//提现类型
		$bank_id = I('bank_id');
		$money = I('money');
		if(empty($money)){
			$this->ajaxReturn(['status'=>-1,'info'=>'请输入金额'],'JSONP');
		}
		if(!regex($money, 'currency')){
			$this->ajaxReturn(['status'=>-1,'info'=>'请输入正确的金额','JSONP']);
		}
		if(empty($bank_id)){
			$this->ajaxReturn(['status'=>-12,'info'=>'请添加银行卡'],'JSONP');
	}
	if($money%100!=0){
			$this->ajaxReturn(['status'=>-14,'info'=>'请输入100的整数倍'],'JSONP');
		}
		if($money<$this->config['tixian_min']){
			$this->ajaxReturn(['status'=>-1,'info'=>'提现金额小于最小限额'],'JSONP');
		}	
		if($money>$this->config['tixian_max']){
			$this->ajaxReturn(['status'=>-1,'info'=>'提现金额大于最大限额'],'JSONP');
		}
		$user_money1 = $this->USERMONEY->getUserMoneyByUid(2);
		if($user_money1['num']<$money){
			$this->ajaxReturn(['status'=>-1,'info'=>'您的余额不足'],'JSONP');
		}

		//减钱  加记录
		
		$USERMONEYAPI = new UserMoneyApi();
		//减少资产金额
		$r_jian = $USERMONEYAPI->setUserMoneyLogic($this->uid,$money, 2, 201, '提现', 201,2);
		//增加记录
		$data = [
			'add_time'=>time(),
			'uid'=>$this->uid,
			'fee'=>$money*$this->config['tixian_fee'],
			'money'=>$money,
			'bank_id'=>$bank_id,
			'status'=>0,
		];
		$r_withdraw = M('Withdraw')->add($data);
		if($r_jian&&$r_withdraw){
			$this->ajaxReturn(['status'=>1,'info'=>'提现申请成功，等待审核'],'JSONP');
		} 
		$this->ajaxReturn(['status'=>-1,'info'=>'服务器繁忙'],'JSONP');
	}

}