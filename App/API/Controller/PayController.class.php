<?php
namespace API\Controller;
use Common\Model\UserRelationModel;
use Common\Api\UserMoneyApi;
class PayController extends UserController {
 	
	public function _initialize(){
		
 		parent::_initialize();
	}
	/**
	 * 确定支付
	 *
	 */
	public function pay(){
		//扣除会员购车劵
		//支付成功
		//修改订单状态
		$open_id = $_SESSION['HOME_USER_ID'];
		$order_id = I('order_id');
		$pay_type = I('pay_type');
		$order = M('Orders')->where(array('order_id'=>$order_id))->find();
		if(!$order){
			$this->ajaxReturn(['status'=>-1,'msg'=>'不存在此订单']);
		}
		if($open_id!=$order['open_id']){
			$this->ajaxReturn(['status'=>-2,'msg'=>'此订单不是登录用户']);
		}
		if($order['is_pay'] ==1 && $order['order_status'] != -1){
			$this->ajaxReturn(['status'=>-3,'msg'=>'此订单已付款，请勿重复提交']);
		}
	
		$order['goods'] = M('Order_goods')->where(array('order_id'=>$order_id))->select();
	
		foreach ($order['goods'] as &$v){
			$all_money += $v['num'] * $v['price'];
			$all_jifen += $v['num'] * $v['jifen'];
			$all_fanjifen += $v['num'] * $v['fanjifen'];
		}
	
		$member = M('User_money'.cuttable($open_id))->where(array('uid'=>$open_id,'type'=>2))->find();
		if($order['send_money'] == 9999){
			$order['send_money'] = 0;
		}
		//1微信 2支付宝 3购车劵
		if($pay_type == 3 ){
			if($all_jifen == 0){
				$this->ajaxReturn(['status'=>-3,'msg'=>'购车劵不可用']);
			}
			if($member['num'] < $all_jifen+$order['send_money']){
				$this->ajaxReturn(['status'=>-4,'msg'=>'您的购车劵不足']);
			}
			M()->startTrans();
			$re[] = M('User_money'.cuttable($open_id))->where(array('uid'=>$open_id,'type'=>2))->setDec('num',$all_jifen+$order['send_money']);
			//状态1，支付完成，等待发货
			$re[] = M('Orders')->where(array('order_id'=>$order_id))->save(array('order_status'=>1,'is_pay'=>1,'pay_type'=>3,'pay_time'=>time(),'total_money'=>$all_jifen+$order['send_money']));
			if(in_array(false, $re)){
				M()->rollback();
				$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试']);
			}else{
				M()->commit();
				$this->ajaxReturn(['status'=>1,'msg'=>'支付成功']);
			}
		}
	}
	/**
	 * 获取支付金额
	 */
	public function getPayMoney(){
		$open_id = $this->uid;
		$order_id = I('order_id');
		$order = M('Orders')->where(array('order_id'=>$order_id))->find();
		if($order['send_money'] == 9999){
		    $order['send_money'] = 0;
		}
		if(!$order){
		    $this->ajaxReturn(['status'=>-1,'msg'=>'不存在此订单'],'JSONP');
		}
		if($open_id!=$order['open_id']){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'此订单不是登录用户'],'JSONP');
		}
		$member = M('User_money'.cuttable($open_id))->where(array('uid'=>$this->uid,'type'=>2))->find();
		$order['goods'] = M('Order_goods')->where(array('order_id'=>$order_id))->select();
		foreach ($order['goods'] as &$v){
		    $all_money += $v['num'] * $v['price']; 
		    $all_jifen += $v['num'] * $v['jifen']; 
		}
		if($all_jifen == 0){
		        $this->ajaxReturn(['status'=>-4,'msg'=>'购车劵不可用','jifen'=>$member['num']],'JSONP');
		}
		if($all_money == 0){
			$this->ajaxReturn(['status'=>-6,'msg'=>'余额不可用','jifen'=>$member['num']],'JSONP');
		}
            if($all_money == 0&&$member['num'] < $all_jifen+$order['send_money']){
		        $this->ajaxReturn(['status'=>-5,'msg'=>'购车劵不足','jifen'=>$member['num']],'JSONP');
            }
		  $this->ajaxReturn(['status'=>1,'msg'=>'你可通过任意方式付款','jifen'=>$member['num']],'JSONP');
	}
	/**
	 * 确定支付
	 * 
	 */
	public function toPay(){
		//扣除会员购车劵
		//支付成功
		//修改订单状态
		$open_id = $this->uid;
		$order_id = I('order_id');
		$pay_type = I('pay_type');
		$order = M('Orders')->where(array('order_id'=>$order_id))->find();
		if(!$order){
		    $this->ajaxReturn(['status'=>-1,'msg'=>'不存在此订单'],'JSONP');
		}
		if($open_id!=$order['open_id']){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'此订单不是登录用户'],'JSONP');
		}
		if($order['is_pay'] ==1 && $order['order_status'] != -1){
		    $this->ajaxReturn(['status'=>-3,'msg'=>'此订单已付款，请勿重复提交'],'JSONP');
		}
		
		$order['goods'] = M('Order_goods')->where(array('order_id'=>$order_id))->select();
		
		foreach ($order['goods'] as &$v){
		    $all_money += $v['num'] * $v['price']; 
		    $all_jifen += $v['num'] * $v['jifen']; 
		    $all_fanjifen += $v['num'] * $v['fanjifen']; 
		}
		
		$member = M('User_money'.cuttable($open_id))->where(array('uid'=>$this->uid,'type'=>2))->find();
		if($order['send_money'] == 9999){
			$order['send_money'] = 0;
		}
		//1微信 2余额 3购车劵
		if($pay_type == 3 ){
		    if($all_jifen == 0){
		        $this->ajaxReturn(['status'=>-4,'msg'=>'购车劵不可用'],'JSONP');
		    }
		    if($member['num'] < $all_jifen+$order['send_money']){
		        $this->ajaxReturn(['status'=>-5,'msg'=>'您的购车劵不足'],'JSONP');
		    }
		    
		    $re[] = M('User_money'.cuttable($open_id))->where(array('uid'=>$this->uid,'type'=>2))->setDec('num',$all_jifen+$order['send_money']);
		    //状态1，支付完成，等待发货
		    $re[] = M('Orders')->where(array('order_id'=>$order_id))->save(array('order_status'=>1,'is_pay'=>1,'pay_type'=>3,'pay_time'=>time(),'total_money'=>$all_jifen+$order['send_money']));
		    $re[] = $this->addFinance($this->uid,1011,2,$all_jifen+$order['send_money'],'购车劵购买商品',2);
		    if(in_array(false, $re)){
		        M()->rollback();
		        $this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		    }else{
		        M()->commit();
		        $this->ajaxReturn(['status'=>1,'msg'=>'支付成功'],'JSONP');
		    }
		}
	}
	//记录 自动程序的表
	private function addProject($uid,$money,$money_type=2,$ranking){
		$data['uid'] 		= $uid;
		$data['money'] 		= $money;
		$data['money_type'] = $money_type;
		$data['status']     = 0;
		$data['add_time'] 	= time();
		$data['ranking'] 	= $ranking;
		$r = M('Project_user_auto')->add($data);
		return $r;
	}
	//检测是否出局
	private function checkOut($id){
		//本人
		$log = M('Project_user_auto')->where(['id'=>$id])->find();
		if(($log['ranking']-1)%($this->config['out_num'])==0){
			//该出局的人
			$where_chuju['ranking'] = ($log['ranking']-1)/$this->config['out_num'];
			$chuju_user = M('Project_user_auto')->where($where_chuju)->find();
			//dump($chuju_user);die;
			//将出局人 自动排在下边
			$ranking = M('Project_user_auto')->order('id desc')->find()['ranking'];			$project_log = M('Project_user_auto')->order('id desc')->find();
			if($project_log){
				$ranking = $project_log['ranking'];
			}
			$re[] = $this->addProject($chuju_user['uid'],$chuju_user['money'],2,$ranking+1);
			//出局次数
			$where_user['uid'] = $chuju_user['uid'];
			$re[] = M('User')->where($where_user)->setInc('out_num',1);
			//修改出局人状态 查询之前是否出局		
			$level = M('User')->where($where_user)->find()['level'];
			if($level!=1){
				$re[] = M('User')->where($where_user)->setField('level',1);
			}			
			$data['uid'] = $chuju_user['uid'];
			$data['add_time'] = time();
			$re[] = M('Project_out')->add($data);
			if(in_array(false, $re)){
				 M()->rollback();
				 return false;
			}else{
				M()->commit();
				return true;
			}
		}else{
			return true;
		}
	}

}