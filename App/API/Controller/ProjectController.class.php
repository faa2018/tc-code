<?php
namespace API\Controller;
use API\Controller\UserController;
use Common\Api\UserMoneyApi;
use Common\Model\UserRelationModel;
/**  
 * 激活控制器/购买矿机控制器 
 * @access public|private|protected
 * @param string xxx
 * @return void|int|string|boolean|array        comment 
 * @author      ZhangYi <1425568992@qq.com>
 * @version	  v1.0.0 
 * @copyright  2017-4-20 下午4:04:40
*/
class ProjectController extends UserController {
	public function _initialize(){
		parent::_initialize();
	}
    /***************************对外接口*****************************************/
    //报单
    public function run(){
			$uid = $this->uid;
			//给别人报单和自己报单
			$baodan_type = I('baodan_type');
			$data = I();
			//报单
			if($baodan_type==2){
				$usermoney =M('User_money'.cuttable($uid))->where(array('uid'=>$uid,'type'=>2))->find()['num'];
				if($usermoney<$this->config['baodan_money']){
					$data_return['status'] = -11;
					$data_return['info'] = '购车劵不足';
					return $this->ajaxReturn($data_return,'jsonp');
				}
				$API_USERMONEYLOGIC = new UserMoneyApi();
				$API_USERMONEYLOGIC->setUserMoneyLogic($uid,$this->config['baodan_money'], 2, 1015, '报单扣除', 1015,2);
			}
			$project_pic['uid'] = $this->uid;
			$project_pic['status'] = 0;
			$project_pic['add_time'] = time();
			$project_user_pic = M('Project_user_pic_log')->add($project_pic);
			foreach ($data['arrayList'] as $k=>$v){
				$where_tuijian_user['phone'] = $v['tuijian'];
				$where_tuijian_user['status'] = array('neq',-404);
				$tuijianren = M('User')->where($where_tuijian_user)->find();
				if(!$tuijianren){
					$data_return['status'] = -10;
					$data_return['info'] = '推荐人不存在';
					return $this->ajaxReturn($data_return,'jsonp');
				}
				$where_user['phone'] = $v['phone'];
				$where_user['status'] = array('neq',-404);
				$user = M('User')->where($where_user)->find();
				//已经注册
				if($user){
				$tuijian_uid = $tuijianren['uid'];
				$user_relation = M('user_relation')->where('uid = '.$user['uid'])->find();
				//已经注册 但是没有推荐人
				if($user_relation['pid']==0){
					$data_user_relation['uid'] = $user['uid'];
					$r[] = M('User_relation')->where($data_user_relation)->setField('pid',$tuijianren['uid']);									
				}
				if($user_relation['pid']!=0){
						//有推荐人 但是推荐人填写错误
						if($user_relation['pid'] != $tuijian_uid){
						$tuijian_user = M('User')->where('uid = '.$user_relation['pid'])->find();
						$data_return['status'] = -10;
						$data_return['info'] = '推荐人填写错误,本账号推荐人为'.$tuijian_user['phone'];
						$this->ajaxReturn($data_return,'jsonp');
					}
				}					
				}else{
					//未注册  自动注册一个账号 绑定推荐人
					$data_user['username'] = $v['real_name'];
					$data_user['pic'] = '';
					$data_user['phone'] = $v['phone'];
					$data_user['idcard'] = $v['idcard'];//报单人身份证
					$data_user['add_time'] = time();
					$data_user['real_name'] = $v['real_name'];
					$data_user['areaid1'] = $v['areaid1'];//省
					$data_user['areaid2'] = $v['areaid2'];//省
					$data_user['areaid3'] = $v['areaid3'];//省
					$data_user['baodan_type'] = $v['baodan_type'];//1 报单充值 2 在线报单
					$data_user['pwd'] = passwordEncryption('123456');
					$data_user['twopwd'] = passwordEncryption('111111');
					$uid = M('User')->add($data_user);
					$data_user_relation['uid'] = $uid;
					$data_user_relation['pid'] = $tuijianren['uid'];
					M('User_relation')->add($data_user_relation);
					//用户资金表
					$money_type = C('USER_MONEY_TYPE');
					foreach ($money_type as $kk=>$vv){
						$data_money['uid']          = $uid;
						$data_money['num']          = 0;
						$data_money['type']         = $k;
						$r[] = M('User_money'.cuttable($uid))->add($data_money);
					}
					$data_token['uid'] = $uid;
					$data_token['token'] = $uid.md5(time());
					M('Token')->add($data_token);
				}
				//加数据
				$data_add['uid'] = $user['uid']?$user['uid']:$uid;
				$data_add['baodan_uid'] = $this->uid;//提交报单得人
				$data_add['phone'] = $v['phone'];//报单手机号
				$data_add['real_name'] = $v['real_name'];//报单人真实姓名
				$data_add['idcard'] = $v['idcard'];//报单人身份证
				$data_add['areaid1'] = $v['areaid1'];//省
				$data_add['areaid2'] = $v['areaid2'];
				$data_add['areaid3'] = $v['areaid3'];
				$data_add['address'] = $v['address'];//详细地址
				$data_add['addressee'] = $v['addressee'];//收件人
				$data_add['addressee_phone'] = $v['addressee_phone'];//收件人手机号
				$data_add['yunlianhui_id'] = $v['yunlianhui_id'];//云联惠id
				$data_add['yunlianhui_phone'] = $v['yunlianhui_phone'];//云联惠手机号			
				$data_add['tuijian'] = $v['tuijian'];//推荐人账号 手机号
				$data_add['tuijian_name'] = $v['tuijian_name'];//推荐人真实姓名
				$data_add['dakuan_time'] = $v['dakuan_time'];//打款时间
				$data_add['dakuanren'] = $v['dakuanren'];//打款人
				$data_add['image_id'] = $project_user_pic;//图片id
				$data_add['add_time'] = time();//添加时间
				$project_id = M('Project_user')->add($data_add);
			}
					
    	 if (in_array(false, $r)){
    		M()->rollback();
    		$data_return['status'] = -4;
            $data_return['info'] = '操作失败';
            return $this->ajaxReturn($data_return,'jsonp');
    	}else {
    		M()->commit();
    		$data_return['status'] = 1;
            $data_return['info'] = '操作成功';
            $data_return['project_id'] = $project_id;
            return $this->ajaxReturn($data_return,'jsonp');
    	} 
    }
    /**
     * 上传打款记录
     */
    public function uploadVoucher(){
    	header('Access-Control-Allow-Origin: *');
    	$array =$_FILES;
    	$image_id = I('id')?I('id'):null;
    	foreach ($array as $k=>$v){
    		$recharge_pic['image_id'] =$image_id;
    		$recharge_pic['uid'] =$this->uid;
    		$recharge_pic['image'] = $this->upload($v);
    		$recharge_pic['add_time'] =time();
    		$r[] =M('Project_user_pic')->add($recharge_pic);
    	}
    	$r[] =M('Project_user')->where(['image_id'=>$image_id])->setField('is_image',1);
    	$r[] =M('Project_user_pic_log')->where(['id'=>$image_id])->setField('is_image',1);
    	if($r){
    		$return['status'] = 1;
    		$return['msg'] = "上传成功";
    		$this->ajaxReturn($return,'JSON');
    	}else{
    		$return['status'] = -1;
    		$return['msg'] = "系统繁忙";
    		$this->ajaxReturn($return,'JSON');
    
    	}
    
    }
    public function  getBaodanList(){
    	$uid = $this->uid;
    	$list = M('Project_user_pic_log')->where(['uid'=>$uid])->select();
    	$this->ajaxReturn($list,'JSONP');
    }
	 //查询当前的报单
	public function getProjectList(){
		$where['pu.uid'] = $this->uid;
		$list = M('Project_user_auto pu')
		->join('left join ztp_user u on u.uid = pu.uid')
		->where($where)
		->select();
		$data_return['status'] = 1;
		$data_return['info']['list']   = $list;
		return $this->ajaxReturn($data_return,'jsonp');
	}
	
}