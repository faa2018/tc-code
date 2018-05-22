<?php
/** 发送短信
 */
function sendPhone($phone,$content,$name,$key){
	//$url = 'http://utf8.sms.webchinese.cn/?Uid='.$name.'&Key='.$key.'&smsMob='.$phone.'&smsText='.$content;
	$smsapi = "http://api.smsbao.com/";
	$url = $smsapi."sms?u=".$name."&p=".$key."&m=".$phone."&c=".urlencode($content);
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 ); //设置连接等待时间
	curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
	$data=curl_exec($ch);
	curl_close($ch);
	if($data==1){
		return array('status'=>1,'msg'=>'短信发送成功!');
	}else{
		return array('status'=>-1,'msg'=>'短信发送失败!');
	}
}
/**
 * 生成订单
 */
function getOrderNo(){
	$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	for($i=0;$i<3;$i++){
		$num = rand(0,24);
		$a .= $str[$num];
	}
	return time().'-'.$a;
}
/**
 * get请求
 */
function vget($url) { // 模拟获取内容函数
	$curl = curl_init (); // 启动一个CURL会话
	if (true) {
		//以下代码设置代理服务器
		//代理服务器地址
		curl_setopt ( $curl, CURLOPT_PROXY, $GLOBALS ['proxy'] );
	}
	curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
	curl_setopt ( $curl, CURLOPT_USERAGENT, USER_AGENT ); // 模拟用户使用的浏览器
	@curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
	curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
	curl_setopt ( $curl, CURLOPT_HTTPGET, 1 ); // 发送一个常规的Post请求
	curl_setopt ( $curl, CURLOPT_COOKIEFILE, COOKIE_FILE ); // 读取上面所储存的Cookie信息
	curl_setopt ( $curl, CURLOPT_TIMEOUT, 120 ); // 设置超时限制防止死循环
	curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
	$tmpInfo = curl_exec ( $curl ); // 执行操作
	if (curl_errno ( $curl )) {
		echo 'Errno' . curl_error ( $curl );
	}
	curl_close ( $curl ); // 关闭CURL会话
	return $tmpInfo; // 返回数据
}
function checkPhoneCode($code,$code_name,$phone){
	if($code!=session('code'.$code_name.$phone)){
		return false;
	}
	return true;
}
/**
 * 验证码检查
 */
function check_verify($code, $id = ""){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/* 函数功能：对二维数组以某一键名进行分组相加，返回新的二维数组 
 * 参数说明：$arr-源数组；$new_arr-相加后得到的新数组；$target_key-要分组的键名 
 */
function add_array($arr, &$new_arr, $target_key) { 
    $num = count($new_arr); //计算新数组的大小，新数组也是二维的，这里计算的是第一维 
    for ($i = 0; $i < $num; $i++) { 
        //循环新数组 
        //if块主要判断当前分组的键名是否已经存在于新数组中，避免重复 
        //由于该函数是被循环调用的，而新数组可能有多于1个的元素，所以必须对新数组中的每一个元素都进行比较， 
        //新数组的元素是一个一维数组，$i动态的比较新的二维数组中的分组键名 
        if ($arr[$target_key] != $new_arr[$i][$target_key]) {//判断新数组中的分组键名是否跟当前源数组中的分组键名相等 
            $cmp_num++; //如果不相等，比较次数自增1 
        } else {//如果相等，说明当前分组键名已经存在 
            $tar_exist = true; //设置存在标识为true 
            $tar_key = $i; //返回当前分组键名在新数组中的数字索引 
            break; //跳出循环 
        } 
    } 
    //如果比较次数跟新数组大小一样，说明当前分组键名不在新数组中，设置存在标识为false 
    if ($cmp_num == $num) 
        $tar_exist = false; 
    if ($tar_exist) {//如果分组键名已经存在，对该分组的数组元素进行相加 
        foreach ($arr as $key => $value) { 
            if ($key != $target_key) {//分组键名对应的元素值不相加 
                $new_arr[$tar_key][$key]+=$value; //其余的元素值进行相加 
            } 
        } 
    } else { 
        //如果分组键名不存在 
        //设置新的分组键名，并对该分组的数组元素进行相加 
        //新数组的第一维使用$num参数来分辨当前分组的秩序 
        //由于$num实际上就是新数组中，按键名分组的个数，并且是从0开始，所以新的分组在新数组中的索引直接用$num即可， 
        //而不须要$num+1 
        $new_arr[$num][$target_key] = $arr[$target_key]; 
        foreach ($arr as $key => $value) { 
            if ($key != $target_key) {//分组键名对应的元素值不相加 
                $new_arr[$num][$key]+=$value; //其余的元素值进行相加 
            } 
        } 
    } 
} 
/**
 * 获取某个IP地址所在的位置
 * @param string $ip	ip地址
 * @return Ambigous <multitype:, NULL, string>
 */
function getIpArea($ip){
	$Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
	$area = $Ip->getlocation($ip); // 获取某个IP地址所在的位置
	return $area?$area['country'].$area['area']:"未知地址";
}
/**
 * 电话号码中间***
 * @param string $phone	电话号码
 * @return mixed
 */
function hidtel($phone){
	$IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话
	if($IsWhat == 1){
		return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);
	}else{
		return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
	}
}
/**
 * 验证18位身份证（计算方式在百度百科有）
 * @param  string $id_card 身份证
 * return boolean
 */
function regexCard($id_card){
	$set = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
	$ver = array('1','0','x','9','8','7','6','5','4','3','2');
	$arr = str_split($id_card);
	$sum = 0;
	for ($i = 0; $i < 17; $i++){
		if (!is_numeric($arr[$i])){
			return false;
		}
		$sum += $arr[$i] * $set[$i];
	}
	$mod = $sum % 11;
	if (strcasecmp($ver[$mod],$arr[17]) != 0){
		return false;
	}
	return true;
}
/**
 * 使用正则验证数据
 * @access public
 * @param string $value  要验证的数据
 * @param string $rule 验证规则
 * @return boolean
 */
function regex($value,$rule) { 
	$validate = array(
			'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
			'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
			'currency'  =>  '/^\d+(\.\d+)?$/',
			'number'    =>  '/^\d+$/',
			'zip'       =>  '/^\d{6}$/',
			'integer'   =>  '/^[-\+]?\d+$/',
			'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
			'english'   =>  '/^[A-Za-z]+$/',
			'name'		=>  '/[^\D]/g',
			'img'		=>	'/\.(jpg|gif|png)$/i',
			'phone'		=>  '/^1[34578]\d{9}$/',
			'password'  =>  '/^[a-zA-Z0-9]{6,20}$/',
			'bankcard'  =>  '/^(\d{16,19})$/',
			'username'  =>  '/^[a-zA-Z0-9_]{5,20}$/',
			'id_card'  =>  '/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/',
			//'username'  =>  '/^[a-zA-Z\u4e00-\u9fa5][a-zA-Z0-9_\u4E00-\u9FA5]{5,15}$/',
	);
	// 检查是否有内置的正则表达式
	if(isset($validate[strtolower($rule)]))
		$rule       =   $validate[strtolower($rule)];
	return preg_match($rule,$value)===1;
}
/**
 * sql注入检测
 * */
function checkSql($str){
	$str=preg_replace("/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i",' ',$str);
	return $str;
}
/**
 * 截取字符串
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool $suffix
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
	if(function_exists("mb_substr")){
		$slice= mb_substr($str, $start, $length, $charset);
	}elseif(function_exists('iconv_substr')) {
		$slice= iconv_substr($str,$start,$length,$charset);
	}else{
		$re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
		$re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
		$re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
		$re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	$fix='';
	if(strlen($slice) < strlen($str)){
		$fix='';
	}
	return $suffix ? $slice.$fix : $slice;
}
/**
 * 人民币格式化
 * @param $num
 * @return array|bool|string
 */
function num_format($num){
	if(!is_numeric($num)){
		return false;
	}
	$rvalue='';
	$num = explode('.',$num);//把整数和小数分开
	$rl = !isset($num['1']) ? '' : $num['1'];//小数部分的值
	$j = strlen($num[0]) % 3;//整数有多少位
	$sl = substr($num[0], 0, $j);//前面不满三位的数取出来
	$sr = substr($num[0], $j);//后面的满三位的数取出来
	$i = 0;
	while($i <= strlen($sr)){
		$rvalue = $rvalue.','.substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
		$i = $i + 3;
	}
	$rvalue = $sl.$rvalue;
	$rvalue = substr($rvalue,0,strlen($rvalue)-1);//去掉最后一个逗号
	$rvalue = explode(',',$rvalue);//分解成数组
	if($rvalue[0]==0){
		array_shift($rvalue);//如果第一个元素为0，删除第一个元素
	}
	$rv = $rvalue[0];//前面不满三位的数
	for($i = 1; $i < count($rvalue); $i++){
		$rv = $rv.','.$rvalue[$i];
	}
	if(!empty($rl)){
		$rvalue = $rv.'.'.$rl;//小数不为空，整数和小数合并
	}else{
		$rvalue = $rv;//小数为空，只有整数
	}
	return $rvalue;
}
/**
 * 随机数字英文字符
 * @param $param 长度
 * @return string 随机数
 */
function getRandom($param){
	$str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$key = "";
	for($i=0;$i<$param;$i++)
	{
		$key .= $str{mt_rand(0,strlen($str))};    //生成php随机数
	}
	return $key;
}
/**
 *  给分页传参数
 * @param  mixed $Page 分页对象
 * @param array $parameter 传参数组
 */
function setPageParameter($Page,$parameter){
	foreach ($parameter as $k=> $v){
		if (isset($v)){
			$Page->parameter[$k]=$v;
		}
	}
}
/**
 * 导出数据为excel表格
 *
 * @param $data 一个二维数组,结构如同从数据库查出来的数组
 * @param $title excel的第一行标题,一个数组,如果为空则没有标题
 * @param $filename 下载的文件名
 *        	@examlpe
 *        	$stu = M ('User');
 *        	$arr = $stu -> select();
 *        	exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
 */
function exportexcel($data = array(), $title = array(), $filename = 'report') {
	header ( "Content-type:application/octet-stream" );
	header ( "Accept-Ranges:bytes" );
	header ( "Content-type:application/vnd.ms-excel" );
	header ( "Content-Disposition:attachment;filename=" . $filename . ".xls" );
	header ( "Pragma: no-cache" );
	header ( "Expires: 0" );
	// 导出xls 开始
	if (! empty ( $title )) {
		foreach ( $title as $k => $v ) {
			$title [$k] = iconv ( "UTF-8", "GB2312", $v );
		}
		$title = implode ( "\t", $title );
		echo "$title\n";
	}
	if (! empty ( $data )) {
		foreach ( $data as $key => $val ) {
			foreach ( $val as $ck => $cv ) {
				$data [$key] [$ck] = iconv ( "UTF-8", "GB2312", $cv );
			}
			$data [$key] = implode ( "\t", $data [$key] );
		}
		echo implode ( "\n", $data );
	}
}
/**
* 对查询结果集  根据二维数组中的字段 进行排序 
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param array $sortby 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}
/**
 *  发送邮箱
 * @param String $emailHost 您的企业邮局域名
 * @param String $emailUserName 邮局用户名(请填写完整的email地址)
 * @param String $emailPassWord 邮局密码
 * @param String $formName 邮件发送者名称
 * @param String $email  收件人邮箱，收件人姓名
 * @param String $title	发送标题
 * @param String $body	发送内容
 * @return boolean
 */
function sendEmail($emailHost,$emailUserName,$emailPassWord,$formName,$email,$title,$body) {
    // 以下内容为发送邮件
    require_once(APP_PATH.'Common/Api/class.phpmailer.php');//下载的文件必须放在该文件所在目录
    $mail=new PHPMailer();//建立邮件发送类
    $mail->IsSMTP();//使用SMTP方式发送 设置设置邮件的字符编码，若不指定，则为'UTF-8
    $mail->Host=$emailHost;//'smtp.qq.com';//您的企业邮局域名
    $mail->SMTPAuth=true;//启用SMTP验证功能   设置用户名和密码。
    $mail->Username=$emailUserName;//'mail@koumang.com'//邮局用户名(请填写完整的email地址)
    $mail->Password=$emailPassWord;//'xiaowei7758258'//邮局密码
    $mail->From=$emailUserName;//'mail@koumang.com'//邮件发送者email地址
    $mail->FromName=$formName;//邮件发送者名称
    $mail->AddAddress($email);// 收件人邮箱，收件人姓名
    $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
    $mail->Subject="=?UTF-8?B?".base64_encode($title)."?=";
    $mail->Body=$body; //邮件内容
    $mail->AltBody = "这是一封HTML格式的电子邮件。"; //附加信息，可以省略
	$mail->Send();
    return $mail->ErrorInfo;
}
//密码加密
function passwordEncryption($password){
	return md5(md5($password).C('PASSWORDSUFFIX'));
}
//加密密码验证 $pwd 前台  $password 数据库数据
function passwordVerification($pwd,$password){
	if(passwordEncryption($pwd)==$password){
		return true;
	}
	return false;
}
//分表原则 3.10 zy 
function cuttable($number){
	return $number%10;
}
//分表原则 3.10 zy
function cuttableMin($number){
	return $number%4;
}
//分表原则 3.10 zy
function cuttableMax($number){
	return $number%20;
}
//格式化 不需要样式的文字 zy
function formatTextNoType($text){
	return strip_tags(trim($text));
} 
//格式化 需要样式的文字 zy
function formatTextNeedType($text){
	return trim(addslashes(htmlspecialchars(($text))));
}
//格式化 需要样式的文字 编辑器 zy
function formatTextNeedTypeK($text){
	return trim(addslashes($text));
}
//  下载csv表格
function export_csv($filename,$data) {
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    echo $data;
}
//格式化会员等级
function userStatus($status){
	return C('USER_STATUS')[$status];
}