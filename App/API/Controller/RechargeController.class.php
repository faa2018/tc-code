<?php
namespace API\Controller;
use API\Controller\HomeController;
class RechargeController extends UserController {
	public function _initialize(){
		parent::_initialize();
	}
	//充值记录
	public function rechargeList(){
		$where['baodan_uid'] = $this->uid;
		$Recharge = M('Recharge'); // 实例化User对象
		$count      = $Recharge->where($where)->count();// 查询满足要求的总记录数
		$Page       = new \Org\Nx\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $Recharge->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	
		$re['list'] = $list;
		$re['page'] = $show;
		$this->ajaxReturn($re,'JSONP');
	}
	/**
	 * 获取充值的银行卡
	 */
	public function getBank(){
		$bank = M('Bank')->where(['status'=>1])->limit(2)->select();
		$this->ajaxReturn($bank,'JSONP');
	}
	/**
	 * 获取充值的银行卡详情
	 */
	public function getBankDetails(){
		$bank_id = I('bank_id');
		$bank = M('Bank')->where(['bank_id'=>$bank_id])->find();
		$this->ajaxReturn($bank,'JSONP');
	}
	/**
	 * 充值方法
	 */
	public function recharge(){
		$user_id = $this->uid;
		$type = I('type')?I('type'):'2';
		$money = I('money')?I('money'):null;
		if(empty($type)){
			$data = [
			'status'=>-5,
			'msg'=>'请选择充值类型',
			];
			$this->ajaxReturn($data,'JSONP');
		}
		if(!floatval($money)){
			$data = [
				'status'=>-1,
				'msg'=>'请输入正确的金额',
			];
			$this->ajaxReturn($data,'JSONP');
		}
		$recharge = [
			'user_id'=>$user_id,
			'baodan_uid'=>$user_id,
			'money'=>$money,
			'money_type'=>$type,
			'type'=>1,
			'add_time'=>time(),
			'status'=>0,
			'image'=>'',
			'bank_id'=>''
		];
		$r = M('Recharge')->add($recharge);
		if(!$r){
			$data = [
			'status'=>-4,
			'msg'=>'服务器繁忙',
			];
			$this->ajaxReturn($data,'JSONP');
		}
		$data = [
		'status'=>1,
		'msg'=>'申请成功，请尽快上传打款凭证，否则平台将不给予审核',
		];
		$this->ajaxReturn($data,'JSONP');
	}
	/**
	 * 上传打款记录
	 */		
	public function uploadPingzheng(){
		header('Access-Control-Allow-Origin: *');
		$array =$_FILES;
		$recharge_id = I('recharge_id')?I('recharge_id'):null;
		$recharge = M('Recharge')->where(['recharge_id'=>$recharge_id])->find();
		if(!$recharge){
			$data = [
			'status'=>-11,
			'msg'=>'非法操作',
			];
			$this->ajaxReturn($data,'JSON');
		}
		$bank_id = I('bank_id')?I('bank_id'):null;
		if(empty($bank_id)){
		$data = [
			'status'=>-6,
			'msg'=>'请选择充值银行',
			];
			$this->ajaxReturn($data,'JSON');
		}
		$bank = M('Bank')->where(['bank_id'=>$bank_id,'status'=>1])->find();
		if(!$bank){
			$data = [
			'status'=>-2,
			'msg'=>'参数错误，未查询到此银行卡',
			];
			$this->ajaxReturn($data,'JSON');
		}	
		$data['bank_id'] = $bank_id;
		$data['image'] = $this->upload($_FILES['file0']);
		$r = M('Recharge')->where(['recharge_id'=>$recharge_id])->save($data);
		/* foreach ($array as $k=>$v){
			$recharge_pic['recharge_id'] =$recharge_id;
			$recharge_pic['image'] = $this->upload($v);
			$recharge_pic['add_time'] =time();
			M('Recharge_pic')->add($recharge_pic);
		} */
			if($r){
			$return['status'] = 1;
			$return['msg'] = "上传成功";
			$this->ajaxReturn($return,'JSON');
			}else{
				$return['status'] = -1;
			$return['msg'] = "系统繁忙";
			$this->ajaxReturn($return,'JSON');
				
			}
	
}
}