<?php
namespace Admin\Controller;
use Admin\Controller\AdminBaseController;
use Think\Page;
class GoodsController extends AdminBaseController {
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 商品列表页
     */
    public function index(){
    	$cat = M('Cat')->select();
    	$this->assign('cat',$cat);
    	$name = I('name')?I('name'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	if(!empty($name)){
    		$where['name']=array("like","%".$name."%");
    	}
    	if(!empty($cat_id)){
    		$where['cat_id']=$cat_id;
    	}
    	$count=M('Goods')->where($where)->count();
    	$Page=new Page($count,30);
    	setPageParameter($Page, array('name'=>$name));
    	$show       = $Page->show();
    	$list =  M('Goods')->where($where)
    	->limit($Page->firstRow.','.$Page->listRows)->order('goods_id desc')->select();
    	foreach ($list as &$v){
    		$names = M('Cat')->where(array('cat_id'=>$v['cat_id']))->find();
    		$v['title'] = $names['name'];
    		$v['comment'] = M("Comment")->where(array('goods_id'=>$v['goods_id']))->count();
    	}
    	$this->assign('list',$list);
    	$this->assign('search',$name);
    	$this->assign('page',$show);
    	$this->display();
    }
    /**
     * 添加商品
     */
    public function addGoods(){
    	$goods_id = I('goods_id')?I('goods_id'):null;
    	$this->assign('goods_id',$goods_id);
    	$attr_array = [];
    	$last = [];
    	if(!empty($goods_id)){
    		$goods = M('Goods')->where(array('goods_id'=>$goods_id))->find();
    		$attr = M('Attr')->where(array('cat_id'=>$goods['cat_id']))->select();
    		foreach ($attr as &$v){
    			if($v['is_yilai']==1){
    				foreach (explode(',', $v['detail']) as &$m){
    					$r_at = M('Goods_attr')->where(array('attr_val'=>$m,'goods_id'=>$goods_id))->find();
    					if($r_at){
    						$attr_array[] = $r_at;
    					}else{
    						$attr_array[] = [
    							'attr_val'=>$m,
    							'attr_id'=>$v['attr_id'],
    							'is_price_main'=>0,
    						];
    					}
    				}
    			$v['totle'] = $attr_array;
    			}else{
    				$r_att = M('Goods_attr')->where(array('attr_id'=>$v['attr_id'],'goods_id'=>$goods_id))->find();
    				$array_son = explode(',', $r_att['attr_val']);
    				foreach (explode(',', $v['detail']) as &$m){
    					if(in_array($m, $array_son)){
    						$attr_array[] = [
    							'status'=>1,
    							'key'=>$m
    						];
    					}else{
    						$attr_array[] = [
    							'status'=>0,
    							'key'=>$m
    						];
    					}
    				}
    			$v['totle'] = $attr_array;
    			}
    			$attr_array = [];
    		}
    		$this->assign('attr',$attr);
    		$goods_attr = M('Goods_attr')->where(array('goods_id'=>$goods_id))->group('attr_id')->select();
     		//dump($attr);die;
    		$this->assign('goods',$goods);
    		$this->assign('status',1);
    	}else{
    		$this->assign('status',2);
    	}
    	//dump($goods);
    	$cat = M('Cat')->where(array('pid'=>0))->select();
    	$this->assign('cat',$cat);
    	$this->display();
    }
    /**
     * 根据pid获取子分类
     * 
     */
    public function getCatChildByPid(){
    	$pid = I('pid');
    	if($pid==0){
    		$this->ajaxReturn([]);
    	}
    	$cat = M('Cat')->where(array('pid'=>$pid))->select();
    	$this->ajaxReturn($cat);
    }
    /**
     * 根据分类获取属性
     */
    public function getAttr(){
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$attr = M('Attr')->where(array('cat_id'=>$cat_id))->select();
    	foreach ($attr as &$v){
    		$v['attr'] = explode(",", $v['detail']);
    	}
		$this->ajaxReturn($attr);
    }
    /**
     * 添加商品执行方法
     */
    public function doAddGoods(){
    	$goods_id = I('goods_id')?I('goods_id'):null;
    	$cat = I('cat');
    	$price = I('price');
    	//$send_money = I('send_money');
    	if(empty($cat)){
    		$this->error('请选择企业');
    	}
    	$name = I('name')?I('name'):null;
    	if(empty($name)){
    		$this->error('请输入商品名称');
    	}
    	if(empty($_FILES['image']['name'])&&empty($goods_id)){
    		$this->error('请上传图片');
    	}
    	$content = I('content')?I('content'):null;
    	if(empty($content)){
    		$this->error('请输入商品的短描述');
    	}
    	$content_all = I('content_all')?$_POST['content_all']:null;
    	if(empty($content_all)){
    		$this->error('请输入手机商品的详情描述');
    	}
    	/* $content_all_pc = I('content_all_pc')?$_POST['content_all_pc']:null;
    	if(empty($content_all_pc)){
    		$this->error('请输入PC商品的详情描述');
    	}
    	$is_yilai = I('yilai')?I('yilai'):null;
    	if(empty($is_yilai)){
    		$this->error('请选择价格推荐');
    	} */
    	if(!empty($_FILES['image'])){
    		$image = $this->imgUpload($_FILES['image']);
    	}
    	if(empty($goods_id)){
	    	$goods = [
	    		'name'=>$name,
	    		'cat_id'=>$cat,
	    		'image'=>$image,
	    		'is_sale'=>1,
	    		'content'=>$content,
	    		'content_all'=>$content_all,
	    		//'content_all_pc'=>$content_all_pc,
	    		'price'=>$price,
	    	];
	    	$goods_id = M('Goods')->add($goods);
	    	$data = [];
	    	$attr = M('Attr')->where(array('cat_id'=>$cat))->select();
	    	foreach ($attr as &$v){
	    		if($v['is_yilai']==1){
	    			$attrs = explode(',', $v['detail']);
	    			foreach ($attrs as $m){
	    				if($_POST[$m.'_kucun']){
	    					/* if($is_yilai == $m){
	    						$is_price_main = 1;
	    					}else{
	    						$is_price_main = 0;
	    					} */
	    					$data = [
	    						'attr_id'=>$v['attr_id'],
	    						'attr_val'=>$m,
	    						'attr_kucun'=>$_POST[$m.'_kucun'],
	    						'price'=>$_POST[$m.'_jiage'],
	    						'fanjifen'=>$_POST[$m.'_fanjifen'],
	    						'goods_id'=>$goods_id,
	    						'is_price_main'=>$is_price_main,
	    					];
	    					if($_POST[$m.'_jifen'] >0 && $_POST[$m.'_jifen']){
	    					    $data['jifen'] = $_POST[$m.'_jifen'];
	    					}else{
    							    $data['jifen'] = null;
	    					}
	    					if($_POST[$m.'_jiage'] >0 && $_POST[$m.'_jiage']){
	    					    $data['price'] = $_POST[$m.'_jiage'];
	    					}else{
    							    $data['price'] = null;
	    					}
	    					
	    					$r_attr_main[] = M('Goods_attr')->add($data);
	    				}
	    			}
	    		}else{
	    		    
	    			$data = [
		    			'attr_id'=>$v['attr_id'],
		    			'attr_val'=>implode(',',I($v['attr_id'].'_check')),
		    			'goods_id'=>$goods_id,
		    			'is_price_main'=>0,
	    			];
	    			$r_attr_fu[] = M('Goods_attr')->add($data);
	    		}
	    	}
	    	$this->success('添加成功',U('Goods/index'));
    	}else{
    		if(!$image){
    			$goods = [
	    			'name'=>$name,
	    			'is_sale'=>1,
	    			'content'=>$content,
	    			'content_all'=>$content_all,
	    			'cat_id'=>$cat,
	    			//'content_all_pc'=>$content_all_pc,
	    			//'send_money'=>$send_money,
    			'price'=>$price,
    			];
    		}else{
    			$goods = [
    			'name'=>$name,
    			'image'=>$image,
    			'is_sale'=>1,
    			'content'=>$content,
    			'content_all'=>$content_all,
    			'price'=>$price,
    			'cat_id'=>$cat,
    			//'content_all_pc'=>$content_all_pc,
    			//'send_money'=>$send_money,
    			];
    		}
    		$r_goods = M('Goods')->where(array('goods_id'=>$goods_id))->save($goods);
    		$attr = M('Attr')->where(array('cat_id'=>$cat))->select();
    		foreach ($attr as &$v){
    			if($v['is_yilai']==1){
    				$attrs = explode(',', $v['detail']);
    				foreach ($attrs as $m){
    					if($_POST[$m.'_kucun']){
    						/* if($is_yilai == $m){
    							$is_price_main = 1;
    						}else{
    							$is_price_main = 0;
    						} */
    						if(M('Goods_attr')->where(array('attr_id'=>$v['attr_id'],'attr_val'=>$m,'goods_id'=>$goods_id))->find()){
    						    //dump($m.'_jifen');
    						    $data = [
    							'attr_id'=>$v['attr_id'],
    							'attr_val'=>$m,
    							'attr_kucun'=>$_POST[$m.'_kucun'],
    							'price'=>$_POST[$m.'_jiage'],
    							'fanjifen'=>$_POST[$m.'_fanjifen'],
    							'goods_id'=>$goods_id,
    							'is_price_main'=>$is_price_main,
    							];
    						    if($_POST[$m.'_jifen'] >0 && $_POST[$m.'_jifen']){
    						        $data['jifen'] = $_POST[$m.'_jifen'];
    						    }else{
    							    $data['jifen'] = null;
	    					}
    							$r_attr_main[] = M('Goods_attr')->where(array('attr_id'=>$v['attr_id'],'attr_val'=>$m,'goods_id'=>$goods_id))->save($data);
    						}else{
    							$data = [
    							'attr_id'=>$v['attr_id'],
    							'attr_val'=>$m,
    							'attr_kucun'=>$_POST[$m.'_kucun'],
    							'price'=>$_POST[$m.'_jiage'],
    							'fanjifen'=>$_POST[$m.'_fanjifen'],
    							'goods_id'=>$goods_id,
    							'is_price_main'=>$is_price_main,
    							];
    							if($_POST[$m.'_jifen'] >0 && $_POST[$m.'_jifen']){
    							    $data['jifen'] = $_POST[$m.'_jifen'];
    							}else{
    							    $data['jifen'] = null;
	    					}
	    					if($_POST[$m.'_jiage'] >0 && $_POST[$m.'_jiage']){
	    					    $data['price'] = $_POST[$m.'_jiage'];
	    					}else{
	    					    $data['price'] = null;
	    					}
    							$r_attr_main[] = M('Goods_attr')->add($data);
    						}
    					}else{
    						M('Goods_attr')->where(array('attr_id'=>$v['attr_id'],'attr_val'=>$m,'goods_id'=>$goods_id))->delete();
    					}
    				}
    			}else{
    				$check_exit = M('Goods_attr')->where(array('goods_id'=>$goods_id,'attr_id'=>$v['attr_id']))->find();
    				if($check_exit){
    					$data = [
    					'attr_val'=>implode(',',I($v['attr_id'].'_check')),
    					];
    					$r_attr_fu[] = M('Goods_attr')->where(array('goods_attr_id'=>$check_exit['goods_attr_id']))->save($data);
    				}else{
    					$data = [
    					'attr_id'=>$v['attr_id'],
    					'attr_val'=>implode(',',I($v['attr_id'].'_check')),
    					'goods_id'=>$goods_id,
    					'is_price_main'=>0,
    					];
    					$r_attr_fu[] = M('Goods_attr')->add($data);
    				}
    			}
    		}
    		$this->success('修改成功');
    	}
    }
    /**
     * 分类列表
     */
    public function cat(){
    	$count=M('Cat')->where(array('pid'=>0))->count();
    	$Page=new Page($count,20);
    	//setPageParameter($Page, array('account'=>$account));
    	$show       = $Page->show();
    	$list =  M('Cat')
    	->order("add_time desc")
    	->where(array('pid'=>0))
    	->limit($Page->firstRow.','.$Page->listRows)->select();
    	/* foreach ($list as &$v){
    		$v['child'] = M('Cat')->where(array('pid'=>$v['cat_id']))->select();
    		foreach($v['child'] as &$vv){
    		    $vv['child'] = M('Cat')->where(array('pid'=>$vv['cat_id']))->select();
    		}
    	} */
    	$this->assign('list',$list);
    	$this->assign('page',$show);
    	$this->display();
    }
    /**
     * 添加分类
     */
    public function addCat(){
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$pid = I('pid');
    	$cat = M('Cat')->where(array('cat_id'=>$cat_id))->find();
    	$this->assign('list',$cat);
    	$this->assign('pid',$pid);
    	$this->display();
    }
    /**
     * 添加分类
     */
    public function doAddCat(){
    	$pid = I('pid');
    	if(empty($pid)){
    		$pid = 0;
    	}
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$name = I('name');
    	$data['name'] = $name;
    	$data['introduce'] = $_POST['introduce'];
    	$data['archives'] = $_POST['archives'];
    	$data['contact'] = $_POST['contact'];
    	$data['honor'] = $_POST['honor'];
    	$data['recruit'] = $_POST['recruit'];
    	$data['zhuying'] = I('zhuying');
    	//dump($data);die;
    	if(!empty($_FILES['image']['name'])){
    		$data['pic'] = $this->imgUpload($_FILES['image']);
    	}
    	if(empty($cat_id)){
    		if(empty($name)){
    			$this->error('请输入名称');
    		}
    		$data['add_time'] = time();
    		$r = M('Cat')->add($data);
    	}else{
    		$r = M('Cat')->where(array('cat_id'=>$cat_id))->save($data);
    	}
    	$this->success('操作成功',U('Goods/cat'));
    }
    /**
     * 删除分类
     */
    public function del(){
    	$cat_id = I('cat_id');
    	$pid = M('Cat')->where(['cat_id'=>$cat_id])->find();
    	$r = M('Cat')->delete($cat_id);
    	$this->success('操作成功',U('Goods/cat'));
    }
    public function delGoods(){
    	$cat_id = I('goods_id');
    	$r = M('Goods')->delete($cat_id);
    	$this->success('操作成功');
    }
    /**
     * 添加属性
     */
    public function addAttr(){
    	$cat_id = I('cat_id');
    	$this->assign('cat_id',$cat_id);
    	$attr_id = I('attr_id')?I('attr_id'):null;
    	$attr = M('Attr')->where(array('attr_id'=>$attr_id))->find();
    	$this->assign('list',$attr);
    	$this->display();
    }
    /**
     * 查看当前分类的属性列表
     */
    public function showAttr(){
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$this->assign('cat_id',$cat_id);
    	$attr = M('Attr')->where(array('cat_id'=>$cat_id))->select();
    	$this->assign('list',$attr);
    	$this->display();
    }
    /**
     * 添加属性方法
     */
    public function doAddAttr(){
    	$attr_id = I('attr_id')?I('attr_id'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$name = I('name')?I('name'):null;
    	$detail = I('detail')?I('detail'):null;
    	if(empty($attr_id)){
    		if(empty($name)){
    			$this->error('请输入名称');
    		}
    		if(empty($detail)){
    			$this->error('请输入属性详情，以英文逗号隔开');
    		}
    		$attr = [
    			'name'=>$name,
    			'cat_id'=>$cat_id,
    			'detail'=>$detail,
    		];
    		$r = M('Attr')->add($attr);
    		$this->success('操作成功',U('Goods/showAttr',array('cat_id'=>$cat_id)));
    	}else{
    		$attr = [
	    		'name'=>$name,
	    		'detail'=>$detail,
    		];
    		$r = M('Attr')->where(array('attr_id'=>$attr_id))->save($attr);
    		$this->success('操作成功',U('Goods/showAttr',array('cat_id'=>$cat_id)));
    	}
    }
    public function setYilai(){
    	$attr_id = I('attr_id')?I('attr_id'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$r = M('Attr')->where(array('attr_id'=>$attr_id))->setField('is_yilai',1);
    	$where['attr_id'] = array('neq',$attr_id);
    	$where['cat_id'] = $cat_id;
    	$r_cancel = M('Attr')->where($where)->setField('is_yilai',0);
    	$this->success('操作成功',U('Goods/showAttr',array('cat_id'=>$cat_id)));
    }
    /**
     * 删除属性
     */
    public function delAttr(){
    	$attr_id = I('attr_id')?I('attr_id'):null;
    	$cat_id = I('cat_id')?I('cat_id'):null;
    	$r = M('Attr')->delete($attr_id);
    	$this->success('操作成功',U('Goods/showAttr',array('cat_id'=>$cat_id)));
    }
    public function images(){
    	$goods_id = I('goods_id')?I('goods_id'):null;
    	$pics = M('Goods_pic')->where(array('goods_id'=>$goods_id))->select();
    	$this->assign('pics',$pics);
    	$goods = M('Goods')->where(array('goods_id'=>$goods_id))->find();
    	session('goods_id',$goods_id);
    	$this->assign('goods_id',$goods_id);
    	$this->assign('goods',$goods);
    	$this->display();
    }
    
    public function getImages(){
    	$url = $this->imgUpload($_FILES['file']);
    	$data = [
    		'goods_id'=>session('goods_id'),
    		'pic'=>$url,
    		'add_time'=>time(),
    	];
    	M('Goods_pic')->add($data);
    	//M('Goods')->add(['pics'=>$_FILES['file']['name']]);
    	//dump($_FILES);die;
    }
    public function deletePic(){
    	$goods_pic_id = I('goods_pic_id');
     	$goods_id = I('goods_id');
    	$pic = M('Goods_pic')->where(array('goods_pic_id'=>$goods_pic_id))->find();
    	//dump('.'.$pic['pic']);
    	$s = unlink('.'.$pic['pic']);
    	//dump($s);die;
    	$r = M('Goods_pic')->delete($goods_pic_id);
    	$this->success('删除成功',U('Goods/images',array('goods_id'=>$goods_id)));
    }
    public function tuijian(){
        $goods_id = I('goods_id')?I('goods_id'):null;
        $is_tuijian = M('Goods')->where('goods_id ='.$goods_id)->find()['is_tuijian'];
        if($is_tuijian == 1){
        	$tuijian = 0;
        }
        if($is_tuijian == 0){
            $tuijian = 1;
        }
        $re = M('Goods')->where('goods_id ='.$goods_id)->save(array('is_tuijian'=>$tuijian));
        if($re){
            $this->success('操作成功');
        }else{
            $this->success('操作失败');
        }
    }
    public function toComment(){
        $goods_id = I('goods_id');
        $list = M("Comment c")->join("ztp_user m ON m.uid=c.open_id")
        ->where(array('goods_id'=>$goods_id))->select();
        $this->assign('list',$list);
    	$this->display();
    }
    public function delComment(){
    	$id = I('id');
    	$re = M("Comment")->where(array("cid"=>$id))->delete();
        if($re){
            $this->success('操作成功');
        }else{
            $this->success('操作失败');
        }
    }
    //下架商品
    public function setSale(){
    	$goods_id = I('goods_id');
    	$r = M("Goods")->where(array('goods_id'=>$goods_id))->find();
    	if($r['is_sale'] == 1){
    		$is_sale = 0;
    	}
    	if($r['is_sale'] == 0){
    		$is_sale = 1;
    	}
        $re = M("Goods")->where(array("goods_id"=>$goods_id))->save(array("is_sale"=>$is_sale));
        if($re){
            $this->success('操作成功');
        }else{
            $this->success('操作失败');
        }
    }
    //设置月销量
    public function setSaleCount(){
        $goods_id = I('id');
        $num = I('num');
        $year = date('y',time());
        $mouth = date('m',time());
        $count = M('count_month')->where(['goods_id'=>$goods_id,'year'=>$year,'month'=>$mouth])->find();
        $data['count'] = $num;
        if($count){
            //存在数据就修改否则就新增
            $re = M('count_month')->where(['goods_id'=>$goods_id,'year'=>$year,'month'=>$mouth])->save($data);
        }else{
            $re = M('count_month')->add(['goods_id'=>$goods_id,'year'=>$year,'month'=>$mouth,'count'=>$num]);
        }
        if($re){
            $this->ajaxReturn(['status'=>1]);
        }else{
            $this->ajaxReturn(['status'=>-1]);
        }
    
    }
}