<?php
namespace Common\Model;
use Think\Model;
//实名认证model
class UserCoinAddressModel extends Model
{
	//获取外网钱包地址，uid
	public function getAddressByUid($uid){
		$where['uid'] = $uid;
		$list = M('User_coin_address')->field('url,wai_id')->where($where)->find();
		return $list;
	}
	//添加外网钱包地址，uid
	public function setAddressByUid($uid,$data =""){
		$where['uid'] = $uid;
		$list = M('User_coin_address')->where($where)->save($data);
		return $list;
	}
}