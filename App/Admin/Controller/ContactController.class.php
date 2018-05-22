<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
use Common\Model\ContactModel;

/**  
* 联系我们管理
*/
class ContactController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * 联系我们
	 */
	public function index(){
		$CONTACT = new ContactModel();
		$list = $CONTACT->getContactList();
		if(IS_POST){
			//接收数据
			$data['phone']      =  I('phone');
			$data['qq'] 		=  I('qq');
			//图片处理
			if($_FILES["pic"]["name"]){
				$data['pic']=$this->upload($_FILES["pic"]);
			}
			//验证数据
			if(empty($data['phone'])){
				$this->error('请填写客服电话');
			}
			if(empty($data['qq'])){
				$this->error('请填写客服qq');
			}
			$where['id'] = $list['id'];
			$save = $CONTACT->saveContact($where, $data);
			if($list){
				$this->success('操作成功',U('Contact/index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->assign('list',$list);
			$this->display();
		}
		
	}
	
	
}
