<?php
namespace Home\Controller;
use Common\Controller\CommonController;
use Common\Model\ConfigModel;
class HomeController extends CommonController {
	protected $config;
	public function _initialize(){
		$M_CONFIG = new ConfigModel();
		$this->config = $M_CONFIG->getConfig();
		$this->assign('CONFIG',$this->config);
		parent::_initialize();
	}
}