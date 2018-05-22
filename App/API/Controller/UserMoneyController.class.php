<?php
namespace API\Controller;
use API\Controller\HomeController;
use Common\Model\UserMoneyModel;
use Common\Model\ConfigModel;
use Common\Api\UserMoneyApi;
use Common\Api\UserCoinApi;
use Common\Model\ArtModel;
use Common\Model\UserModel;
use Think\Model;
use Common\Model\UserRelationModel;
class UserMoneyController extends UserController {
	protected $USER_MONEY;
	protected $USER_MONEY_LOGIC;
	public function _initialize(){
		parent::_initialize();
		$this->USERMONEY = new UserMoneyModel($this->uid);
		$this->USER_MONEY_LOGIC = new UserMoneyApi($this->uid);
	}
	/**************************以下为展示页面方法*******************************************/

	//用户资金
	public function getUserMoney(){
		$USERMONEY = new UserMoneyModel($this->uid);
		$jifen = $USERMONEY->getUserMoneyByUid(2)['num'];
		$lingdongbi = $USERMONEY->getUserMoneyByUid(4)['num'];
		$back['jifen'] = $jifen;
		$back['lingdongbi'] = $lingdongbi;
		$this->ajaxReturn($back,'jsonp');
	}
	//积分转账(给其他会员)
	public function huzhuan(){
			$uid  = $this->uid;
			$num  = floatval(I('num'));
			$username = I('username');//PHONE
			$type   = I('type');
			$beizhu   = I('beizhu');
				
			if($this->config['jifen_kg']!=1){
				$back['status'] = '-3';
				$back['info'] = '该功能暂时关闭';
				$this->ajaxReturn($back,'JSONP');
			}		 
			if( $num <=0 || $num =='') {
				$back['status'] = '-2';
				$back['info'] = '请输入正确的金额';
				$this->ajaxReturn($back,'JSONP');
			}
			/* if(!is_int($num/50)){
			 $data['status'] = '-111';
			$data['info'] = '互转应为50的整数倍';
			$this->ajaxReturn($data);
			} */
			$USER = new UserModel();
			//检验用户是否存在
			$getuser = $USER->getUserByUsername($username);
			if(!$getuser||$getuser['uid']==$uid){
				$back['status'] = '-3';
				$back['info'] = '请输入正确的用户名';
				$this->ajaxReturn($back,'JSONP');
			}
			//检验余额
			$USERMONEY = new UserMoneyModel($uid);
			$usermoney = $USERMONEY->getUserMoneyByUid(2);
			if( $num > $usermoney['num'] ) {
				$back['status'] = '-5';
				$back['info'] = '余额不足';
				$this->ajaxReturn($back,'JSONP');
			}
			/* //检验一条线的人
			$up_limit = $this->findUpUser($getuser['uid']);
			if(!$up_limit){
				$down_limit = $this->findDownUser($this->uid,$getuser['uid']);
				if($down_limit){
					$data['info']   = '当前转账人和本人不在一个网体';
					$data['status'] = '-1';
					$this->ajaxReturn($data);
				}
			} */
			$userlist = $USER->getUserByUid($uid);
			//先扣自己的币
			$USER_MONEY_API = new UserMoneyApi();
			//$other_name = $USER->getUserByUid($getuser['uid'])['username'];
			$decmoney  = $USER_MONEY_API ->setUserMoneyLogic( $uid,$num, 2, 12, '向'.$getuser['username'].'转账扣除'.$num, 54,2);
			//加别人的币
			//$name = $USER->getUserByUid($uid)['username'];
			$bili =  floatval($this->config['zhuanhuan_bili']);
			if($decmoney){
				if($type==2){
					$incmoney = $USER_MONEY_API ->setUserMoneyLogic($getuser['uid'],$num, 1, 13, '来自'.$userlist['username'].'转账收入'.$num, 64,$type);
				}else{
					$incmoney = $USER_MONEY_API ->setUserMoneyLogic($getuser['uid'],$num/$bili, 1, 13, '来自'.$userlist['username'].'转账收入'.$num, 64,$type);
				}
			}
			//echo M('User_money2')->_sql();
			if($incmoney){
				$back['status'] = '1';
				$back['info'] = '转账成功';
				$this->ajaxReturn($back,'JSONP');
			}else{
				$back['status'] = '-5';
				$back['info'] = '系统繁忙';
				$this->ajaxReturn($back,'JSONP');
			}
	}	
    //积分转换(自己的两种资金按比例相互转换)
    public function zhuanhuan(){
			$uid = $this->uid;
			$money  = floatval(I('money'));
    		$type   = I('type');
			if($type==2){
				$type_other = 4;
			}else{
				$type_other = 2;
			}
			if($this->config['jifen_kg']!=1){
				$back['status'] = '-3';
				$back['info'] = '该功能暂时关闭';
				$this->ajaxReturn($back,'JSONP');
			}	
			//检测二级密码
			if(!$type || !in_array($type,array(2,4))){
				$back['status'] = '-5';
				$back['info'] = '请选择正确的转换种类';
				$this->ajaxReturn($back,'JSONP');
    		}
    		if(!$money||$money<0||!is_float($money)){
    			$back['status'] = '-5';
				$back['info'] = '请选择正确的金额';
				$this->ajaxReturn($back,'JSONP');
    		}
    	
    		$USERMONEY = new UserMoneyModel($uid);
    		$usermoney = $USERMONEY->getUserMoneyByUid($type);
    		
    		//判断是否有这么多金额
    		if($usermoney['num']<$money){
    			$back['status'] = '-5';
				$back['info'] = '您当前账户余额不足';
				$this->ajaxReturn($back,'JSONP');
    		}
    		//执行
    		$USERMONEYLOGIC = new UserMoneyApi();
			$bili = $this->config['zhuanhuan_bili'];
    		$a = $USERMONEYLOGIC->setUserMoneyLogic($uid, $money, 2, 22, '转换扣除', 22,$type);
			//echo M('User_money1')->_sql();die;
			if($type==2){
				$r = $USERMONEYLOGIC->setUserMoneyLogic($uid, $money/$bili, 1, 23, '转换增加', 23,4);
			}else{
				$r = $USERMONEYLOGIC->setUserMoneyLogic($uid, $money*$bili, 1, 23, '转换增加', 23,2);
			}
    		if($r){
    			$back['status'] = '1';
				$back['info'] = '转换成功';
				$this->ajaxReturn($back,'JSONP');
    		}else{
    			$back['status'] = '-5';
				$back['info'] = '系统繁忙';
				$this->ajaxReturn($back,'JSONP');
    		}
    	}
    	
	//查询我是不是要转的人的上线
	private function findUpUser($uid){
		$USERRELATION = new UserRelationModel();
		$user = $USERRELATION->getUserByUid($uid);
		if(!$user){
			return false;
		}
		if($this->uid != $user['pid']){
			return $this->findUpUser($user['pid']);
		}
		return true;
	}
	//查询要转的人是不是我的上线
	private function findDownUser($uid,$check_uid){
		$USERRELATION = new UserRelationModel();
		$user = $USERRELATION->getUserByUid($uid);
		if(!$user){
			return false;
		}
		if($check_uid != $user['pid']){
			return $this->findUpUser($user['pid']);
		}
		return true;
	}
	
   
    
         
    
}