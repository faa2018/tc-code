<?php
namespace API\Controller;
use API\Controller\UserController;
use Common\Model\UserMoneyModel;
use Common\Api\UserMoneyApi;
use Common\Model\ConfigModel;
use Common\Model\UserRelationModel;
use Common\Model\UserModel;
use Common\Model\FinanceModel;
use Common\Model\CoinModel;
use Common\Api\FinanceApi;
class FinanceController extends UserController {
public function _initialize(){
	parent::_initialize();
	}
	//财务日志
	public function index(){
	    $M_USERMONEY = new UserMoneyModel($this->uid);
		$list['lingdongbi'] = $M_USERMONEY->getUserMoneyByUid(4)['num'];//可用积分 已返还积分
        $list['jifen_use'] = $M_USERMONEY->getUserMoneyByUid(2)['num'];//可用积分 已返还积分
        $list['jifen_zong'] = l;//总消费积分 累计获取总积分
        $list['jifen_jifenchi'] = $M_USERMONEY->getUserMoneyByUid(3)['num'];//总消费积分 剩余回馈积分积分
        $list['jifen_usezong'] = $M_USERMONEY->getUserMoneyByUid(6)['num'];//累计回馈积分 累计返还积分
        $list['jifen_tuijianzong'] = $M_USERMONEY->getUserMoneyByUid(7)['num'];//总推荐积分 所有推荐奖的积分
        $list['jifen_userzong2'] = l;//？？？
        $data_return['status'] = 1;
        $data_return['info']['finance']   = $list;
        return $this->ajaxReturn($data_return,'jsonp');
	}
	//根据订单列表获取详情
	public function getProjectFinance(){
		$type_name       = C('FINANCE_TYPE');//财务种类
    	$sz_type_name    = C('FINANCE_SZ_TYPE');//收支类型
    	$money_type_name = C('USER_MONEY_TYPE'); //资金类型
		$project_id = I('project_id');
		$where['uid'] = $this->uid;
		$where['project_id'] = $project_id;
		$list = M('Finance'.cuttable($this->uid))->where($where)->select();
		foreach ($list as $k => $v){
	    	$list[$k]['type_name']    = $type_name[$v['type']];
	    	$list[$k]['sz_type_name'] = $sz_type_name[$v['sz_type']]; //Lb修改
	    	$list[$k]['money_type_name'] = $money_type_name[$v['money_type']];// Lb修改
	    }
		$data_return['status'] = 1;
        $data_return['info']['finance']   = $list;
        return $this->ajaxReturn($data_return,'jsonp');
	}
	//积分记录 日统计 
	public function getJifenDayCountList(){
	    //获取本日时间戳
	    $list = M('Project_jifen_day_zong_log')->where('uid = '.$this->uid)->order('id desc')->select();
	    $data_return['status'] = 1;
	    $data_return['info']['log'] = $list;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//财务明细
	public function getFinanceList(){
	    $uid 		= $this->uid;//uid
	    $start_time = strtotime(I('start'));//起始时间
	    $end_time = strtotime(I('end'));//终止时间
	    //格式化两个状态
	    $type_name    = C('FINANCE_TYPE');//财务日志种类
	    $sz_type_name = C('FINANCE_SZ_TYPE');//收支种类
	    $money_type_name = C('USER_MONEY_TYPE');//收支种类
	    //搜索用户名转成用户id;
	    $USER = new UserModel();
	    $type = I('type')?I('type'):9999;
	    //如果有搜索查对应   没有搜索查全部
	    $API_FINANCE = new FinanceApi();
	    $Parameter['uid'] = $uid;
	    $Parameter['start'] = $start_time;
	    $Parameter['end'] = $end_time;
	    $list = $API_FINANCE->getFinanceByUid($Parameter,$uid,$type,array_keys($sz_type_name),array_keys($money_type_name), $start_time, $end_time);
	    $data_return['status'] = 1;
	    $data_return['info']['finance'] = $list['list'];
	    return $this->ajaxReturn($data_return,'jsonp');
	}
}