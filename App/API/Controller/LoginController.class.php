<?php
namespace API\Controller;
use Common\Model\UserModel;
use API\Controller\CommonController;
use Common\Api\SendPhoneCodeApi;
class LoginController extends HomeController{
	//登录控制器
	public function _initialize(){
	    parent::_initialize();
	}
    //登录
    public function login(){
		session(HOME_USER,'null');
	    //$this->ajaxReturn(array('info'=>'很抱歉，当前系统正在升级中，请稍后登录'));
	    $username = I('username')?I('username'):null;
	    $password = I('password')?I('password'):null;
	    if(empty($username)){
	       $data_return['status'] = -1;    
	       $data_return['info'] = '请输入手机号';    
           return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
	    if(empty($password)){
	       $data_return['status'] = -2;    
	       $data_return['info'] = '请输入密码';    
           return $this->ajaxReturn($data_return,'jsonp');//请输入密码
	    }
	    $M_USER = new UserModel();
	    //dump($M_USER);
	    $where_getuser['phone'] = $username;
	    $user = $M_USER->getUser($where_getuser);
	    if(!$user){
	       $data_return['status'] = -3;    
	       $data_return['info'] = '该用户不存在';    
           return $this->ajaxReturn($data_return,'jsonp');//用户名不存在
	    }
	    if(!passwordVerification($password,$user['pwd'])){
	       $data_return['status'] = -4;    
	       $data_return['info'] = '密码不正确';    
           return $this->ajaxReturn($data_return,'jsonp');//密码错误
	    }
	    //获取token
	    $where_token['uid'] = $user['uid'];
	    $token_log = M('Token')->where($where_token)->find();
	    $token = $token_log['token'];
		//dump($token);
	    if(!$token_log){
	        $data_token['uid']   = $user['uid'];
	        $data_token['token'] = $user['uid'].md5(time());
	        M('Token')->add($data_token);
	        $token = $data_token['token'];
	    }
		//dump($token);die;
	    $data_return['info']['token'] = $token;
	    $data_return['status'] = 1;
	    $this->ajaxReturn($data_return,'jsonp');
    }
	//注册
	public function reg(){
		$username = I('username')?I('username'):null;
		$phone = I('phone')?I('phone'):null;
		$phone_code = I('phone_code')?I('phone_code'):null;
		$pwd = I('password')?I('password'):null;
		$repwd = I('repwd')?I('repwd'):null;
		$tuijian_name = I('tuijian_name');
		$id_card = I('id_card');
		$areaid1 = I('areaid1');
		$areaid2 = I('areaid2');
		$areaid3 = I('areaid3');
		$USERMODEL = new UserModel();
	    // if(empty($tuijian_name)){
	    	// $data_return['status'] = -4;
	    	// $data_return['info'] = '请输入推荐人';
	    	// return $this->ajaxReturn($data_return,'jsonp');
	    // }
		if(empty($username)){
	       $data_return['status'] = -1;    
	       $data_return['info'] = '请输入用户名';    
           return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
		if(empty($phone)){
	       $data_return['status'] = -8;    
	       $data_return['info'] = '请输入手机号';    
           return $this->ajaxReturn($data_return,'jsonp');
	    }
		if(empty($phone_code)){
	       $data_return['status'] = -9;    
	       $data_return['info'] = '请输入手机验证码';    
           return $this->ajaxReturn($data_return,'jsonp');
	    }
		if(empty($pwd)){
	       $data_return['status'] = -2;    
	       $data_return['info'] = '请输入密码';    
           return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
		if(empty($repwd)){
	       $data_return['status'] = -3;    
	       $data_return['info'] = '请确认密码';    
           return $this->ajaxReturn($data_return,'jsonp');
	    }
	    if(!$id_card){
	    	$data_return['status'] = -6;
	    	$data_return['info']   = '请输入身份证号';
	    	return $this->ajaxReturn($data_return,'jsonp');
	    }
	    if(!$areaid1){
	    	$data_return['status'] = -6;
	    	$data_return['info']   = '请输入地址';
	    	return $this->ajaxReturn($data_return,'jsonp');
	    }
		if($pwd!=$repwd){
	       $data_return['status'] = -5;    
	       $data_return['info'] = '两次密码不一致';    
           return $this->ajaxReturn($data_return,'jsonp');
	    }
		if(!regex($phone,'phone')){
    			$data_return['status'] = -3;
				$data_return['info']   = '手机号格式错误';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
		if(!regex($pwd,'password')){
	    	$data_return['status'] = -6;    
	       $data_return['info'] = '请输入6~20位数字字母组成的密码';    
           return $this->ajaxReturn($data_return,'jsonp');
	    	}
    	$check_username = $USERMODEL->getUserByUsername($username);
    	if($check_username){
    		$data_return['status'] = -2;
    		$data_return['info'] = '用户名重复';
    		return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
    	}
		$check_phone = $USERMODEL->getUserByPhone($phone);
		if($check_phone){
	       $data_return['status'] = -10;    
	       $data_return['info'] = '手机号重复';    
           return $this->ajaxReturn($data_return,'jsonp');
	    }
		if($tuijian_name){
	    	$tuijian_user = M('User')->where(array('phone'=>$tuijian_name,'status'=>array('neq',-404)))->find();
			if(!$tuijian_user){
				$data_return['status'] = -3;
				$data_return['info']   = '推荐人不存在';
				return $this->ajaxReturn($data_return,'jsonp');
			}
	    }
	    
		if($phone_code != session('codezhuce'.$phone)){
			$data_return['status'] = -5;
			$data_return['info']   = '手机验证码有误';
	       return $this->ajaxReturn($data_return,'jsonp');
		}
		//注册
		$data_user['username'] = $phone;
		$data_user['pic'] = '';
		$data_user['phone'] = $phone;
		$data_user['idcard'] = $id_card;
		$data_user['add_time'] = time();
		$data_user['id_card'] = $id_card;
		$data_user['real_name'] = $username;
		$data_user['areaid1'] = $areaid1;
		$data_user['areaid2'] = $areaid2;
		$data_user['areaid3'] = $areaid3;
		$data_user['pwd'] = passwordEncryption($pwd);
		$data_user['twopwd'] = passwordEncryption($pwd);
		$uid = M('User')->add($data_user);
		$data_user_relation['uid'] = $uid;
		$data_user_relation['pid'] = $tuijian_user['uid']?$tuijian_user['uid']:'';
		M('User_relation')->add($data_user_relation);
		//用户资金表
		$money_type = C('USER_MONEY_TYPE');
		foreach ($money_type as $k=>$v){
			$data_money['uid']          = $uid;
			$data_money['num']          = 0;
			$data_money['type']         = $k;
			$r[] = M('User_money'.cuttable($uid))->add($data_money);
		}
		$data_token['uid'] = $uid;
		$data_token['token'] = $uid.md5(time());
		M('Token')->add($data_token);
	    $data_return['info'] = '注册成功';
	    $data_return['status'] = 1;
	    $this->ajaxReturn($data_return,'jsonp');
	}
    //忘记密码
    public function forgetPassword(){
    		$phone 		= I('phone');
    		$pwd   		= I('pwd');
    		$repwd      = I('repwd');
    		$phone_code = I('phone_code');
			if(!$phone_code){
				$data_return['status'] = -4;
				$data_return['info']   = '请输入手机验证码';
				return $this->ajaxReturn($data_return,'jsonp');
			}
    		//判断是否为空
    		if(empty($phone)){
    			$data_return['status'] = -1;
				$data_return['info']   = '请输入手机号';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		if(empty($phone_code)){
    			$data_return['status'] = -2;
				$data_return['info']   = '请输入验证码';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		if(empty($pwd)){
    			$data_return['status'] = -3;
				$data_return['info']   = '请输入新密码';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		if(empty($repwd)){
    			$data_return['status'] = -4;
				$data_return['info']   = '请确认新密码';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		//判断正则
    		if(!regex($phone,'phone')){
    			$data_return['status'] = -3;
				$data_return['info']   = '手机号格式错误';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		if(!regex($pwd,'password')){
    			$data_return['status'] = -3;
				$data_return['info']   = '密码格式错误';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		//密码确认密码是否相等
    		if($pwd!=$repwd){
    			$data_return['status'] = -3;
				$data_return['info']   = '两次密码不一致';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
    		
    		//查询数据库
    		$USER = new UserModel();
    		$uid = $USER->getUserByPhone($phone)['uid'];
    		if(!$uid){
    			$data_return['status'] = -3;
				$data_return['info']   = '该用户不存在';
				return $this->ajaxReturn($data_return,'jsonp');
    		}
			//手机验证码
	    	if($phone_code != session('codezhaohuimima'.$phone)){
			$data_return['status'] = -5;
			$data_return['info']   = '手机验证码有误';
	        return $this->ajaxReturn($data_return,'jsonp');
			}
    		//修改密码
    		$data['pwd'] = passwordEncryption($pwd);
    		$editpwd = $USER->saveUser($data,$uid);
			//echo M('User')->_sql();

    			$data_return['status'] = 1;
				$data_return['info']   = '密码找回成功';
				return $this->ajaxReturn($data_return,'jsonp');
    		
    }
	public function checkUsername(){
  		$username = I('username');
		$USERMODEL = new UserModel();
	    $check_username = $USERMODEL->getUserByUsername($username);
		if($check_username){
	       $data_return['status'] = -2;    
	       $data_return['info'] = '用户名重复';    
           return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }else{
		$data_return['status'] =1;    
	    $data_return['info'] = '用户名可以注册';    
        return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
		}
	}
	//发送验证码
    public function sendPhone(){
    	$phone = I('phone')?I('phone'):null;
		
		$type = I('type');
    	if(empty($phone)){
    		$data_return['status'] = -41;
	        $data_return['info']   = '请输入手机号';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	
    	$USER = new UserModel();
    	$checkphone = $USER->getUserByPhone($phone);
    	if($type==1){
    		$code_name = 'zhuce';
	    	if($checkphone){
	    		$data_return['status'] = -42;
		        $data_return['info']   = '手机号已存在';
		        return $this->ajaxReturn($data_return,'jsonp');
	    	}
    	}else{
    		$code_name = 'zhaohuimima';
    		if(!$checkphone){
    			$data_return['status'] = -42;
    			$data_return['info']   = '手机号不存在';
    			return $this->ajaxReturn($data_return,'jsonp');
    		}
    	}
    	$SENDPHONE = new SendPhoneCodeApi($phone);
    	$send = $SENDPHONE->send($code_name);
    	if($send == 1){
    		$data_return['status'] = 1;
	        $data_return['info']   = '操作成功';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}else{
    		$data_return['status'] = -1;
	        $data_return['info']   = $send;
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    }
public function loginOut(){
    	 $token = I('token');
		if(!$token){
			$data_return['status'] ='-1';
	       $data_return['info'] = '参数错误';
    			return $this->ajaxReturn($data_return,'jsonp');
    		}
			$r = M('Token')->where(['token'=>$token])->delete();
	    if($r){
	    $data_return['status'] =1;
	    		$data_return['info'] = '退出成功';
	    		return $this->ajaxReturn($data_return,'jsonp');
	    }
    } 
}