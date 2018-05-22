<?php
namespace Common\Model;
use Think\Model;
class UserRelationModel extends Model
{	
	//用户结构    推荐关系 接点关系	
	//根据uid获取自己信息
	public function getUserByUid($uid){
		$where['uid'] = $uid;
		$user = $this->where($where)->find();
		return $user;
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
	public function getUpAllByXY($x,$y,$up_arr,$k,$k_limit=9999){
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
	public function getUpAllByPid($pid,$up_arr,$k,$k_limit=9999){
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
	public function getDownByXY($x,$y){
		$sql = "
			SELECT
			uid,x,y,yeji
			FROM ztp_user_relation
			WHERE x in array(".($x*2-1).",".($x*2).") and y=".($y+1);
		return $this->query($sql)[0];
	}
	/**
	 * 根据pid获取一级下线 推荐关系
	 * @param unknown $pid
	 */
	public function getDownByPid($pid){
		$sql = "
			SELECT
			uid,yeji
			FROM ztp_user_relation
			WHERE pid = ".$pid;
		return $this->query($sql)[0];
	}
	/**
	 * 根据pid获取一级下线 推荐关系
	 * @param unknown $pid
	 */
	public function getDownDetailsByPid($pid){
		if(!$pid){
			$pid = 0;
		}
		$where['pid']    = $pid;
		$where['status'] = array('neq',-2);
		return M('User_relation')->where($where)->select();
	}
	/**
	 * 获取上线信息 接点关系
	 * @param unknown $x
	 * @param unknown $y
	 */
	public function getUpByXY($x,$y){
		$x_chuli = ceil($x/2);
		$sql = "
			SELECT
			uid,x,y,yeji
			FROM ztp_user_relation
			WHERE x = ".$x_chuli." and y=".($y-1);
		return $this->query($sql)[0];
	}
	/**
	 * 根据pid获取上线 推荐关系
	 * @param unknown $pid
	 */
	public function getUpByPid($pid){
		$sql = "
			SELECT
			*
			FROM ztp_user_relation
			WHERE uid = ".$pid;
		return $this->query($sql)[0];
	}
	//增加个人直推人数
	public function addZhituiNum($uid,$num){
		$where['uid'] = $uid;
		return $this->where($where)->setInc('p_num',$num);
	}
	//增加团队直推人数
	public function addTeamNum($uid,$num){
		$where['uid'] = $uid;
		return $this->where($where)->setInc('team_num',$num);
	}
	//增加团队直推人数
	public function addTeamYeji($uid,$money){
		$where['uid'] = $uid;
		$this->where($where)->setInc('yeji',$money);
		$r = $this->where($where)->setInc('yeji_new',$money);
	}
}