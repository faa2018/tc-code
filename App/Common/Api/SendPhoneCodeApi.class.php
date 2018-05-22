<?php
namespace Common\Api;
class SendPhoneCodeApi  {
	private $user;
	private $pwd;
	private $phone;
	public function __construct($phone){
		$this->user = "jkwl527";
		$this->pwd  = "jkwl527800";
		if(!$phone){
			return '-23001';
		}
		$this->phone = $phone; 
	}	
	/** 发送短信
	 * @param $phone 电话号码
	 * @param $user  短息接口用户名
	 * @param $get_content  短信用途 	例：【XXX】您的验证码为12345,2016-8-3 14:24:8申请用于'.$get_content.'，30分钟内有效，请勿告诉他人
	 * @param $code_name  session的后缀 不同验证码后缀不要相同与验证有关
	 * @param $pass  短信接口密码
	 * @return mixed 错误信息
	 */
	/* public function sendPhoneJufu($code_name,$get_content,$send_name){
		$time=session('time'.$code_name.$this->phone);
		$this->checkTime($time);//检测超时
		$code=$this->getCode();//获取验证码
		session('code'.$code_name.$this->phone,$code);
		session('time'.$code_name.$this->phone,time(),1800);
		//短信接口
		$smsapi = "http://web.900112.com";
		$content="【".$send_name."】您的验证码为".$code.','.date('Y-m-d').'申请用于'.$send_name.$get_content.'，30分钟内有效，请勿告诉他人';
		$sendurl = $smsapi.'?action=send&userid=&account='.$this->user.'&password='.md5($this->pwd).'&mobile='.$this->phone.'&content='.$content.'&sendTime=&extno=';
		$result =file_get_contents($sendurl) ;
		$result = json_decode($result);
		return $result->successCounts;
	} */
	function send($code_name,$content=''){
		$time = session('send_time');
		$now_time = time();
		if($now_time-$time<60){
			return "您当前发送过于频繁，请一分钟之后发送";
		}
		 $check = $this->checkTime($time);//检测超时
		if($check == -1){
			return "您当前发送过于频繁，请三分钟之后发送";
		} 
		$code=$this->getCode();//获取验证码
		session('code'.$code_name.$this->phone,$code);
		session('time'.$code_name.$this->phone,time(),1800);
		session('send_time',time());
		if(empty($content)){
			$content="【传奇汽车俱乐部】您的验证码为".$code."，10分钟有效，请勿告诉他人。";//要发送的短信内容
		}
	//企业ID $userid
		$userid = '1111';
		//用户账号 $account
		$account = $this->user;
		//用户密码 $password
		$password = $this->pwd;
		//发送到的目标手机号码 $mobile
		$mobile = $this->phone;
		//短信内容 $content
	
		
		
		//发送短信（其他方法相同）
		$gateway = "http://sh2.cshxsp.com/sms.aspx?action=send&userid={$userid}&account={$account}&password={$password}&mobile={$mobile}&content={$content}&sendTime=";
		$result = file_get_contents($gateway);
		$xml = simplexml_load_string($result);
		if($xml->successCounts == 1){
			return 1;
		}else{
			return $xml->message;
		}
	}
	public function sendMessage($content="",$send_name="传奇汽车俱乐部"){
	//企业ID $userid
		$userid = '1111';
		//用户账号 $account
		$account = $this->user;
		//用户密码 $password
		$password = $this->pwd;
		//发送到的目标手机号码 $mobile
		$mobile = $this->phone;
		//短信内容 $content	
		//发送短信（其他方法相同）
		$gateway = "http://sh2.cshxsp.com/sms.aspx?action=send&userid={$userid}&account={$account}&password={$password}&mobile={$mobile}&content={$content}&sendTime=";
		$result = file_get_contents($gateway);
		$xml = simplexml_load_string($result);
		if($xml->successCounts == 1){
			return 1;
		}else{
			return $xml->message;
		}
	}
	private function getCode(){
		return rand(100000, 999999);
	}
	//判断时间是否超时
	public function checkTime($time,$time_limit=180){
		if ((time()-$time)<$time_limit&&!empty($time)){
			return '-1';
		}
	}
}