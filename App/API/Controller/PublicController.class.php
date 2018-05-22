<?php
namespace Home\Controller;
use Home\Controller\HomeController;
class PublicController extends HomeController {
	//用于非登录状态的总控制器
	public function _initialize(){
		
	}
	public function index(){
    	echo "共用部分的总控制器";
    }
    public function _404(){
    	echo "404页面";
    	$this->display();
    }
}