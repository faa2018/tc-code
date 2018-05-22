<?php
namespace Common\Model;
use Think\Model;
class UserModel extends Model
{
	public function __construct(){
	}
    //根据uid获取个人信息
	public function getUserByUid($uid){
		$where['uid'] = $uid;
		return $this->getUser($where);
	}
	//根据uid获取个人详细信息
	public function getUserNoteByUid($uid){
		$where['uid'] = $uid;
		return $this->getUser($where);
	}
	//根据$username获取个人信息
	public function getUserByUsername($username){
		$where['username'] = $username; 
		return $this->getUser($where);
	}
	//根据$email获取个人信息
	public function getUserByEmail($email){
		$where['email'] = $email;
		return $this->getUser($where);
	}
	//根据phone获取个人信息
	public function getUserByPhone($phone){
		$where['phone'] = $phone;
		return $this->getUser($where);
	}
	// like $username 获取uid 用于筛选
	public function getUserLikeUsername($username){
		$where_username['phone'] = $username;
		return  M('User')->where($where_username)->find()['uid'];
	}
	//修改用户状态
	public function setUserStatus($uid,$status){
		$where = $this->where;
		$where['uid'] = $uid;
		return M('User')->where($where)->setField('status',$status);
	}
	//修改用户状态
	public function setUserLevel($uid,$level){
		$where = $this->where;
		$where['uid'] = $uid;
		return M('User')->where($where)->setField('level',$level);
	}
	//新增用户
	public function addUser($data){
		return M('User')->add($data);
	}
	//修改用户
	public function saveUser($data,$uid){
		return M('User')->where(array('uid'=>$uid))->save($data);
	}
	//根据username email phone 获取个人信息
	public function getUserByname($username){
		$where['username'] = $username;
		$where['status'] = array('neq',-2);
		return M('User')->where($where)->find();
	}
	//根据条件获取用户
	public function getUser($where){
		$where['status'] = array('neq',-404);
		return M('User')->where($where)->find();
	}
}