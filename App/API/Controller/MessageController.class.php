<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\QuestionModel;
use Common\Model\ArtModel;
class MessageController extends UserController {
	public function _initialize(){
		parent::_initialize();
	}
	//发件箱
	public function questionList(){
		$where[userId] = $this->uid;
		$QUESTION = new QuestionModel();
		$re = $QUESTION ->selectQuestion();
		$list['list'] = $re['list'];
		$list['page'] = $re['page'];
	 	$this->ajaxReturn($list,'jsonp');
	 }
	//提交问题留言
	public function addQuestion(){
		$data['userId'] = $this->uid;
		$data['title']  = I('title');
		$data['type']  = I('type');
		$data['content']  = I('content');
		$data['add_time'] =time();
		if($data['title']=="" || $data['content']==""){
			$back['status']=-2;
			$back['info']='填写信息不全';
			$this->ajaxReturn($back,'jsonp');
		}
		$where['userid']=$this->uid;
		$where['status']=0;
		$QUESTION = new QuestionModel();
		$re = $QUESTION ->add_question($data);
		if($re){
			$back['status']=1;
			$back['info']='提交成功';
			$this->ajaxReturn($back,'jsonp');
		}else{
			$back['status']=-1;
			$back['info']='提交失败';
			$this->ajaxReturn($back,'jsonp');
		}
	}
	//详情
	public function questionDetails(){
		$id = I('id');
		$where['id'] = $id;
		$QUESTION = new QuestionModel();
		$list = $QUESTION ->findQuestion($where);
		//$wher['questionId'] = $id;
		//$relist = $QUESTION ->findReQuestion($wher);
		$this->ajaxReturn($list,'jsonp');
		//dump($list);
		//$this->assign('relist',$relist);
	}
	//收件箱
	public function inbox(){
		$where['uid'] = $this->uid;
		$QUESTION = new QuestionModel();
		$re = $QUESTION ->selectAnswer($where);
		$list['list'] = $re['list'];
		$list['page'] = $re['page'];
	 	$this->ajaxReturn($list,'jsonp');
	}
	//详情
	public function answerDetails(){
		$id = I('id');
		$where['id'] = $id;
		$QUESTION = new QuestionModel();
		$list = $QUESTION ->findAnswer($where);
		//$wher['questionId'] = $id;
		//$relist = $QUESTION ->findReQuestion($wher);
		$this->ajaxReturn($list,'jsonp');
		//$this->assign('relist',$relist);
	}
}