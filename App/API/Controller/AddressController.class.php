<?php
/*********
*    首页控制器
*/
namespace API\Controller;
class AddressController extends UserController {
 	public function _initialize(){
 		parent::_initialize();
 		
 	}
/*********以下是接口*********/
 	/**
 	 * 获取用户地址
 	 */
	public function getUserAddress(){
		$uid = $this->uid;
		$address = M('User_address')->where(array('uid'=>$uid))->select();
		$this->ajaxReturn($address,'JSONP');
	}
	public function addUserAddress(){
		$uid = $this->uid;
		$username = I('username');
		$phone = I('phone');
		$areaid_1 = I('areaid_1');
		$areaid_2 = I('areaid_2');
		$areaid_3 = I('areaid_3');
		$address = I('address');
		$address_id = I('address_id');
		if(empty($username)){
			$this->ajaxReturn(['status'=>-1,'msg'=>'请输入收件人姓名'],'JSONP');
		}
		if(empty($phone)){
			$this->ajaxReturn(['status'=>-2,'msg'=>'请输入收件人电话'],'JSONP');
		}
		if(!regex($phone,'phone')){
    		$data_return['status'] = -2;
	        $data_return['msg']   = '手机号格式不正确';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
		if(empty($areaid_1)){
			$this->ajaxReturn(['status'=>-3,'msg'=>'请选择省份'],'JSONP');
		}
		if(empty($areaid_2)){
			$this->ajaxReturn(['status'=>-4,'msg'=>'请选择城市'],'JSONP');
		}
		if(empty($areaid_3)){
			$this->ajaxReturn(['status'=>-5,'msg'=>'请选择地级县'],'JSONP');
		}
		if(empty($address)){
			$this->ajaxReturn(['status'=>-6,'msg'=>'请输入详细地址'],'JSONP');
		}
		$address_data = [
			'uid'=>$uid,
			'username'=>$username,
			'phone'=>$phone,
			'areaid_1'=>$areaid_1,
			'areaid_2'=>$areaid_2,
			'areaid_3'=>$areaid_3,
			'add_time'=>time(),
			'address'=>$address,
		];
		if($address_id){
			$r = M('User_address')->where(array('address_id'=>$address_id))->save($address_data);
		}else{
			$cunzai = M('User_address')->where(array('uid'=>$uid))->find();
			if(!$cunzai){
				$address_data['isDefault'] = 1;
			}
			$r = M('User_address')->add($address_data);
		}
		//dump($r);
		//echo M('User_address')->_sql();
		if(!$r){
			$this->ajaxReturn(['status'=>-1,'msg'=>'服务器繁忙'],'JSONP');
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	}
	public function setDefault(){
	    $uid = $this->uid;
		$address_id = I('address_id');
		$address_id_check = M('User_address')->where(array('address_id'=>$address_id))->find()['uid'];
		if($uid != $address_id_check){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'服务器繁忙'],'JSONP');
		}
		if($address_id){
		    $r = M('User_address')->where(array('uid'=>$uid))->save(array('isDefault' =>0));
		    $r = M('User_address')->where(array('address_id'=>$address_id,'uid'=>$uid))->save(array('isDefault' =>1));
		}
		if(!$r){
		    $this->ajaxReturn(['status'=>-1,'msg'=>'服务器繁忙'],'JSONP');
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	}
	public function getUserAddressMoren(){
		$uid = $this->uid;
		$address = M('User_address')->where(array('isDefault'=>1,'uid'=>$uid))->find();
		$this->ajaxReturn(['address'=>$address],'JSONP');
	}
	public function delAddress(){
	     $uid = $this->uid;
		$address_id = I('address_id');
		$address_id_check = M('User_address')->where(array('address_id'=>$address_id))->find()['uid'];
		if($uid != $address_id_check){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'服务器繁忙'],'JSONP');
		}
		if($address_id){
		    $r = M('User_address')->where(array('address_id'=>$address_id,'uid'=>$uid))->delete();
		}
		if(!$r){
		    $this->ajaxReturn(['status'=>-1,'msg'=>'服务器繁忙'],'JSONP');
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	}
}
