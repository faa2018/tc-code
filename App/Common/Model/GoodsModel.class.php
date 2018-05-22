<?php
namespace Common\Model;
use Think\Model;
class GoodsModel extends Model
{
	//添加或修改商品
    public function addGoods($id,$title,$pic,$content,$price,$stock,$status){
    	$data['id']        = $id;
    	$data['title']     = $title;
    	$data['content']   = $content;
    	$data['pic']  	   = $pic;
    	$data['price']     = $price;
    	$data['stock']     = $stock;
    	$data['add_time']  = time();
    	$data['status']    = 1;
    	if(!$id){
    		return M('Goods')->add($data);
    	}else{
    		$where['id'] = $id;
    		return M('Goods')->where($where)->save($data);
    	}
    }
	/**
	 * 查询所有商品
	 */
    public function getGoods($where=array(),$limit=15){
    	$where['status'] = array('neq','-1');
    	$count      = M('Goods')->where($where)->count();
    	$Page       = new \Org\Nx\Page($count,$limit);
    	$list = M("Goods")->order('add_time desc')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$list_re['page'] = $Page->show();
    	$list_re['list'] = $list;
    	return $list_re;
    }
    /**
     * 修改某个商品的状态
     */
    public function setGoodsStatus($id,$status){
    	return M('Goods')->where(array('id'=>$id))->setField('status',$status);
    }
    /**
     * 库存减少
     */
    public function setGoodsStock($id,$num){
    	return M('Goods')->where(array('id'=>$id))->setDec('stock',$num);
    }
    //查询某个商品
    public function getGoodsById($id){
    	$where['id'] = $id;
    	return M('Goods')->where($where)->find();
    }
    //分页
    public function Page($where = array(),$limit = 16){
    	$count      = M('Goods')->where($where)->count();
    	$Page       = new \Think\Page($count,$limit);
    	setPageParameter($Page, array('title'=>$where['title']));
    	$show       = $Page->show();
    	return $show;
    }
}