<?php
namespace Common\Model;
use Think\Model;
/**
 * 公告model
 */
class NoticeModel extends Model{

	/**
	 * 查询数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function selectNotice($where=array()){
		$list = M('Notice')->where($where)->order('add_time desc')->select();
		foreach ($list as $k=>$v){
			$list[$k]['content']=html_entity_decode($v['content']);
		}
		return $list;
	}
	//分页
	public function Page($where = array(),$limit = 10){
		$count      = M('Notice')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$show       = $Page->show();
		return $show;
	}
	//添加公告
	public function addNotice($data){
		$re = M('Notice')->add($data);
		return $re;
	}
	//修改公告
	public function saveNotice($where,$data){
		$res = M('Notice')->where($where)->save($data);
		return $res;
	}
	//查询单个公告
	public function findNotice($where){
		$notice = M('Notice')->where($where)->find();
		$notice['content'] = html_entity_decode($notice['content']);
		return $notice;
	}
	//删除公告
	public function delNotice($where){
		$res=M('Notice')->where($where)->delete();
		return $res;
	}




}
