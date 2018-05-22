<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>用户列表 </title>
        <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/font-awesome-4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/Public/Admin/css/base.css" />
</head>
<body>
<!-- 导航栏开始 -->
<div class="bjy-admin-nav">
    <a href="<?php echo U('Admin/Index/index');?>"><i class="fa fa-home"></i> 首页</a>
    &gt;
    申请报单列表
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
         <a href="#">申请报单列表</a>
   </li>
</ul>
<form>
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th width="5%">搜索</th>
            <td width="15%">
               	<input class="form-control" type="text" name="username" value="<?php echo ($_GET['username']); ?>"  placeholder="用户账号" >
            </td>
           
            <td width="80%">
                <input class="btn btn-success" type="submit" value="搜索">
            </td>
        </tr>
    </table>
</form>
<form id='listform' action="<?php echo U('User/baodan_adopt');?>">

<!-- 导航栏结束 -->
<table class="table table-striped table-bordered table-hover table-condensed">
    <tr>
	<th width='70px'>选中</th>
        <th>报单人账号(提交资料人)</th>        
        <th>报单人姓名(提交资料人)</th>        
        <th>报单人账号</th>        
        <th>报单类型</th>        
        <th>是否上传凭证</th>        
        <th>申请报单时间</th>
        <th>状态</th>
		<th>操作</th>
    </tr>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
		<td><input type="checkbox" name="id[]" value="<?php echo ($vo["id"]); ?>" /></td>
            <td><?php echo ((isset($vo['baodan_phone']) && ($vo['baodan_phone'] !== ""))?($vo['baodan_phone']):'暂无'); ?></td>           
            <td><?php echo ((isset($vo['baodan_username']) && ($vo['baodan_username'] !== ""))?($vo['baodan_username']):'暂无'); ?></td>           
            <td><?php echo ((isset($vo['phone']) && ($vo['phone'] !== ""))?($vo['phone']):'暂无'); ?></td>   
            <td><?php if(($vo["baodan_type"]) == "1"): ?>报单充值<?php else: ?>在线保单<?php endif; ?></td>           
            <td><?php if(($vo["is_image"]) == "1"): ?>已上传<?php else: ?>未上传<?php endif; ?></td>          
            <td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>
            <td><?php if(($vo["status"]) == "0"): ?>未审核<?php else: ?><p style='color:blue'>已审核</p><?php endif; ?></td>
			<td>
			<?php if(($vo["status"]) == "0"): ?><!-- <a href="<?php echo U('Coin/findPingzheng',array('project_id'=>$vo['id']));?>" class="btn btn-success">查看凭证</a> -->
			<a href="<?php echo U('User/addOutUser',array('id'=>$vo['id'],'image_id'=>$vo['image_id']));?>" class="btn btn-info">查看信息</a>
			<a class="btn btn-success" href="<?php echo U('User/baodan_adopt',array('id'=>$vo['id']));?>">通过 </a>
			<a class="btn btn-danger" href="<?php echo U('User/baodan_refuse',array('id'=>$vo['id']));?>">拒绝</a><?php endif; ?>
			<a href="<?php echo U('User/getJiegou',array('uid'=>$vo['uid']));?>" class="btn btn-info">查看结构关系</a>
			</td>
        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
	<tr>
        <td><input type="checkbox" class="checkall" onclick='checkAll()'/>
          全选 </td>
        <td colspan="8">
		<a class="btn btn-success" onclick='adoptAll()'>全部通过 </a>	 
      </tr>
</table>
</form>
<div class="xb-page" style="margin-bottom:50px;"><?php echo ($page); ?></div>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
<script>
//全选
function checkAll(){
$("input[name='id[]']").each(function(){
	  if (this.checked) {
		  this.checked = false;
	  }
	  else {
		  this.checked = true;
	  }
  });
}
//批量保单
function adoptAll(){
	var Checkbox=false;
	 $("input[name='id[]']").each(function(){
	  if (this.checked==true) {		
		Checkbox=true;	
	  }
	});
	if (Checkbox){
		var t=confirm("您确认要全部通过选中的内容吗？");
		if (t==false) return false;
		else
		$("#listform").submit();		
	}
	else{
		alert("请选择您要通过的内容!");
		return false;
	}
}
</script>
</body>
</html>