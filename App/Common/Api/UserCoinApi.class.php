<?php
namespace Common\Api;
use Common\Model\CoinModel;
class UserCoinApi  {
	/**
	 * 修改用户资金并增加财务日志
	 * @param unknown $uid
	 * @param unknown $money		变动金额
	 * @param unknown $sz_type		收支 1收入  2支出
	 * @param unknown $type			财务日志种类
	 * @param unknown $content		财务日志内容
	 * @param unknown $message_type	消息表 type
	 * @param unknown $money_type	币种类型 1人民币
	 * @param unknown $project_id	项目id
	 */
	public function setUserCoinLogic($uid,$money,$sz_type,$coin_type=1){
		//处理金额变动
		$COIN = new CoinModel();
		$r[]  =  $COIN->updateUserCoin($uid,$money,$sz_type,$coin_type);
		/* //处理财务日志变动
		$FINANCE = new FinanceModel();
		$r[]  =  $FINANCE->addFinance($uid, $type, $sz_type,$money,$content,$coin_type,$project_id);
		//发送信息
		$MESSAGE = new MessageModel();
		$r[]  =  $MESSAGE->addMessage($uid, $message_type, $content); */
		return $r;
	}
}

