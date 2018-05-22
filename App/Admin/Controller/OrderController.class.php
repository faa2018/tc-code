<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
use Common\Model\OrderModel;
use Common\Model\GoodsModel;

/**  
* 订单管理
*/
class OrderController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
    /**
     * 订单列表
     */
    public function showOrderList(){
		$order_num = I('order_num')?I('order_num'):'';
		if(!empty($order_num)){
			$where['order_num'] = array('like','%'.$order_num.'%');
		}
		$ORDER = new OrderModel();
		$list = $ORDER->getOrder($where,15);
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
    	$this->display();

    }
	/**
	 * 查看订单详情或发货
	 */
	public function getOrderDetails(){
		$ORDER = new OrderModel();
		$id = I('id');
		if(IS_POST){
			//接收数据
			$data['status']    =  I('status');
			$data['express']    =  I('express');
			$data['number']    =  I('number');
			$list = $ORDER->updateOrder($id,$data);
			if($list){
				$this->success('操作成功',U('Order/showOrderList'));
			}else{
				$this->success('系统繁忙');
			}
		}else{
			//修改页面显示
			$list = $ORDER->getOrderById($id);
			$this->assign('list',$list);
			$this->display();
		}
	}
	//下载表格
	public function to_download(){
	    $status = I('status');
	    $where['status'] = $status;
	    if($status == ''){
	    	$where = [];
	    }
	    
	    $str = "编号,商品名,用户名,订单号,支付方式,订单金额,地址,添加时间,状态\n";
	    $str = iconv('utf-8','gb2312',$str);
	    $list = M('Order')->where($where)->order('add_time desc')->select();
    	$ORDER_TYPE = C('ORDER_TYPE');
    	$PAY_TYPE = C('USER_MONEY_TYPE');
    	foreach ($list as $k => $v){
    		$list[$k]['status'] = $ORDER_TYPE[$v['status']];
    		$list[$k]['username'] = M("User")->where(array('id'=>$v['uid']))->find()['username'];
    		$list[$k]['goodsname'] = M("Goods")->where(array('id'=>$v['good_id']))->find()['title'];
    		$list[$k]['pay_type'] = $PAY_TYPE[$v['pay_type']];
    	}
	    //echo M('Orders')->_sql();die;
	    //dump($list);die;
	    foreach($list as $k=>$v){
	        
	        if($v['orderStatus'] == 1){
	            $v['orderStatus']= "已发货";
	        }if($v['orderStatus'] == 0){
	            $v['orderStatus']= "等待处理";
	        }
	        $xuhao = iconv('utf-8','gb2312',$v['id']);
	        $goodsname = iconv('utf-8','gb2312',$v['goodsname']);
	        $userName = iconv('utf-8','gb2312',$v['username']);
	        $order_num = iconv('utf-8','gb2312',$v['order_num']);
	        $pay_type = iconv('utf-8','gb2312',$v['pay_type']);
	        $money = iconv('utf-8','gb2312',$v['money']);
	        $address = iconv('utf-8','gb2312',$v['address']);
	        $add_time = iconv('utf-8','gb2312',date("Y-m-d H:i:s",$v['add_time']));
	        $status = iconv('utf-8','gb2312',$v['status']);
	        
	        $str .= $xuhao.",".$goodsname.",".$userName.",".$order_num.",".$pay_type.",".$money.",".$address.",".$add_time.",".$status."\n";
	    }
	    $filename = '订单数据表.csv';
	    export_csv($filename,$str);
	}
}
