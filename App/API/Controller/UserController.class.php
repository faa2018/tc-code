<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\UserModel;
use Common\Model\ConfigModel;
use Common\Model\UserRelationModel;
use Common\Api\SendPhoneCodeApi;
use Common\Model\ArtModel;
use Common\Model\CoinModel;
use Common\Model\UserMoneyModel;
use Common\Model\UserCoinAddressModel;
class UserController extends HomeController {
	protected $uid;
	protected $user;
	protected $USER;
	/**  
	 * 用于登录状态的总控制器  包括个人信息的一些部分修改
	 * @author      ZhangYi <1425568992@qq.com>
	 * @version	  v1.0.0 
	 * @copyright  2017-3-29 下午5:02:07
	*/
	public function _initialize(){
		//检测是否登录
		parent::_initialize();
		$token = I('token')?I('token'):'';
		//if($token != session('HOME_TOKEN')){
			$user = $this->checkUser($token);
			//session('HOME_USER',$user);
		//}else{
			//$user = session('HOME_USER');
		//}
		//session('HOME_TOKEN',$token);
        $this->user = $user['user'];
        $this->uid = $user['uid'];
	}
	//检测用户token
	protected function checkUser($token) {
		//下面是针对登录注册 手机版的检测方法
		$where_token['token'] = $token;
		$token_log = M('Token')->where($where_token)->find();
		if(!$token_log){
			$data_return['status'] = -101;
			$data_return['info'] = 'token不存在';
			return $this->ajaxReturn($data_return,'jsonp');
		}
		$where_user['uid'] = $token_log['uid'];
		$where_user['status'] = array('neq',-404);
		$user = M('User')->where($where_user)->find();
		if(!$user){
			$data_return['status'] = -102;
			$data_return['info'] = '查无此人';
			return $this->ajaxReturn($data_return,'jsonp');
		}
		$data_return['uid'] = $token_log['uid'];
		$data_return['user'] = $user;
		return $data_return;
	}
    /*******************************以下为展示页面的功能方法***************************************/
	//判断能否访问页面
	public function checkPid(){
	    $user = M('User_relation')->where('uid = '.$this->uid)->find();
	    if($user['pid'] == 0){
	        $data_return['status'] = -1;
	        $data_return['info']   = '操作失败';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }else{
	        $data_return['status'] = 1;
	        $data_return['info']   = '操作成功';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	}
	//修改头像
	public function touxiang(){
		header('Access-Control-Allow-Origin: *');
		$files=$_FILES['photo'];
		$photo = $this->upload($files);
		$uid = $this->uid;
		if(!uid){
			$return['status'] = -11;
			$return['msg'] = "请登录";
			$this->ajaxReturn($return,'JSON');
		}
		$r = M('User')->where(['uid'=>$uid])->setField('pic',$photo);
		if($r){
		$return['status'] = 1;
		$return['msg'] = "上传成功";
		$this->ajaxReturn($return,'JSON');
		}else{
		$return['status'] = -1;
		$return['msg'] = "系统繁忙";
		$this->ajaxReturn($return,'JSON');
				
		}
	}
	//会员首页
	public function index(){
		//会员基本信息
		$data_return['status'] = $this->uid;
		$data_return['info']['user'] = $this->user;
		//会员金额
		$M_USERMONEY = new UserMoneyModel($this->uid);
		$data_return['info']['money'] = $M_USERMONEY->getUserMoneyByUid(2)['num']?$M_USERMONEY->getUserMoneyByUid(2)['num']:'0';
		return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取会员基本信息
	public function getUsrInfo(){
	    //会员基本信息
	    $data_return['status'] = 1;
	    $data_return['info']['user'] = $this->user;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取上级信息
	public function getUpInfo(){
	    $pid = M('User_relation')->where('uid = '.$this->uid)->find();
	    //获取上线信息
	    $up = M('User_relation')->where('uid = '.$pid['pid'])->find();
	    if($up['uid'] == 0){
	        $data_return['status'] = -1;    
	        $data_return['info'] = '上线不存在';    
            return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
	    $where_user['uid']    = $up['uid'];
	    $where_user['status'] = array('neq',-404);
	    $user = M('User')->where($where_user)->find();
	    if(!$user){
	        $data_return['status'] = -2;    
	        $data_return['info'] = '上线不存在';    
            return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
	    $data_return['status'] = 1;
	    $data_return['info']['user'] = $user;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取直推会员列表
	public function getDownUserList(){
	$list = M('User_relation')
	           ->alias('ur')
	           ->join('left join ztp_user u on u.uid = ur.uid')
			  ->join('left join ztp_project_user_auto pu on pu.uid = ur.uid')
	           ->where('ur.pid = '.$this->uid)
	           ->select();
	    foreach($list as $k=>$v){
	    	if($v['ranking']==0){
	    	$list[$k]['ranking'] = '暂未排名';
	    	$list[$k]['add_time'] = M('Project_user_auto')->where(['uid'=>$v['uid']])->find()['add_time'];
	    	}
	    	}
	    $last = M('Project_user_auto')->order('id desc')->find();
	    $list['list'] = $list;
	    $list['zongpaiming'] = $last['ranking']?$last['ranking']:'0';
	    return $this->ajaxReturn($list,'jsonp');
	}
	//下级分销产生奖金列表
	public function getDownUserTuijianMoneyList(){
	    $list = M('Project_money_tuijian_log')->where('uid = '.$this->uid)->select();
	    $data_return['status'] = 1;
	    $data_return['info']['list'] = $list;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//我的评价
	public function getPingjiaByOpenid(){
		$open_id = $this->uid;
		$re = M('Comment c','ztp_')
		->join("ztp_user m ON c.open_id=m.uid")
		->join("ztp_goods g ON c.goods_id=g.goods_id")
		->where(array('open_id' =>$open_id))->select();
		$this->ajaxReturn(['status'=>1,'msg'=>'请求成功','list'=>$re],'JSONP');
	}
	//获取真实姓名
	public function getRealName(){
	    $real_name = M('User')->where('uid = '.$this->uid)->find()['real_name'];
	    $data_return['status'] = 1;
	    $data_return['info']['real_name'] = $real_name;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//设置真实姓名
	public function setRealName(){
	    $real_name = I('real_name');
	    $real_name = substr($real_name,0,10);
	    if(!$real_name){
	        $data_return['status'] = -2;
	        $data_return['info'] = '请输入真实姓名';
	        return $this->ajaxReturn($data_return,'jsonp');//请输入用户名
	    }
	    $r = M('User')->where('uid = '.$this->uid)->setField('real_name',$real_name);
	    if($r){
	        $data_return['status'] = 1;
	        $data_return['info']   = '操作成功';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }else{
	        $data_return['status'] = -1;
	        $data_return['info']   = '操作失败';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	}
	//邦定上级方法
	public function setTuijian(){
	    $tuijian_name = I('tuijian_name');
	    $user = M('User')->where(array('username'=>$tuijian_name,'status'=>array('neq',-404)))->find();
	    if(!$user){
	        $data_return['status'] = -1;
	        $data_return['info']   = '上线不存在';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	    $r = M('User_relation')->where('uid = '.$this->uid)->setField('pid',$user['uid']);
	    if($r){
	        $data_return['status'] = 1;
	        $data_return['info']   = '操作成功';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }else{
	        $data_return['status'] = -2;
	        $data_return['info']   = '操作失败';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	}
	//修改手机号码
    public function updatePhone(){
		$phone_code = I('phone_code');
        $data['phone'] = I('phone');
        if(empty($data['phone'])){
            $back['status'] = -1;
            $back['info'] = '请输入手机号';
			 return $this->ajaxReturn($back,'jsonp');

        }
		 if(empty($phone_code)){
            $back['status'] = -2;
            $back['info'] = '请输入验证码';
			 return $this->ajaxReturn($back,'jsonp');
        }
		if(!regex($data['phone'],'phone')){
    		$back['status'] = -3;
            $back['info'] = '手机号格式不正确';
			 return $this->ajaxReturn($back,'jsonp');
    	}
		$phone_code = I('phone_code');
		$phone = I('phone');
		if(!$phone_code){
			$data_return['status'] = -4;
			$data_return['info']   = '请输入手机验证码';
			return $this->ajaxReturn($data_return,'jsonp');
		}
		if($phone_code != session('codexiugaishoujihao'.$phone)){
			$data_return['status'] = -5;
			$data_return['info']   = '手机验证码有误';
			return $this->ajaxReturn($data_return,'jsonp');
		}
        $USER = new UserModel();
        $list = $USER->saveUser($data,$this->uid);
        //if($list){
            $back['status'] = 1;
            $back['info'] = '修改成功';
			 return $this->ajaxReturn($back,'jsonp');
        //}else{
        //    $back['status'] = -1;
        //    $back['info'] = '修改失败';
		//	 return $this->ajaxReturn($back,'jsonp');
       // }
    }
	//获取会员收货地址
	public function getUserAddressMoren(){
	    $address = M('User_address','ztp_')->where(['uid'=>$this->uid,'isDefault'=>1])->find();
	    //echo M('User_address','ztp_')->_sql();
		if(!$address){
	        $data_return['status'] = -1;
	        $data_return['info']   = '当前没有绑定地址';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	    $data_return['status'] = 1;
	    $data_return['info']['address']   = $address;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取会员资金
	public function getUsrMoney(){
	    //会员金额
		$M_USERMONEY = new UserMoneyModel($this->uid);
		$data_return['info']['money'] = $M_USERMONEY->getUserMoneyByUid();
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//获取会员银行卡信息
	public function getUserBank(){
	    $bank = M('User_bank')->where(['user_id'=>$this->uid])->select();
	    if(!$bank){
	        $data_return['status'] = -1;
	        $data_return['info']   = '当前没有银行卡绑定';
	        return $this->ajaxReturn($data_return,'jsonp');
	    }
	    $data_return['status'] = 1;
	    $data_return['info']['bank']   = $bank;
	    return $this->ajaxReturn($data_return,'jsonp');
	}
	//显示我的订单记录
	public function getProject(){
		$relation_money = M('UserRelation')->where(['uid'=>$this->uid])->find()['shouyi'];
		$this->assign('leji_money',$relation_money);
		//个人信息
		$this->assign('user',$this->user);
		//获取投资记录
		$where['uid'] = $this->uid;
		$log = M('Project_user_auto')->where($where)->order('add_time desc')->select();
		foreach ($log as $k=>$v){
			$where['id'] = $v['type'];
			$log[$k]['level_name'] = M('Project')->where($where)->find()['name'];
			if($v['over_time'] == 0){
				$log[$k]['over_time']  = $v['add_time']+$v['cycle']*24*3600;
			}
			
		}
		$this->assign('log',$log);
		$this->display();
	}
    //邀请记录
    public function getInvitationRecord(){
    	$uid = $this->uid;
    	//查询全部下线
    	$USER_JIEGOU = new UserRelationModel();
    	$list = $USER_JIEGOU->getDownDetailsByPid($uid);
    	$user_status = C('USER_STATUS');
    	foreach ( $list as $k => $v ) {
    		$list[$k]['reg_time'] = $v['reg_time'];
    		$list[$k]['status']   = $user_status[$v['status']];
    	}
    	$this->assign('list',$list);
    	$this->display();
    }
    //显示团队信息
    public function tuijian(){
    	$user = $this->user;
    	$M_USERRELATION = new UserRelationModel();
    	$user_relation = $M_USERRELATION->getUserByUid($this->uid);
    	$user = array_merge($user,$user_relation);
    	$where['uid'] = $user['uid'];
    	$where['status'] = 0;
    	$user['touzi_money'] = M('Project_user_auto')->where($where)->find('money');
    	$this->assign ( 'user', $user );
    	$USER = new UserModel();
    	//我的邀请链接
    	$link = $_SERVER['SERVER_NAME']."/home/reg/reg?p_username=".$this->user['username'];
    	$this->assign('link',$link);
    	//我的二维码
    	$this->display();
    }
    /*******************************以下为AJAX返回的功能方法***************************************/
    //修改密码
    public function updatePwd(){
    	$uid = $this->uid;
    	$USER = new UserModel();
    	$user = $USER->getUserByUid($uid);
    	$oldpwd		= I('old_password');//原密码
    	$newpwd 	= I('new_password');//新密码
    	$renewpwd 	= I('confirm_password');//确认新密码
    	//验证
    	//$this->checkUpdatePwd($oldpwd, $newpwd, $renewpwd, 'pwd', 'twopwd');
    	$phone_code = I('phone_code');
    	//判断是否为空
    	 if(empty($oldpwd)){
    		$data_return['status'] = -1;
	        $data_return['info']   = '请输入原密码';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	if(empty($newpwd)){
    		$data_return['status'] = -2;
	        $data_return['info']   = '请输入新密码';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	if(empty($renewpwd)){
    		$data_return['status'] = -3;
	        $data_return['info']   = '请确认新密码';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	 // if(empty($phone_code)){
    		// $data_return['status'] = -4;
	        // $data_return['info']   = '请输入手机验证码';
	        // return $this->ajaxReturn($data_return,'jsonp');
    	// } 
    	// if(!checkPhoneCode($phone_code,'xiugaimima',$user['phone'])){
    	//	$data_return['status'] = -51;
	     //   $data_return['info']   = '验证码不正确';
	    //    return $this->ajaxReturn($data_return,'jsonp');
    	//} 
    	//密码确认密码是否相等
    	if($newpwd!=$renewpwd){
    		$data_return['status'] = -6;
	        $data_return['info']   = '两次密码不一致';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	$USER = new UserModel();
    	$userlist = $USER->getUserByUid($uid);
    	//判断密码输入是否正确
    	 if($userlist['pwd']!=passwordEncryption($oldpwd)){
    		$data_return['status'] = -7;
	        $data_return['info']   = '原密码不正确';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	//判断新密码不能与原密码相同
    	if($userlist['pwd']==passwordEncryption($newpwd)){
    		$data_return['status'] = -8;
	        $data_return['info']   = '原密码与新密码相同';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}

    	//将新密码加密并修改
    	$data['pwd'] = passwordEncryption($newpwd);
    	//dump($data);dump($uid);die;
    	$list = $USER->saveUser($data,$uid);
    	if($list){
    		$data_return['status'] = 1;
	        $data_return['info']   = '修改成功';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}else{
    		$data_return['status'] = -10;
	        $data_return['info']   = '修改失败';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    }
    //添加或修改银行卡
    public function updateBank(){
    	$data['user_id'] = $this->uid;
    	$id = I('id');
		$data['name'] = I('name');//持卡人
		$data['number'] = I('number');//卡号
		$data['type'] = I('type');//卡类型
		$data['phone'] = I('phone');//电话
		$phone_code = I('phone_code');//验证码
		$phone = I('phone');
		if(!$phone_code){
			$data_return['status'] = -4;
	        $data_return['info']   = '请输入手机验证码';
	        return $this->ajaxReturn($data_return,'jsonp');
		}
		if($phone_code != session('codebangdingyinhangka'.$phone)){
			$data_return['status'] = -5;
			$data_return['info']   = '手机验证码有误';
	        return $this->ajaxReturn($data_return,'jsonp');
		}
    	//判断是否为空
    	if(empty($data['name'])){
    		$data_return['status'] = -1;
	        $data_return['info']   = '请输入持卡人';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	if(empty($data['number'])){
    		$data_return['status'] = -2;
	        $data_return['info']   = '请输入卡号';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
		if(empty($data['type'])){
    		$data_return['status'] = -3;
	        $data_return['info']   = '请选择银行卡类型';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
		if(empty($data['phone'])){
    		$data_return['status'] = -2;
	        $data_return['info']   = '请输入手机号';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
		if(empty($phone_code)){
    		$data_return['status'] = -2;
	        $data_return['info']   = '请输入手机验证码';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	//判断正则
    	if(!regex($data['phone'],'phone')){
    		$data_return['status'] = -2;
	        $data_return['info']   = '手机号格式不正确';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    	//if(!checkPhoneCode($phone_code,'xiugaimima',$data['phone'])){
    	//	$data_return['status'] = -5;
	    //    $data_return['info']   = '手机验证码不正确';
	    //    return $this->ajaxReturn($data_return,'jsonp');
    	//} 
		$count = M('User_bank')->where(['id'=>$id])->count();
		if(!$id){
			if($count>2){
				$data_return['status'] = -7;
				$data_return['info']   = '银行卡最多只能绑定三张';
				return $this->ajaxReturn($data_return,'jsonp');
			}
		}
    	if($id){
			$list = M('User_bank')->where(['id'=>$id])->save($data);
		}else{
			$list = M('User_bank')->add($data);
		}
		//echo M('User_bank')->_sql();
    	if($list){
    		$data_return['status'] = 1;
	        $data_return['info']   = '添加成功';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}else{
    		$data_return['status'] = -2;
	        $data_return['info']   = '系统繁忙';
	        return $this->ajaxReturn($data_return,'jsonp');
    	}
    }
	//设为默认
	public function setDefaultBank(){
	    $user_id = $this->uid;
		$bank_id = I('bank_id');
		$bank_id_check = M('User_bank')->where(array('id'=>$bank_id))->find()['user_id'];
		if($user_id != $bank_id_check){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'参数错误'],'JSONP');
		}
		if($bank_id){
		    $r = M('User_bank')->where(array('user_id'=>$user_id))->save(array('isDefault' =>0));
		    $r = M('User_bank')->where(array('id'=>$bank_id,'user_id'=>$user_id))->save(array('isDefault' =>1));
		}
		//if(!$r){
		//    $this->ajaxReturn(['status'=>-1,'msg'=>'服务器繁忙'],'JSONP');
		//}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	}
	//删除银行卡
	public function delBank(){
	     $user_id = $this->uid;
		$bank_id = I('bank_id');
		$bank_id_check = M('User_bank')->where(array('id'=>$bank_id))->find()['user_id'];
		if($user_id != $bank_id_check){
		    $this->ajaxReturn(['status'=>-2,'msg'=>'服务器繁忙'],'JSONP');
		}
		if($bank_id){
		    $r = M('User_bank')->where(array('id'=>$bank_id,'user_id'=>$user_id))->delete();
		}
		if(!$r){
		    $this->ajaxReturn(['status'=>-1,'msg'=>'服务器繁忙'],'JSONP');
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'操作成功'],'JSONP');
	}
    /**
     * 修改用户名
     */
    public function saveUserName(){
    	$USER = new UserModel();
    	$user = $USER->getUserByUid($this->uid);
    	//$phone_code = I('phone_code');
    	$data['username'] = I('username');
    	/* if(empty($phone_code)){
    		$this->ajaxReturnPushError('-2000');
    	} */
    	if(empty($data['username'])){
    		$this->ajaxReturnPushError('-2003');
    	}
    	if(!regex($data['username'],'username')){
    		$this->ajaxReturnPushError('-2015');
    	}
    	/*验证手机验证码*/
    	/* if(!checkPhoneCode($phone_code,'xiugaizhanghao',$user['phone'])){
    		$this->ajaxReturnPushError('-2018');
    	} */
    	$USER = new UserModel();
    	$checkusername = $USER->getUserByUsername($data['username']);
    	if($checkusername){
    		$this->ajaxReturnPushError('-2025');
    	}
    	$list = $USER->saveUser($data,$user['uid']);
    	if($list){
    		unset ($_SESSION['HOME_USER_ID']);
    		S('USER_SESSION_'.$this->uid,null);
    		$this->ajaxReturnPushError('1');
    	}else{
    		$this->ajaxReturnPushError('-1');
    	}
    }
   
   /**
     * 修改EMAIL
     */
    public function saveEmail(){
    	$USER = new UserModel();
    	$user = $USER->getUserByUid($this->uid);
    	$two_pwd = I('two_password');
    	$data['email'] = I('email');
    	if(empty($two_pwd)){
    		$this->ajaxReturnPushError('-2022');
    	}
    	if(empty($data['email'])){
    		$this->ajaxReturnPushError('-2012');
    	}
    	if(!regex($data['email'],'email')){
    		$this->ajaxReturnPushError('-2013');
    	}
     	if(!passwordVerification($two_pwd,$user['twopwd'])){
    	    $this->ajaxReturnPushError('-2007');//密码错误
    	}
    	$list = $USER->saveUser($data,$this->uid);
    	if($list){
    		S('USER_SESSION_'.$this->uid,null);
    		$this->ajaxReturnPushError('1');
    	}else{
    		$this->ajaxReturnPushError('-1');
    	}
    }
    /**
     * 修改用户名
     */
    public function saveRealName(){
    	$phone_code = I('phone_code');
    	$data['real_name'] = I('real_name');
    	/* if(empty($phone_code)){
    		$this->ajaxReturnPushError('-2000');
    	} */
    	if(empty($data['real_name'])){
    		$this->ajaxReturnPushError('-2003');
    	}
    	/* if(!regex($data['real_name'],'name')){
    		$this->ajaxReturnPushError('-1999');
    	} */
    	/*验证手机验证码*/
    	/* if(!checkPhoneCode($phone_code,'xiugaizhanghao',$user['phone'])){
    		$this->ajaxReturnPushError('-2018');
    	} */
    	$USER = new UserModel();
//     	$checkusername = $USER->getUserByUsername($data['username']);
//     	if($checkusername){
//     		$this->ajaxReturnPushError('-2025');
//     	}
    	$list = $USER->saveUser($data,$this->uid);
    	if($list){
    		S('USER_SESSION_'.$this->uid,null);
    		$this->ajaxReturnPushError('1');
    	}else{
    		$this->ajaxReturnPushError('-1');
    	}
    }
	/**
	 * 获得下线信息
	 */
	public function getInivt() {
		$username = I('username');
		$path = I('path');
		if($path==1 || $path==2 || $path==3){
			//获取id
			$pid = M('User')->where(array('username'=>$username))->getField('uid');
			//查询全部下线
    		$where['pid']    = $pid;
    		$field = 'uid';
    		$user = M('User_relation')->field($field)->where($where)->select();
    		if(!$user){$data ['status'] = 0;
				$this->ajaxReturn ( $data );}
    		foreach ( $user as $k => $v ) {
    			$user[$k] = M('User')->field('username,real_name')->where(array('uid'=>$v['uid']))->find();
    			$user[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
    			$where_project['uid']    = $v['uid'];
    			$user[$k]['touzi_money']= M('Project_user_auto')->where($where_project)->getField('money');
    		}
			if ($user) {
				$data ['path'] = $path+1;
				$data ['status'] = 1;
				$data ['user'] = $user;
				$this->ajaxReturn ( $data );
			} else {
				$data ['status'] = 0;
				$this->ajaxReturn ( $data );
			}
		}else{
			$data ['status'] = 0;
			$this->ajaxReturn ( $data );
		}
	}
	/*******************************以下为本类以及子类调用的功能方法***************************************/
	protected function checkTwopwd($twopwd){
		//记录上次进入方法存储的路径
		$rule_name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		session('URL_CHECK_TWOPWD',$rule_name);
		//检验密码
		$checkpwd = $this->USER->getUserByUid($this->uid)['twopwd'];
		if (passwordEncryption($twopwd)!=$checkpwd){
			$this->ajaxReturnPushError('-2007');
		}
	}
	/*******************************以下为本类调用的功能方法***************************************/
	
	/**
	 * 验证修改密码时候的数据
	 * @param unknown $oldpwd  旧密码
	 * @param unknown $newpwd	新密码
	 * @param unknown $renewpwd	确认新密码
	 * @param unknown $check_pwd_one 要检测的密码 即要修改数据库的密码
	 * @param unknown $check_pwd_two 要检测的密码 要避免和新密码一致 的密码 从数据库查询得到
	 */
	private function checkUpdatePwd($oldpwd,$newpwd,$renewpwd,$check_pwd_one,$check_pwd_two){
		//判断是否为空
		if(empty($oldpwd)){
			$this->ajaxReturnPushError('-2029');
		}
		if(empty($newpwd)){
			$this->ajaxReturnPushError('-2030');
		}
		if(empty($renewpwd)){
			$this->ajaxReturnPushError('-2031');
		}
		//判断正则
		if(!regex($newpwd,'password')){
			$this->ajaxReturnPushError('-2010');//密码格式错误
		}
		//密码确认密码是否相等
		if($newpwd!=$renewpwd){
			$this->ajaxReturnPushError('-2024');
		}
		//获取个人信息
		$user = $this->USER->getUserByUid($this->uid);
		//判断密码输入是否正确
		if($user[$check_pwd_one]!=passwordEncryption($oldpwd)){
			$this->ajaxReturnPushError('-2007');
		}
		//判断新密码不能与原密码相同
		if($user[$check_pwd_one]==passwordEncryption($newpwd)){
			$this->ajaxReturnPushError('-2032');
		}
		//判断新密码是否与登录密码相同
		if($user[$check_pwd_two]==passwordEncryption($newpwd)){
			$this->ajaxReturnPushError('-2011');
		}
	}
	//发送验证码
	public function sendPhone(){
		$phone = I('phone');
		$SENDPHONE = new SendPhoneCodeApi($phone);
		$send = $SENDPHONE->send();		
		if($send==1){
			$data_return['status'] = 1;
	        $data_return['info']   = '发送成功';
	        return $this->ajaxReturn($data_return,'jsonp');
		}else{
			return $this->ajaxReturn(array('info'=>$send),'jsonp');
		}
	}
	/**
	 * 实名认证
	 * @return uid
	 */
	public function Auth(){
		$data['uid'] = $this->uid;
		$data['real_name'] = I('real_name');
		$data['id_card'] = I('id_card');
		if(IS_POST){
			//图片处理
			if($_FILES["Filedata"]["name"]){
				$data['pic'] = $this->upload($_FILES["Filedata"]);
			}
			if(empty($data['real_name'])||empty($data['id_card'])){
				$return = array('status' => -6,'info'=> "信息填写不完整");
				$this->ajaxReturn($return);
			}
			if(empty($data['pic'])){
				$return = array('status' => -3,'info'=> "请选择图片");
				$this->ajaxReturn($return);
			}
			if(!regex($data['id_card'],'id_card')){
				$return = array('status' => -4,'info'=> "身份证格式不正确");
				$this->ajaxReturn($return);
			}
			$real = M('Real') ->where(array('uid'=>$data['uid']))->find();
			//待审核 审核通过  不能再提交
			if($real['status']===0||$real['status']==1){
				$return = array('status' => -2,'info'=> "您已提交认证,请勿重复提交");
				$this->ajaxReturn($return);
			}
			$data['add_time'] = time();
			//审核不通过 再次提交
			if($real['status']==2){
				//再次提交删除原图
				unlink($_SERVER['DOCUMENT_ROOT'].$real['pic']);
				//再次提交之后将状态改为待审核
				$data['status'] = 0;
				$list = M('Real')->where(array('uid'=>$data['uid']))->save($data);
			}else{
				//第一次提交
				$list = M('Real')->add($data);
			}
			if($list){
				$data = array('status' => 1,'info'=> "操作成功,请等待审核通过");
				$this->ajaxReturn($data);
			}else{
				$this->ajaxReturnPushError('-1');
			}
		}else{
			$USER = new UserModel();
			$list = $USER->getAuthList($data['uid']);
			$this->assign('list',$list);
			//提示语
			$ART = new ArtModel();
			$cue = $ART->selectCue(7);
			$this->assign('cue',$cue);
			$this->display();
		}
	}
	public function checkTime($type){
			$time = time();
			$addtime = session('MONEY_TIME'.$type);
			if($addtime!= ""&&$time-$addtime<10){
				return false;
			}else{
				session('MONEY_TIME'.$type,time());
				return true;
			}
	}
}