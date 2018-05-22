<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
use Common\Model\ArtModel;

/**  
* 文章管理
*/
class ArtController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
    /**
     * 文章列表
     */
    public function showArtList(){
		$title = I('title')?I('title'):'';
		if(!empty($title)){
			$where['title'] = array('like','%'.$title.'%');
		}
		$ART = new ArtModel();
		$list = $ART->selectArt('',$where);
		$show = $ART->page($where,10);
		$this->assign('list',$list);
		$this->assign('page',$show);
    	$this->display();

    }
	/**
	 * 文章的添加或修改
	 */
	public function addArt(){
		if(IS_POST){
			//接收数据
			$data['title']    =  I('title');
			$data['content']  =  $_POST['content'];
			$data['type']     =  I('type');
			$data['add_time'] =  time();
			//图片处理
			//if($_FILES["pic"]["name"]){
			//	$data['pic']=$this->upload($_FILES["pic"]);
			//}
			//验证数据
			if(empty($data['title'])){
				$this->error('请填写文章标题');
			}
			if(empty($data['content'])){
				$this->error('请填写文章内容');
			}
			$ART = new ArtModel();
			$list = $ART->addArt($data);
			if($list){
				$this->success('操作成功',U('Art/showArtList'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->display();
		}
		
	}
	//修改文章
	public function updateArt(){
		//接收数据
		$where['id'] = I('id');
		if(empty($where['id'])){
			$this->error('参数错误');
		}
		if(IS_POST){

			//接收数据
			$data['title']    =  I('title');
// 			$data['en_title']    =  I('en_title');
			$data['content']  =  $_POST['content'];
			$data['type']     =  I('type');
			$data['update_time'] =  time();
			//图片处理
			//if($_FILES["pic"]["name"]){
			//	$data['pic']=$this->upload($_FILES["pic"]);
			//}
			//验证数据
			if(empty($data['title'])){
				$this->error('请填写文章标题');
			}
// 			if(empty($data['en_title'])){
// 				$this->error('请填写文章英文标题');
// 			}
			if(empty($data['content'])){
				$this->error('请填写文章内容');
			}
			$ART = new ArtModel();
			$list = $ART->findArt($where);
			
			//if($data['pic']){
			//	unlink($_SERVER['DOCUMENT_ROOT'].$list['pic']);//删除原图
			//}
			$re = $ART->saveArt($where, $data);
			if($re){
				$this->success('操作成功',U('Art/showArtList'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$ART = new ArtModel();
			$list = $ART->findArt($where);
			$this->assign('list',$list);
			$this->display();
		}
		
		
	}
	/**
	 * 文章删除
	 */
	public function deleteArt(){
		$ART = new ArtModel();
		$where['id']=I('id')?I('id'):null;
		if(!empty($where['id'])){
			$res=$ART->findArt($where);
		}
		if($res['status']==1){
			$this->error('状态为显示时,不能删除');
		}
		$res=$ART->delArt($where);
		if($res){
			$this->success('删除成功',U('Art/showArtList'));
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 状态修改
	 */
	public function open(){
		$where['id']=I('id')?I('id'):null;
		$ART = new ArtModel();
		$list=$ART->findArt($where);
		if($list['status']==0){
			$list=$ART->statusArt($where, '1');
			$this->success('状态修改成功');
		}
		if($list['status']==1){
			$list=$ART->statusArt($where, '0');
			$this->success('状态修改成功');
		}
	}
	/**
	 * 友情链接列表
	 */
	public function linkList(){
		$ART = new ArtModel();
		$list = $ART->selectLink();
		$this->assign('list',$list);
		$this->display();
	
	}
	/**
	 * 添加友情链接
	 */
	public function addLink(){
		$id = I('id');
		$ART = new ArtModel();
		if(IS_POST){
			$data['title'] = I('title');
			$data['url'] = I('url');
			$data['desc'] = I('desc');
			$data['add_time'] = time();
			$list = $ART->addLink($id,$data);
			if($list){
				$this->success('操作成功',U('Art/linkList'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$list = $ART->findLink($id);
			$this->assign('list',$list);
			$this->display();
		}
	}
	/**
	 * 删除
	 */
	public function delLink(){
		$id = I('id');
		$list = M('Link')->where(array('id'=>$id))->delete();
		if($list){
				$this->success('操作成功',U('Art/linkList'));
		}else{
			$this->error('操作失败');
		}
	}
	/**
	 * 下载中心列表
	 */
	public function downList(){
		$id = I('id');
		$ART = new ArtModel();
		$list = $ART->selectDownLoad();
		$this->assign('list',$list);
		$this->display();
	}
	/**
	 * 下载中心
	 */
	public function addDown(){
		$id = I('id');
		$ART = new ArtModel();
		if(IS_POST){
			$data['title'] = I('title');
			$data['content'] = $_POST['content'];
			$data['add_time'] = time();
			$list = $ART->saveDownLoad($id,$data);
			if($list){
				$this->success('操作成功',U('Art/downList'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$list = $ART->selectDownLoad($id);
			$this->assign('list',$list);
			$this->display();
		}
	}
	/**
	 * 删除下载中心
	 */
	public function delDown(){
		$id = I('id');
		$list = M('Down')->where(array('id'=>$id))->delete();
		if($list){
			$this->success('操作成功',U('Art/downList'));
		}else{
			$this->error('操作失败');
		}
	}
	/**
	 * 提示语列表
	 */
	public function cueList(){
		$ART = new ArtModel();
		$list = $ART->selectCue();
		$this->assign('list',$list);
		$this->display();
	}
	/**
	 * 添加修改提示语
	 */
	public function addCue(){
		$id = I('id');
		$ART = new ArtModel();
		if(IS_POST){
			$data['content'] = $_POST['content'];
			$data['add_time'] = time();
			$list = $ART->saveCue($id,$data);
			if($list){
				$this->success('操作成功',U("Art/cueList"));
			}else{
				$this->error('操作失败');
			}
		}else{
			$list = $ART->selectCue($id);
			$this->assign('list',$list);
			$this->display();
		}
	}
	/**
	 * 首页模块列表
	 */
	public function artMoKuaiList(){
		$ART = new ArtModel();
		$list = $ART->selectArtMoKuai();
		$this->assign('list',$list);
		$this->display();
	}
	
	/**
	 * 修改提示语
	 */
	public function addMoKuai(){
		$id = I('id');
		$ART = new ArtModel();
		if(IS_POST){
			$data['content'] = $_POST['content'];
			$data['add_time'] = time();
			$list = $ART->saveMoKuai($id,$data);
			if($list){
				$this->success('操作成功',U("Art/artMoKuaiList"));
			}else{
				$this->error('操作失败');
			}
		}else{
			$list = $ART->selectArtMoKuai($id);
			$this->assign('list',$list);
			$this->display();
		}
	}
}
