<?php
namespace Common\Model;
use Think\Model;
//实名认证model
class UserModel extends Model
{
	//实名认证
	public function getAuthList($uid = "",$limit=15){
		if($uid){
			$list = $this->where(array('uid'=>$uid))->find();
		}else{
			 $list = $this->limit($limit)->select();
			foreach($list as $k=>$v){
				$list[$k]['username'] = M('User')->where(array('id'=>$v['uid']))->find()['username'];
			}
		}
		return $list;
	}
	//实名认证通过
	public function setAuthStatus($uid,$status,$data =""){
		if($status==1){
			M('User')->where(array('id'=>$uid))->setField('level',1);
			M('User_note')->where(array('uid'=>$uid))->save($data);
		}
		$list = $this->where(array('uid'=>$uid))->setField('status',$status);
		return $list;
	}
}