<?php
namespace Admin\Controller;
use Common\Model\NoticeModel;
class NoticeController extends AdminBaseController {
	/**
	 * 公告列表页面
	 */
	public function showNoticeList(){
		$title = I('title');
		if(!empty($title)){
			$where['title'] = array('like','%'.$title.'%');
		}
		$NOTICE = new NoticeModel();
		$list = $NOTICE->selectNotice($where);
		$show = $NOTICE->page($where,10);

		$this->assign('list',$list);
		$this->assign('page',$show);
    	$this->display();
	}
	/**
	 * 添加公告
	 */
	public function addNotice(){
		if($_POST){
			$title		=	formatTextNoType(I('title'));
			$content	=	formatTextNeedTypeK($_POST['content']);
			if(empty($title)||empty($content)){
				$this->error('标题或内容不能为空');
			}
			$data['title']=$title;
			$data['content']=$content;
			$data['add_time']=time();
			$NOTICE = new NoticeModel();
			$res = $NOTICE->addNotice($data);
			if($res){
				$this->success('添加成功');exit();
			}else{
				$this->error('添加失败');exit();
			}
		}
		$this->display();
	}
	/**
	 * 修改公告
	 */
	public function changeNotice(){
		if($_POST){
			$where['id']=I('id',null);
			$data['content']=formatTextNeedTypeK($_POST['content']);
			$data['title']=formatTextNoType(I('title'));
			$data['add_time']=time();
			if(empty($data['title'])||empty($data['content'])){
				$this->error('标题或内容不能为空');
			}
			$NOTICE = new NoticeModel();
			$res = $NOTICE->saveNotice($where,$data);
			if($res){
				$this->success('修改成功');exit();
			}else{
				$this->error('修改失败');exit();
			}
		}
		$where['id']=I('id');
		$NOTICE = new NoticeModel();
		$notice = $NOTICE->findNotice($where);
		$this->assign('notice',$notice);
		$this->display();
	}
	/**
	 * 删除公告
	 */
	public function delNotice(){
		$id=I('id');
		if(empty($id)){
			$this->error('请选择删除公告');
		}
		$where['id']=$id;
		$Notice = new NoticeModel();
		$res=M('Notice')->where($where)->delete();
		if($res){
			$this->success('删除成功');
		}else {
			$this->error('删除失败');
		}
	}
}