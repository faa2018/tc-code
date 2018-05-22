<?php
namespace Admin\Controller;
use Think\Page;
class ProjectController extends AdminBaseController {
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 设定project
     */
    public function index(){
		$list = M('Project')->select();
    	$this->assign('list',$list);
		$this->display();
    }
	public function save(){
		$id = I('id');
		$data['name'] = I('name');
		$data['money'] = I('money');
		$data['tuijian_money'] = I('tuijian_money');
		$r = M('Project')->where('id = '.$id)->save($data);
		if($r){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
}