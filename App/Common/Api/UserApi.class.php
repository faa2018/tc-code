<?php
namespace Common\Api;
use Common\Model\UserModel;
use Common\Model\CoinModel;
use Common\Model\UserInvitationCodeModel;
class UserApi  {
	/**
	 * 添加基础表 分别处理添加还是修改
	 * @param unknown $uid
	 * @param unknown $username
	 * @param unknown $email
	 * @param unknown $phone
	 * @param number $pwd
	 * @param number $twopwd
	 * @return Ambigous <\Think\mixed, boolean, unknown, string>
	 */
	public function setUserLogic($uid,$username,$email,$phone,$level,$pid=1,$status=0,$pwd=123456,$twopwd=123456){
		$data['username'] 	= $username;  		
		$data['email']		= $email;  		
		$data['phone']		= $phone;  		
		$data['level']		= $level;  		
		$data['status']		= $status;  		
		$data['pwd'] 		= $pwd;  	
		$data['pid']		= $pid;	
		$data['twopwd'] 	= $twopwd;
		$data['add_time'] 	= time();
		$USER = new UserModel();
		//开启事务
    	M()->startTrans();
    	global $r;
		if(!$uid){
			$data['pwd']    = passwordEncryption($data['pwd']);
			$data['twopwd'] = passwordEncryption($data['twopwd']);
			$uid = M('User')->add($data);
			//添加副表
			$addfu = $this->addUserFu($uid,$pid);
		}else{
			$data['uid']       	= $uid;
			$uid = M('User')->where('uid='.$uid)->save($data);
		} 
		if (!$uid){
			M()->rollback();
			return false;
		}else {
			M()->commit();
			return $uid;
		}
		return $uid;	
	} 
	//添加user表之后生成的数据
	private function addUserFu($uid,$pid){ 
		//用户资金表
		$money_type = C('USER_MONEY_TYPE'); 
		foreach ($money_type as $k=>$v){
			$data_money['uid']          = $uid;
			$data_money['num']        = 0;
			$data_money['type']         = $k;
			$r[] = M('User_money'.cuttable($uid))->add($data_money);
		}
		//用户推荐码
		$USER = new UserInvitationCodeModel();
		$USER->getInvitationCode($uid);
		//邀请人
		$data_relation['uid']       	= $uid;
		$data_relation['pid']       	= $pid;
		$r[] = M('User_relation')->add($data_relation);
	}
}

