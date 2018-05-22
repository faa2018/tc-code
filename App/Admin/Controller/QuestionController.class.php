<?php
namespace Admin\Controller;
use Common\Model\QuestionModel;
class QuestionController extends AdminBaseController {
	/**
	 * 公告列表页面
	 */
	public function showQuestionList(){
		$title = I('title');
		if(!empty($title)){
			$where['title'] = array('like','%'.$title.'%');
		}
		$QUESTION = new QuestionModel();
		$list = $QUESTION->selectQuestion($where);
		$show = $QUESTION->page($where,20);
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
    	$this->display();
	}
	//留言详情
	
	public function findQuestion(){
		$where['id']=I('id');
		$where_a['question_id'] = I('id');
		$QUESTION = new QuestionModel();
		$view = $QUESTION->findQuestion($where);
		$reView = $QUESTION->findReQuestion($where_a);
		//dump($reView);
		$this->assign('view',$view);
		$this->assign('reView',$reView);
    	$this->display();
	}
	/**
	 * 回复留言
	 */
	public function reQuestion(){
		if($_POST){
			$title=formatTextNoType(I('title'));
			$id=I('questionid',null);
			$content=formatTextNeedTypeK($_POST['content']);;
			if(empty($id)){
				$this->error('留言不存在');
			}
			if(empty($content)){
				$this->error('内容不能为空');
			}
			$data['question_id']=intval($id);
			$data['title']=$title;
			$data['content']=$content;
			$data['add_time']=time();
			$data['status']=1;
			$data['uid']=I('uid');
			$where['id']=$id;
			$QUESTION = new QuestionModel();
			$res = $QUESTION->reQuestion($data,$where);
			if($res){
				$this->success('回复成功',U('Question/showQuestionList'));exit();
			}else{
				$this->error('回复失败');exit();
			}
		}else{
			$this->error('请求错误');exit();
		}
	}
	
	/**
	 * 删除留言
	 */
	public function delQuestion(){
		$id=I('id');
		if(empty($id)){
			$this->error('请选择删除留言');
		}
		$where['id']=$id;
		$QUESTION = new QuestionModel();
		$res = $QUESTION->del($where);
		if($res){
			$this->success('删除成功');
		}else {
			$this->error('删除失败');
		}
	}
}