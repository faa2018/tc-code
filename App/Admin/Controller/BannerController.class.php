<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
/**  
* Banner管理
*/
class BannerController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
    public function bannerList(){
    	$banner=M('Banner');
    	$list=$banner->order('add_time desc')->select();
    	$this->assign('list',$list);
        $this->display();
    }
	/**
	 * Banner的添加或修改
	 */
	public function addBanner(){		
		$where['id'] = I("id")?I("id"):null;
 		$but=I('id')?'修改':'添加';
		if(!empty($where['id'])){
			$list = M("Banner")->where($where)->find();
			$this->assign("list",$list);
		}
		$this->assign('but',$but);
		$this->display(); 
	}
	//添加修改操作
	public function editBanner(){
		$user=M('Banner');
		if(IS_POST){
			//$data['title']=I('title')?I('title'):null;
			$data['type']=I('type')?I('type'):null;
			$data['sort']=I('sort')?I('sort'):null;
			$data['add_time']=time();	
			$where['id']=I("id")?I("id"):null;
			if(isset($where['id'])){
				//修改
 				if($_FILES["pic"]["name"]){
 					$files=$_FILES["pic"];
 					if(!empty($files)){
 						$data['pic']=$this->upload($files);
 						$url=M('Banner')->where($where)->find();
 						unlink('Uploads'.$url['pic']);
 					}
 				}
				$list=M('Banner')->where($where)->save($data);
				$str_su='修改成功';
				$str_er='修改失败';
			}else{
				//添加
				//检测填写信息是否完整
				foreach ($data as $v){
					if($v ==''){
						$this->error('信息填写不完整');
					}
				}
 				if($_FILES["pic"]["name"]){
 					$data['pic']=$this->upload($_FILES["pic"]);
				}
				if(empty($data['pic'])){
					$this->error('请上传图片');
				}
				$list=M('Banner')->add($data);
				$str_su='添加成功';
				$str_er='添加失败';
			}
			if($list){
				$this->success($str_su,U('Banner/bannerList'));
			}else{
				$this->error($str_er);
			}
		}
	}
	/**
	 * 轮播删除
	 */
	public function delBanner(){
		$banner=M('banner');
		$where['id']=I('id')?I('id'):null;
		if(!empty($where['id'])){
			$res=$banner->where($where)->find();
		}
		if($res['status']==1){
			$this->error('状态为显示时,不能删除');
		}
		$res=$banner->where($where)->delete();
		if($res){
			$this->success('删除成功',U('Banner/bannerList'));
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 状态修改
	 */
	public function open(){
		$where['id']=I('id')?I('id'):null;
		$list=M('banner')->where($where)->find();
		if($list['status']==0){
			$list=M('banner')->where($where)->setField('status','1');
			$this->success('状态修改成功');
		}
		if($list['status']==1){
			$list=M('banner')->where($where)->setField('status','0');
			$this->success('状态修改成功');
		}
	}
	//首页四个图标
	public function iconList(){
		$list = M('Icon')->select();
		$this->assign('list',$list);
		$this->display();
	}
	//添加修改操作
	public function addIcon(){
		$where['id'] = I('id');
		if(IS_POST){
			$data['title'] = I('title');
			$data['sort'] = I('sort');
			//图片处理
			if($_FILES["pic"]["name"]){
				$data['pic'] = $this->upload($_FILES["pic"]);
			}
			$list = M('Icon')->where($where)->save($data);
			if($list){
				$this->success('操作成功',U('Banner/iconList'));
			}else{
				$this->error('系统繁忙');
			}
		}else{
			$list = M('Icon')->where($where)->find();
			$this->assign('list',$list);
			$this->display();
		}
		
	}
}
