<?php
namespace API\Controller;
use Common\Controller\CommonController;
use Common\Model\ConfigModel;
class HomeController extends CommonController {
	protected $config;
	protected $assess_token;
	public function _initialize(){
		$M_CONFIG = new ConfigModel();
		$this->config = $M_CONFIG->getConfig();
		parent::_initialize();
		
	}
	//使用curl的GET请求数据  返回数组
	protected function curl_get($http){
	    //初始化
	    $ch = curl_init();
	    //设置选项，包括URL
	    curl_setopt($ch, CURLOPT_URL, $http);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    //执行并获取HTML文档内容
	    $output = curl_exec($ch);
	    //释放curl句柄
	    curl_close($ch);
	    //打印获得的数据
	    $result = json_decode($output,true);
	    return $result;
	}
	//财务日志
	public function addFinance($uid,$type,$sz_type,$money,$content,$money_type=1){
		//sz_type 1 收入 2 支出
		//$type  1余额 2 积分
		if(!$uid){
			$data['uid'] =$this->uid;
		}else{
			$data['uid'] = $uid;
		}
	
		if(!$data['uid']){
			$this->ajaxReturn(['status'=>-1,'msg'=>'系统繁忙，请稍后再试'],'JSONP');
		}
		$data['uid']=$uid;
		$data['type']=$type;
		$data['sz_type']=$sz_type;
		$data['money']=$money;
		$data['content']=$content;
		$data['money_type']=$money_type;
		$data['add_time']=time();
		return M('Finance'.cuttable($uid))->add($data);
	}
	//微信自动授权
	public function userAuthorize($a) {
	    $redirect_uri = urlencode('http://pan2.money654.com');
	    $get_token_url = "http://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo#wechat_redirect";
	    header("Location:".$get_token_url);
	}
	/**
	 * 微信登录
	 */
	public function weiXinLogin($aaa = "") {
	    $appid='wx911b7cf7b6169721';
	    $secret='b0cb4af091c7a02f7e278e0b842e3a23';
	    if (!isset($_GET["code"])) {
	        $this->userAuthorize($aaa);
	    }
	    $code = $_GET["code"];
	    // 获取access_token、openid等其他信息

	    $get_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
	    $res = file_get_contents($get_token_url);
	    // 解析返回结果
	    $json_obj = json_decode($res, true);
	    if (!empty($json_obj['access_token'])) {
	        // 根据openid和access_token查询用户信息
	        return $json_obj;
	    }else {
	         
	        return -1001;
	    }
	}

}