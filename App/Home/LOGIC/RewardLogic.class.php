<?php
namespace Home\LOGIC;
/**  
 * 奖励 逻辑层 
 * @author      ZhangYi <1425568992@qq.com>
 * @version	     v1.0.0 
 * @copyright   2017-9-1 下午2:38:05
*/
class RewardLogic  {
	function test(){
		echo "成功连接奖励逻辑层";die;
	}
	//运行奖励 对外的接口
	public function runReward($uid,$money){
		//配置项
		$config_old = M('Sys_configs')->where('parentId=3')->select();
		foreach($config_old as $k=>$v){
			$config[$v['fieldCode']] = $v['fieldValue'];
		}
		//获取个人资料
		$user = $this->getUserInfo($uid);
		//获取个人关系结构
		$user_relation = $this->getUserRelation($uid);
		//发放直推奖
		$this->runZhituiMoney($user,$user_relation,$money,$config);
		//发放代数奖 业绩
		$this->runDaishuMoney($user_relation,$money,$config);
	}
	//发放代数奖
	private function runDaishuMoney($user_relation,$money,$config){
		//查询上级
		$up_where['uid'] = $user_relation['point_id'];
		$up = M('UserRelation')->where($up_where)->find();
		if(!$up){
			return;
		}
		//发奖励 确定对上级来说 自己是不是小区
		$a=array('1'=>$up['yeji_left'],'2'=>$up['yeji_right']);
		$pos=array_search(min($a),$a);
		if($pos == $user_relation['quyu']){
			//本人是小区
			$daishu_money = $money*$config['daishuBili'];
			$this->runUserMoney($up['uid'],$daishu_money);
		}
		$this->runDaishuMoney($up,$money,$config);
	}
	//发放直推奖
	private function runZhituiMoney($user,$user_relation,$money,$config){
		//查询上级
		$up = $this->getUserRelation($user_relation['pid']);
		if(!$up){
			return;
		}
		//发奖励
		$zhitui_money = $money*$config['zhituiBili'];
		$this->runUserMoney($up['uid'],$zhitui_money);
	}
	//修改用户资金 增加奖金发放记录
	private function runUserMoney($uid,$money){
		//优先判断积分池是不是满了
		$jifen = M('User_jifen')->where('userId='.$uid)->getField('shifangJifen');
		//已满
		if($jifen['allJifen'] == $jifen['shifangJifen']){
			return;
		}
		//超出
		if(($jifen['allJifen']-$jifen['shifangJifen'])<$money){
			$money = $jifen['allJifen']-$jifen['shifangJifen'];
		}
		//修改资金
		M('User_jifen')->where('userId='.$uid)->setInc('shifangJifen',$money);
		//积分获取记录
		$data['uid'] = $uid;
		$data['money'] = $money;
		$data['add_time'] = $add_time;
		M('User_jifen_log')->add($data);
	}
	//获取个人资料
	private function getUserInfo($uid){
		$field = "*";
		$where['userId'] = $uid;
		$user = M('Users')->field($field)->where($where)->find();
		return $user;
	}
	//获取个人关系结构
	private function getUserRelation($uid){
		$field = "*";
		$where['uid'] = $uid;
		$user = M('UserRelation')->field($field)->where($where)->find();
		return $user;
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
	private function list_sort_by($list,$field, $sortby='asc') {
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
}

