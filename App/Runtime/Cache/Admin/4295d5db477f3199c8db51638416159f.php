<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>用户列表 </title>
        <script src="/Public/Home/js/jquery-1.11.3.min.js"></script>
<script src="/Public/Home/layer/layer.js"></script>
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
    用户列表
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
         <a href="<?php echo U('Admin/User/showUserList');?>">用户列表</a>
   </li>
   <li >
        <a href="<?php echo U('Admin/User/updateUser');?>">添加用户</a>
    </li>
</ul>
<div style='min-height:400px'>
<form action="<?php echo U('User/showUserList');?>">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th width="5%">搜索</th>
            <td width="15%">
               	<input class="form-control" type="text" name="username" value="<?php echo ($_GET['username']); ?>"  placeholder="用户名" >
            </td>
            <td width="15%">
                <input class="form-control" type="text" name="phone" value="<?php echo ($_GET['phone']); ?>" placeholder="手机号码">
            </td>
            <td width="30%">
                <input class="btn btn-success" type="submit" value="搜索">
            </td>
			</form>
			<form action="<?php echo U('User/to_download');?>">
			<td width="30%">
                <input class="btn btn-success" type="submit" value="下载表格">
            </td>
            </form>
            <td width="30%">
            <form action="<?php echo U('User/daoru');?>" enctype="multipart/form-data" method="post">
			<input type="file" name="photo" />
			<input type="submit" class="btn btn-success" value="导入数据">
			</form>
</td>
        </tr>
    </table>
<input id="abc" type="hidden">
<form id='listform' action="<?php echo U('User/shengji');?>">
<!-- 导航栏结束 -->
<table class="table table-striped table-bordered table-hover table-condensed">
    <tr>
       <th width='70px'>选中</th>
       <th>用户账号</th>
        <th>用户姓名</th>
        <th>注册时间</th>
        <th>是否为代理</th>
		<th>是否出局</th>
		<th>出局次数</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
        	<td><input type="checkbox" name="uid[]" value="<?php echo ($vo["uid"]); ?>" /></td>
			<td><?php echo ((isset($vo['phone']) && ($vo['phone'] !== ""))?($vo['phone']):'暂无'); ?></td>
			<th><?php echo ((isset($vo['real_name']) && ($vo['real_name'] !== ""))?($vo['real_name']):'暂无'); ?></th>
            <td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>    
              <td><?php if(($vo["is_daili"]) == "1"): ?>是<?php else: ?>否<?php endif; ?></td> 
			 <td><?php if(($vo["level"]) == "1"): ?>出局<?php else: ?>未出局<?php endif; ?></td>
			  <td><?php echo ($vo["out_num"]); ?></td>
			    <td><?php echo ($vo['status_name']); ?></td>
            <td>
            		<a href="<?php echo U('User/setUserStatus',array('uid'=>$vo['uid']));?>" class="btn btn-success">
	            	<?php if(($vo['status'] == 1) OR ($vo['status'] == 0) ): ?>冻结<?php else: ?>启用<?php endif; ?></a> 
            	<div class="btn-group">
					<button type="button" class="btn btn-primary">操作</button>
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu dropdown-warning no-spacing" role="menu">
						<li>
							<a  target='_blank' href="<?php echo U('User/keyLogin',array('id'=>$vo['uid']));?>">一键登录</a>
						</li>
						<li>
							<a href="<?php echo U('User/updateUser',array('id'=>$vo['uid']));?>">查看用户资料</a>
						</li>
						<li>
							<a href="<?php echo U('userMoney/updateUserMoney',array('uid'=>$vo['uid']));?>">查看用户资金</a>
						</li>
						<li>
							<a href="<?php echo U('Finance/index',array('uid'=>$vo['uid']));?>">查看财务日志</a>
						</li>
						<li>
							<a href="<?php echo U('User/getJiegou',array('uid'=>$vo['uid']));?>">查看结构关系</a>
						</li>
						<li>
							<a href="<?php echo U('User/getUserAddress',array('uid'=>$vo['uid']));?>">查看用户地址</a>
						</li>
							<li>
						<a href="javascript:;" onclick="save(<?php echo ($vo['uid']); ?>)"  >设置排名</a>
				</li>
						<!-- <li>
							<a href="<?php echo U('User/getUserBank',array('uid'=>$vo['id']));?>">查看银行卡信息</a>
						</li>
						
						<li>
							<a href="<?php echo U('User/getUserAddress',array('uid'=>$vo['uid']));?>">查看用户地址</a>
						</li> -->
						<!-- <li>
							<a href="<?php echo U('UserMoney/getUserPayList',array('uid'=>$vo['id']));?>">查看充值记录</a>
						</li>
						<li>
							<a href="<?php echo U('UserMoney/getUserTixian',array('uid'=>$vo['id']));?>">查看提现记录</a>
						</li> -->
						
						
					</ul>
				</div>
            </td>
        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    <tr>
        <td><input type="checkbox" class="checkall" onclick='checkAll()'/>
          全选 </td>
        <td colspan="9">
		<a class="btn btn-success" onclick='adoptAll()'>升级为代理 </a>	 
      </tr>
</table>
</form>
</div>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
</body>
<script>
//全选
function checkAll(){
$("input[name='uid[]']").each(function(){
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
	 $("input[name='uid[]']").each(function(){
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
<script>
//页面层
function save(id){
	$('#abc').val(id);
layer.open({
  type: 1,
  skin: 'layui-layer-rim', //加上边框
  area: ['420px', '240px'], //宽高
  content: '<div id="close" style="font-size:16px;padding:30px;"><center><span>设置排名：</span><input type="text" id="jifen_a"/></center></div><center><div style="font-size:12px;"><input onclick="jifen()"  type="button" class="btn btn-success" value="确定" style="margin-right:10px;padding:3px 6px;"/></div></center>'
});

}
function jifen(){
	var id = $("#abc").val();
	var jifen = $("#jifen_a").val();
	$.post("<?php echo U('User/setRanking');?>",{uid:id,ranking:jifen},function(d){
		if(d.status==1){
			alert("操作成功");
			window.location.reload();
		}else{
			alert("参数错误，稍后再试");
		}
	})
}
</script>
<div class="xb-page" style="margin-bottom:50px;"><?php echo ($page); ?></div>
</html>