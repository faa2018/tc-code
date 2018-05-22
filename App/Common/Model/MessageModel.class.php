<?php
namespace Common\Model;
use think\Model;
class MessageModel extends Model
{
	/**
	 * //添加消息
	 * @param unknown $uid          
	 * @param unknown $type         
	 * @param unknown $content     
	 * @return Ambigous <\Think\mixed, boolean, unknown, string>
	 */
	public function addMessage($uid,$type,$content){
		$data['uid'] = $uid;
		$data['type'] = $type;
		$data['content'] = $content;
		$data['add_time'] = time();
		$data['status'] = 0;
		return M('Message')->add($data);
	}
}