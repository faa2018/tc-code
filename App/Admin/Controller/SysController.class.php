<?php
namespace Admin\Controller;
use Org\Nx\Page;
use Admin\Controller\AdminBaseController;
use Common\Model\FinanceModel;
/**  
* 后台会员管理 
*/
class SysController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
	//展示统计信息
	public function count(){
		//上次统计时间
		$last_count_time = I('last_time');
		//点击按钮之后统计数据
		if($last_count_time){
			//查询今日天数 本月第一天天数
			$month_one_time = strtotime(date("Y-m-01"));
			//当前时间
			$today_time     = time();
			$FINANCE = new FinanceModel();
			//比较上次统计时间和本月第一天时间 判断需要统计一个月还是两个月
			if($last_count_time>$month_one_time){
				//本月 统计本月数据
				$FINANCE->runFinanceSysCount(1,$last_count_time,$today_time);
			}else{
				//上次统计时间是上个月 统计上月和本月数据
				$FINANCE->runFinanceSysCount(2,$last_count_time,$today_time,$month_one_time);			
			}
			//查询上个月的记录
			$up_month_last_time = $month_one_time-1;
		}
		$list = M('Sys_count')->select();
		$this->assign('sys',$list);
		//获取最后一次的结算时间
		$last_time = $list[count($list)-1]['add_time']?$list[count($list)-1]['add_time']:1;
		$this->assign('last_time',$last_time);
		$this->display();
	}
	
}
