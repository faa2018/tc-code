<?php 
	//负责处理虚拟币相关的自动程序
	//中文编码
	header("Content-type:text/html;charset=utf-8");
	include_once("./base.php") ;
	class coin extends  base{
		//准备数据   配置项（比例，增加字段） 数据库  
		function __construct(){
			set_time_limit(0);
			include_once("Easycoin.class.php") ;
			parent::__construct();
		}
		//开始自动程序
		public function run(){
			$this->runCoinPay();
		}
		//自动检测充币程序
		private function runCoinPay(){
			//获取要接收充值的币种
			$coin = $this->getCoinOpen();
			foreach ($coin as $coin_k=>$coin_v){
				//链接钱包 
				$coin_qianbao = $this->linkCoinQianbao($coin_v['rpc_username'],$coin_v['rpc_pwd'],$coin_v['rpc_host'],$coin_v['rpc_port']);
				//获取钱包的充值记录
				$coin_log = $coin_qianbao->listtransactions('*',100,0);
				if(!$coin_log)continue;
				//循环处理每条记录
				foreach($coin_log as $v){
					//根据钱包记录的信息 判断要处理的数据
					if($v['category']=='receive' && $v['account'] != ''){
						//查询本条记录是否处理过
						$coinPayLog = $this->getCoinPayLogByTxid($v['txid']);
						if($coinPayLog){
							continue;
						}
						//对应地址查询属于哪个人
						$user = $this->getUserByCoinAddress($v['address']);
						if(!$user){
							continue;
						}
						//换算相当于充值了多少钱
						$money = (float)$v['amount'];
						//加充值表
						$this->addCoinPay($user['uid'],$v['address'],$v['account'],$v['amount'],$coin_v['bili'],$coin_v['id'],$v['txid']);
						//判断如果确认数大于3 代表充值成功
						if($v['confirmations']>3){
							//加钱 加财务日志
							$this->addUserCoin($user['uid'], $money,$coin_v['id']);
							//修改充币表的状态
							$this->updateCoinPayByTxid($v['txid']);
						}
					}
				}
			}
		}
		//加用户币
		private function addUserCoin($uid,$money,$money_type){
			//处理金额变动
			$sql ="
			UPDATE ztp_user_coin".cuttable($uid)."
			SET num=num+".$money."
			WHERE uid=".$uid." and type=".$money_type;
			$this->query($sql);
		}
		//查询钱包最近的充值记录
		private function linkCoinQianbao($name,$pwd,$host,$port,$url=null){
			$easycoin=new Easycoin($name,$pwd,$host,$port,$url);
			return $easycoin;
		}
		//获取接收充币的币种
		private function getCoinOpen(){
			$sql = "SELECT id,name,bili,rpc_username,rpc_pwd,rpc_host,rpc_port FROM ztp_coin where switch=1";
			return $this->select($sql);
		}
		//根据txid获取充币记录
		private function getCoinPayLogByTxid($txid){
			$sql = "SELECT id FROM ztp_coin_pay WHERE txid = '".$txid."'";
			return $this->find($sql);
		}
		//根据地址查询会员账号
		private function getUserByCoinAddress($address){
			$sql = "SELECT uid FROM ztp_user_coin_address WHERE url = '".$address."'";
			return $this->find($sql);
		}
		//增加充值订单表
		private function addCoinpay($uid,$url,$name,$num,$bili,$coin_type,$txid){
			$sql = "INSERT INTO ztp_coin_pay(uid,url,name,num,bili,coin_type,add_time,txid,status) 
					VALUES('".$uid."','".$url."','".$name."','".$num."','".$bili."','".$coin_type."','".time()."','".$txid."','0')";
			$this->query($sql);
		}
		//充值成功 修改状态
		private function updateCoinPayByTxid($txid){
			$sql = "UPDATE ztp_coin_pay set status = 1 , check_time =".time()." where txid='".$txid."'";
			$this->query($sql);
		}	
	}
	$coin=new coin();
	$coin->run();
?>