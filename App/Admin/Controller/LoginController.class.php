<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
/**
 * admin 基类控制器
 */
class LoginController extends CommonController{
	/**
	 * 初始化方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	public function showLogin(){
		if($_POST){
			$username=I('username',null);
			$password=I('password',null);
			$verify=I('verify',null);
			if(empty($verify)){
				$this->error('请输入验证码');
			}
			if(!$this->checkVerify($verify)){
				$this->error('验证码不正确');
			}
			if(empty($username)||empty($password)){
				$this->error('管理员账号或密码不能为空');
			}
			$where['username']=$username;
			$admin=M('admin_user')->where($where)->find();
			if (!$admin ) {
				$this->error('账号不存在');
			}
			if ($admin['status']==2) {
				$this->error('您的账号已被禁用');
			}
			if(!passwordVerification($password,$admin['password'])){
				$this->error('管理员密码错误');
			}
			session('user',$admin);
			session('state',$admin['status']);
			$this->redirect('Index/index');
		}
		$this->display();
	}
	public function logout(){
		session('user',null);
		$this->success('退出成功','showLogin');
	}
	/**
	 * 检测输入的验证码是否正确，
	 * @param string $code 用户输入的验证码字符串
	 * @param string $id
	 * @return boolean
	 */
	public function checkVerify($code, $id = ''){
		$verify = new \Think\Verify();
		return $verify->check($code,$id);
	}
	//一键登录
	public function keyLogin(){
		$id = I('id');
		session('HOME_USER_ID',$id);
		$this->redirect('/Home/User/index');
	}
	
}
