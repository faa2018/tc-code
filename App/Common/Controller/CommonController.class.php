<?php
namespace Common\Controller;
use Think\Controller;
use Common\Model\UserMoneyModel;
use Common\Model\UserModel;
use Common\Model\ConfigModel;
use Common\Model\ArtModel;
class CommonController extends Controller {
	protected $web_config;
	protected $AppID;
	protected $AppSecret;
	public function _initialize(){
		$M_CONFIG = new ConfigModel();
		$this->config = $M_CONFIG->getConfig();
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
	//微信自动授权
	public function userAuthorize($a) {
	    $redirect_uri = urlencode('http://sc1.yljz158.com');
	    $get_token_url = "http://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo#wechat_redirect";
	    header("Location:".$get_token_url);
	}
	/**
	 * 微信登录
	 */
	public function weiXinLogin($aaa = "") {
	    if (!isset($_GET["code"])) {
	        $this->userAuthorize($aaa);
	    }
	    $code = $_GET["code"];
	    // 获取access_token、openid等其他信息
	    $get_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->AppID."&secret=".$this->AppSecret."&code=".$code."&grant_type=authorization_code";
		$res = file_get_contents($get_token_url);
		//$res = $this->curl_get($get_token_url);
	    // 解析返回结果
	    $json_obj = json_decode($res, true);
	    if (!empty($json_obj['access_token'])) {
	        // 根据openid和access_token查询用户信息
	        return $json_obj;
	    }else {
	         
	        return -1001;
	    }
	}
	/**  
	 * 获取文件中存储的配置项 
	 * @access public|private|protected
	 * @param string xxx
	 * @return void|int|string|boolean|array        comment 
	 * @author      ZhangYi <1425568992@qq.com>
	 * @version	  v1.0.0 
	 * @copyright  2017-3-11 下午3:33:39
	*/
	protected function getConfig(){
		$config = C('WEB_CONFIG');
		return $config;
	}
	//验证码
	public function verify(){
		$config =    array(
				'fontSize'	=>	30,  // 验证码字体大小
				'length'	=>	4,    // 验证码位数
				'useCurve'	=>	false,
				'useNoise'	=>	false,  // 验证码杂点
				'imageW'	=>	220,
		        'codeSet'   =>  '1234567890',
		);
		$Verify =     new \Think\Verify($config);
		$Verify->entry();
	}
	//图片处理
	public function upload($file){
		switch($file['type'])
		{
			case 'image/jpeg': $ext = 'jpg'; break;
			case 'image/gif': $ext = 'gif'; break;
			case 'image/png': $ext = 'png'; break;
			case 'image/tiff': $ext = 'tif'; break;
			default: $ext = ''; break;
		}
		if (empty($ext)){
			return false;
		}
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath  =      './Uploads/'; // 设置附件上传目录
		// 上传文件
		$info   =  $upload->uploadOne($file);
		if(!$info) {
			// 上传错误提示错误信息
			$this->error($upload->getError());exit();
		}else{
			// 上传成功
			$pic=$info['savepath'].$info['savename'];
			$url='/Uploads'.ltrim($pic,".");
		}
		return $url;
	}
	
	/**
	 * 格式化错误提示信息
	 * @param unknown $status
	 * @return multitype:unknown string
	 */
	protected function ajaxReturnPushError($status=1){
		$error_info = C('ERROR_INFO');
		$data = array('status' => $status,'info'=> $error_info[$status]);
		$this->ajaxReturn($data);
	}
	//测试方法
	public function test(){
	    echo '测试';die;
	}
}

