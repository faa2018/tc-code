<?php
namespace Common\Api;
use Common\Model\UserModel;
class FormatApi  {
	/**
	 * 格式化 筛选 
	 * 通过前台传的参数 查询出id并返回
	 */
	public function formatScreenUser(){
		$USER = new UserModel();
		$username = I('username');
		if($username){
			$id = $USER->getUserByUsername($username)['id'];
		}
		$phone    = I('phone');
		if($phone){
			$id = $USER->getUserByPhone($phone)['id'];
		}
		$email    = I('email');
		if($email){
			$id = $USER->getUserByEmail($email)['id'];
		}
// 		$username = I('username');
// 		$phone    = I('phone');
// 		$email    = I('email');
// 		$where['username'] = array('like','%'.$username.'%');
// 		$where['phone']    = array('like','%'.$phone.'%');
// 		$where['email']    = array('like','%'.$email.'%');
		return $id;
	}
	/**
	 * 格式化 筛选  
	 * 通过前台传的参数 查询出id并返回‘
	 * 返回where条件 和传来的参数
	 */
	public function formatScreenWhereUser(){
 		$data['username'] = I('username');
 		$data['phone']    = I('phone');
 		$where['username'] = array('like','%'.$data['username'].'%');
 		$where['phone']    = array('like','%'.$data['phone'].'%');
		$arr['data']  = $data;
		$arr['where'] = $where;
 		return $arr;
	}
	/**
	 * 格式化数据 获取的user数组
	 * @param unknown $user
	 */
	public function formatForeachUser($user){
		if(!$user)return;
		$user_level_name  = C('User_LEVEL');
		$user_status_name = C('User_STATUS');
		$USER = new UserModel();
		foreach($user as $k=>$v){
			$user[$k]['level_name']  = $user_level_name[$v['level']];
			$user[$k]['status_name'] = $user_status_name[$v['status']];
			//$user[$k]['center'] = $USER->getTuijianByUid($v['id'])['center'];
		}
		return $user;
	}
}

