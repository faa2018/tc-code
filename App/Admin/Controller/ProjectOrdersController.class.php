<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
use Common\Model\OrderModel;
use Common\Model\GoodsModel;

/**  
* 订单管理
*/
class ProjectOrdersController extends AdminBaseController{
	public function _initialize(){
		parent::_initialize();
	}
    /**
     * 订单列表
     */
    public function index(){
		$type = I('type')?I('type'):null;
		if(!empty($type)){
			$where['order_status']=$type;
		}else{
		    $where['order_status'] = array('neq','-9');
		}
		$order_no = I('order_no');
		if(!empty($order_no)){
			$where['order_no']=['like','%'.$order_no.'%'];
		}
		$count=M('Project_orders')->where($where)->order('create_time desc')->count();
		$Page= new \Org\Nx\Page($count,20);
		setPageParameter($Page, array('type'=>$type,'order_no'=>$order_no));
		$show       = $Page->show();
		$list =  M('Project_orders')->where($where)
		->limit($Page->firstRow.','.$Page->listRows)->order('create_time desc')->select();
		$ORDER_TYPE = C('ORDER_TYPE');
		foreach ($list as $k=>$v){
			$og = M('Project_og')->where(array('order_id'=>$v['order_id']))->select();
			foreach($og as $kk=>$vv){
				$list[$k]['goods'] = M('Project_goods')->where(['project_goods_id'=>$vv['goods_id']])->find();
			}
			$list[$k]['order_status']= $ORDER_TYPE[$v['order_status']];
			$list[$k]['username']= M('User')->where(['uid'=>$v['uid']])->find()['username'];
			
		}
		//dump($list);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();

    }
	/**
	 * 查看订单详情或发货
	 */
	public function details(){
		$order_id = I('order_id');
        $list = M('Project_orders')->where(array('order_id'=>$order_id))->find();
        $og = M('Project_og')->where(array('order_id'=>$list['order_id']))->select();
		//dump($og);
        foreach($og as $k=>$v){
        	$og[$k]['name'] = M('Project_goods')->where(['project_goods_id'=>$v['goods_id']])->find()['name'];
			$og[$k]['image'] = M('Project_goods')->where(['project_goods_id'=>$v['goods_id']])->find()['image'];
$og[$k]['num'] =$v['num'];				
        }
		//dump($og);
		$ORDER_TYPE = C('ORDER_TYPE');
		$list['order_status']= $ORDER_TYPE[$list['order_status']];
       $list['username'] = M('User')->where(['uid'=>$list['uid']])->find()['username'];
        $this->assign('list',$list);
        $this->assign('goods',$og);
		//dump($list);
        $this->display();
	}
	public function setStatus(){
		$order_id = I('order_id');
        $list = M('Project_orders')->where(array('order_id'=>$order_id))->setField('order_status',2);
		$this->success('操作成功');
	}
	//下载表格
	public function to_download(){
	    $status = I('status');
 $where['p.order_status'] = $status;   
	    if($status == ''){
	    	$where = [];
	    }
	    
	    $str = "编号,商品名,用户名,真实姓名,订单号,数量,订单金额,地址,添加时间,状态\n";
	    $str = iconv('utf-8','gb2312',$str);
	    //$list = M('Project_orders')->where($where)->order('create_time desc')->select();
    	$ORDER_TYPE = C('ORDER_TYPE');
    	$PAY_TYPE = C('USER_MONEY_TYPE');
		$list = M('Project_og o')
		->join('ztp_project_orders p ON o.order_id = p.order_id')
		->where(['o.order_id = p.order_id'])
		->where($where)
		->order('create_time desc')
		->select();	
    	foreach ($list as $k => $v){
    		$list[$k]['status'] = $ORDER_TYPE[$v['order_status']];
    		$list[$k]['username'] = M("User")->where(array('uid'=>$v['uid']))->find()['username'];
			$list[$k]['real_name'] = M("User")->where(array('uid'=>$v['uid']))->find()['real_name'];
			$list[$k]['goodsname'] = M('Project_goods')->where(['project_goods_id'=>$v['goods_id']])->find()['name'];
			$money = M('Project_goods')->where(['project_goods_id'=>$v['goods_id']])->find()['price'];
			$list[$k]['money_zong'] = $money*$v['num'];
			
    	}
	    foreach($list as $k=>$v){
	        $xuhao = iconv('utf-8','gb2312',$v['order_id']);
	        $goodsname = iconv('utf-8','gb2312',$v['goodsname']);
	        $userName = iconv('utf-8','gb2312',$v['username']);
			$num = iconv('utf-8','gb2312',$v['num']);
			$real_name = iconv('utf-8','gb2312',$v['real_name']);
	        $order_num = iconv('utf-8','gb2312',$v['order_no']);
	        $order_money = iconv('utf-8','gb2312',$v['money_zong']);
	        $address = iconv('utf-8','gb2312',$v['address']);
	        $add_time = iconv('utf-8','gb2312',date("Y-m-d H:i:s",$v['create_time']));
	        $status = iconv('utf-8','gb2312',$v['status']);
	        
	        $str .= $xuhao.",".$goodsname.",".$userName.",".$real_name.",".$order_num.",".$num.",".$order_money.",".$address.",".$add_time.",".$status."\n";
	    }
	    $filename = '订单数据表.csv';
	    export_csv($filename,$str);
	}
}
