<?php 
	//负责每日自动结算的自动程序
	//中文编码
	header("Content-type:text/html;charset=utf-8");
	
	include_once("/www/web/huasu-nvxing_com/public_html/AUTO_Z/base.php");
	class auto extends  base{
		//准备数据   配置项（比例，增加字段） 数据库  
		function __construct(){
			set_time_limit(0);
			parent::__construct();
		}
		public $user_get_money;
		//开始自动程序
		public function run(){
                    $sql = "UPDATE ztp_config SET value = 111 where id in (84)";   
 		    $this->query($sql);	
echo 123456789;
		    error_reporting( E_ALL&~E_NOTICE );
		    //设置时区
		    date_default_timezone_set('PRC');
		    $this->runFenhongMoneyDay();
			echo "结算完成";
			die;
		}
		//每日回馈积分奖励
		//当天所有报单总额的20% 根据份数回馈积分
		private function runFenhongMoneyDay(){
		    //获取当前0点时间
		    $time = strtotime(date('Ymd'));
		    //判断本日是否执行回馈积分
		    $time_day_limit = explode(',', $this->config['time_day_limit']);
		    $today_w = date("w");
		    if(in_array($today_w, $time_day_limit)){
				//本日执行过回馈积分
$sql = "SELECT id FROM ztp_project_fenhong_log WHERE money>0 and add_time = ".$time;
				$log_benri = $this->find($sql);
				if($log_benri){
					return;
				}
                //判断时间是否应该结束
    		    $sql = "select id,uid,gufen,money,money_huoqu from ztp_project_user_auto where status = 0 and add_time < ".$time;
    		    $project = $this->select($sql);//谁可以拿
    		    //统计一共多少股份
    		    $gufen_zong = 0;
    		    foreach ($project as $k=>$v){
    		        $gufen_zong += $v['gufen'];
    		    }
				//记录结算
				$sql = "INSERT INTO ztp_project_fenhong_log(money,gufen,add_time) VALUES('".$this->config['project_money_count_day']*$this->config['bili_fenhong_day']."','".$gufen_zong."','".$time."')";
				$this->query($sql);
				//没有新增业绩
				if($this->config['project_money_count_day'] == 0){
					return;
				}
    		    //计算每股多少钱
    		    $money_gufen = $this->config['project_money_count_day']*$this->config['bili_fenhong_day']/$gufen_zong;
    		    $money_gufen = substr(sprintf("%.3f",$money_gufen),0,-1);
				//结算静态
    		    foreach ($project as $k=>$v){
    		        $this->runStaticMoney($v,$time,$money_gufen);
    		    }
    		    //统计每个人本日的总收入
    		    foreach ($this->user_get_money as $k=>$v){
    		        $sql = "SELECT username FROM ztp_user WHERE uid = ".$k;
    		        $username = $this->find($sql)['username'];
    		        $sql = "INSERT INTO ztp_project_jifen_day_zong_log(uid,username,money,date) VALUES('".$k."','".$username."','".$v['money']."','".date('Y/m/d',$time)."')";
    		        $this->query($sql);
    		    }
    		    $sql = "UPDATE ztp_config SET value = 0 where id in (79)";
    		    $this->query($sql);
		    }
		}
		//发静态收益
		private function runStaticMoney($user_project,$time,$money_gufen){
            //应得多少
            $money = $user_project['gufen']*$money_gufen;
            //判断是否超额
            $money_kede = $user_project['money']-$user_project['money_huoqu']; 
            $is_done = 0;
            if($money >= $money_kede){
                $money = $money_kede;
                $is_done = 1;//本单收益完成
            }
			$money = substr(sprintf("%.3f",$money),0,-1);
            //加钱
            $this->setUserMoneyLogic($user_project['uid'], $money, 2, 201 ,'回馈积分释放剩余回馈积分', 201 , 3,$user_project['id']);
            $this->setUserMoneyLogic($user_project['uid'], $money, 1, 202 ,'回馈积分获取积分', 202 , 2,$user_project['id']);
            $this->setUserMoneyLogic($user_project['uid'], $money, 1, 203 ,'累积释放的剩余回馈积分', 203 , 6,$user_project['id']);
            //每个人获取的金额累积
			$user_day_get_money = $this->user_get_money[$user_project['uid']]['money']?$this->user_get_money[$user_project['uid']]['money']:0;
            $this->user_get_money[$user_project['uid']]['money'] = $user_day_get_money+$money;
            //增加每日获取积分记录 TODO
            $sql = "SELECT username FROM ztp_user WHERE uid = ".$user_project['uid'];
            $username = $this->find($sql)['username'];
            $sql = "INSERT INTO ztp_project_jifen_day_log(uid,username,money,add_time) VALUES('".$user_project['uid']."','".$username."','".$money."','".$time."')";
            $this->query($sql);
            //处理project_user表
            if($is_done == 1){
                $sql = "UPDATE ztp_project_user_auto SET money_huoqu = money_huoqu+".$money.",status = 1,over_time = ".$time." WHERE id = ".$user_project['id'];
                $this->query($sql);
            }else{
                $sql = "UPDATE ztp_project_user_auto SET money_huoqu = money_huoqu+".$money." WHERE id = ".$user_project['id'];
                $this->query($sql);
            }
		}
		//判断是否本人是否累计收益足够
		private function addShouyiMoney($uid,$money,$project_money,$project_id){
			//查询累计收益
			$sql = "select uid,shouyi from ztp_user_relation where uid = ".$uid;
			$money_shouyi = $this->find($sql)['shouyi'];
			//判断是否达到5倍 出局
			$money_add = $money;
			if(($money_shouyi+$money) >= $project_money*5){
				//可加数量
				$money_add = $project_money*5-$money_shouyi;
				$sql = "UPDATE ztp_project_user_auto SET status = 1,over_time = ".time()." ,in_num = in_num + 1,money_huoqu = ".$money_add." WHERE id = ".$project_id;
				$this->query($sql);
				return -1;
			}
			//返回的只是可加的数量
			return $money_add;
		}
		/**
		 * 获取全部上线 接点
		 * @param unknown $x
		 * @param unknown $y
		 * @param unknown $up_arr  	 	返回的数据
		 * @param unknown $k			记录		
		 * @param number  $k_limit		限制代数
		 * @return unknown
		 */
		private function getUpAllByXY($x,$y,$up_arr,$k,$k_limit=9999){
			if($k_limit != 9999){
				if($k >= $k_limit){
					return $up_arr;
				}	
			}
			$up = $this->getUpByXY($x,$y);
			if(!$up){
				$up_arr[] = $up;
				$k++;
				$this->getUpAllByXY($up['x'],$up['y'],$up_arr,$k,$k_limit);
			}else{
				return $up_arr;
			}
		}
		/**
		 * 查询所有上线 推荐关系
		 * @param unknown $pid
		 * @param unknown $up_arr
		 * @param unknown $k
		 * @param number $k_limit
		 * @return unknown
		 */
		private function getUpAllByPid($pid,$up_arr,$k,$k_limit=9999){
			if($k_limit != 9999){
				if($k >= $k_limit){
					return $up_arr;
				}
			}
			$up = $this->getUpByPid($pid);
			if(!$up){
				$up_arr[] = $up;
				$k++;
				$this->getUpAllByXY($up['pid'],$up_arr,$k,$k_limit);
			}else{
				return $up_arr;
			}
		}
		/**
		 * 根据x,y获取一级下线 即接点关系 
		 * @param unknown $x
		 * @param unknown $y
		 */
		private function getDownByXY($x,$y){
			$sql = "
			SELECT 
			uid,x,y,yeji 
			FROM ztp_user_relation
			WHERE x in array(".($x*2-1).",".($x*2).") and y=".($y+1);
			return $this->query($sql);
		}
		/**
		 * 根据pid获取一级下线 推荐关系
		 * @param unknown $pid
		 */
		private function getDownByPid($pid){
			$sql = "
			SELECT
			uid,yeji
			FROM ztp_user_relation	
			WHERE pid = ".$pid; 
			return $this->query($sql);
		}
		/**
		 * 获取上线信息 接点关系
		 * @param unknown $x
		 * @param unknown $y
		 */
		private function getUpByXY($x,$y){
			$x_chuli = ceil($x/2);
			$sql = "
			SELECT 
			uid,x,y,yeji 
			FROM ztp_user_relation
			WHERE x = ".$x_chuli." and y=".($y-1);
			return $this->query($sql);
		}
		/**
		 * 根据pid获取上线 推荐关系
		 * @param unknown $pid
		 */
		private function getUpByPid($pid){
			$sql = "
			SELECT
			uid,yeji
			FROM ztp_user_relation
			WHERE uid = ".$pid;
			return $this->query($sql);
		}
		//检测自身是大中小哪个区 排序  0,1,2  小,中,大
		private function checkQuyu($yeji,$pid){
			$sql="select yeji from  ".$this->db['DB_PREFIX']."member where pid = ".$pid;
			$list=$this->lianKuArray($sql);
			//查询不到上线
			if(!$list){
				return false;
			}
			foreach($list as $k=>$v){
				$arr[]=$v['yeji'];
			}
			//降序排序
			rsort($arr);
			array_values($arr);
			$quyu=array_search($yeji,$arr);
			unset($yeji);
			unset($pid);
			return $quyu;
		}
		//代数判断
		private function checkDaishu($p_num){
			$config = $this->config;
			$case = 0;
			if($config['daishu_money_limit_num1']<=$p_num && $p_num<$config['daishu_money_limit_num2'])$case = 1;
			if($config['daishu_money_limit_num2']<=$p_num && $p_num<$config['daishu_money_limit_num3'])$case =2;
			if($p_num>=$config['daishu_money_limit_num3'])$case = 3;
			switch ($case){
				case 1	:$type =$config['daishu_money_limit_daishu1'];break;
				case 2	:$type =$config['daishu_money_limit_daishu2'];break;
				case 3	:$type =$config['daishu_money_limit_daishu3'];break;
				default:$type = 0;
			}
			return $type;
		}
		//通过
		private function getUserByUid($uid){
			$sql = "select * from ztp_user_relation where uid=".$uid;
			return $this ->find($sql);
		}
	}
	$auto=new auto();
	$auto->run();
	dump('成功');
?>