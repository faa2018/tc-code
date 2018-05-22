<?php 
	//中文编码
	header("Content-type:text/html;charset=utf-8");
	include_once("function.php") ;
	/**
	*   基础表 用于存储方法 
	*	链接数据库方法
	*	添加财务日志方法
	*	获取列表方法
	*/
	class base{
		//设置链接数据库参数
		private $db;
		private $host;
		private $user;
		private $pwd;
		private $name;
		private $charset;
		protected $config;
		function __construct(){
			//获取数据库链接
			$this->host    = 'localhost';
			$this->user    = 'huasu101';
			$this->pwd     = 'shHuasu123';
			$this->name    = 'huasu101';
			$this->charset = 'utf8';
			//获取配置项
			$this->config=$this->config();
			//不执行自动程序
			if($this->config['auto_switch']!=1){
				return;
			}
			
		}
		//系统参数的数组
		protected function config(){
			//$config = require_once('../App/Common/Conf/money_config.php');\
			$sql = "SELECT * FROM ztp_config";
			$config = $this->select($sql);
			foreach ($config as $k=>$v){
				$config_true[$v['key']] = $v['value'];
			}
			unset($config);
			return $config_true;
		}
		/***************************数据库链接部分*******************************************/
		//执行语句
		protected function query($sql){
			$link= mysql_connect($this->host,$this->user,$this->pwd) or die("数据库连接失败");
			mysql_select_db($this->name,$link);
			mysql_set_charset($this->charset);
			$result=mysql_query($sql,$link);
			mysql_close($link);
		}
		//返回一个结果集 只查一个的时候用
		protected function find($sql){
			$link= mysql_connect($this->host,$this->user,$this->pwd) or die("数据库连接失败");
			mysql_select_db($this->name,$link);
			mysql_set_charset($this->charset);
			$result=mysql_query($sql,$link);
			$user=mysql_fetch_assoc($result);
			mysql_free_result($result);
			mysql_close($link);
			return $user;
		}
		//返回一个结果集 查多个的时候用
		protected function select($sql){
			$res="";
			$link= mysql_connect($this->host,$this->user,$this->pwd) or die("数据库连接失败");
			mysql_select_db($this->name,$link);
			mysql_set_charset($this->charset);
			$result=mysql_query($sql,$link);
			while($user=mysql_fetch_assoc($result)){
				$res[]=$user;
			}
			mysql_free_result($result);
			mysql_close($link);
			return $res;
		}
		//////////////////////////////////////////////////////////////////////////////////////
		/**********************************************************************/
		//增加财务日志
		protected function addFinance($uid,$money,$type,$log_id){
			$sql="INSERT INTO  ztp_finance (member_id,money,type,add_time,content,money_type,currency_id,log_id) VALUES (".$user.",'".$money."','".$type."',".time().",".$money.",1,-1,".$log_id.")";
			$this->sqlCarry($sql);
			unset($user);
			unset($money);
			unset($type);
			unset($log_id);
		}
		/**
		 * 修改用户资金并增加财务日志
		 * @param unknown $uid
		 * @param unknown $money		变动金额
		 * @param unknown $sz_type		收支 1收入  2支出
		 * @param unknown $type			财务日志种类
		 * @param unknown $content		财务日志内容
		 * @param unknown $message_type	信息表种类
		 * @param unknown $money_type	币种类型 1人民币
		 * @param unknown $project_id	项目id
		 */
		protected function setUserMoneyLogic($uid,$money,$sz_type,$type,$content,$message_type,$money_type=1,$project_id=0){
			if($sz_type == 1){
				$fuhao = " + ";
			}else{
				$fuhao = "-";
			}
			//处理金额变动
			$sql =" 
			UPDATE ztp_user_money".cuttable($uid)." 
			SET num=num".$fuhao.$money." 
			WHERE uid=".$uid." and type=".$money_type;
			$this->query($sql);
			//处理财务日志变动
			$sql = "SELECT username FROM ztp_user WHERE uid = ".$uid;
			$username = $this->find($sql)['username'];
			$sql = "
			INSERT INTO ztp_finance".cuttable($uid)."
			(uid,project_id,type,sz_type,money,content,add_time,money_type,username)
			VALUES ('".$uid."','.".$project_id."','".$type."','".$sz_type."','".$money."','".$content."','".time()."','".$money_type."','".$username."')		
			";
			$this->query($sql);
			//发送信息表
			$sql = "
			INSERT INTO ztp_message
			(uid,type,content,add_time) 
			VALUES('".$uid."','".$message_type."','".$content."','".time()."')";
			$this->query($sql);
		}
	}
?>