<?php
namespace Common\Model;
use Think\Model;
/**
 * 轮播规则model
 */
class BannerModel extends Model{

	/**
	 * 查询数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function selectBanner($limit,$where=array()){
		$list = M('Banner')->where($where)->order('sort asc')->limit($limit)->select();
		return $list;
	}
	//分页
	public function Page($where = array(),$limit = 10){
		$count      = M('Banner')->where($where)->count();
		$Page       = new \Org\Nx\Page($count,$limit);
		$show       = $Page->show();
		return $show;
	}
// 	//添加公告
// 	public function addArt($data){
// 		$re = M('Art')->add($data);
// 		return $re;
// 	}
// 	//修改公告
// 	public function saveArt($where,$data){
// 		$res = M('Art')->where($where)->save($data);
// 		return $res;
// 	}
// 	//查询单个公告
// 	public function findArt($where){
// 		$art = M('Art')->where($where)->find();
// 		$art['content'] = html_entity_decode($art['content']);
// 		return $art;
// 	}
// 	//删除公告
// 	public function delArt($where){
// 		$res=M('Art')->where($where)->delete();
// 		return $res;
// 	}
// 	//更改状态
// 	public function statusArt($where,$status){
// 		$res = M('Art')->where($where)->setField('status',$status);
// 		return $res;
// 	}
// 	//友情链接
// 	public function selectLink(){
// 		$list = M('Link')->order('add_time desc')->select();
// 		return $list;
// 	}
// 	//友情链接
// 	public function findLink($id){
// 		$list = M('Link')->where(array('id'=>$id))->find();
// 		return $list;
// 	}
// 	//友情链接
// 	public function addLink($id,$data){
// 		if($id){
// 			$list = M('Link')->where(array('id'=>$id))->save($data);
// 		}else{
// 			$list = M('Link')->add($data);
// 		}
// 		return $list;
// 	}
// 	//下载中心
// 	public function selectDownLoad($id,$limit=20){
// 		if($id){
// 			$list = M('Down')->where(array('id'=>$id))->find();
// 		}else{
// 			$list = M('Down')->limit($limit)->select();
// 		}
// 		return $list;
// 	}
// 	//下载中心
// 	public function saveDownLoad($id,$data){
// 		if($id){
// 			$list = M('Down')->where(array('id'=>$id))->save($data);
// 		}else{
// 			$list = M('Down')->add($data);
// 		}
// 		return $list;
// 	}
// 	//查询提示语
// 	public function selectCue($id = ''){
// 		if($id){
// 			$where['id'] = $id;
// 			$list = M('Cue')->where($where)->find();
// 		}else{
// 			$list = M('Cue')->select();
// 		}
// 		return $list;
// 	}
// 	//修改提示语
// 	public function saveCue($id,$data){
// 		$list = M('Cue')->where(array('id'=>$id))->save($data);
// 		return $list;
// 	}
	
}
