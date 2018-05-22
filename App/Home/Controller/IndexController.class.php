<?php
namespace Home\Controller;
use Common\Model\UserModel;
use Common\Model\UserMoneyModel;
use Common\Model\ArtModel;
use Home\Controller\HomeController;
use Common\Model\BannerModel;
class IndexController extends HomeController {
	public function _initialize(){
		parent::_initialize();
	}
	public function index(){
		$this->display();
    }
	
}