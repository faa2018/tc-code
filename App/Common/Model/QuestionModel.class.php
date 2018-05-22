<?php
namespace Common\Model;
use Think\Model;
/**
 * 留言回复model
 */
class QuestionModel extends Model{

	/**
	 * 查询数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function selectQuestion($where,$limit=10){
		$count      = M('Question')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$show       = $Page->show();
		$list = M('Question')->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$QUESTION_STATUS = C('QUESTION_STATUS');
		$USER = new UserModel();
		foreach ($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['userid'])['username'];
			$list[$k]['content']=html_entity_decode($v['content']);
			$list[$k]['status'] = $QUESTION_STATUS[$v['status']];
		}
		$list_re['page'] = $Page->show();
		$list_re['list'] = $list;
		return $list_re;
	}
	/**
	 * 查询数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function selectAnswer($where,$limit=10){
		$count      = M('Answer')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$show       = $Page->show();
		$list = M('Answer')->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$QUESTION_STATUS = C('QUESTION_STATUS');
		$USER = new UserModel();
		foreach ($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['userid'])['username'];
			$list[$k]['content']=html_entity_decode($v['content']);
			$list[$k]['status'] = $QUESTION_STATUS[$v['status']];
		}
		$list_re['page'] = $Page->show();
		$list_re['list'] = $list;
		return $list_re;
	}
	//分页
	public function Page($where = array(),$limit = 1){
		$count      = M('Question')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$show       = $Page->show();
		return $show;
	}
	//添加回复
	public function reQuestion($data,$where){
		$re = M('Answer')->add($data);
		if($re){
			$rs = M('Question')->where($where)->save(array('status'=>1));
			return $re;
		}
		
	}
	
	//查询单个留言
	public function findQuestion($where){
		$Question = M('Question')->where($where)->find();
		$Question['username'] = M('User')->where(['uid'=>$Question['userid']])->find()['username'];
		return $Question;
	}
	//查询单个回复
	public function findReQuestion($where){
		$Answer = M('Answer')->where($where)->find();
		if($Answer){
		$Answer['username'] = M('User')->where(['uid'=>$Answer['userid']])->find()['username'];
		}
		return $Answer;
	}
	//查询留言的回复
	public function findAnswer($where){
		$list = M('Answer')->where($where)->find();
		return $list;
	}
	//删除留言
	public function delQuestion($where){
		$res=M('Question')->where($where)->delete();
		return $res;
	}
	//新增留言
	public function add_question($data){
		$re = M('Question')->add($data);
		return $re;
	}
	//查询本人留言数量
	public function getCount($where){
		$count     = M('Question')->where($where)->count();
		return $count;
	}


}
