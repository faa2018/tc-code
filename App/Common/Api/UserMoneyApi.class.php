<?php
namespace Common\Api;
use Common\Model\FinanceModel;
use Common\Model\UserMoneyModel;
use Common\Model\MessageModel;
use Common\Model\UserModel;
class UserMoneyApi  {
	/**  
	 * 处理用户资金的功能方法 
	 * @access public|private|protected
	 * @param string xxx
	 * @return void|int|string|boolean|array        comment 
	 * @author      ZhangYi <1425568992@qq.com>
	 * @version	  v1.0.0 
	 * @copyright  2017-7-3 下午9:39:42
	*/
	private $uid;
	public function __construct(){
	}
	/**
	 * 处理资金变动时的小功能方法
	 * @param unknown $money
	 * @param unknown $sz_type
	 * @param unknown $type
	 * @param unknown $content
	 * @param unknown $message_type
	 * @param number $money_type
	 * @param number $project_id
	 * @return Ambigous <\Common\Model\Ambigous, \Think\mixed, boolean, unknown, string>
	 *								金额		增加减少修改 日志类型	财务日志内容  消息类型			币种	       
	 */
	public function setUserMoneyLogic($uid,$money,$sz_type,$type,$content,$message_type,$money_type=1,$project_id=0){
		//处理金额变动
		$USER = new UserMoneyModel($uid);
		$r[]  =  $USER->updateUserMoney($money,$sz_type,$money_type);
		//获取用户名
		$M_USER = new UserModel();
		$username = $M_USER->getUserByUid($uid)['username'];
		//处理财务日志变动
		$FINANCE = new FinanceModel();
		$r[]  =  $FINANCE->addFinance($uid, $type, $sz_type, $money, $content, $username,$money_type,$project_id);
		/* //发送信息
		$MESSAGE = new MessageModel();
		$r[]  =  $MESSAGE->addMessage($uid, $message_type, $content); */
		return $r;
	}
	/**
	 * 个人账号两个资金类型互转
	 * 必须是1:1
	 * @param unknown $uid
	 * @param unknown $money
	 * @param unknown $dec_money_type
	 * @param unknown $inc_money_type
	 * @return Ambigous <\Common\Api\Ambigous, \Common\Model\Ambigous, \Think\mixed, boolean, unknown, string>
	 */
	public function setMoneyExchangeLogic($uid,$money,$dec_money_type,$inc_money_type){
		switch ($dec_money_type.$inc_money_type){
			case 21:
				$type_dec = "11";
				$content_dec="转换--收益积分转消费积分";
				$type_inc = "51";
				$content_inc="转换--购物积分获取";
				break;
			case 23:
				$type_dec = "13";
				$content_dec="转换--收益积分转购物积分";
				$type_inc = "53";
				$content_inc="转换--消费积分获取";
				break;
			case 24:
				$type_dec = "14";
				$content_dec="转换--收益积分转股权积分";
				$type_inc = "54";
				$content_inc="转换--股权积分获取";
				break;
		}
		$r[] = $this->setUserMoneyLogic($uid, $money, 2, $type_dec, $content_dec, $type_dec,$dec_money_type);
		$r[] = $this->setUserMoneyLogic($uid, $money, 1, $type_inc, $content_inc, $type_dec,$inc_money_type);
		return $r;
	}
}

