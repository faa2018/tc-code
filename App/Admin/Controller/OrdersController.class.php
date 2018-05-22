<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseControllerController;
use Think\Page;
class OrdersController extends AdminBaseController {
    public function _initialize(){
        parent::_initialize();
    }
	public function index(){
		$order_no = I('order_no')?I('order_no'):null;
		$type = I('type')?I('type'):null;
		if(!empty($order_no)){
			$where['order_no']=array('like','%'.$order_no.'%');
		}
		if(!empty($type)){
			$where['order_status']=$type;
		}else{
		    $where['order_status'] = array('neq','-9');
		}
		$count=M('Orders')->where($where)->order('add_time desc')->count();
		$Page=new Page($count,30);
		//setPageParameter($Page, array('account'=>$account));
		$show       = $Page->show();
		$list =  M('Orders')->where($where)
		->limit($Page->firstRow.','.$Page->listRows)->order('add_time desc')->select();
		foreach ($list as &$v){
			$nickname = M('User')->where(array('uid'=>$v['open_id']))->find();
			$v['nickname'] = $nickname['username'];
			if($v['send_money'] == '9999.00'){
			    $v['send_money'] = "邮费到付";
			}
			if($v['send_money'] == '0.00'){
			    $v['send_money'] = "包邮";
			}
		}
		//dump($list);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	public function detail(){
        $order_id = I('order_id');
        $is_tui  = I('is_tui');
        $list = M('Orders o')
                ->field('o.*,o.add_time as oadd_time,m.*')
                ->join('ztp_user m ON m.uid=o.open_id')
                ->where('o.order_id ='.$order_id)
                ->find();
        $goods = M('Order_goods og')
                ->field("og.*,g.*")
                ->join('ztp_goods g ON og.goods_id=g.goods_id')
                //->join('ztp_goods_attr ga ON og.attr_id=ga.goods_attr_id')
                ->where('og.order_id ='.$order_id)
                ->select();
        if($is_tui){
        	$goods_id = I('goods_id');
            $list = M('Orders o')
            ->field('o.*,o.add_time as oadd_time,m.*,ot.*')
            ->join('ztp_user m ON m.uid=o.open_id')
            ->join('ztp_order_tui ot ON ot.order_id=o.order_id')
            ->where(array('o.order_id'=>$order_id,'order_status' =>4))
            ->find();
            //dump($list);die;
            $goods = M('Order_goods og')
            ->field("og.*,g.*")
            ->join('ztp_goods g ON og.goods_id=g.goods_id')
            //->join('ztp_goods_attr ga ON og.attr_id=ga.goods_attr_id')
            /* ->join('ztp_order_tui ot ON ot.order_id=o.order_id,ot.goods_id=og.goods_id') */
            ->where(array('og.order_id'=>$order_id,'og.goods_id'=>$goods_id))
            ->select();
            foreach ($goods as &$v){
                $tui = M('Order_tui')->where(array('order_id'=>$v['order_id'],'goods_id'=>$v['goods_id'],'attr_name'=>$v['attr_name']))->find();
                if($tui){
    				$v['tui'] =$tui['status'];
    			}else{
    				$v['tui'] = -99;
    			}
    			if($v['tui'] == 0){
    			    $v['tui'] = '已发起申请';
    			}
    			if($v['tui'] == 1){
    			    $v['tui'] = '同意申请退款';
    			}
    			if($v['tui'] == -1){
    			    $v['tui'] = '拒绝申请退款';
    			}
    			if($v['tui'] == -99){
    			    $v['tui'] = '尚未发起申请';
    			}
            }
            $this->assign('tui',1);
        }
        if($list['send_money'] == '9999.00'){
            $list['send_money'] = "邮费到付";
        }
        if($list['send_money'] == '0.00'){
            $list['send_money'] = "包邮";
        }
        $this->assign('list',$list);
        $this->assign('goods',$goods);
        $this->display();
	}
	public function setStatus(){
		$order_id = I('order_id');
		$kuaidi = I('kuaidi');
		$is_tui = I('is_tui');
		$re = M('Orders')->where('order_id ='.$order_id)->find();
		if($is_tui == 1){
		    $goods_id = I('goods_id');
			if($re['order_status'] !=4){
			    $this->error('参数错误');
			}
			$res = M('Order_tui')->where(array('order_id' =>$order_id,'goods_id'=>$goods_id))->save(array('status'=>1));
		}else{
		    
		    if($re['order_status'] == 2 && $re['kuaidi'] == $kuaidi){
		        $this->error('已经是发货状态了！');
		    }else{
		        
		        if($re['order_status'] == 1 && $kuaidi == ''){
		            $this->error("请选择是否发货并填写快递单号");
		        }
		    	
		    }
		    if($re['kuaidi'] == ''){
		        $time = time();
		        $res  = M('Orders')->where('order_id ='.$order_id)->save(array('order_status'=>2,'kuaidi'=>$kuaidi,'send_time'=>$time));
		    }
		    if($re['kuaidi']){
		        $res  = M('Orders')->where('order_id ='.$order_id)->save(array('kuaidi'=>$kuaidi));
		    }
		}
		if($res){
		    $this->error('操作成功',U('Admin/Orders/index'));
		}else{
		    $this->error('操作失败');
		}
	}
	//退款列表
	public function tuiIndex(){
	    /*  $order_no = I('order_no')?I('order_no'):null;
	   // $type = I('type')?I('type'):null;
	    if(!empty($order_no)){
	        $where['order_no']=array('like','%'.$order_no.'%');
	    }
	        $where['order_status']=4;
	    
	    $count=M('Orders')->where($where)->order('add_time desc')->count();
	    $Page=new Page($count,30);
	    //setPageParameter($Page, array('account'=>$account));
	    $show       = $Page->show();
	    $list =  M('Orders')->where($where)
	    ->limit($Page->firstRow.','.$Page->listRows)->order('add_time desc')->select();
	    foreach ($list as &$v){
	        $nickname = M('User')->where(array('uid'=>$v['open_id']))->find();
	        $v['nickname'] = $nickname['username'];
	    if($v['send_money'] == '9999.00'){
			    $v['send_money'] = "邮费到付";
			}
			if($v['send_money'] == '0.00'){
			    $v['send_money'] = "包邮";
			}
	    }  */
		 $order_no = I('order_no')?I('order_no'):null;
		 $where = '';
		if($order_no){
		$where['order_no']=array('like','%'.$order_no.'%');
		}
		$count=M('Order_tui ot')
		//->join('think_card ON think_artist.card_id = think_card.id')
		->join('ztp_orders o ON ot.order_id = o.order_id')
		->where('o.order_status != -9')
		->order('time desc')
		->count();
		$Page=new Page($count,30);
		//setPageParameter($Page, array('account'=>$account));
		$show       = $Page->show();
		$list =  M('Order_tui ot')
		->join('ztp_orders o ON ot.order_id = o.order_id')
		->where('o.order_status != -9')
		->where($where)
		->limit($Page->firstRow.','.$Page->listRows)->order('time desc')->select();
		foreach ($list as &$v){
			$order = M('Orders')->where(['order_id'=>$v['order_id']])->find();
			$nickname = M('User')->where(array('uid'=>$order['open_id']))->find();
			$v['nickname'] = $nickname['username'];
			$v['order_status'] = $order['order_status'];
			$v['order_no'] = $order['order_no'];
		}
	    //dump($list);die;
	    $this->assign('list',$list);
	    $this->assign('page',$show);
	    $this->display();
	}
	//退款操作
	public function setTuiStatus(){
		$where['goods_id'] = I('goods_id');
		$where['attr_name'] = I('attr_name');
		$where['order_id'] = I('order_id');
		$where['open_id'] = I('open_id');
		$q = I('q');
		$j = I('j');
		if($q == 1){
			$status = 1;
		}
		if($j == 1){
			$status = -1;
		}
		$list = M('Order_tui ot')
		->join('ztp_orders o ON ot.order_id = o.order_id')
		->where(['ot.order_id'=>I('order_id')])
		->find();
		if($list['pay_type']==3){
			$re[] = M('User_money'.cuttable($list['open_id']))->where(array('uid'=>$list['open_id'],'type'=>2))->setDec('num',$list['total_money']);
			$re[] = $this->addFinance($list['open_id'],101,1,$list['total_money'],'退款返还积分',2);
		}
		$re[] = M('Order_tui')->where($where)->save(array('status'=>$status));
		if($re){
			$data['status'] = 1;
			$data['msg'] = "操作成功，请尽快处理";
			$this->ajaxReturn($data);
		}else{
		    $data['status'] = -1;
		    $data['msg'] = "操作失败，请稍后再试";
		    $this->ajaxReturn($data);
		}
	}
}