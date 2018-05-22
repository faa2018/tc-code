<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
/**
 * 后台首页控制器
 */
class AdminUserController extends AdminBaseController{

	/**
	 * 用户列表
	 */
	public function index(){
		$word=I('get.word','');
		if (empty($word)) {
			$map=array();
		}else{
			$map=array(
				'username'=>$word
				);
		}
		$assign=D('AdminUser')->getAdminPage($map,'register_time desc');
		$this->assign($assign);
		$this->display();
	}




}
