<?php
namespace API\Controller;
class CollectController extends HomeController {
	/**
	 * 添加收藏
	 */
	public function setCollect(){
		//是否登录
		//获取商品id
		//添加收藏
		$open_id = $this->uid;
		$goods_id = I('goods_id');
		$attr_id = I('attr_id');
		if(empty($goods_id)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}
		
		$is_shoucang = M('Collect')->where(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'open_id'=>$open_id))->find();
		if($is_shoucang){
		    $re = M('Collect')->where(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'open_id'=>$open_id))->delete();
			if($re){
				$this->ajaxReturn(['status'=>1,'msg'=>'取消收藏'],'JSONP');
			}
		}else{
		    $data['open_id'] = $open_id;
		    $data['goods_id'] = $goods_id;
		    $data['add_time'] = time();
		    $data['attr_id'] = $attr_id;
		    $re = M('Collect')->add($data);
		    if($re){
		    	$this->ajaxReturn(['status'=>1,'msg'=>'收藏成功'],'JSONP');
		    }
		}
	}
	/**
	 * 收藏列表
	 */
	public function CollectList(){
		//是否登录
		//列出收藏列表
		$open_id = $this->uid;
		$list = M('Collect c')
		->join('ztp_goods g ON c.goods_id = g.goods_id')
		->join('ztp_goods_attr ga ON ga.goods_attr_id = c.attr_id')
		->where(array('open_id'=>$open_id))->select();
		/* foreach($list as $k=>$v){
			$list[$k]['price'] = M('Goods_attr ga')
		                          ->where(array('ga.goods_id'=>$v['goods_id']))->find()['price']; 
		}  */
		//dump($list);die;
		$this->ajaxReturn($list,'JSONP');
	}
	
	/**
	 * 删除收藏
	 */
	public function delCollectById(){
		$open_id = $this->uid;
		$collect_id = I('collect_id');
		if(empty($collect_id)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍候再试'],'JSONP');
		}	
		$re = M('Collect')->where(array('collect_id'=>$collect_id))->delete();
		if($re){
			$this->ajaxReturn(['status'=>1,'msg'=>'请求成功'],'JSONP');
		}else{
			$this->ajaxReturn(['status'=>-2,'msg'=>'系统繁忙，请稍候再试'],'JSONP');
		}
	}
}