<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class TestController extends CommonController {
	public function aaa(){
		$user=M('User_currency')->where('num<0')->select();
		foreach($user as $k=>$v){
			$user[$k]['user']=M('User')->where('uid='.$v['uid'])->find();
			$user[$k]['currency']=M('Currency')->where('id='.$v['currency_id'])->find();
		}
			echo "<table border='1' width='600px'>";
			echo "<tr>";
			echo "<td>用户名</td>";
			echo "<td>剩余金额</td>";
			echo "<td>币种</td>";
			echo "</tr>";
		foreach($user as $k=>$vo){
			echo "<tr>";
			echo "<td>".$vo['user']['username']."</td>";
			echo "<td>".$vo['num']."</td>";
			echo "<td>".$vo['currency']['currency_name']."</td>";
			echo "</tr>";
		}
			echo "</table>";
	}

	
}