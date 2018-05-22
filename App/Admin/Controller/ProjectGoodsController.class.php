<?php
namespace Admin\Controller;
use Think\Page;
class ProjectGoodsController extends AdminBaseController {
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 商品列表页
     */
    public function index(){
    	$cat = M('Cat')->select();
    	$this->assign('cat',$cat);
    	$name = I('name')?I('name'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	if(!empty($name)){
    		$where['name']=$name;
    	}
    	if(!empty($cat_id)){
    		$where['cat_id']=$cat_id;
    	}
    	$count=M('Project_goods')->where($where)->count();
    	$Page=new Page($count,8);
    	//setPageParameter($Page, array('account'=>$account));
    	$show       = $Page->show();
    	$list =  M('Project_goods')->where($where)
    	->limit($Page->firstRow.','.$Page->listRows)->select();
    	/* foreach ($list as &$v){
    		$names = M('Cat')->where(array('cat_id'=>$v['cat_id']))->find();
    		$father = M('Cat')->where(array('cat_id'=>$names['pid']))->find();
    		$v['title'] = $father['name'].'---'.$names['name'];
    	} */
    	//dump($list);die;
    	$this->assign('list',$list);
    	$this->assign('page',$show);
    	$this->display();
    }
    /**
     * 添加商品
     */
    public function addProject_goods(){
    	$project_goods_id = I('project_goods_id')?I('project_goods_id'):null;
    	$this->assign('project_goods_id',$project_goods_id);
    	$cat = M('Cat')->where(array('pid'=>0))->select();
    	$this->assign('cat',$cat);
    	if(!empty($project_goods_id)){
    		$Project_goods = M('Project_goods')->where(array('project_goods_id'=>$project_goods_id))->find();
    		$Project_goods['cat_name'] = M('Cat')->where(array('cat_id'=>$Project_goods['cat_id']))->find()['name'];
    		$this->assign('Project_goods',$Project_goods);
    	}
    	//dump($Project_goods);die;
    	$this->display();
    }
    /**
     * 添加商品执行方法
     */
    public function doAddProject_goods(){
		$project_goods_id = I('project_goods_id')?I('project_goods_id'):null;
    	$cat = 1;
    	$name = I('name')?I('name'):null;
    	if(empty($name)){
    		$this->error('请输入商品名称');
    	}
    	$price = I('price')?I('price'):null;
    	if(empty($price)){
    		$this->error('请输入商品价格');
    	}
		//dump($_FILES);dump($project_goods_id);die;
    	if(empty($_FILES['image']['name'])&&empty($project_goods_id)){
    		$this->error('请上传图片');
    	}
    	if($_FILES['image']['name']!=''){
    		$image = $this->upload($_FILES['image']);			
    	}
    	$content = $_POST['content'];
    	if(empty($project_goods_id)){
	    	$Project_goods = [
	    		'name'=>$name,
	    		//'cat_id'=>$cat,
	    		'image'=>$image,
	    		'is_sale'=>I('is_sale'),
	    		'price'=>$price,
	    		'content'=>$content,
	    		'add_time'=>time(),
	    	];
	    	$project_goods_id = M('Project_goods')->add($Project_goods);
	    	$this->success('添加成功');
    	}else{

    		$data['name'] = $name;
			//$data['cat_id'] = $cat;
			$data['is_sale'] = I('is_sale');
			$data['price'] = $price;		
    		$r_Project_goods = M('Project_goods')->where(array('project_goods_id'=>$project_goods_id))->save($data);
			$this->success('修改成功');
    	}
    }

    public function setIsSale(){
    	$project_goods_id = I('project_goods_id');
    	$Project_goods = M('Project_goods')->where(array('project_goods_id'=>$project_goods_id))->find();
    	if($Project_goods['is_sale'] == 1){
    		M('Project_goods')->where(array('project_goods_id'=>$project_goods_id))->setField('is_sale',-1);
    	}
    	if($Project_goods['is_sale'] != 1){
    		M('Project_goods')->where(array('project_goods_id'=>$project_goods_id))->setField('is_sale',1);
    	}
    	$this->success('修改成功');
    }
    
    
    /**
     * 分类列表
     */
    public function cat(){
    	$count=M('Cat')->where(array('pid'=>0))->count();
    	$Page=new Page($count,20);
    	//setPageParameter($Page, array('account'=>$account));
    	$show       = $Page->show();
    	$list =  M('Cat')
    	->order("add_time")
    	->where(array('pid'=>0))
    	->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('list',$list);
    	$this->assign('page',$show);
    	$this->display();
    }
    /**
     * 添加分类
     */
    public function addCat(){
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$pid = I('pid');
    	$cat = M('Cat')->where(array('cat_id'=>$cat_id))->find();
    	$this->assign('list',$cat);
    	$this->assign('pid',$pid);
    	$this->display();
    }
    /**
     * 添加分类
     */
    public function doAddCat(){
    	$pid = I('pid');
    	if(empty($pid)){
    		$pid = 0;
    	}
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$name = I('name');
    	if(empty($cat_id)){
    		if(empty($name)){
    			$this->error('请输入名称');
    		}
    		$cat = [
    		'name'=>$name,
    		'add_time'=>time(),
    		'pid'=>$pid,
    		];
    		$r = M('Cat')->add($cat);
    		$this->success('操作成功',U('Project_goods/cat'));
    	}else{
    		$cat = [
	    		'name'=>$name,
    		];
    		$r = M('Cat')->where(array('cat_id'=>$cat_id))->save($cat);
    		$this->success('操作成功',U('Project_goods/cat'));
    	}
    }
    /**
     * 删除分类
     */
    public function del(){
    	$cat_id = I('cat_id');
    	$r = M('Cat')->delete($cat_id);
    	$this->success('操作成功',U('Project_goods/cat'));
    }
    public function setYilai(){
    	$attr_id = I('attr_id')?I('attr_id'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$r = M('Attr')->where(array('attr_id'=>$attr_id))->setField('is_yilai',1);
    	$where['attr_id'] = array('neq',$attr_id);
    	$where['cat_id'] = $cat_id;
    	$r_cancel = M('Attr')->where($where)->setField('is_yilai',0);
    	$this->success('操作成功',U('Project_goods/showAttr',array('cat_id'=>$cat_id)));
    }
}