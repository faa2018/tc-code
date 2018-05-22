<?php
namespace Common\Api;
use Org\Nx\Page;
use Common\Model\UserModel;
class FinanceApi  {
	/**
	 * 
	 * @param int $uid
	 * @param array $type
	 * @param array $sz_type
	 * @param array $money_type
	 * @param int $start_time
	 * @param int $end_time
	 * @param unknown $time
	 * @param int $limit
	 * @param string $order
	 * @return string
	 */
	public function getFinanceByUid($Parameter,$uid,$type=9999,$sz_type=array(1,2),$money_type,$start_time,$end_time,$limit=100,$order='id desc'){
    	//格式化两个状态
    	$type_name       = C('FINANCE_TYPE');//财务种类
    	$sz_type_name    = C('FINANCE_SZ_TYPE');//收支类型
    	$money_type_name = C('USER_MONEY_TYPE'); //资金类型
    	//不传type 查全部的情况
    	if($type == 9999){
			$type = array_keys($type_name);
		}
		//三个类型的格式化
		if(is_array($type)){
			$where['type']  = array('in',$type);
		}else{
			$where['type']  = $type;
		}
		if(is_array($sz_type)){
			$where['sz_type']  = array('in',$sz_type);
		}else{
			$where['sz_type']  = $sz_type;
		}
		if(is_array($money_type)){
			$where['money_type']  = array('in',$money_type);
		}else{
			$where['money_type']  = $money_type;
		}
		if($start_time){
			$where['add_time'] = array('gt',$start_time);
		} 
		if($end_time){
			$where['add_time'] = array('lt',$end_time);
		}
    	if($uid){
    		$where['uid']     = $uid;
    		$table = M('Finance'.cuttable($uid));
			
    	}else{
    		$table = M('Finance_view');
    	}
    	$count = $table->where($where)->count();// 查询满足要求的总记录数
    	
		$Page       = new Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		setPageParameter($Page,$Parameter);
		$show       = $Page->show();// 分页显示输出
		$list = 
    		$table
			->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order($order)
			->select();
		$USER = new UserModel();
	    foreach ($list as $k => $v){
	    	$list[$k]['type_name']    = $type_name[$v['type']];
	    	$list[$k]['sz_type_name'] = $sz_type_name[$v['sz_type']]; //Lb修改
	    	$list[$k]['money_type_name'] = $money_type_name[$v['money_type']];// Lb修改
			$list[$k]['username'] = $USER->getUserByUid($v['uid'])['username'];
		}
    	$list['list'] = $list;
    	$list['page'] = $show;//分页
    	if(!$uid){
    		$count_money_all  = $table->where($where)->sum('money');// 查询满足要求的总记录数
    		$count_money_page = $table->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order)->sum('money');
    	}
    	$list['count_money_all']  = $count_money_all;//总统计金额
    	$list['count_money_page'] = $count_money_page;//分页统计金额
    	return $list;
    }
    public function getFinanceSql(){
    	//纵向连表语句
    	for($i=0;$i<10;$i++){
    		$sql_union_all .=
    		"select * from ztp_finance".$i."
			as f".$i."
			 union all ";
    	}
    	$sql = substr($sql_union_all,0,-10);
    	return $sql;
    }
}

