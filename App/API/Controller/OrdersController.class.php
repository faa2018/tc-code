<?php
namespace API\Controller;
class OrdersController extends UserController {
 	
	public function _initialize(){
		
 		parent::_initialize();
	}
	/**
	 * 添加订单
	 */
	public function addOrders(){
	    $open_id = $this->uid;
	    $cart = M('Cart')->where(array('uid'=>$open_id,'is_check'=>1))->select();
	    foreach ($cart as &$v){
	    	$cart_id[] = $v['cart_id'];
	    }
	    $remarks = I('remarks');
		$order_no = getOrderNo();
		while(M('Orders')->where(array('order_no'=>$order_no))->find()){
			$order_no = getOrderNo();
		}
		$order_status = -1;
		//$send_money = $this->config['send_money'];
		$totle_money = 0;
		$order_goods_ids = [];
		$orders = [];
		//插入order_goods表
		foreach ($cart_id as $v){
			$cart_info = M('Cart')->where(array('cart_id'=>$v))->find();
			$goods_attr = M('Goods_attr')->where(array('goods_attr_id'=>$cart_info['goods_attr_id']))->find();
			$goods = M('Goods')->where(array('goods_id'=>$cart_info['goods_id']))->find();
			$order_goods = [
				'order_id'=>0,
				'goods_id'=>$cart_info['goods_id'],
				'num'=>$cart_info['num'],
				'price'=>$goods_attr['price'],
				'attr_id'=>$cart_info['goods_attr_id'],
				'attr_name'=>$cart_info['attr_names'],
				'goods_name'=>$goods['name'],
				'image'=>$goods['image'],
				'jifen'=>$goods_attr['jifen'],
				'fanjifen'=>intval($goods_attr['fanjifen'])
			];
			if($cart_info['jifen'] == null){
			    $jifen_arr[] = $goods_attr['jifen'];
			}
			
			
		      /* if($cart_info['jifen'] != null){
		        if(in_array(null,$jifen_arr)){
		            $this->ajaxReturn(['status'=>-9,'msg'=>'积分价格的商品不可与现金商品一起结算'],'JSONP');
		        }
		    } */
                      if(!empty($goods_attr['price'])){
                      	$totle_money += $cart_info['num']*$goods_attr['price'];
                      }else{
                      $totle_money += $cart_info['num']*$goods_attr['jifen'];
                      }
			
			M('Goods_attr')->where(array('goods_attr_id'=>$cart_info['goods_attr_id']))->setDec('attr_kucun',$cart_info['num']);
			$arr_goods[] = $order_goods;
		}
		
		if(in_array(null,$jifen_arr)){
		    if(count($cart_id) != count($jifen_arr)){
		        $this->ajaxReturn(['status'=>-9,'msg'=>'积分价格的商品不可与现金商品一起结算'],'JSONP');
		    }
		}
		foreach ($arr_goods as &$v){
		    $r = M('Order_goods')->add($v);
		    $order_goods_ids[] = $r;
		    $send_money[] = $goods['send_money'];
		    if($v['jifen'] !=null){
		    	$send_money[] = 9999;
		    }
		}
		$send_money = max($send_money);
		if($send_money == 9999){
			$send_money_s = 0;
		}else{
		    $send_money_s = $send_money;
		}
		$totle_money += $send_money_s;
		$is_pay = 0;
		
		$address = M('User_address')->where(array('uid'=>$open_id,'isDefult'=>1))->find();
		$username = $address['username'];
		$user_address = $address['areaid_1'].$address['areaid_2'].$address['areaid_3'].$address['address'];
		$phone = $address['phone'];
		$add_time = time();
		$orders = [
			'order_no'=>$order_no,
			'order_status'=>$order_status,
			'total_money'=>$totle_money,
			'send_money'=>$send_money,
			'pay_type'=>0,
			'is_pay'=>$is_pay,
			'open_id'=>$open_id,
			'username'=>$username,
			'user_address'=>$user_address,
			'phone'=>$phone,
			'add_time'=>$add_time,
			'remarks'=>$remarks,
			'is_pingjia'=>0,
		];
		//插入订单表
		$order_add = M('Orders')->add($orders);
		//处理插入的order_goods表中的数据 添加 order_id字段属性
		M('Order_goods')->where(array('id'=>array('in',$order_goods_ids)))->setField('order_id',$order_add);		
		
		foreach($cart_id as $v){
			M('Cart')->where(array('cart_id'=>$v))->delete();
		}
            	$ip = get_client_ip();
		$this->ajaxReturn(['status'=>1,'msg'=>'成功生成订单','totle_money'=>$totle_money,'order_id'=>$order_add,'order_no'=>$order_no,'ip'=>$ip],'JSONP');
	}
  	/**
          *立即购买
          */
        public function buyNow(){
	$goods_id = I('goods_id');
          $num = I('num');
          if(!empty(session('buyNow'))){
            	session('buyNow',null);
          }
	 /* $price_attr_id = I('price_attr_id');
         // dump($price_attr_id);die;
	$other_attr = I('order_attr');
         // dump($other_attr);die;
          $goods = M('Goods_attr')->where(array('goods_id'=>$goods_id,'goods_attr_id'=>$price_attr_id))->find();
        //echo M('Goods_attr')->_sql();
        //  dump($goods);die;
          if($goods['attr_kucun']<$num){
           $this->ajaxReturn(['status'=>-1,'msg'=>'库存不足'],'JSONP');
          } */
          session('buyNow',['goods_id'=>$goods_id,'num'=>$num]);
          $this->ajaxReturn(['status'=>1,'msg'=>'成功'],'JSONP');
        }
  
  
  
  
  /**
	 * 预订单展示
	 */
	public function getQuasiOrderByGoodsId(){
		$open_id = $this->uid;
		$goods_id = session('buyNow')['goods_id'];
		$num = session('buyNow')['num'];
		$other_attr = session('buyNow')['order_attr'];
		$price_attr_id = session('buyNow')['price_attr_id'];
			$goods_attr = M('Goods_attr')->where(array('goods_attr_id'=>$price_attr_id))->find();
            	
			$goods = M('Goods')->where(array('goods_id'=>$goods_id))->find();
			$cart_info['price'] = $goods['price'];
			$cart_info['attr_id'] = $goods_attr['attr_id'];
			$cart_info['goods_name'] = $goods['name'];
			$cart_info['image'] = $goods['image'];
			$cart_info['num'] = $num;
			$cart_info['fanjifen'] = $goods_attr['fanjifen'];
            		$cart_info['attr_names'] = $other_attr;
			if($goods_attr['jifen'] == null){
				$cart_info['jifen'] = null;
                                	$cart_info['payway'] = 1;
			}else{
				$cart_info['jifen'] = $goods_attr['jifen'];
                                $cart_info['payway'] = 2;
			}
            /* if($goods_attr['price']!=0&&$goods_attr['price']!=null){
            $totle_money = $num*$goods_attr['price'];
            }else{
               $totle_money = $num*$goods_attr['jifen'];
                 } */
			
			$list['list'][] = $cart_info;
			$send_money = $goods['send_money'];
			if($cart_info['jifen'] == null){
				$jifen_arr[] = $cart_info['jifen'];
			}
	
	
	
			$list['jifen'] = $cart_info['jifen'];
		$list['goods_money'] = $goods['price'];
		$list['send_money'] = $send_money;
		if($send_money == 9999){
			$send_money_s = 0;
		}else{
			$send_money_s = $send_money;
		}
		$list['totle_money'] = $num*$goods['price'];
		$address = M("User_address")->where(array('uid'=>$open_id,'isDefault'=>1))->find();
		if(!$address){
			$list['address'] = null;
		}else{
			$list['address'] = $address;
		}
           
		$this->ajaxReturn($list,'JSONP');
	}
  
  
  
  
  
  /**
  *立即购买生成订单
  */
      public function buyNowOrders(){
        	$open_id = $this->uid;
        $goods_id = session('buyNow')['goods_id'];
        $num = session('buyNow')['num'];
        $other_attr = session('buyNow')['order_attr'];
		
          $price_attr_id = session('buyNow')['price_attr_id'];
		$remarks = I('remarks');
		$order_no = getOrderNo();
		while(M('Orders')->where(array('order_no'=>$order_no))->find()){
			$order_no = getOrderNo();
		}
		$order_status = -1;
		//$send_money = $this->config['send_money'];
		$totle_money = 0;
        $goods_attr = M('Goods_attr')->where(array('goods_attr_id'=>$price_attr_id))->find();
			$goods = M('Goods')->where(array('goods_id'=> $goods_id))->find();
			foreach ($other_attr as $k=>$v){
			$other_attr1 .=  $v.",";
			}
			$other_attr1 = rtrim($other_attr1,','); 
			$order_goods = [
			'order_id'=>0,
			'goods_id'=>$goods_id,
			'num'=>$num,
			'price'=>$goods_attr['price'],
			'attr_id'=>$price_attr_id,
			'attr_name'=>$other_attr1,
			'goods_name'=>$goods['name'],
			'image'=>$goods['image'],
			'jifen'=>$goods['price'],
			'fanjifen'=>intval($goods_attr['fanjifen'])
			];
			
			if($cart_info['jifen'] == null){
			    $jifen_arr[] = $goods_attr['jifen'];
			}
			
			
		      /* if($cart_info['jifen'] != null){
		        if(in_array(null,$jifen_arr)){
		            $this->ajaxReturn(['status'=>-9,'msg'=>'积分价格的商品不可与现金商品一起结算'],'JSONP');
		        }
		    } */
			if(!empty($goods_attr['price'])&&$goods_attr['price']!=0){
				$totle_money = $num*$goods_attr['price'];
			}else{
				$totle_money = $num*$goods_attr['jifen'];
			}
			M('Goods_attr')->where(array('goods_attr_id'=>$price_attr_id))->setDec('attr_kucun',$num);
			//$arr_goods[] = $order_goods;
         $r = M('Order_goods')->add($order_goods);
         $send_money = $goods['send_money'];
         if($order_goods['jifen'] !=null){
		    	$send_money = 9999;
		    }
        
        if($send_money == 9999){
			$send_money_s = 0;
		}else{
		    $send_money_s = $send_money;
		}
		$totle_money += $send_money_s;
		$is_pay = 0;
        
        
        $address = M('User_address')->where(array('uid'=>$open_id,'isDefult'=>1))->find();
		$username = $address['username'];
		$user_address = $address['areaid_1'].$address['areaid_2'].$address['areaid_3'].$address['address'];
		$phone = $address['phone'];
		$add_time = time();
		$orders = [
			'order_no'=>$order_no,
			'order_status'=>$order_status,
			'total_money'=>$totle_money,
			'send_money'=>$send_money_s,
			'pay_type'=>0,
			'is_pay'=>$is_pay,
			'open_id'=>$open_id,
			'username'=>$username,
			'user_address'=>$user_address,
			'phone'=>$phone,
			'add_time'=>$add_time,
			'remarks'=>$remarks,
			'is_pingjia'=>0,
		];
		//插入订单表
		$order_add = M('Orders')->add($orders);
		//处理插入的order_goods表中的数据 添加 order_id字段属性
		M('Order_goods')->where(array('id'=>$r))->setField('order_id',$order_add);		
		
		session('buyNow',null);
        $ip = get_client_ip();
		$this->ajaxReturn(['status'=>1,'msg'=>'成功生成订单','totle_money'=>$totle_money,'order_id'=>$order_add,'order_no'=>$order_no,'ip'=>$ip],'JSONP');
        
      }
  
	/**
	 * 获取我的订单列表
	 */
	public function getOrdersByOpenId(){
		$open_id = $this->uid;
		$page = I('page')?I('page'):null;
		if(empty($page)){
			$page = 1;
		}
		$limit = 4;
		$pages = dealPage($page,$limit);
		$orders = M('Orders')->field('order_id,order_status,total_money')->where(array('open_id'=>$open_id))->order('add_time desc')->limit($pages['begin'],$pages['over'])->select();
		foreach ($orders as &$v){
			$v['goods'] = M('Order_goods')->where(array('order_id'=>$v['order_id']))->select();
		}
		$this->ajaxReturn($orders,'JSONP');
	}
	/**
	 * 获取订单详情
	 */
	public function getOrdersByOrderId(){
	    $open_id = $this->uid;
		$order_id = I('order_id');
		$order = M('Orders')->where(array('order_id'=>$order_id))->find();
		$order['goods'] = M('Order_goods')->where(array('order_id'=>$order_id))->select();
		foreach ($order['goods'] as &$v){
			$attr = M('Attr')->where(array('attr_id'=>$v['attr_id']))->find();
			$v['attr'] = $attr['name'];
			$v['order_status'] = intval($v['order_status']);
			$tui = M('Order_tui')->where(array('order_id'=>$order_id,'goods_id'=>$v['goods_id'],'open_id'=>$open_id))->find();
			if($tui){
				$v['tui'] =$tui['status'];
			}else{
				$v['tui'] = -99;
			}
			
		}
		if($order['send_money'] == 9999){
			$order['send_money'] = 0;
		}
		$order['goods_money'] = intval($order['total_money']) - intval($order['send_money']);
		$this->ajaxReturn($order,'JSONP');
	}
	//退款展示
	public function getTuiOrder(){
	    $open_id = $this->uid;
	    $order_id = I('order_id');
	    $goods_id = I('goods_id');
	    $attr_name = I('attr_name');
	    $order = M('Orders')->where(array('order_id'=>$order_id))->find();
	    $order['goods'] = M('Order_goods')->where(array('order_id'=>$order_id,'goods_id'=>$goods_id,'attr_name'=>$attr_name))->select();
	    foreach ($order['goods'] as &$v){
	        $attr = M('Attr')->where(array('attr_id'=>$v['attr_id']))->find();
	        $v['attr'] = $attr['name'];
	        $v['order_status'] = intval($v['order_status']);
	        $tui = M('Order_tui')->where(array('order_id'=>$order_id,'goods_id'=>$v['goods_id'],'open_id'=>$open_id))->find();
	          if($tui){
				$v['tui'] =$tui['status'];
			}else{
				$v['tui'] = -99;
			}
	    }
	    if($order['send_money'] == 9999){
	        $order['send_money'] = 0;
	    }
	    $order['goods_money'] = intval($order['total_money']) - intval($order['send_money']);
	    $this->ajaxReturn($order,'JSONP');
	}
	
	
	/**
	 * 预订单展示
	 */
	public function getQuasiOrderByCartId(){
	    $open_id = $this->uid;
	    $where['open_id'] = $open_id;
	    $where['is_check'] = 1;
	    $cart_id = M("Cart")->where($where)->select();
	    foreach ($cart_id as $kk => $vv){
	        $arr[] = $vv['cart_id'];
	    }
	    $cart_id = implode(",",$arr);
		$cart_id = explode(',', $cart_id);
	   
		//$send_money = $this->config['send_money'];
		foreach ($cart_id as $v){
			$cart_info = M('Cart')->where(array('cart_id'=>$v))->find();
			$goods_attr = M('Goods_attr')->where(array('goods_attr_id'=>$cart_info['goods_attr_id']))->find();
			$goods = M('Goods')->where(array('goods_id'=>$cart_info['goods_id']))->find();
			$cart_info['price'] = $goods_attr['price'];
			$cart_info['attr_id'] = $goods_attr['attr_id'];
			$cart_info['goods_name'] = $goods['name'];
			$cart_info['image'] = $goods['image'];
			$cart_info['fanjifen'] = $goods_attr['fanjifen'];
			if($goods_attr['jifen'] == null){
			    $cart_info['jifen'] = null;
                                $totle_money += $cart_info['num']*$goods_attr['price'];
                                 $cart_info['payway'] = 1;
			}else{
			    $cart_info['jifen'] = $goods_attr['jifen'];
                                 $cart_info['payway'] = 2;
                                $totle_money += $cart_info['num']*$goods_attr['jifen'];
			}
			$list['list'][] = $cart_info;
		    $send_money[] = $goods['send_money'];
		    if($cart_info['jifen'] == null){
		        $jifen_arr[] = $cart_info['jifen'];
		    }
		    
		    
		    
		    $list['jifen'] += $cart_info['jifen']; 
		}
		if(in_array(null,$jifen_arr)){
    		if(count($cart_id) != count($jifen_arr)){
    		    $this->ajaxReturn(['status'=>-9,'msg'=>'积分价格的商品不可与现金商品一起结算'],'JSONP');
    		}
		}
		 
		 
		if(!in_array(null,$jifen_arr)){
		           $send_money[] = 9999;
		    }
		$send_money = max($send_money);
		$list['goods_money'] = $totle_money;
		$list['send_money'] = $send_money;
		if($send_money == 9999){
			$send_money_s = 0;
		}else{
		    $send_money_s = $send_money;
		}
		$list['totle_money'] = $list['goods_money']+$send_money_s;
		$address = M("User_address")->where(array('uid'=>$open_id,'isDefault'=>1))->find();
		if(!$address){
		    $list['address'] = null;
		}else{
		    $list['address'] = $address;
		}
		$this->ajaxReturn($list,'JSONP');
	}
	/**
	 * 删除订单
	 */
	public function delOrders(){
		$order_id = I('order_id');
		$open_id  = $this->uid;
		$order = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->find();
		$goods = M('Order_goods')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->select();
		foreach ($goods as &$v){
			$tui = M('Order_tui')
			         ->where(array('order_id'=>$v['order_id'],'open_id'=>$open_id,'goods_id'=>$v['goods_id']))
			        ->find();
			$arr[] = $tui['status'];
		}
		if($order['order_status'] != 3){
		    if($order['order_status'] == 4  && !in_array('0',$arr) ){
		        $re = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->save(array('order_status'=>-9));
		    }else{
		        $this->ajaxReturn(['status'=>-2,'msg'=>'该状态下无法删除订单'],'JSONP');
		    }
		   
		}else{
		    $re = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->save(array('order_status'=>-9));
		    
		}
		
		if($re){
			$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
		}else{
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}
	}
	/**
	 * 取消订单
	 */
	public function cancelOrders(){
		$order_id = I('order_id');
		$open_id  = $this->uid;
		$order = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->find();
		if($order['order_status'] != -1 || $order['is_pay'] !=0 ){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'该状态下无法取消订单'],'JSONP');
		}else{
		    $re = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->delete();
		    if($re){
		        $re = M('Order_goods')->where(array('order_id'=>$order_id))->delete();
		        
		    }
		}
		
		if($re){
			$this->ajaxReturn(['status'=>1,'msg'=>'取消成功'],'JSONP');
		}else{
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}
	}
	/**
	 * 再来一单
	 */
	public function againOrder(){
		$open_id = $this->uid;
// $open_id = 'oQCprw4jPDyL5LD5gsiLBWIdtFXU';
		$order_id = I('order_id');
// $order_id = 8;
		$list = M('Order_goods')->field('goods_id,attr_id,attr_name,num')->where(array('order_id'=>$order_id))->select();
		M()->startTrans();
		foreach($list as $k=>$v){
			$data['goods_id'] = $v['goods_id'];
			$data['goods_attr_id'] = $v['attr_id'];
			$data['attr_names'] = $v['attr_name'];
			$data['num'] = $v['num'];
			$data['is_check'] = 1;
			$data['open_id'] = $open_id;
			$re[] = M('Cart')->add($data);
		}
		if(in_array(false, $re)){
			M()->rollback();
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}else{
			M()->commit();
			$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
		}
	}

	//评价订单
	public function addPingjia(){
	   /*  $order_id  = I('order_id');
	    $data['goods_id'] = I('goods_id'); */
	    $data['open_id']  = $this->uid;
	  /*   $data['content']  = I('content');
	    $data['score']    = I('score'); */
	    $data['time']     = time();
	    $data['status']   = 1;
	    $pingjia_goods_arr = I('pingjia_goods_arr');
	    //dump($pingjia_goods_arr);die;
	    $arr1 = explode(',',$pingjia_goods_arr);
	    $arr = explode(',@a|z|j@,',$pingjia_goods_arr);
	    if(count($arr) <1 || count($arr1)/count($arr)!=5 ){
	        $this->ajaxReturn(['status'=>-6,'msg'=>'参数错误'],'JSONP');
	    }
	    foreach( $arr as $k=>$v){
	        if( !$v ){
	            unset( $arr[$k] );
	        }
	    }
	    foreach ($arr as $k=>$v){
	        if(!empty($v)){
	            $arra = explode(',',$v);
	                    //dump($vv);
	                    $data['order_id']  = $arra[3];
	                    $data['goods_id'] = $arra[2];
	                    $res1 = M("Comment")->where(array('order_id'=>$data['order_id'],'goods_id'=>$data['goods_id']))->find();
	                    if($res1){
	                        $this->ajaxReturn(['status'=>-4,'msg'=>'您已经评价过了哦'],'JSONP');
	                    }
	                    $data['content']  = $arra[1];
	                    $data['score']    = $arra[0];
                         $r = M("Order_goods og")->join('ztp_orders o ON og.order_id=o.order_id')->where(array('o.open_id'=>$data['open_id'],'og.goods_id'=>$data['goods_id'],'og.order_id'=>$data['order_id']))->find();
                    if(!$r){
                        $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
                    }
                    $re = M("Comment")->add($data);
	        }
	        $order_id = $data['order_id'];
	    }
	    if($re){
	        M("Orders")->where(array('order_id'=>$order_id))->save(array("is_pingjia"=>1));
	        $this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	    }else{
	        $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
	    }
	}
	//退货
	public function tuiOrderByorderId(){
	    $open_id = $this->uid;
		$data['order_id'] = I('order_id');
		$data['goods_id'] = I('goods_id');
		$data['attr_name'] = I('attr_name');
		$data['open_id'] = $open_id;
		$res = M('Order_tui')->where($data)->find();
		$data['content'] = I('content');
		$data['reason'] = I('reason');
		$data['time'] = time();
		$data['status'] = 0;
		if($res){
		    $this->ajaxReturn(['status'=>-8,'msg'=>'您已经进行过次操作，请勿重复操作'],'JSONP');
		}
		$re = M('Order_tui')->add($data);
		if($re){
		    $res = M('Orders')->where(array('order_id'=>$data['order_id'],'open_id'=>$open_id))->find();
		    if($res['order_status'] != 4){
		        $res = M('Orders')->where(array('order_id'=>$data['order_id'],'open_id'=>$open_id))->save(array('order_status'=>4));
		    }else{
		    	$res =1;
		    }
			
		      if($res){
			     $this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
		      }else{
			     $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙'],'JSONP');
		      }
		}else{
			     $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		      }
	}
	// 按订单状态获取列表
	public function getOrderByOrderStatus(){
	    /*-1未支付
	    1已支付
	    2配送中
	    3已完成
	    4售后
	    is_pingjia 0待评价
	     */
	    $open_id = $this->uid;
		$order_status = I('order_status');
		$is_pingjia = I('is_pingjia');
		if($is_pingjia){
			$where['order_status'] = 3;
			$where['is_pingjia'] = 0;
		}
		if($order_status){
			$where['order_status'] = $order_status;
		}
		if(!$order_status && !$is_pingjia){
		    $where['order_status'] = array('neq','-9');
		}
		
		$where['open_id'] = $open_id;
		$list = M('Orders')->where($where)->order('add_time desc')->select();
		foreach ($list as &$v){
		    $v['goods'] = M('Order_goods')->where(array('order_id'=>$v['order_id']))->select();
		}
		
		$this->ajaxReturn($list,'JSONP');
	}
	//确认收货
	public function receiveGoods(){
		$order_id = I('order_id');
		$open_id = $this->uid;
		$res = M('Orders')->where(array('order_id'=>$order_id))->find();
		if($res['open_id'] != $open_id || $res['order_status'] != 2){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}
		$data['order_status'] = 3;
		$data['receive_time'] = time();
 		$r = M('Orders')->where(array('order_id'=>$order_id,'open_id'=>$open_id))->save($data);
 		if($r){
 		    $goods = M('Order_goods')->where('order_id ='.$order_id)->select();
 		    foreach ($goods as &$v){
 		        $add['year'] = date('y',time());
 		        $add['month'] = date('m',time());
 		        $add['goods_id'] = $v['goods_id'];
 		        $add['count'] = $v['num'];
 		        $month_count = M('Count_month')->where(array('goods_id'=>$v['goods_id'],'year'=>$add['year'],'month'=>$add['month']))->find();
 		        if($month_count){
 		            M('Count_month')->where('count_id ='.$month_count['count_id'])->setInc('count',$v['num']);
 		        }else{
 		        	M('Count_month')->add($add);
 		        }
 		        
 		    }
 		    $this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
 		}else{
 		    $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
 		}
	}
	//退货列表
	public function getTuiOrderList(){
		$open_id = $this->uid;
		$order_id = I('order_id');
		if($order_id){
			$where['order_id'] = $order_id;
		}else{
		    $where['order_status'] = 4;
		}
		
		$where['open_id'] = $open_id;
		$list = M('Orders')->where($where)->order('add_time desc')->select();
		
		foreach ($list as $k=>$v){
		    $list[$k]['goods'] = M('Order_tui ot')
		    ->join('ztp_order_goods og ON ot.goods_id=og.goods_id and ot.order_id=og.order_id and ot.attr_name=og.attr_name')
		    ->join('ztp_goods g ON ot.goods_id=g.goods_id')
		    ->where(array('ot.order_id'=>$v['order_id']))
		    ->find();
		    if($v['pay_type'] == 3){
		    	$list[$k]['goods']['price'] = $list[$k]['goods']['jifen'];
		    }
		    
		     if($list[$k]['goods']['status'] == '1'){
		        $list[$k]['goods']['status'] = "退款成功";
		    }
		    if($list[$k]['goods']['status'] == '-1'){
		        $list[$k]['goods']['status'] = "退款驳回";
		    }
		    if($list[$k]['goods']['status'] == '0'){
		        $list[$k]['goods']['status'] = "退款申请中";
		    } 
		    //echo M('Order_tui')->_sql();
		    //dump($list[$k]['goods']['status']);
		}
		
		$this->ajaxReturn($list,'JSONP');
	}
  public function getStatusByOrderNo(){
  	$a = Vendor('WxPays.lib.WxPayApi');
    	 Vendor('WxPays.example.log');
    	$v = Vendor('WxPays.lib.WxPayData');
          function printf_info($data)
          {
              foreach($data as $key=>$value){
                  echo "<font color='#f00;'>$key</font> : $value <br/>";
              }
          }
    	$out_trade_no = $_REQUEST["out_trade_no"];
	$input = new WxPayOrderQuery();
	$input->SetOut_trade_no($out_trade_no);
	printf_info(WxPayApi::orderQuery($input));
	exit();
  }
	
}