<?php
namespace Admin\Controller;
//币种管理
class CurrencyController extends AdminBaseController {
	/**
	 * 自动加载方法
	 */
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * 显示币种列表
	 */
	public function showCurrencyList(){
		$list=M('Coin')->order('add_time desc')->select();
		$this->assign('list',$list);
		$this->display();
	}
	/**
	 * 添加/修改币种页面
	 */
	public function addCurrency(){
		$id = I('id');
		$where['id'] = $id;
		if($id){
			$list=M('Coin')->where($where)->find();
			$this->assign('list',$list);
		}
		$this->display();
	}
	/**
	 * 添加/修改币种操作
	 */
	public function updateCurrency(){
		$id = I('id');
		$where['id'] = $id;
		$data['name'] = I('name');
		$data['mark'] = I('mark');
		$data['bili'] = I('bili');
		$data['url'] = I('url');
		$data['rpc_username'] = I('rpc_username');
		$data['rpc_pwd'] = I('rpc_pwd');
		$data['rpc_host'] = I('rpc_host');
		$data['rpc_port'] = I('rpc_port');
		$data['chongbikg'] = I('chongbikg');
		$data['tibikg'] = I('tibikg');
		$data['add_time'] = time();
		if($id){
			$list=M('Coin')->where($where)->save($data);
		}else{
			$list=M('Coin')->add($data);
		}
		if($list){
			$this->success('操作成功',U('Currency/showCurrencyList'));
		}else{
			$this->error('系统繁忙');
		}
	}
	/**
	 * 是否锁定 switch开关
	 */
	public function setCurrencySwitch(){
		$id = I('id')?I('id'):null;
		$where['id'] = $id;
		$currency =  M('Coin')->where($where)->find();
		if($currency['switch']==0){
			$list = M('Coin')->where($where)->setField('switch','1');
		}elseif($currency['switch']==1){
			$list = M('Coin')->where($where)->setField('switch','0');
		}
		if($list){
			$this->success('状态修改成功',U('Currency/showCurrencyList'));
		}else{
			$this->error('状态修改成功');
		}
	}

}