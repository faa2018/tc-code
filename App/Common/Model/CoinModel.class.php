<?php
namespace Common\Model;
use Think\Model;
class CoinModel extends Model
{
	/**
	 * 获取用户币种列表
	 * @param int $uid
	 * @param  $type  对应币种类型 
	 */
	public function getUserCoinByUid($uid,$type){
		$where['uid']  = $uid;
		if($type){
			$where['uc.type'] = $type;
		}
		$where['switch'] = 1;
		$list = M('User_coin'.cuttable($uid))
		->alias('uc')
		->join("__COIN__ AS ci ON ci.id = uc.type")
		->where($where)
		->select();
		//echo M('User_coin'.cuttable($uid))->_sql();
		foreach($list as $k=>$v){
			$where['id'] = $v['type'];
			$list[$k]['name'] = M('Coin')->where($where)->find()['name'];
		}
		return $list;
	}
	//根据uid,type获取币种详情
	public function getUserCoinByType($uid,$type){
		$where['uid']  = $uid;
		$list = $this->getNameByCointype($type);
		$where['type'] = $type;
		$list['num'] = M('User_coin'.cuttable($uid))->where($where)->find()['num'];
		$list['money_num'] = $list['num']*$list['bili'];
		//获取总钱包能换多少虚拟币 Lb
		$USER_MONEY = new UserMoneyModel();
		$money = $USER_MONEY->getUserMoneyByUid($uid,6);
		$list['coin_num_change'] = $money['num']/$list['bili'];
		//echo M('User_coin'.cuttable($uid))->_sql();die;
		return $list;
	}
	/**
	 * 修改用户虚拟币
	 * @param unknown $uid
	 * @param unknown $money
	 * @param unknown $type  1增加 2减少 3 修改
	 * @param unknown $money_type  修改资金种类 1人民币
	 * @return Ambigous <boolean, unknown>
	 */
	public function updateUserCoin($uid,$num,$type,$coin_type = 1){
		$where['uid']        = $uid;
		$where['type'] = $coin_type;
		//根据分表原则确定修改哪一张表
		$type_table = cuttable($uid);
		//判断对应该币种的记录是否存在，不存在要新增一条
		$log = M('User_coin'.$type_table)->where($where)->find();
		if(!$log){
			$add_data['uid']  = $uid;
			$add_data['type'] = $coin_type;
			$add_data['num']  = 0;
			M('User_coin'.$type_table)->add($add_data);
		}
		//dump($type);dump($num);die;
		if($type == 1){
			$r = M('User_coin'.$type_table)->where($where)->setInc('num',$num);
		}
		if($type == 2){
			$r = M('User_coin'.$type_table)->where($where)->setDec('num',$num);
		}
		if($type == 3){
			$r = M('User_coin'.$type_table)->where($where)->setField('num',$num);
		}
		return $r;
	}
	//获取提币记录
	public function getTibiLog($where,$limit){
		$count      = M('Coin_withdraw')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$list = M("Coin_withdraw")->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$tibi_status = C('TIBI_STATUS');
		$money_type = C('USER_MONEY_TYPE');
		foreach($list as $k=>$v){
			$list[$k]['feemoney'] = $v['money']*$v['fee'];
			$list[$k]['factmoney'] = $v['money']-$list[$k]['feemoney'];
			$list[$k]['tibi_status'] = $tibi_status[$v['status']];
			$list[$k]['money_type'] = $money_type[$v['type']];
			$list[$k]['coin_type'] = $this->getNameByCointype($v['coin_type'])['name'];
		}
		$list_re['page'] = $Page->show();
		$list_re['list'] = $list;
		return $list_re;
	}
	//获取充币记录
	public function getChongbiLog($where,$limit){
		$count      = M('Coin_pay')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$list = M("Coin_pay")->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$chongbi_status = C('CHONGBI_STATUS');
		$money_type = C('USER_MONEY_TYPE');
		foreach($list as $k=>$v){
			$list[$k]['chongbi_status'] = $chongbi_status[$v['status']];
			$list[$k]['money_type'] = $money_type[$v['type']];
			$list[$k]['coin_type'] = $this->getNameByCointype($v['coin_type'])['name'];
		}
		$list_re['page'] = $Page->show();
		$list_re['list'] = $list;
		return $list_re;
	}
	//根据币种id获取币种名称
	public function getNameByCointype($coin_type){
		$where_type['id'] = $coin_type;
		return  M('Coin')->where($where_type)->find();
	}
	//获取全部币种信息
	public function getCoinList(){
		return  M('Coin')->where($where)->select();
	}
	//获取全部币种信息
	public function getCoinById($id){
		//$where_type['switch'] = 1;
		$where['id'] = $id;
		return  M('Coin')->where($where)->find();
	}
	//获取用户充币地址
	public function getUserCoinUrl($uid,$coin){//调用钱包生成
		$where['uid'] = $uid;
		$where['type'] = $coin['id'];
		$url = M('User_coin_pay_address')->where($where)->find();
		if($url['url']){
			return $url['url'];
		}else{
			//调用钱包生成
			include_once("./Easycoin.class.php") ;
 			$QIANBAO = new \Easycoin($coin['rpc_username'],$coin['rpc_pwd'],$coin['rpc_host'],$coin['rpc_port']);
 			dump($QIANBAO);
 			dump($QIANBAO->getInfo());
 			die;
 			$address = $QIANBAO->getnewaddress($uid);
			if($address){
				$data['uid'] 	 = $uid;
				$data['type']    = $coin['id'];
				$data['url']     = $address;
				M('User_coin_pay_address')->add($data);
			}
			return $address;
		}
	}
	//运行提币 
	public function runCoinWithfraw($num,$coin,$url){
		//调用钱包生成
		include_once("AUTO_Z/Easycoin.class.php") ;
		$QIANBAO = new \Easycoin($coin['rpc_username'],$coin['rpc_pwd'],$coin['rpc_host'],$coin['rpc_port']);
		//$QIANBAO->walletlock();//强制上锁
		//$QIANBAO->walletpassphrase($coin['rpc_key'],600);
		$id = $QIANBAO->sendtoaddress($url,(int)$num);
		//强制上锁;
		return $id;
	}
	//检测钱包地址真实性
	public function checkCoinAddress($url,$coin){
		//调用钱包生成
		include_once("./Easycoin.class.php") ;
		$QIANBAO = new \Easycoin($coin['rpc_username'],$coin['rpc_pwd'],$coin['rpc_host'],$coin['rpc_port']);
		//$QIANBAO->walletpassphrase('jufukeji',100);
		$address = $QIANBAO->validateaddress($url);
		if($address['isvalid']){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 添加用户的外网地址及ID
	 */
	public function addUserUrlAndID($data){
		return M('User_coin_address')->add($data);
	}
	/**
	 * 修改用户的外网地址及ID
	 */
	public function editUserUrlAndID($uid,$data){
		return M('User_coin_address')->where(array('uid'=>$uid))->save($data);
	}
	
	/**
	 * 查询用户的外网地址及ID
	 */
	public function getUserUrlAndID($uid){
		return M('User_coin_address')->where(array('uid'=>$uid))->find();
	}
	/**
	 * 添加提币记录
	 */
	public function addCoinWithdrawLog($data){
		return M('Coin_withdraw')->add($data);
	}
	
}