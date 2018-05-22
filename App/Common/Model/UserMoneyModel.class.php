<?php
namespace Common\Model;
use Think\Model;
use Think\Page;
class UserMoneyModel extends Model
{	
	protected  $uid;
	protected  $table;
	public function __construct($uid){
		parent::__construct();
		$this->uid = $uid;
		$this->tableName = M('User_money'.cuttable($uid));
	}
	/**
	 * 获取用户资金列表
	 * @param int $uid
	 * @param array $type  对应资金类型 1人民币 传入时需要数组
	 * @param int $limit   分页 数量
	 * @param str $order   排序
	 */
	public function getUserMoneyByUid($type=-1,$order='id'){
		$where['uid']  = $this->uid;
		//获取全部种类
		$money_type_name = C('USER_MONEY_TYPE');
		if($type == -1){
			$type = array_keys($money_type_name);
		}
		if(is_array($type)){
			$where['type'] = array('in',$type);
			$list = $this->tableName->where($where)->order($order)->select();
			//格式化钱的种类名称
			foreach($list as $k=>$v){
				$list[$k]['money_type_name'] = $money_type_name[$v['type']];
			}
		}else{
			$where['type'] = $type;
			$list = $this->tableName->where($where)->find();
			$list['money_type_name'] = $money_type_name[$list['type']];
		}
		return $list;
	}
	/**
	 * 修改用户金额
	 * @param unknown $uid
	 * @param unknown $money
	 * @param unknown $type  1增加 2减少 3 修改
	 * @param unknown $money_type  修改资金种类 1人民币
	 * @return Ambigous <boolean, unknown>
	 */
	public function updateUserMoney($money,$type,$money_type = 1){
		$uid = $this->uid;
		$where['uid']  =  $uid;
		$where['type'] = $money_type;
		//判断对应该的记录是否存在，不存在要新增一条
		$log = $this->tableName->where($where)->find();
		if(!$log){
			$add_data['uid']  = $uid;
			$add_data['type'] = $money_type;
			$add_data['num']  = 0;
			$this->tableName->add($add_data);
		}
		if($type == 1){
			$r = $this->tableName->where($where)->setInc('num',$money);
		}
		if($type == 2){
			$r = $this->tableName->where($where)->setDec('num',$money);
		}
		if($type == 3){
			$r = $this->tableName->where($where)->setField('num',$money);
		}
		return $r;
	}
}