<?php
namespace Home\Controller;
use Home\Controller\HomeController;
use Common\Model\UserModel;
use Common\Model\ConfigModel;
use Common\Api\UserApi;
use Common\Api\SendPhoneCodeApi;
use Think\Model;
class RegController extends HomeController {
	protected $uid;
	protected $user;
	public function _initialize(){
		parent::_initialize();
	}
	//展示注册页面
	public function reg(){
		$username = I('p_username');
		if($username){
			$this->assign('username',$username);
		}
    	$this->display();
    }
    //展示注册页面
    public function reg1(){
        $tuijian = I('p');
        $this->assign('tuijian',$tuijian);
        $this->display();
    }
    //注册方法
    public function runRegist(){
    	if(IS_POST){
	    	$username = I('name');
	    	$phone =I('phone');
	    	$pwd = I('pass');
	    	$repwd = I('pass_t');
	    	$two_pwd = I('two_pass');
	    	$two_repwd = I('two_pass_t');
	    	$code = I('code');
	    	$phone_code = I('phone_code');
	    	$tuijian = I('tuijian');
	    	/* 判断是否为空 */
	    	if(empty($code)){
	    		$this->ajaxReturnPushError('-2001');
	    	}
	    	if(empty($phone_code)){
	    		$this->ajaxReturnPushError('-2000');
	    	}
	    	if(empty($username)){
	    		$this->ajaxReturnPushError('-2003');
	    	}
	    	if(empty($phone)){
	    		$this->ajaxReturnPushError('-23001');
	    	}
	    	if(empty($pwd)){
	    		$this->ajaxReturnPushError('-2004');
	    	}
	    	if(empty($repwd)){
	    		$this->ajaxReturnPushError('-2016');
	    	}
	    	if(empty($two_pwd)){
	    		$this->ajaxReturnPushError('-2022');
	    	}
	    	if(empty($two_repwd)){
	    		$this->ajaxReturnPushError('-2017');
	    	}
	    	/* 判断正则 */
	    	if(!regex($username,'username')){
	    		$this->ajaxReturnPushError('-2015');
	    	}
	    	if(!regex($phone,'phone')){
	    		$this->ajaxReturnPushError('-2008');
	    	}
	    	if(!regex($pwd,'password')){
	    		$this->ajaxReturnPushError('-2010');
	    	}
	    	if(!regex($repwd,'password')){
	    		$this->ajaxReturnPushError('-2010');
	    	}
	    	if(!regex($two_pwd,'password')){
	    		$this->ajaxReturnPushError('-2010');
	    	}
	    	if(!regex($two_repwd,'password')){
	    		$this->ajaxReturnPushError('-2010');
	    	}
	    	/* 判断密码是否相等 */
	    	if($pwd!=$repwd){
	    		$this->ajaxReturnPushError('-2023');
	    	}
	    	if($two_pwd!=$two_repwd){
	    		$this->ajaxReturnPushError('-2024');
	    	}
	    	/*验证手机验证码*/
	    	if(!checkPhoneCode($phone_code,'zhuce',$phone)){
	    		$this->ajaxReturnPushError('-2018');
	    	}
	    	/*验证验证码*/
	    	if(!check_verify($code)){
	    		$this->ajaxReturnPushError('-2002');//验证码错误
	    	}
	    	/* 检验数据库是否存在 */
	    	//用户名是否存在
	    	$USERMODEL = new UserModel();
	    	$checkusername = $USERMODEL->getUserByUsername($username);
	    	if($checkusername){
	    		$this->ajaxReturnPushError('-2025');
	    	}
    	   //邀请人是否存在
	    	$pid = $USERMODEL->getUserByUsername($tuijian)['uid'];
			
	    	if(!$pid){
	    		$this->ajaxReturnPushError('-2020');
	    	}
	    	//新增会员
	    	$USERAPI = new UserApi();
	    	$addUser = $USERAPI->setUserLogic('',$username,'',$phone,'',$pid,'',$pwd,'');
	    	if($addUser){
	    		$this->ajaxReturnPushError('1');
	    	}else{
	    		$this->ajaxReturnPushError('-1');
	    	}
    	}else{
    		$this->ajaxReturnPushError('-1');
    	}
    }
    //发送验证码
    public function sendPhone(){
    	$phone = I('phone')?I('phone'):null;
    	$code_name = 'zhuce';
    	if(empty($phone)){
    		$this->ajaxReturnPushError('-23001');
    	}
    	/* //验证码发送次数
    	if(session('num')>3){
    		$this->ajaxReturnPushError('-5');
    	} */
    	$USER = new UserModel();
    	$checkphone = $USER->getUserByPhone($phone);
    	if($checkphone){
    		$this->ajaxReturnPushError('-2009');
    	}
    	$SENDPHONE = new SendPhoneCodeApi($phone);
    	$send = $SENDPHONE->send($code_name);
    	if($send==1){
    		$this->ajaxReturnPushError('1');
    	}else{
    		$this->ajaxReturn(array('info'=>$send));
    	}
    }
}