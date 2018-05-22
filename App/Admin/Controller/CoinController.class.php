<?php
namespace Admin\Controller;
use Common\Model\UserModel;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
class CoinController extends AdminBaseController {
	/**
	 * 自动加载方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * 充币记录
	 */
	public function showChongBiLog(){
		//查询可选的银行卡
		$bank = M('Bank')->select();
		$this->assign('bank',$bank);
		$uid = I('user_id');
		if(!empty($uid)){
			$where['user_id'] = $uid;
		}
		$username = I('username');
		if(!empty($username)){
			$where['user_id'] = M('User')->where(['username'=>$username])->find()['uid'];
			$this->assign('username',$username);
		}
		$bank_id = I('bank_id');
		if(!empty($bank_id)){
			$where['bank_id'] = $bank_id;
		}
		$types = I('types');
		if($types){
		if($types==1){
			$where['image'] = '已上传';
		}
		if($types==2){
			$where['image'] = array('neq','已上传');
		}
		}
		$status = I('status');
		if(!empty($status)){
			switch ($status){
				case 1:
					$where['status'] = 1;break;
				case -1:
					$where['status'] = -1;break;
				case 2:
					$where['status'] = 0;break;
			}
			$this->assign('status',$status);
		}
		$parameter = [
		'status'=>$status,
		'user_id'=>$uid,
		'username'=>$username,
		];
		$RECHARGE = M('Recharge'); // 实例化User对象
		$count      = $RECHARGE->where($where)->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		setPageParameter($Page, $parameter);
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $RECHARGE->where($where)->order('add_time desc,status desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k=>$v){
			$list[$k]['user'] = M('User')->where(['uid'=>$v['user_id']])->find();
			$list[$k]['bank_name'] = M('Bank')->where(['bank_id'=>$v['bank_id']])->find()['bank_name'];			
			$list[$k]['shenqing'] = M('User')->where(['uid'=>$v['baodan_uid']])->find();
		}
		$recharge_status = C('RECHARGE_STATUS');
		$this->assign('recharge_status',$recharge_status);
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		//echo M('Recharge')->_sql();
		//dump($list);
		$this->display();
	}
	/**
	 * 充值积分通过
	 */
	public function adoptRecharge(){
		$where['recharge_id'] = I('recharge_id');
		$where['status'] = 0;
		$recharge = M('Recharge')->where($where)->find();
		if(!$recharge){
			$this->error('记录不存在');
		}
		$USERMONEY = new UserMoneyApi();
		$adopt = $USERMONEY->setUserMoneyLogic($recharge['user_id'],$recharge['money'],1,101,'充值'.$recharge['num'],101,$recharge['money_type']);
		if($adopt){
			M('Recharge')->where($where)->setField('status','1');
		}
		if($adopt){			
				$this->success('操作成功');
			}else{
			$this->error('操作失败');
		}
	}
	/**
	 * 充值拒绝
	 */
	public function refuseRecharge(){
	$recharge_id = I('recharge_id');
		$recharge = M('Recharge')->where(['recharge_id'=>$recharge_id])->find();
		if(empty($recharge_id)||!$recharge||$recharge['status']!=0){
			$data = [
				'status'=>-1,
				'msg'=>'参数错误'
			];
			$this->ajaxReturn($data);
		}
		$r_recharge = M('Recharge')->where(['recharge_id'=>$recharge_id])->setField('status',-1);
		if(!$r_recharge){
			$data = [
			'status'=>-2,
			'msg'=>'服务器繁忙'
					];
			$this->ajaxReturn($data);
		}
		$data = [
		'status'=>1,
		'msg'=>'拒绝成功'
				];
		$this->ajaxReturn($data);
	}
	//提现记录
	public function showTiBiLog(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_POST){
			$url = I('url');if($url){
				$where['url'] = array('like','%'.$url.'%');
			}
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
		$count=M("Withdraw")->where($where)->count();
		$Page=new \Org\Nx\Page($count,15);
		setPageParameter($Page, array('uid'=>$uid,'status'=>$status));
		$show=$Page->show();
		$list=M("Withdraw")->where($where)->order('status,add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$withdraw_status = C('WITHDRAW_STATUS');
		foreach($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['uid'])['username'];
			$list[$k]['bank_num'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['number'];
			$list[$k]['bank_type'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['type'];
			$list[$k]['realname'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['name'];
			$list[$k]['withdraw_status'] = $withdraw_status[$v['status']];
		}
		$this->assign('list',$list);
		$this->assign('withdraw_status',$withdraw_status);
		$this->assign('page',$show);
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
			$this->success('操作成功');
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
			$adopt = $USERMONEY->setUserMoneyLogic($withdraw['uid'],$withdraw['money'],1,201,'提现失败返还'.$withdraw['money'],201,2);
			if(!$adopt){
				$this->error('修改资金失败');
			}
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}
	public function findPingzheng(){
		$project_id = I('project_id');
		$list = M('Project_user_pic')->where(['project_id'=>$project_id])->select();
		$this->assign('list',$list);
		$this->display();
	}
//下载表格
	public function to_download(){
			$where['status']= I('status');
		$CONTACT_TYPE = C('CONTACT_TYPE');
		$str = "编号,用户名,卡号,开户行,开户人,提现金额,手续费,提现时间,状态\n";
		$str = iconv('utf-8','gb2312',$str);
		$list=M("Withdraw")->where($where)->order('status,add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$withdraw_status = C('WITHDRAW_STATUS');
		$USER = new UserModel();
		foreach($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['uid'])['username'];
			$list[$k]['bank_num'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['number'];
			$list[$k]['bank_type'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['type'];
			$list[$k]['realname'] = M('User_bank')->where(['user_id'=>$v['uid']])->find()['name'];
			$list[$k]['withdraw_status'] = $withdraw_status[$v['status']];
		}
		//dump($list);die;
		foreach($list as $k=>$v){
			$xuhao = iconv('utf-8','gb2312',$v['id']);
			$username = iconv('utf-8','gb2312',$v['username']);
			$bank_num = iconv('utf-8','gb2312','	'.$v['bank_num']);
			$bank_type = iconv('utf-8','gb2312',$v['bank_type']);			
			$realname = iconv('utf-8','gb2312',$v['realname']);
			$money = iconv('utf-8','gb2312',$v['money']);
			$fee = iconv('utf-8','gb2312',$v['fee']);
			$add_time = iconv('utf-8','gb2312',date("Y-m-d H:i:s",$v['add_time']));
			$status = iconv('utf-8','gb2312',$v['withdraw_status']);
			 
			$str .= $xuhao.",".$username.",".$bank_num.",".$bank_type.",".$realname.",".$money.",".$fee.",".$add_time.",".$status."\n";
		}
		$filename = '提现记录表.csv';
		export_csv($filename,$str);
	}
	//下载表格
	public function to_download1(){
		$status = I('status');
		if(!empty($status)){			
			$where['status'] = $status ;
				if($status==4){
					$where['status'] = 0 ;
				}
		}
		$CONTACT_TYPE = C('CONTACT_TYPE');
		$str = "编号,用户名,充值银行,充值金额,申请时间,状态\n";
		$str = iconv('utf-8','gb2312',$str);
		$list=M("Recharge")->where($where)->order('status,add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$recharge_status = C('RECHARGE_STATUS');
		$USER = new UserModel();
		foreach($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['user_id'])['username'];			
			$list[$k]['bank_name'] = M('Bank')->where(['bank_id'=>$v['bank_id']])->find()['bank_name'];			
			$list[$k]['recharge_status'] = $recharge_status[$v['status']];
		}
		//dump($list);die;
		foreach($list as $k=>$v){
			$xuhao = iconv('utf-8','gb2312',$v['recharge_id']);
			$username = iconv('utf-8','gb2312',$v['username']);
			$bank_name = iconv('utf-8','gb2312',$v['bank_name']);
			$money = iconv('utf-8','gb2312',$v['money']);
			$add_time = iconv('utf-8','gb2312',date("Y-m-d H:i:s",$v['add_time']));
			$status = iconv('utf-8','gb2312',$v['recharge_status']);
			 
			$str .= $xuhao.",".$username.",".$bank_name.",".$money.",".$add_time.",".$status."\n";
		}
		$filename = '充值记录表.csv';
		export_csv($filename,$str);
	}
}