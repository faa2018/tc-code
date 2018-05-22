<?php
namespace Common\Model;
use Think\Model;
//邀请推荐码model
class UserInvitationCodeModel extends Model
{
	//生成唯一编码 并添加数据库
	public function getInvitationCode($uid){
		$unique_code = getRandom(8);//获取随机字符串
		$code = $this->getUserByInvitationCode($unique_code);//根据生成的唯一编码  查询表中是否存在
		//避免重复
		if($code){
			$this->getInvitationCode();
		}
		$data['uid'] 			= $uid;
		$data['unique_code'] 	= $unique_code;
		//新增唯一编码
		return $this->add($data);
	}
	//根据邀请码查询用户是否存在
	public function getUserByInvitationCode($unique_code){
		$where['unique_code'] = $unique_code;
		return $this->where($where)->find();
	}
	//根据uid查询邀请码
	public function getUniqueCodeByUid($uid){
		$where['uid'] = $uid;
		return $this->where($where)->find()['unique_code'];
	}
}