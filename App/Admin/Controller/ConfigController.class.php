<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
/**  
 * 系统配置 
*/
class ConfigController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * 配置项主页
	 */
	public function index(){
		$where['id'] = array('NEQ',51);
		$where['key'] = array('NEQ','zhifu_code');
		$where['status'] =0;
		$weixin = M('Config')->where(array('key'=>'weixin'))->find();
		$zhifubao = M('Config')->where(array('key'=>'zhifu_code'))->find();
		$config=M('Config')->where($where)->order('sort')->select();
		$this->assign('config',$config);
		$this->assign('weixin',$weixin);
		$this->assign('zhifu_code',$zhifubao);
		$this -> display();
	}
	/**
	 * 修改配置项
	 */
	public function editConfig(){
         if($_FILES["logo"]["tmp_name"]){
                $_POST['logo']=$this->upload($_FILES["logo"]);
                if (!$_POST['logo']){
                    $this->error('非法上传');
                }
         }
         if($_FILES["trademark"]["tmp_name"]){
         	$_POST['trademark']=$this->upload($_FILES["trademark"]);
         	if (!$_POST['trademark']){
         		$this->error('非法上传');
         	}
         }
		 if($_FILES["weixin"]["name"]){
 					$weixin=$this->upload($_FILES["weixin"]);
 					$list = M('Config')->where(array('key'=>'weixin'))->setField('value',$weixin);
				}
				if($_FILES["zhifu_code"]["name"]){
					$zhifubao=$this->upload($_FILES["zhifu_code"]);
					$list = M('Config')->where(array('key'=>'zhifu_code'))->setField('value',$zhifubao);
				}
     	foreach ($_POST as $k=>$v){
     		$rs[]=M('Config')->where(C("DB_PREFIX")."config.key='{$k}'")->setField('value',$v);
     	}
     	if($rs){
     		$this->success('配置修改成功');
     	}else{
     		$this->error('配置修改失败');
     	}
     }
	 
}
