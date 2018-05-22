<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\UserMoneyModel;
use Org\Nx\Page;
use Common\Api\UserMoneyApi;
use Common\Model\UserModel;
use Common\Model\UserRelationModel;
//会员消费专区商品控制器
class ProjectGoodsController extends HomeController {
	public function _initialize(){
		//检测是否登录
		parent::_initialize();
	}
	//获取 商品列表
	public function getGoodList(){
	    $goods_list = M('Project_goods')->where(array('is_sale'=>1))->select();
	    $data_return['info']['good'] = $goods_list;
		$data_return['status'] = 1;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取商品详情
	public function getGoodDetail(){
	    $good_id = I('project_goods_id');
	    $good = M('Project_goods')->where(array('is_sale'=>1,'project_goods_id'=>$good_id))->find();
	    if(!$good){
	        $data_return['status'] = -1;
	        $data_return['info'] = '商品不存在';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	    $data_return['info']['good'] = $good;
		$data_return['status'] = 1;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	public function getGoods(){
		$where = [
			'is_sale'=>1,
		];
    	$count=M('Goods')->where($where)->count();
    	$Page=new Page($count,8);
    	//setPageParameter($Page, array('account'=>$account));
    	$show       = $Page->show();
    	$list =  M('Goods')->where($where)
    	->limit($Page->firstRow.','.$Page->listRows)->select();
    	//dump($list);die;
    	$this->assign('goods',$list);
    	$this->assign('page',$show);
    	$this->display();
	}

	public function addCart(){
		if(!IS_AJAX){
			$this->ajaxReturn(['status'=>-1,'msg'=>'非法操作']);
		}
		$goods_id = I('goods_id');
		$goods = M('Goods')->where(['goods_id'=>$goods_id])->find();
		if(!$goods&&$goods['is_sale']!=1){
			$this->ajaxReturn(['status'=>-1,'msg'=>'非法操作']);
		}
		$cart_log = M('Cart')->where(array('goods_id'=>$goods_id,'user_id'=>$this->uid))->find();
		if($cart_log){
			$r_cart = M('Cart')->where(['cart_id'=>$cart_log['cart_id']])->setInc('num',1);
		}else{
			$cart = [
				'user_id'=>$this->uid,
				'goods_id'=>$goods_id,
				'num'=>1,
			];
			$r_cart = M('Cart')->add($cart);
		}
		
		if(!$r_cart){
			$this->ajaxReturn(['status'=>-2,'msg'=>'系统繁忙']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'加入购物车成功']);
	}
	
	public function getMyCart(){
		session('cart',null);
		$where = [
			'user_id'=>$this->uid,
		];
		$count=M('Cart')->where($where)->count();
		$Page=new Page($count,8);
		//setPageParameter($Page, array('account'=>$account));
		$show       = $Page->show();
		$list =  M('Cart')->where($where)
		->limit($Page->firstRow.','.$Page->listRows)->select();
		//dump($list);die;
		foreach($list as &$v){
			$v['goods'] = M('goods')->where(array('goods_id'=>$v['goods_id']))->find();
			$v['all_money'] = $v['goods']['price']*$v['num'];
		}
		$this->assign('cart',$list);
		$this->assign('page',$show);
		$this->display();
	}
	
	public function setNum(){
		$type = I('type');
		$cart_id = I('cart_id');
 		$cart = M('Cart')->where(['cart_id'=>$cart_id])->find();
 		if(!$cart||$cart['user_id']!=$this->uid){
 			$this->ajaxReturn(['status'=>-1,'msg'=>'非法请求']);
 		}
 		if($type==1){
 			$r = M('Cart')->where(['cart_id'=>$cart_id])->setInc('num',1);
 		}else{
 			if($cart['num']!=0){
 				$r = M('Cart')->where(['cart_id'=>$cart_id])->setDec('num',1);
 			}
 		}
 		
 		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
	//结算 存session
	public function setSession(){
		session('cart',null);
		$cart_id = I('cart_id');
		$carts = explode(',', $cart_id);
		if(!empty($cart_id)){
			session('cart',$carts);
			$url  = U('Goods/showOrders');
			$this->ajaxReturn(['status'=>1,'msg'=>'当前正在跳转结算页面','url'=>$url]);
		}
		$this->ajaxReturn(['status'=>-1,'msg'=>'当前操作有误，请稍后再试']);
	}
	
	public function showOrders(){
		$cart = session('cart');
		if(empty($cart)){
			$this->redirect('Goods/getMyCart');
		}
		$carts = [];
		foreach($cart as &$v){
			$cart_one = M('Cart')->where(['cart_id'=>$v])->find();
			$cart_one['goods'] = M('Goods')->where(['goods_id'=>$cart_one['goods_id']])->find();
			$carts[] = $cart_one;
		}
		$address = M('Useraddress')->where(['user_id'=>$this->uid])->select();
		$this->assign('address',$address);
		$this->assign('carts',$carts);
		$this->display();
	}
	
	public function newAddress(){
		$phone = I('phone');
		$name = I('name');
		$address = I('address');
		$xiangxi = I('xiangxi');
		if(empty($phone)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'请输入手机号']);
		}
		if(empty($name)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'请输入收获人姓名']);
		}
		if(empty($address)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'请输入地址']);
		}
		$array_data = [
				'phone'=>$phone,
				'name'=>$name,
				'address'=>$address.','.$xiangxi,
				'user_id'=>$this->uid,
		];	
		$r = M('Useraddress')->add($array_data);
		if(!$r){
			$this->ajaxReturn(['status'=>-3,'msg'=>'服务器繁忙']);
		}	
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
	public function setMoRenAddress(){
		$address_id =I('address_id');
		$address = M('Useraddress')->where(['id'=>$address_id])->find();
		if($address['user_id']!=$this->uid){
			$this->ajaxReturn(['status'=>-3,'msg'=>'非法请求']);
		}
		$r_set = M('Useraddress')->where(['user_id'=>$this->uid])->setField('is_moren',0);
		$r_set_moren = M('Useraddress')->where(['id'=>$address_id])->setField('is_moren',1);
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
	
	public function delAddress(){
		$address_id = I('address_id');
		$address = M('Useraddress')->where(['id'=>$address_id])->find();
		if(!$address||$address['user_id']!=$this->uid){
			$this->ajaxReturn(['status'=>-3,'msg'=>'非法请求']);
		}
		$r = M('Useraddress')->delete($address_id);
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
	
	//下单
	public function submitOrders(){
		$type = I('type');
		if($type == 1)$money_type = 2;
		if($type == 2)$money_type = 6;
		$carts = session('cart');
		$og_ids = [];
		$total_money = 0;
		if(empty($carts)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'当前购物车为空']);
		}
		if(empty($type)){
			$this->ajaxReturn(['status'=>-2,'msg'=>'请选择支付方式']);
		}
		$address_id = I('address_id');
		if(empty($address_id)){
			$this->ajaxReturn(['status'=>-3,'msg'=>'请选择收货地址']);
		}
		$M_USERMONEY = new UserMoneyModel($this->uid);
		$shangcheng_money = $M_USERMONEY->getUserMoneyByUid($money_type);
		foreach($carts as &$v){
			//插入订单商品表  og
			$cart = M('Cart')->where(['cart_id'=>$v])->find();
			$goods = M('Goods')->where(['goods_id'=>$cart['goods_id']])->find();
			$total_money_check += $cart['num']*$goods['price'];
		}
		if($shangcheng_money['num']<$total_money_check){
			$this->ajaxReturn(['status'=>-4,'msg'=>'您的资金余额不足']);
		}
		foreach($carts as &$v){
			//插入订单商品表  og
			$cart = M('Cart')->where(['cart_id'=>$v])->find();
			$goods = M('Goods')->where(['goods_id'=>$cart['goods_id']])->find();
			$og = [
				'goods_id'=>$v['goods_id'],
				'order_id'=>1,
				'num'=>$v['num'],
			];
			$r = M('Og')->add($og);
			$og_ids[] = $r;
			$total_money += $cart['num']*$goods['price'];
		}
		//删除购物车
		foreach($carts as $q){
			M('Cart')->where(['cart_id'=>$q])->delete();
		}
		session('cart',null);
		//地址
		$address = M('Useraddress')->where(['id'=>$address_id])->find();
		//插入订单表
		$orders = [
			'order_no'=>$this->uid.'-'.time(),
			'order_status'=>1,//已经支付
			'order_money'=>$total_money,
			'pay_way'=>$type,
			'user_id'=>$this->uid,
			'create_time'=>time(),
			'address'=>$address['address'],
			'phone'=>$address['phone'],
			'name'=>$address['name']
		];
		$r_orders = M('Orders')->add($orders);
		$r_setField = M('Og')->where(array('og_id'=>array('in',$og_ids)))->setField('order_id',$r_orders);
		if($type == 1){
			//钱的处理
			$API_USERMONEY = new UserMoneyApi();
			$API_USERMONEY->setUserMoneyLogic($this->uid, $total_money, 2, 4001, "商城消费花费七乐积分", 4001,2);
			$API_USERMONEY->setUserMoneyLogic($this->uid, $total_money, 1, 4002, "商城消费获取增值积分", 4002,3);
			//实例化结构model
			$USER_RELATION = new UserRelationModel();
			$user = $USER_RELATION->getUserByUid($this->uid);//本人直推关系
			$up_user = $USER_RELATION->getUserByUid($user['pid']);//直推上线
			if($up_user){
				//加上线业绩
				$this->addTeamYeji($up_user,$total_money,0);
			}
			//判断是否超过冻结积分的10%
			$money_dongjie = $M_USERMONEY->getUserMoneyByUid(8)['num'];
			if($money_dongjie != 0 && ($money_dongjie*0.1 <= $total_money)){
				$API_USERMONEY->setUserMoneyLogic($this->uid, $money_dongjie*0.3, 1, 4003, "释放未解冻积分到增值积分", 4003,3);
				$API_USERMONEY->setUserMoneyLogic($this->uid, $money_dongjie*0.3, 2, 4004, "释放未解冻积分", 4004,8);
				//记录
				M('User_jiedong')->where(array('uid'=>$this->uid))->setInc('shifang_num',1);
				M('User_jiedong')->where(array('uid'=>$this->uid))->setInc('shifang_money',$money_dongjie*0.3);
				M('User_jiedong')->where(array('uid'=>$this->uid))->setField('status',1);
			}
		}else{
			//钱的处理
			$API_USERMONEY = new UserMoneyApi();
			$API_USERMONEY->setUserMoneyLogic($this->uid, $total_money, 2, 4001, "商城消费花费消费积分", 4001,6);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
	//加上线的团队业绩
	private function addTeamYeji($user,$money,$k){
		if($k>20){
			return;
		}
		$k++;
		$uid = $user['uid'];
		$USERRELATION = new UserRelationModel();
		//加团队业绩
		$USERRELATION->addTeamYeji($uid,$money);
		//查询本人
		$user_relation = $USERRELATION->getUserByUid($uid);
		//查询上级 推荐关系
		$up = $USERRELATION->getUpByPid($user['pid']);
		if(!$up){
			return;
		}
		$this->addTeamYeji($up, $money,$k);
	}
	public function delCart(){
		$cart_id = I('cart_id');
		$cart = M('Cart')->where(['cart_id'=>$cart_id])->find();
		if($cart['user_id']!=$this->uid){
			$this->ajaxReturn(['status'=>-1,'msg'=>'非法请求']);
		}
		$r = M('Cart')->where(['cart_id'=>$cart_id])->delete();
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
	}
}


	