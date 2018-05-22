<?php
namespace Common\Model;
use think\Model;
use Org\Nx\Page;
class FinanceModel extends Model{
	/**
	 * 添加财务日志
	 * @param unknown $uid
	 * @param unknown $type
	 * @param unknown $sz_type    1 收入 2 支出
	 * @param unknown $money
	 * @param unknown $content    内容
	 * @param number $money_type  币种类型 1人民币
	 * @return Ambigous <\Think\mixed, boolean, unknown, string>
	 */
    public function addFinance($uid,$type,$sz_type,$money,$content,$username,$money_type=1,$project_id=0){
    	$data['uid']=$uid;
    	$data['type']=$type;
    	$data['sz_type']=$sz_type;
    	$data['money']=$money;
    	$data['content']=$content;
    	$data['money_type']=$money_type;
    	$data['username']=$username;
    	$data['add_time']=time();
    	$data['project_id']=$project_id;
    	return M('Finance'.cuttable($uid))->add($data);
    }
    /**
     * 获取个人的财务日志  带分页
     * @param int $uid
     * @param int $type			  默认是查所有
     * @param tinyint $sz_type	  1 收入 2 支出
     * @param int $money_type    币种类型 1人民币
     * @return $list  财务日志数组
     */
    public function getFinanceByUid($uid,$type=9999,$sz_type=array(1,2),$money_type,$limit=10,$order='id desc',$start,$end,$time,$search,$type2){
    	$USER = new UserModel();
    	//格式化两个状态
    	$type_name    = C('FINANCE_TYPE');
    	$sz_type_name = C('FINANCE_SZ_TYPE');
    	$money_type_name = C('USER_MONEY_TYPE'); //Lb 修改     $money_type=array(1,2,3,4,5) 原为$money_type=1
    	if($type == 9999){
			$type = array_keys($type_name);
		}
		//不传type 查全部的情况
		$where['type']     		 = array('in',$type);
		$where['sz_type'] 		 = array('in',$sz_type);
		 if($time){
			$where['add_time'] = $time;
		} 
		if(is_array($money_type)){
			$where['money_type']  = array('in',$money_type);
		}else{
			$where['money_type']  = $money_type;
		}
    	if($uid){
    		$where['uid']     = $uid;
			//Lb修改
			$count      =  M('Finance'.cuttable($uid))->where($where)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
			//给分页传参数
			setPageParameter($Page, array('uid'=>$uid,'type'=>$type2,'sz_type'=>$sz_type,'money_type'=>$money_type,'order'=>$order,'start'=>$start,'end'=>$end,'search'=>$search));
			$show       = $Page->show();// 分页显示输出
			$list = M('Finance'.cuttable($uid))->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
	    	foreach ($list as $k => $v){
	    		$list[$k]['type_name']    = $type_name[$v['type']];
	    		$list[$k]['sz_type_name'] = $sz_type_name[$v['sz_type']]; //Lb修改
	    		$list[$k]['money_type_name'] = $money_type_name[$v['money_type']];// Lb修改
	    		$list[$k]['username'] = $USER->getUserByUid($uid)['username'];
	    	}
    	}else{
    		$count = M('Finance_view')->where($where)->count();
			$Page       = new Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
			//给分页传参数
			setPageParameter($Page, array('uid'=>$uid,'type'=>$type,'sz_type'=>$sz_type,'money_type'=>$money_type,'order'=>$order,'start'=>$start,'end'=>$end));
			$show       = $Page->show();// 分页显示输出
			$list = 
    			M('Finance_view')
				->where($where)
				->limit($Page->firstRow.','.$Page->listRows)
				->order($order)
				->select();
	    	foreach ($list as $k => $v){
	    		$list[$k]['username']     = $USER->getUserByUid($v['uid'])['username'];
	    		$list[$k]['type_name']    = $type_name[$v['type']];
	    		$list[$k]['sz_type_name'] = $sz_type_name[$v['sz_type']]; //Lb修改
	    		$list[$k]['money_type_name'] = $money_type_name[$v['money_type']];// Lb修改
	    	}
    	}
    	$list['list'] = $list;
    	$list['page'] = $show;//分页
    	return $list;
    }
    //WM//////////////////////////////////////////////以下方法暂未使用///////////////////////////////////////////////////////
    /**
     * 统计数据并存储数据库
     * @param unknown $type					1只处理本月 2本月+上月
     * @param unknown $last_count_time		上次统计时间
     * @param unknown $today_time			当前时间
     * @param number $month_one_time		本月第一天时间
     */
	public function runFinanceSysCount($type,$last_count_time,$today_time,$month_one_time=0){
		//判断只处理本月还是要处理本月+上月
		if($type == 1){
			//本月
			$this->addSysCount($last_count_time, $today_time);
		}else{
			//本月+上月
			//上月
			$this->addSysCount($last_count_time, ($month_one_time-1));
			//本月
			$this->addSysCount($month_one_time, $today_time);
		}
	}
	//统计数据 处理数据库部分
	private function addSysCount($last_count_time,$today_time){
		//收入统计
		$data['money_s']  = $this->getFinanceCount(2,$last_count_time,$today_time);
		//支出统计
		$data['money_z']  = $this->getFinanceCount(1,$last_count_time,$today_time);
		//月份
		$data['month']    = date('Y-m',$today_time);
		//加入时间
		$data['add_time'] = $today_time;
		//查询是不是本月第一次插入数据
		$r = M('Sys_count')->where("month='".$data['month']."'")->find();
		if($r){
			//不是第一次 修改
			M('Sys_count')->where('id='.$r['id'])->setInc('money_s',$data['money_s']);
			M('Sys_count')->where('id='.$r['id'])->setInc('money_z',$data['money_z']);
			M('Sys_count')->where('id='.$r['id'])->setField('add_time',$data['add_time']);
		}else{
			//是第一次 新增
			M('Sys_count')->add($data);
		}
	}
	//根据特定条件统计数据
	private function getFinanceCount($sz_type,$time_start,$time_end){
		//纵向连表语句
		for($i=0;$i<10;$i++){
			$sql_union_all .=
			"select money from ztp_finance".$i." 
			as f".$i."
			where sz_type = ".$sz_type."
			and add_time between ".$time_start." and ".$time_end." 
			and money_type = 1 
			 union all ";
		}
		$sql_union_all = substr($sql_union_all,0,-10);
		//统计
		$sql = "select sum(money) as count from ( ".$sql_union_all." ) as finance";
		$r = M()->query($sql);
		if($r[0]['count'] == null){
			$r[0]['count'] = 0;	
		}
		return $r[0]['count'];
	}
}