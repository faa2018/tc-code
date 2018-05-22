<?php
namespace Common\Model;
use Think\Model;
class ConfigModel extends Model
{
	//获取单项的配置项数值
	public function getConfigByKey($key){
		$where['key'] = $key;
		return $this->where($where)->find()['value'];
	}
	//获取全部的配置项数值
	public function getConfig(){
		$config = $this->select();
		foreach ($config as $k=>$v){
			$config_re[$v['key']] = $v['value'];
		}
		return $config_re;
	}
}