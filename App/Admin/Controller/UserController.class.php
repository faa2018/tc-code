<?php
namespace Admin\Controller;
use Org\Nx\Page;
use Admin\Controller\AdminBaseController;
use Common\Model\UserModel;
use Common\Api\UserApi;
use Common\Model\UserRelationModel;
use Common\Api\FormatApi;
use Common\Model\UserNoteModel;
use Common\Model\CoinModel;
use Common\Api\UserMoneyApi;
use Common\Api\SendPhoneCodeApi;
/**  
* 后台会员管理 
*/
class UserController extends AdminBaseController{
	/**
	 * 自动加载方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	//报单通过
	public function baodan_adopt(){
		$id = I('id');
		if(!$id){
			$this->error('参数错误');
		}
		if(!empty($id)){
			$where['id'] = array('in',$id);
			$baodan_log = M('Project_user')->where($where)->select();
			foreach($baodan_log as $k=>$v){
				if($v['status']!=0){
					$this->error('请选中待审核状态的数据');
				}
				$baodan_user = M('User')->where(['phone'=>$v['phone']])->find();
				$uid = $baodan_user['uid'];
				//查询以前有没有
				$yiqian = M('Project_user_auto')->where(['uid'=>$uid])->find();
				$userlist = M('User')->where(['uid'=>$uid])->find();
				M()->startTrans();
				//加报单记录
				$project_log = M('Project_user_auto')->order('id desc')->find();
				if($project_log){
					$ranking = $project_log['ranking'];
				}else{
					$ranking = 0;
				}
				$project_id = $this->addProject($uid,$this->config['baodan_money'],2,$ranking+1);
				//本人
				$log = M('Project_user_auto')->where(['id'=>$project_id])->find();
				$paiming = $log['ranking']%30;
				$array = array('3','7','11','15','20','24','28');
				if(in_array($paiming,$array)){
					$project_id = $this->addProject(2,$this->config['baodan_money'],2,$log['ranking']+1);
				}
				$zuixin_log = M('Project_user_auto')->order('id desc')->find();
				if($zuixin_log['ranking']%30==16){
					$project_id = $this->addProject(3,$this->config['baodan_money'],2,$zuixin_log['ranking']+1);
				}
				$zuixin_log = M('Project_user_auto')->order('id desc')->find();
				if($zuixin_log['ranking']%30==29){
					$project_id = $this->addProject(4,$this->config['baodan_money'],2,$zuixin_log['ranking']+1);
				}
				//实例化结构model
				$API_USERMONEYLOGIC = new UserMoneyApi();
				$USER_RELATION = new UserRelationModel();
				$user = $USER_RELATION->getUserByUid($uid);//本人直推关系
				$up_user = $USER_RELATION->getUserByUid($user['pid']);//直推上线
				$up_level = M('User')->where(['uid'=>$up_user['uid']])->find()['level'];//上级级别
				if($log['ranking']!=1){
					if(($log['ranking']-1)%($this->config['out_num'])==0){
						//该出局的人
						$where_chuju['ranking'] = ($log['ranking']-1)/$this->config['out_num'];
						$chuju_user = M('Project_user_auto')->where($where_chuju)->find();
						$r[] = $this->checkOut($chuju_user);
					}
					if($up_user['p_num']>=24&&$up_level==0){
						//该出局的人
						$where_chuju['uid'] = $up_user['uid'];
						$chuju_user = M('Project_user_auto')->where($where_chuju)->find();
						if($chuju_user){
							$r[] = $this->checkOut($chuju_user);
						}
					}
				}
				//直推人数超过25 成为报单中心
				if($up_level!=2&&$up_user['p_num']>=24){
					//修改状态 查询之前是否
					$re[] = M('User')->where(['uid'=>$up_user['uid']])->setField('level',2);
				}
				//第一次报单
				if(!$yiqian&&$up_user){
					//加上线直推数量
					$r[] = $USER_RELATION->addZhituiNum($up_user['uid'],1);
				}
				if($up_user){
					//直推奖
					$API_USERMONEYLOGIC->setUserMoneyLogic($up_user['uid'],$this->config['zhitui_money'], 1, 1013, '获得'.$userlist['username'].'的直推奖', 1013,2);
					//代数奖
					$this->runDaishuMoney($userlist,$up_user,1,1);
				}
				$r[] = M('Project_user')->where(['id'=>$v['id']])->setField('status',1);
				$pic_log = M('Project_user_pic_log')->where(['id'=>$v['image_id']])->find();
				if($pic_log['status']==0){
				M('Project_user_pic_log')->where(['id'=>$v['image_id']])->setField('status',1);
				}
				//填写资料的人
				$baodan = M('User')->where(['uid'=>$v['baodan_uid']])->find();
				$content = "【传奇汽车俱乐部】,报单人".$baodan['phone']."已成功报单,您的登录账号为".$v['phone']."。";
				$r[] = $this->sendPhone($baodan['phone'],$content);
			}
		}
		if (in_array(false, $r)){
			M()->rollback();
			$this->error('系统繁忙');
		}else {
			M()->commit();
			$this->success('操作成功',U('User/baodan'));
		}
	}
	//代数奖
	public function runDaishuMoney($user,$up_user,$k,$level){
		$k++;
		if($k>20){
			return;
		} 
		$up_note = M('User')->where(['uid'=>$up_user['uid']])->find();
		//代理
		if($up_note['is_daili'] == 1){
			//代理代数奖
			$this->runYejiMoney($up_note,$user,2,$level);
			return;
		}
		//报单中心
		if($up_note['level'] == 2 && $level != 2){
			//报单中心代数奖
			$this->runYejiMoney($up_note,$user,1,$level);
			//下个报单中心不拿
			$level = 2;
		}
		//查询上级 推荐关系
		$USERRELATION = new UserRelationModel();
		$up = $USERRELATION->getUpByPid($up_user['pid']);
		if(!$up){
			return;
		}
		$this->runDaishuMoney($user,$up,$k,$level);
	
	}
	//发放代数
	private function runYejiMoney($user,$user_from,$type,$level){
		$data_tuijian['uid'] = $user['uid'];
		$data_tuijian['username'] = $user['phone'];
		$data_tuijian['sz_type'] = 1;
		//正常 200
		$data_tuijian['money'] = $this->config['zhitui_money_baodanzhongxin'];
		if($type == 2 && $level != 2){
			//代理 下面没有报单中心 400
			$data_tuijian['money'] = $this->config['zhitui_money_daili']+$this->config['zhitui_money_baodanzhongxin'];
		}
		$data_tuijian['money_type'] = 2;
		$data_tuijian['uid_from'] = $user_from['uid'];
		$data_tuijian['username_from'] = $user_from['phone'];
		$data_tuijian['status'] = 0;
		$data_tuijian['content'] = '获得代数奖';
		$data_tuijian['add_time'] = time();
		M('Project_daishu_log')->add($data_tuijian);
	}
	///////////////////////////////////展示页面方法///////////////////////////////////
    /**
     * 会员列表
     */
    public function showUserList(){
    	$FORMAT = new FormatApi();
    	//筛选
    	if(IS_GET){
    		$data  = $FORMAT->formatScreenWhereUser();
    		$where = $data['where'];
    	}
    	$where['status'] = array('neq','-2');
    	$count             =  M('User')->where($where)->count();// 查询满足要求的总记录数
    	$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
    	//给分页传参数
    	setPageParameter($Page, array('username'=>$data['data']['username'],'phone'=>$data['data']['phone'],'email'=>$data['data']['email']));
    	$show       = $Page->show();// 分页显示输出
    	$list =  M('User')
    	->where($where)
    	->order('uid desc')
    	->limit($Page->firstRow.','.$Page->listRows)
    	->select();
		//处理状态 ，等级的格式化问题
	    $user = $FORMAT->formatForeachUser($list);
    	$this->assign('list',$user);
    	$this->assign('page',$show);
        $this->display('User/index');
    }
    //会员升级
    public function shengji(){
    	$uid = I('uid');
		$type = I('type');
    	if(!$uid){
    		$this->error('参数错误');
    	}
		if($type == 1){
			if(!empty($uid)){
				$where['uid'] = array('in',$uid);
				$userlist= M('User')->where($where)->select();
				foreach($userlist as $k=>$v){
					if($v['level'] == 2){
						$r = M('User')->where(['uid'=>$v['uid']])->setField('is_daili',1);
					}
				}
				if($r){
					$this->success('操作成功');
				}else{
					$this->error('操作失败,必须先成为报单中心才能升级代理');
				}
			}
		}
    	if($type == 2){
			if(!empty($uid)){
				$where['uid'] = array('in',$uid);
				$userlist= M('User')->where($where)->select();
				foreach($userlist as $k=>$v){
					if($v['is_daili'] == 1){
						$r = M('User')->where(['uid'=>$v['uid']])->setField('is_daili',0);
					}
				}
				if($r){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}
		}
    }
	//会员撤销
    public function chexiaoshengji(){
    	$uid = I('uid');
    	if(!$uid){
    		$this->error('参数错误');
    	}
    	if(!empty($uid)){
    		$where['uid'] = array('in',$uid);
    		$userlist= M('User')->where($where)->select();
    		foreach($userlist as $k=>$v){
				if($v['is_daili'] == 1){
					$r = M('User')->where(['uid'=>$v['uid']])->setField('is_daili',0);
				}
    		}
    		if($r){
    			$this->success('操作成功');
    		}else{
    			$this->error('操作失败');
    		}
    	}
    }
	/**
	 * 会员添加或修改 显示页面
	 */
 	public function updateUser(){
 		$id = I('id');
 		$this->assign('geshi','添加');
 		if($id){
 			$USER = new UserModel();
 			$list = $USER->getUserByUid($id);

			$user_relation = M('User_relation')->where('uid = '.$list['uid'])->find();
			$list['tuijian_phone'] = M('User')->where('uid = '.$user_relation['pid'])->find()['phone'];
			$list['tuijian_name'] = M('User')->where('uid = '.$user_relation['pid'])->find()['real_name'];
 			$this->assign('list',$list);
 			$this->assign('geshi','修改');
 		}
 		$user_level_name = C('User_LEVEL');
 		$user_status_name = C('User_STATUS');
 		$this->assign('user_level_name',$user_level_name);
 		$this->assign('user_status_name',$user_status_name);
		$this->display();
 	}
 	//查看用户结构
 	public function getJiegou(){
 		$FORMAT = new FormatApi();
 		$type = I('type')?I('type'):1;
 		if($type == 1){
 			$uid = I('uid')?I('uid'):1;
 			if(IS_GET && !$uid && $uid!=1){
 				$uid = $FORMAT->formatScreenUser();
 			}
 			$where['uid'] = $uid;
 			$user = M('User_jiegou_view')->where($where)->find();
 			$where_project['uid'] = $user['uid'];
 			$where_project['status'] = 0;
 			$user['touzi_money'] = M('Project_user_auto')->where($where_project)->sum('money');
 			$this->assign ( 'user', $user );
 			$this->display();
 		}
 		if($type == 2){
 			//获取数据 $id
 			$pid = I('uid');
 			//筛选
 			if(IS_POST){
 				$pid = $FORMAT->formatScreenUser();
 			}
 			//查询本人信息
 			if($pid != 0){
 				$USER = new UserModel();
 				$up_name = $USER->getUserByUid($pid);
 				$this->assign('up_username',$up_name);
 			}
 			//查询全部下线
 			$USER_JIEGOU = new UserRelationModel();
 			$user = $USER_JIEGOU->getDownDetailsByPid($pid);
 			//处理状态 ，等级的格式化问题
 			$user = $FORMAT->formatForeachUser($user);
 			//发送变量，显示页面
 			$this->assign('list',$user);
 			$this->display('getJiegouTable');
 		}
 	}
 	//////////////////////////////功能方法///////////////////////////////////////
	//添加或修改个人基础信息
	public function updUser(){
		$phone = I('phone');
		$real_name= I('real_name');
		$pwd = I('pwd'); 
		$tuijian_phone = I('tuijian_phone'); 
		$USER = new UserModel();
		$phone_check = $USER->getUserByPhone($phone);
		if(empty($phone)){
			$data['status'] = -1;
			$data['info'] = '请输入手机号';
			$this->ajaxReturn($data);
		}
		if(empty($real_name)){
			$data['status'] = -1;
			$data['info'] = '请输入用户姓名';
			$this->ajaxReturn($data);
		}
		if(empty($pwd)){
			$data['status'] = -1;
			$data['info'] = '请输入密码';
			$this->ajaxReturn($data);
		}
		$data_user['username'] = $phone;
		$data_user['phone'] = $phone;
		$data_user['real_name'] = $real_name;
		$data_user['add_time'] = time();
		$data_user['pwd'] = passwordEncryption($pwd);
		$list = M('User')->add($data_user);
		$up = M('User')->where("phone = '".$tuijian_phone."'")->find();
		//新增
		$data_user_relation['uid'] = $list;
		$data_user_relation['pid'] = $up['uid']?$up['uid']:0;
		M('User_relation')->add($data_user_relation);
		//用户资金表
		$money_type = C('USER_MONEY_TYPE');
		foreach ($money_type as $k=>$v){
			$data_money['uid']          = $list;
			$data_money['num']          = 0;
			$data_money['type']         = $k;
			$r[] = M('User_money'.cuttable($list))->add($data_money);
		}
		if($r){
			$data['status'] = 1;
			$data['info'] = '操作成功';
			$this->ajaxReturn($data);
		}else{
			$data['status'] = -1;
			$data['info'] = '系统繁忙';
			$this->ajaxReturn($data);
		}
	}
	public function editUser(){
		if(!IS_POST){
			$this->error('参数错误');
		}
		//接收数据
		$uid               = I('id'); 	
		$username          = I('username');
		$phone    		   = I('phone');
		$status    		   = I('status');
		$pwd    	  	   = I('pwd')?I('pwd'):'';
		$twopwd	  		   = I('twopwd')?I('twopwd'):'';
		$tuijian_name	  		   = I('tuijian_name')?I('tuijian_name'):'';
		//前台传0会报错 用2代替0的状态 0 非正式
		if($status == 2){
			$status = 0;
		}
		$USER = new UserModel();
		$USERLOGIC = new UserApi();
		//$username_check = $USER->getUserByUsername($username);
		//邮箱是否注册过
		//$email_check = $USER->getUserByEmail($email);
		//手机号是否注册过
		$phone_check = $USER->getUserByPhone($phone);
		if(empty($phone)){
			$this->error('请填写手机号');
		}
		$up = M('User')->where("username = '".$tuijian_name."'")->find();
		//新增
		if(!$uid){
			/* if($username_check){
				$this->error('该用户名已经注册过！');
			} */
			// if($email_check){
				// $this->error('该邮箱已经注册过');
			// }
			if($phone_check){
				$this->error('该手机号已经注册过');
			}
			$data_user['username'] = $phone;
			$data_user['pic'] = '';
			$data_user['phone'] = $phone;
			//$data_user['email'] = '';
			$data_user['add_time'] = time();
			$data_user['pwd'] = passwordEncryption($pwd);
			//$data_user['twopwd'] = passwordEncryption($pwd);
			$list = M('User')->add($data_user);
			if($up){
			$data_user_relation['uid'] = $list;
			$data_user_relation['pid'] = $up['uid'];
			M('User_relation')->add($data_user_relation);
			}
			//用户资金表
			$money_type = C('USER_MONEY_TYPE');
			foreach ($money_type as $k=>$v){
				$data_money['uid']          = $list;
				$data_money['num']          = 0;
				$data_money['type']         = $k;
				$r[] = M('User_money'.cuttable($list))->add($data_money);
			}
			$data_token['uid'] = $list;
			$data_token['token'] = $list.md5(time());
			M('Token')->add($data_token);
		}else{
			$user   = $USER->getUserByUid($uid);

			if($phone != $user['phone']){
				if($phone_check){
					$this->error('该手机号已经注册过');
				}
			}
			$pwd    = I('pwd')?passwordEncryption(I('pwd')):$user['pwd'];
			//$twopwd = I('twopwd')?passwordEncryption(I('twopwd')):$user['twopwd'];
			//$data['email'] = $email;
			$data['phone'] = $phone;
			$data['real_name'] = I('real_name');
			$data['status'] = $status;
			$data['pwd'] = $pwd;
			$data['username'] = $username;
			$list[] = M('User')->where(['uid'=>$uid])->save($data);
			//$list[] = M('User_relation')->where(['uid'=>$uid])->setField('pid',$up['uid']);
		}
		if($list){
			S('USER_SESSION_'.$uid,null);
			$this->success('操作成功',U('User/showUserList'));
		}else{
			$this->error('操作失败');
		}
	}
	public function checkTuijian(){
		$phone = I('phone');
		$user = M('User')->where("phone = '".$phone."'")->find();
		if(!$user){
			$data['status'] = -1;
			$data['info'] = '推荐人不存在';
			$this->ajaxReturn($data);
		}
		$data['status'] = 1;
		$data['info'] = '推荐人存在';
		$this->ajaxReturn($data);
	}
	/**
	 * 是否锁定 状态修改
	 */
	public function setUserStatus(){
		$uid = I('uid')?I('uid'):null;
		$USER = new UserModel();
		$user = $USER->getUserByUid($uid);
		if($user['status']==0){
			$list = $USER->setUserStatus($uid,'-1');
		}elseif($user['status']==-1){
			$list = $USER->setUserStatus($uid,'0');
		}
		if($list){
			S('USER_SESSION_'.$uid,null);
			$this->success('状态修改成功',U('User/showUserList'));
		}else{
			$this->error('状态修改成功');
		}
	}
	/**
	 * 获得下线信息
	 */
	public function getInivt() {
		$USER = new UserModel();
		$username = I('username');
		//获取id
		$pid = $USER->getUserByUsername($username)['uid'];
			//查询全部下线
    		$where['pid']    = $pid;
    		$user = M('User_jiegou_view')->where($where)->select();
    		foreach ( $user as $k => $v ) {
    			$user[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
				$where_project['uid'] = $v['uid'];
				$money = M('Project_user_auto')->where($where_project)->sum('money');
				if(!$money){
					$money = 0;
				}
    			$user[$k]['touzi_money']= $money;
    		}
			//dump($user);
		if ($user) {
			$data ['status'] = 1;
			$data ['user'] = $user;
			$this->ajaxReturn ( $data );
		} else {
			$data ['status'] = 0;
			$this->ajaxReturn ( $data );
		}
	}
	//实名认证列表
	public function authList(){
		$USER = new  UserModel();
		$status = 999;
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
			$status = I('status');
			if($status === ''){
				$status = 999;
			}else{
				$where['status'] = $status;
			}
		}
		$count             =  M('Real')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//给分页传参数
		setPageParameter($Page, array('username'=>$username));
		$show       = $Page->show();// 分页显示输出
		$list =  M('Real')
		->where($where)
		->order('add_time desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		foreach($list as $k=>$v){
			$list[$k]['username'] = M('User')->where(array('id'=>$v['uid']))->find()['username'];
		}
		$this->assign('list',$list);
		$this->display();
	}
	//实名认证通过
	public function authDetails(){
		$uid = I('uid');
		$USER = new UserModel();
		$list = $USER->getAuthList($uid);
		$list['username'] = $USER->getUserByUid($uid)['username'];
		$this->assign('list',$list);
		$this->display();
	}
	//实名认证通过
	public function authAdopt(){
		$uid = I('uid');
		$status = I('status');
		$USER = new UserModel();
		$list = $USER->getAuthList($uid);
		if($list['status']==0){
			$data['real_name'] = $list['real_name'];
			$data['idcard'] = $list['id_card'];
			$setstatus = $USER->setAuthStatus($uid, $status,$data);
			if($setstatus){
				$this->success('操作成功',U('User/authList'));
			}else{
				$this->error('系统繁忙');
			}
		}else{
			$this->error('已认证,请勿重复认证');
		}
	}
	//一键登录
	public function keyLogin(){
		$id = I('id');
		session('HOME_USER_ID',$id);
		$this->redirect('Home/Index/index');
	}
	/**
	 *申请消费列表
	 */
	public function xiaofeiList(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
			$status = I('status');
			if($status === ''){
				$status = 999;
			}else{
				$where['status'] = $status;
			}
		}
		$count             =  M('Project_user_auto')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//setPageParameter($Page, array('username'=>$username,'status'=>$status));
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =  M('Project_user_auto')
		->where($where)
		->order('id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		foreach($list as $k=>$v){
			$list[$k]['user'] = $USER->getUserByUid($v['uid']);
			$tuijian = M('User_relation')->where(['uid'=>$v['uid']])->find();
			$list[$k]['tuijian'] = $USER->getUserByUid($tuijian['pid']);
		}
		//处理状态 ，等级的格式化问题
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	/**
	 *报单列表
	 */
	public function baodan(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
			$status = I('status');
			if($status === ''){
				$status = 999;
			}else{
				$where['status'] = $status;
			}
		}
		$count             =  M('Project_user')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//setPageParameter($Page, array('username'=>$username,'status'=>$status));
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =  M('Project_user')
		->where($where)
		->order('id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		foreach($list as $k=>$v){
			$list[$k]['baodan_username'] = $USER->getUserByUid($v['baodan_uid'])['username'];
			$list[$k]['baodan_phone'] = $USER->getUserByUid($v['baodan_uid'])['phone'];
		}
		//处理状态 ，等级的格式化问题
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	/**
	 出局列表
	 */
	public function outList(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
		}
		$count             =  M('Project_out')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//setPageParameter($Page, array('username'=>$username,'status'=>$status));
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =  M('Project_out')
		->where($where)
		->order('id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		foreach($list as $k=>$v){
			$list[$k]['username'] = $USER->getUserByUid($v['uid'])['username'];
		}
		//处理状态 ，等级的格式化问题
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	public function addOutUser(){
		$id = I('id');
		$image_id =I('image_id');
		$list = M('Project_user')->where(['id'=>$id])->find();
		$pingzheng = M('Project_user_pic')->where(['image_id'=>$image_id])->select();
		$this->assign('pingzheng',$pingzheng);
		$USER = new  UserModel();
		$list['username'] = $USER->getUserByUid($list['uid'])['username'];
		$this->assign('list',$list);
		$this->display();
	}
	
			//发送验证码
	public function sendPhone($phone,$content=''){
		$SENDPHONE = new SendPhoneCodeApi($phone);
		$send = $SENDPHONE->sendMessage($content);
		return $send;
	}
	//报单拒绝
	public function baodan_refuse(){
		$id = I('id');
		if(!$id){
			$this->error('参数错误');
		}
		$baodan_log = M('Project_user')->where(['id'=>$id])->find();
		$uid = $baodan_log['uid'];
		M()->startTrans();			
			//扣钱
			$r[] =M('User_money'.cuttable($uid))->where(array('uid'=>$uid,'type'=>2))->setInc('num',$this->config['baodan_money']);
		$r[] = M('Project_user')->where(['id'=>$id])->setField('status',1);
		if (in_array(false, $r)){
    		M()->rollback();
    		$this->error('系统繁忙');
    	}else {
    		M()->commit();
    		$this->success('操作成功',U('User/baodan'));
    	} 
	}	
	//记录 自动程序的表
	public function addProject($uid,$money,$money_type=2,$ranking){
		$data['uid'] 		= $uid;
		$data['money'] 		= $money;
		$data['money_type'] = $money_type;
		$data['status']     = 0;
		$data['add_time'] 	= time();
		$data['ranking'] 	= $ranking;
		$r = M('Project_user_auto')->add($data);
		return $r;
	}
	//出局操作
	public function checkOut($chuju_user){
			M('Project_user_auto')->where(['uid'=>$chuju_user['uid'],['status']=>0])->order('add_time')->setField('status',1);
			//将出局人 自动排在下边		
			$project_log = M('Project_user_auto')->order('id desc')->find();
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
		}
	//修改用户提币地址
	public function editTibiAddress(){
		$uid = I('uid');
		$COIN = new CoinModel();
		$list = $COIN->getUserUrlAndID($uid);
		if(IS_POST){
			$data['uid'] = I('uid');
			$data['wai_id'] = I('wai_id');
			$data['url'] = I('url');
			//检测是否是本钱包的地址
			$coin = array(
					'rpc_username'=>'username',
					'rpc_pwd'=>'password',
					'rpc_host'=>'112.196.204.87',
					'rpc_port'=>'23313',
					'id'=>'1',
			);
			//判断看这个钱包地址是否是真实地址
			if(!$COIN->checkCoinAddress($data['url'],$coin)){
				$this->error('提币地址不是一个有效地址');

			}
			//查询到 修改 查不到 添加
			if($list){
				$edit = $COIN->editUserUrlAndID($data['uid'],$data);
			}else{
				//没有地址就添加
				$edit = $COIN->addUserUrlAndID($data);
			}
			if($edit){
				$this->success('操作成功');
			}else{
				$this->error('系统繁忙');
			}
		}else{
			$this->assign('uid',$uid);
			$this->assign('list',$list);
			$this->display();
		}
		
	}
//下载表格
	public function to_download(){
	    $str = "编号,用户名,真实姓名,手机号,注册时间,状态\n";
	    $str = iconv('utf-8','gb2312',$str);
	    $list = M('User')->order('add_time desc')->select();
    	$user_status_name = C('USER_STATUS');
		foreach($list as $k=>$v){
			$list[$k]['status_name'] = $user_status_name[$v['status']];
			}
	    foreach($list as $k=>$v){
	        $xuhao = iconv('utf-8','gb2312',$v['uid']);
	        $username = iconv('utf-8','gb2312',$v['username']);	       
			$real_name = iconv('utf-8','gb2312',$v['real_name']);
			$phone = iconv('utf-8','gb2312',$v['phone']);
	        $add_time = iconv('utf-8','gb2312',date("Y-m-d H:i:s",$v['add_time']));
	        $status = iconv('utf-8','gb2312',$v['status_name']);
	        
	        $str .= $xuhao.",".$username.",".$real_name.",".$phone.",".$add_time.",".$status."\n";
	    }
	    $filename = '会员信息表.csv';
	    export_csv($filename,$str);
	}
	public function getUserAddress(){
		$uid = I('uid');
		$list = M('User_address')->where(['uid'=>$uid])->select();
		foreach ($list as $k=>$v){
			$list[$k]['username'] = M('User')->where(['uid'=>$v['uid']])->find()['username'];
			$list[$k]['real_name'] = M('User')->where(['uid'=>$v['uid']])->find()['real_name'];
		}
		$this->assign('list',$list);
		$this->display();
	}
	//检测是否出局
	public function setOutstatus(){
		$id = I('id');
		//该出局的人
		$chuju_user = M('Project_user_auto')->where(['id'=>$id])->find();
			M('Project_user_auto')->where(['id'=>$id])->setField('status',1);
			//将出局人 自动排在下边		
			$project_log = M('Project_user_auto')->order('id desc')->find();
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
			if($re){
				$this->success('操作成功');
			}else{
				$this->error('系统繁忙');
			}
	}
	//表格导入
	public function daoru() {
		ini_set('memory_limit','1024M');
		if (!empty($_FILES)) {
			$config = array(
					'exts' => array('xlsx','xls'),
					'maxSize' => 3145728000,
					'rootPath' =>"./Public/",
					'savePath' => 'Uploads/',
					'subName' => array('date','Ymd'),
			);
			$upload = new \Think\Upload($config);
			if (!$info = $upload->upload()) {
				$this->error($upload->getError());
			}
			vendor("PHPExcel.PHPExcel");
			$file_name=$upload->rootPath.$info['photo']['savepath'].$info['photo']['savename'];
			$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
			if ($extension == 'xlsx') {
				$objReader =\PHPExcel_IOFactory::createReader('Excel2007');
				$objPHPExcel =$objReader->load($file_name, $encode = 'utf-8');
			} else if ($extension == 'xls'){
				$objReader =\PHPExcel_IOFactory::createReader('Excel5');
				$objPHPExcel =$objReader->load($file_name, $encode = 'utf-8');
			}
			$sheet =$objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();//取得总行数
			$highestColumn =$sheet->getHighestColumn(); //取得总列数
			for ($i = 1; $i <= $highestRow; $i++) {
				//看这里看这里,前面小写的a是表中的字段名，后面的大写A是excel中位置
				$data['phone'] =$objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();
				$data['username'] =$objPHPExcel->getActiveSheet()->getCell("A" .$i)->getValue();
				$data['real_name'] =$objPHPExcel->getActiveSheet()->getCell("B" .$i)->getValue();
				$data['pwd'] =passwordEncryption($objPHPExcel->getActiveSheet()->getCell("E" .$i)->getValue());
				$data['add_time'] =time();
				$tuijian_phone = $objPHPExcel->getActiveSheet()->getCell("C" .$i)->getValue();
				//看这里看这里,这个位置写数据库中的表名
				$r =M('User')->add($data);
				$add['uid'] = $r;
				if($tuijian_phone){
					$user = M('User')->where(['phone'=>$tuijian_phone])->find();
					if(!$user){
						$this->error('推荐人不存在');
					}
					$add['pid'] = $user['uid'];
				}else{
					$add['pid'] =0;
				}
				
				$r =M('User_relation')->add($add);
			}
			$this->success('导入成功!');
		} else {
			$this->error("请选择上传的文件");
		}
	}
	//自定义排名列表
	public function rankingList(){
		$status = 999;
		$USER = new  UserModel();
		if(IS_GET){
			$username = I('username');
			if($username){
				$uid = $USER->getUserLikeUsername($username);
				$where['uid'] = $uid;
			}
		}
		$count             =  M('User_ranking')->where($where)->count();// 查询满足要求的总记录数
		$Page              = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		//setPageParameter($Page, array('username'=>$username,'status'=>$status));
		//给分页传参数
		$show       = $Page->show();// 分页显示输出
		$list =  M('User_ranking')
		->where($where)
		->order('id desc')
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		foreach($list as $k=>$v){
			$list[$k]['user'] = $USER->getUserByUid($v['uid']);
		}
		//处理状态 ，等级的格式化问题
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	//设置排名
	public function setRanking(){
		$id = I('id');
		$log = M('Project_user_auto')->where('id = '.$id)->find();
		$uid = $log['uid'];
		$ranking = I('ranking');
		$count = M('User_ranking')->where(['uid'=>$uid])->find();
		$data['uid'] = $uid;
		$data['ranking'] = $ranking;
		$data['add_time'] = time();
		if($count){
			//存在数据就修改否则就新增
			$re = M('User_ranking')->where(['uid'=>$uid])->save($data);
		}else{
			$re = M('User_ranking')->add($data);
		}
		//去掉排单记录
		M('Project_user_auto')->where('id = '.$id)->delete();
		if($re){
			$this->ajaxReturn(['status'=>1]);
		}else{
			$this->ajaxReturn(['status'=>-1]);
		}
	
	}
	//报单资料 
	public function setProjectInfo(){
		$id = I('id');
		$USER = new  UserModel();
		$log = M('Project_user_auto')->where('id = '.$id)->find();
		$log['user'] = $USER->getUserByUid($log['uid']);
		$tuijian = M('User_relation')->where(['uid'=>$log['uid']])->find();
		$log['tuijian'] = $USER->getUserByUid($tuijian['pid']);
		$this->assign('log',$log);
		$this->display();
	}
	//报单资料修改
	public function setProjectInfoRun(){
		$id = I('id');
		$where['id'] = $id;
		$phone = I('phone');
		$uid = M('User')->where("phone = '".$phone."'")->find()['uid'];
		$data['uid'] = $uid;
		$re = M('Project_user_auto')->where('id = '.$id)->save($data);
		if($re){
			$this->ajaxReturn(['status'=>1,'info'=>'修改成功']);
		}else{
			$this->ajaxReturn(['status'=>-1,'info'=>'修改失败']);
		}
	}
	//删除会员
	public function delUser(){
		$uid = I('uid');
		if(!$uid){
			$this->error('参数错误');
		}
		$USER = new UserModel();
		$list[] = M('Admin_recharge')->where(array('uid'=>$uid))->delete();
		$list[] = M('Answer')->where(array('uid'=>$uid))->delete();
		$list[] = M('Finance'.cuttable($uid))->where(array('uid'=>$uid))->delete();
		$list[] = M('Message')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_daishu_log')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_fenhong_log')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_orders')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_out')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_user')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_user_auto')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_user_pic')->where(array('uid'=>$uid))->delete();
		$list[] = M('Project_user_pic_log')->where(array('uid'=>$uid))->delete();
		$list[] = M('Question')->where(array('uid'=>$uid))->delete();
		$list[] = M('Recharge')->where(array('uid'=>$uid))->delete();
		$list[] = M('Recharge_pic')->where(array('uid'=>$uid))->delete();
		$list[] = M('Token')->where(array('uid'=>$uid))->delete();
		$list[] = M('Recharge_pic')->where(array('uid'=>$uid))->delete();
		$list[] = M('User')->where(array('uid'=>$uid))->delete();
		$list[] = M('User_address')->where(array('uid'=>$uid))->delete();
		$list[] = M('User_bank')->where(array('uid'=>$uid))->delete();
		$list[] = M('User_invitation_code')->where(array('uid'=>$uid))->delete();
		$list[] = M('User_money'.cuttable($uid))->where(array('uid'=>$uid))->delete();
		$list[] = M('Withdraw')->where(array('uid'=>$uid))->delete();
		$list[] = M('User_relation')->where(array('uid'=>$uid))->delete();
		if($list){
			$this->success('操作成功',U('User/showUserList'));
		}else{
			$this->error('操作失败');
		}
	}
}
