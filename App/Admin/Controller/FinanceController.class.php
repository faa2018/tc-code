<?php
namespace Admin\Controller;
use Common\Model\FinanceModel;
use Org\Nx\Page;
use Common\Model\UserModel;
use Common\Model\CoinModel;
use Common\Api\FinanceApi;
use Common\Api\UserMoneyApi;
class FinanceController extends AdminBaseController{
	//财务日志列表
	public function index(){
		$username 	= I('username');//用户名
		$this->assign('username',$username);
		$uid 		= I('uid');//uid
		$this->assign('uid',$uid);
		$start_time = strtotime(I('start'));//起始时间
    	$this->assign('start',I('start'));
		$end_time = strtotime(I('end'));//终止时间
    	$this->assign('end',I('end'));
		//格式化两个状态
		$type_name    = C('FINANCE_TYPE');//财务日志种类
		$this->assign('type_name',$type_name);
		$sz_type_name = C('FINANCE_SZ_TYPE');//收支种类
    	$this->assign('sz_type_name',$sz_type_name);
    	$money_type_name = C('USER_MONEY_TYPE');//收支种类
    	$this->assign('money_type_name',$money_type_name);
		//搜索用户名转成用户id;
		$USER = new UserModel();
		$type = I('type')?I('type'):9999;
		//如果有搜索查对应   没有搜索查全部
		$API_FINANCE = new FinanceApi();
		$Parameter['username'] = $username;
		if(!$uid){
		$uid = M('User')->where(['username'=>$username])->find()['uid'];
		}
		$Parameter['uid'] = $uid;
		$Parameter['start'] = $start_time;
		$Parameter['end'] = $end_time;
		$list = $API_FINANCE->getFinanceByUid($Parameter,$uid,$type,array_keys($sz_type_name),array_keys($money_type_name), $start_time, $end_time);
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
		$this->assign('money_count',$list['count_money_page']);
		$this->assign('money_count_zong',$list['count_money_all']);
		$this->display();
	}
	//每日回馈积分记录
	public function getFenhongLog(){
		$where = '';
		$count = M('Project_fenhong_log')->where($where)->count();
		$Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =
		M('Project_fenhong_log')
		->where($where)
		->limit($Page->firstRow.','.$Page->listRows)
		->order('add_time desc')
		->select();
	    $this->assign('list',$list);
		 $this->assign('page',$show);
	    $this->display();
	}
	//获取积分的列表
	public function jifenList(){
	    $where['uid'] = $uid;
		$count = M('Project_jifen_day_log')->where($where)->count();
		$Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =
		M('Project_jifen_day_log')
		->where($where)
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
	    $this->assign('list',$list);
		 $this->assign('page',$show);
	    $this->display();
	}
	//日统计列表
	public function jifenDayList(){
		$id = I('id');
		if($id){
		$fenhong_log_id = I('id');
		$fenhong_log = M('Project_fenhong_log')->where('id = '.$fenhong_log_id)->find();
	    $where['date']= date('Y/m/d',$fenhong_log['add_time']);	
		}		
		$count = M('Project_jifen_day_zong_log')->where($where)->count();
		$Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =
		M('Project_jifen_day_zong_log')
		->where($where)
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		$this->assign('list',$list);
		 $this->assign('page',$show);
	    $this->display();
	}
	public function adminMoneyLog(){
		$type = I('type');
		$USER = new UserModel();
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
		}
		$count = M('Admin_recharge')->where($where)->count();
		$Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =
		M('Admin_recharge')
		->where($where)
		->limit($Page->firstRow.','.$Page->listRows)
		->order('add_time desc')
		->select();
		$money_type_name = C('USER_MONEY_TYPE');
		foreach ($list as $k => $v){
			$list[$k]['username']     = $USER->getUserByUid($v['uid'])['username'];
			$list[$k]['admin_name']     = M('Admin_user')->where(array('id'=>$v['admin_id']))->find()['username'];
			$list[$k]['money_type'] = $money_type_name[$v['money_type']];
		
		}
		$this->assign('type',$type);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	/**
	 *代数奖列表
	 */
	public function daishuList(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_GET){
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
		$count             =  M('Project_daishu_log')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//setPageParameter($Page, array('username'=>$username,'status'=>$status));
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =  M('Project_daishu_log')
		->where($where)
		->order('id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		//处理状态 ，等级的格式化问题
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	public function setDaishuStatus(){
		$id = I('id');
		if(!$id){
			$this->error('参数错误');
		}
		if(!empty($id)){
			$where['id'] = array('in',$id);
			$log = M('Project_daishu_log')->where($where)->select();
			foreach($log as $k=>$v){
				if($v['status']!=0){
					$this->error('请选中待审核状态的数据');
				}
				$USERMONEY = new UserMoneyApi();
				$money_daishu = $this->config['daishu_money'];
				$r[] = $USERMONEY->setUserMoneyLogic($v['uid'],$money_daishu, 1, 1014, '获得代数奖',1014,2);
				$r[] =  M('Project_daishu_log')->where(['id'=>$v['id']])->setField('status',1);
			}
		}
		if (in_array(false, $r)){
			M()->rollback();
			$this->error('系统繁忙');
		}else {
			M()->commit();
			$this->success('操作成功',U('Finance/daishuList'));
		}
	}
}