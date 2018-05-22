<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
/**
 * admin 基类控制器
 */
class AdminBaseController extends CommonController{
	/**
	 * 初始化方法
	 */
	public function _initialize(){
		parent::_initialize();
		if(!session('?user')){
			$this->redirect('Login/showLogin');
		}
		$auth=new \Think\Auth();
		$rule_name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		$result=$auth->check($rule_name,$_SESSION['user']['id']);
		
		//统计数据 平台总业绩、今日新增业绩、今日回馈积分份数、每份回馈积分预计金额、今日回馈积分总金额、总回馈积分支出
		$tongji['yeji_zong'] = M('Config')->where(array('key'=>'project_money_count'))->find()['value'];
		$tongji['yeji_xinzeng'] = M('Config')->where(array('key'=>'project_money_count_day'))->find()['value'];
		$project_list = M('Project_user_auto')->where(array('status'=>0))->select();
		foreach ($project_list as $k=>$v){
			$gufen_zong += $v['gufen'];
		}
		$tongji['fenhong_gufen'] = $gufen_zong;
		$bili = M('Config')->where(array('key'=>'bili_fenhong_day'))->find()['value'];
		$tongji['money_gufen'] = round($tongji['yeji_xinzeng']*$bili/$tongji['fenhong_gufen'],2);
		$tongji['money_fenhong_day'] = $tongji['yeji_xinzeng']*$bili;
		$tongji['money_febhong_zong'] = '暂未统计';
		$this->assign('tongji',$tongji);
// 		if(!$result){
// 			$this->error('您没有权限访问');
// 		}
	}
	/**
	 *图片上传方法
	 *参数：$_FILES['name'] 数组
	 *返回值：图片访问路径
	 **/
	protected function imgUpload($file){
		switch($file['type'])
		{
			case 'image/jpeg': $ext = 'jpg'; break;
			case 'image/gif': $ext = 'gif'; break;
			case 'image/png': $ext = 'png'; break;
			case 'image/tiff': $ext = 'tif'; break;
			default: $ext = ''; break;
		}
		if (empty($ext)){
			return false;
		}
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     9999999999 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'tif');// 设置附件上传类型
		$upload->saveExt   =     $ext;
		$upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
		// 上传文件
		$info   =  $upload->uploadOne($file);
		if(!$info) {
			// 上传错误提示错误信息
			$this->error($upload->getError());exit();
		}else{
			// 上传成功
			$pic=$info['savepath'].$info['savename'];
			$url='/Uploads/'.ltrim($pic,".");
		}
		return $url;
	}
	public function getUserIdByUserName($username){
		$where['username']=$username;
		return M('User')->where($where)->getField('uid');
	}
	/**
	 * 根据id获取用户信息
	 * @param int $uid	用户id
	 * @return array
	 */
	protected function getUserByUid($uid){
		return M('User')->where(array('uid'=>$uid))->find();
	}
	/**
	 * 检测用户字段
	 * @param string $key	字段名
	 * @param string $value	值
	 * @return boolean
	 */
	protected function checkUserFiledExist($key,$value){
		$r = M('User')->where(array($key=>$value))->find();
		return $r?true:false;
	}
	
	/**
	 * 检测币种字段是否存在
	 * @param string $key	字段名
	 * @param string $value	值
	 * @return boolean
	 */
	protected function checkCurrencyFiledExist($key,$value){
		$r = M('Currency')->where(array($key=>$value))->find();
		return $r?true:false;
	}
}

