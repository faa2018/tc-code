<?php
namespace Common\Model;
use Think\Model;
class OrderModel extends Model
{
	/**
	 * 添加订单表
	 * @param int $uid     	    会员id
	 * @param int $good_id		对应产品id
	 * @param float $money		总金额
	 * @param int $pay_money	支付币种 1 人民币
	 * @param int $pay_type		支付方式 1 线上支付 账户扣款
	 * @return 添加成功返回成功id
	 */
    public function addOrder($uid,$good_id,$money,$address,$name,$phone,$num){
    	$data['uid']       = $uid;
    	$data['good_id']   = $good_id;
    	$data['order_num'] = $this->getOrdernum($uid);
    	$data['pay_type']  = 3;//积分
    	$data['money']     = $money;
    	$data['add_time']  = time();
    	$data['status']    = 0;
    	$data['address']  = $address;
    	$data['name']    = $name;
    	$data['phone']    = $phone;
    	$data['num']    = $num;
    	return M('Order')->add($data);
    }
    //查看所有订单
    public function getOrder($where=array(),$limit=15){
    	$count      = M('Orders')->where($where)->count();
    	$Page       = new \Org\Nx\Page($count,$limit);
    	$list = M('Orders')->where($where)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    	$ORDER_TYPE = C('ORDER_TYPE');
    	$PAY_TYPE = C('USER_MONEY_TYPE');
    	$GOODS = new GoodsModel();
    	foreach ($list as $k => $v){
    		//$list[$k]['num'] = round($v['money']/$price);
    		$list[$k]['status'] = $ORDER_TYPE[$v['order_status']];
//     		$list[$k]['username'] = M("User")->where(array('uid'=>$v['uid']))->find()['username'];
//     		$list[$k]['goodsname'] = M("Goods")->where(array('id'=>$v['good_id']))->find()['title'];
//     		$list[$k]['pay_type'] = $PAY_TYPE[$v['pay_type']];
    	}
    	$list_re['page'] = $Page->show();
    	$list_re['list'] = $list;
    	return $list_re;
    }
    //查看所有订单
    public function getProjectOrder($where=array(),$limit=15){
        $count      = M('Project_orders')->where($where)->count();
        $Page       = new \Org\Nx\Page($count,$limit);
        $list = M('Project_orders')->where($where)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $ORDER_TYPE = C('ORDER_TYPE');
        $PAY_TYPE = C('USER_MONEY_TYPE');
        $GOODS = new GoodsModel();
        foreach ($list as $k => $v){
            //$list[$k]['num'] = round($v['money']/$price);
            $list[$k]['status'] = $ORDER_TYPE[$v['order_status']];
//          $list[$k]['username'] = M("User")->where(array('uid'=>$v['uid']))->find()['username'];
//          $list[$k]['goodsname'] = M("Goods")->where(array('id'=>$v['good_id']))->find()['title'];
//          $list[$k]['pay_type'] = $PAY_TYPE[$v['pay_type']];
        }
        $list_re['page'] = $Page->show();
        $list_re['list'] = $list;
        return $list_re;
    }
    //分页
    public function Page($where = array(),$limit = 25){
    	$count      = M('Orders')->where($where)->count();
    	$Page       = new \Org\Nx\Page($count,$limit);
    	setPageParameter($Page, array('order_num'=>$where['order_num']));
    	$show       = $Page->show();
    	return $show;
    }
    //查看订单详情
    public function getOrderById($id){
    	$list = M('Orders')->where(array('id'=>$id))->find();
    	$list['goodsname'] = M('Goods')->where(array('id'=>$list['good_id']))->find()['title'];//商品名
    	$list['username'] = M('User')->where(array('uid'=>$list['uid']))->find()['username'];//用户名
    	$pay_type = C('USER_MONEY_TYPE');
    	$order_type = C('ORDER_TYPE');
    	$list['order_status'] = C('ORDER_TYPE');
    	$list['pay_type'] = $pay_type[$list['pay_type']];//支付方式
    	$list['order_type'] = $order_type[$list['status']];//订单状态
    	return $list;
    }
     //查看订单详情
    public function getProjectOrderById($id){
        $list = M('Project_orders')->where(array('order_id'=>$id))->find();
        $list['goodsname'] = M('Project_goods')->where(array('id'=>$list['good_id']))->find()['title'];//商品名
        $list['username'] = M('User')->where(array('uid'=>$list['uid']))->find()['username'];//用户名
        $pay_type = C('USER_MONEY_TYPE');
        $order_type = C('ORDER_TYPE');
        $list['order_status'] = C('ORDER_TYPE');
        $list['pay_type'] = $pay_type[$list['pay_type']];//支付方式
        $list['order_type'] = $order_type[$list['status']];//订单状态
        return $list;
    }
	/**
	 * 修改订单状态
	 * @param unknown $id             	id
	 * @param unknown $status			要修改的状态
	 * @return Ambigous <boolean, unknown>
	 */
    public function updateOrder($id,$data){
    	return M('Orders')->where(array('id'=>$id))->save($data);
    }
    /**
     * 修改订单状态
     * @param unknown $id               id
     * @param unknown $status           要修改的状态
     * @return Ambigous <boolean, unknown>
     */
    public function updateProjectOrder($id,$data){
        return M('Project_orders')->where(array('id'=>$id))->save($data);
    }
    //生成订单号
    public function getOrdernum($uid){
    	$ordernum = $uid.time();
    	$order = M('Orders')->where(array('order_num'=>$ordernum))->find();
    	if($order){
    		$this->getOrdernum($uid);
    	}
    	return $ordernum;
    }
}