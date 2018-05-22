<?php
namespace Home\Controller;
use Home\Controller\HomeController;
use Common\Model\ContactModel;

/**  
* 联系我们
*/
class ContactController extends HomeController{
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * 联系我们
	 */
	public function index(){
		$CONTACT = new ContactModel();
		$list = $CONTACT->getContactList();
		$this->assign('list',$list);
		$this->display();
		
	}
	
	
}
