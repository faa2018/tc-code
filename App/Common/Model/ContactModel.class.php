<?php
namespace Common\Model;
use Think\Model;
/**
 * 联系我们model
 */
class ContactModel extends Model{

	/**
	 * 查询数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function getContactList(){
		$list = M('Contact')->find();
		return $list;
	}
	
	//修改
	public function saveContact($where,$data){
		//如果修改的时候不传图品
		if(empty($data['pic'])){
			$data['pic']= $this->getContactList()['pic'];
		}
		$res = M('Contact')->where($where)->save($data);
		return $res;
	}
	

}
