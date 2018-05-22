<?php
namespace API\Controller;
//商品控制器
class GoodsController extends HomeController {
	public function _initialize(){
		//检测是否登录
		parent::_initialize();
	}
	//获取 商品列表
	public function getGoodList(){
		$search= I('search');
		if($search){
			$where['name'] = array('LIKE','%'.$search.'%');
		}
		$cat_id= I('cat_id');
		if($cat_id){
			$where['cat_id'] =$cat_id;
		}
		$where['is_sale'] =1;
		$goods = M('Goods')->where($where)->select();
		foreach($goods as $k=>$v){
			$goods[$k]['qiye'] = M('Cat')->where(array('cat_id'=>$v['cat_id']))->find()['name'];
		}
		$data_return['info']['good'] = $goods;
		$data_return['status'] = 1;
		return $this->ajaxReturn($data_return,'jsonp');
	}
	/**
	 * 查询所有主分类
	 */
	public function getGoodsCat(){
		$cat = M('Cat')->where('pid ='."0")->select();
		$this->ajaxReturn($cat,'JSONP');
	}
	/**
	 * 查询子分类
	 */
	public function getGoodsCatByPid(){
		$pid = I('pid');
		$cat = M('Cat')->where('pid ='.$pid)->select();
		foreach($cat as &$v){
			$v['cat'] = M('Cat')->where('pid ='.$v['cat_id'])->select();
		}
		$this->ajaxReturn($cat,'JSONP');
	}
	/**
	 * 根据分类获取商品
	 */
	public function getGoodsByCat(){
		$cat_id = I('cat_id')?I('cat_id'):null;
		$name = I('name');
		if($name){
			$where['name'] = array('LIKE','%'.$name.'%');
		}
		if($cat_id!=''){
				$where['cat_id'] = $cat_id;
		}
		$where['is_sale'] = 1;
		$goods = M('Project_goods')->where($where)->select();
		$this->ajaxReturn($goods,'JSONP');
	}
	//获取商品详情
	public function getGoodDetail(){
		$open_id = $this->uid;
		$goods_id = I('goods_id');
		$goods = M('Goods')->where(array('goods_id'=>$goods_id,'is_sale'=>1))->find();
		 $goods['attr'] = M('Goods_attr')->where(array('goods_id'=>$goods_id))->group('attr_id')->select();
		// foreach($goods['attr'] as &$v){
			// $name = M('Attr')->where(array('attr_id'=>$v['attr_id']))->find();
			// $v['name'] = $name['name'];
			// if(($v['price']!=null && $v['price'] !=0)||($v['jifen']!=null && $v['jifen'] !=0)){
				// $v['attr'] = M('Goods_attr')->where(array('goods_id'=>$goods_id,'attr_id'=>$v['attr_id']))->select();
			// }else{
				// $attr = M('Goods_attr')->where(array('goods_id'=>$goods_id,'attr_id'=>$v['attr_id']))->find();
				// foreach (explode(',', $attr['attr_val']) as &$s){
					// $v['attr'][]['attr_val'] = $s;
				// }
			// }
		// } 
		//评论
		$pinglun = M('Comment c')->join("ztp_user m ON m.uid = c.open_id")->where(array('goods_id'=>$goods_id))->order('time desc')->select();
		if(!empty($pinglun)){
			$goods['pinglun'] = $pinglun;
		}else{
			$goods['pinglun'] = null;
		}
		//$goods['kucun'] = M('Goods_attr')->where(array('goods_id'=>$goods_id))->sum('attr_kucun');
		/* $price = M('Goods_attr')->where(array('goods_id'=>$goods_id,'is_price_main'=>1))->find();
		$goods['price'] = $price['price'];
		$goods['jifen'] = $price['jifen'];
		$goods['fanjifen'] = $price['fanjifen']; */
		//企业名称
		$goods['qiye'] =M('Cat')->where(array('cat_id'=>$goods['cat_id']))->find()['name'];
		// $year = date('y',time());
		// $mouth = date('m',time());
		// $month_count = M('Count_month')->where(array('goods_id'=>$goods_id,'year'=>$year,'month'=>$mouth))->find();
		// if(!$month_count){
			// $data = [
			// 'goods_id'=>$goods_id,
			// 'count'=>0,
			// 'year'=>$year,
			// 'month'=>$mouth,
			// ];
			// M('Count_month')->add($data);
			// $goods['month_count'] = 0;
		// }else{
			// $goods['month_count'] = $month_count['count'];
		// }
		$is_shoucang = M('Collect')->where(array('goods_id'=>$goods_id,'open_id'=>$open_id))->find();
		if($is_shoucang){
			$goods['is_shoucang'] = 1;
		}else{
			$goods['is_shoucang'] = 0;
		}
		//编辑器里存的图片路径是../../../../ 换成http://www.xxx.com/格式 否则图片不显示
		$url = '<img src="'."http://".$_SERVER['SERVER_NAME']."/Public";
		$goods['content_all'] = preg_replace ('/<img src="..\/..\/..\/..\/Public/',$url,$goods['content_all']);	
		$this->ajaxReturn($goods,'JSONP');
	}
	/**
	 * 企业列表
	 */
	public function getCompanyList(){
		$cat = M('Cat')->order('add_time desc')->select();
		$this->ajaxReturn($cat,'JSONP');
	}
	/**
	 * 企业详情
	 */
	public function getCompanyDetails(){
		$cat_id = I('cat_id');
		$cat = M('Cat')->where(array('cat_id'=>$cat_id))->find();
		$this->ajaxReturn($cat,'JSONP');
	}
	//根据企业获取商品列表
	public function getGoodsListByCat(){
		$cat_id  = I('cat_id');
		$goods = M('Goods')->where(array('is_sale'=>1,'cat_id'=>$cat_id))->select();
		return $this->ajaxReturn($goods,'jsonp');
	}
}


	