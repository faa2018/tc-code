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
    申请消费列表
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
         <a href="<?php echo U('Admin/User/showUserList');?>">申请消费列表</a>
   </li>
</ul>
<form>
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th width="5%">搜索</th>
            <td width="15%">
               	<input class="form-control" type="text" name="username" value="<?php echo ($_GET['username']); ?>"  placeholder="用户账号" >
            </td>
            <td width="15%">
               	<select class="form-control" name="status" id="droCoinType">
					<option  value="" >==请选择状态==</option>
						<option  value="0" <?php if(($_GET['status']) === "0"): ?>selected<?php endif; ?> >返利中</option>
						<option  value="1" <?php if(($_GET['status']) === "1"): ?>selected<?php endif; ?> >已完结</option>
				</select>
            </td>
            <td width="80%">
                <input class="btn btn-success" type="submit" value="搜索">
            </td>
        </tr>
    </table>
</form>
<!-- 导航栏结束 -->
<table class="table table-striped table-bordered table-hover table-condensed">
    <tr>
	<th>排行</th>
        <th>用户账号</th>
        <th>用户姓名</th>
        <th>推荐人账号</th>
        <th>推荐人姓名</th>
        <th>报单时间</th>
        <th>状态</th>
		<th>操作</th>
    </tr>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
			<td><?php echo ($vo["ranking"]); ?></td>
            <td><?php echo ((isset($vo['user']['phone']) && ($vo['user']['phone'] !== ""))?($vo['user']['phone']):'暂无'); ?></td>           
            <td><?php echo ((isset($vo['user']['real_name']) && ($vo['user']['real_name'] !== ""))?($vo['user']['real_name']):'暂无'); ?></td>           
            <td><?php echo ((isset($vo['tuijian']['phone']) && ($vo['tuijian']['phone'] !== ""))?($vo['tuijian']['phone']):'暂无'); ?></td>           
            <td><?php echo ((isset($vo['tuijian']['real_name']) && ($vo['tuijian']['real_name'] !== ""))?($vo['tuijian']['real_name']):'暂无'); ?></td>           
            <td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>
            <td><?php if(($vo["status"]) == "0"): ?>未出局<?php else: ?><p style='color:blue'>已出局</p><?php endif; ?></td>
			<td>
			<?php if(($vo["status"]) == "0"): ?><a href="<?php echo U('User/setOutStatus',array('id'=>$vo['id']));?>" class="btn btn-success">设置为出局</a>
			<?php else: ?>无法操作<?php endif; ?>
			</td>
        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<div class="xb-page" style="margin-bottom:50px;"><?php echo ($page); ?></div>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
</body>
</html>