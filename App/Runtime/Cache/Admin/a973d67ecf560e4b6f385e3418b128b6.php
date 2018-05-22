<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>充值记录 </title>
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
    充币记录
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
        <a href="#">充币记录</a>
    </li>
</ul>
<!-- 导航栏结束 -->
<form method='post' action="<?php echo U('Coin/showChongBiLog');?>">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th width="5%">搜索</th>
            <td width="15%">
               	<input class="form-control" type="text" name="username" value="<?php echo ($username); ?>" placeholder="用户名" >
            </td>
            <td width="15%">
            <select class="form-control" name="status" id="droCoinType">
					<option  value="" >=选择充值状态=</option>
					<option  value="1">成功</option>
					<option  value="2">审核中</option>
					<option  value="-1">驳回</option>
			</select>
			</td>
			<td  width="15%">
			
			  <select class="form-control" name="bank_id" id="droCoinType">
					<option  value="" >=选择充值银行=</option>
					<?php if(is_array($bank)): foreach($bank as $key=>$vo): ?><option  value="<?php echo ($vo['bank_id']); ?>"><?php echo ($vo['bank_name']); ?></option><?php endforeach; endif; ?>
			</select>
			</td>
			<td  width="15%">
			
			  <select class="form-control" name="types" id="droCoinType">
					<option  value="" >=选择打款凭证状态=</option>
					<option  value="1">已经上传</option>
					<option  value="2">暂未上传</option>
			</select>
			</td>
			</form>
            <td width="10%">
                <input class="btn btn-success" type="submit" value="搜索">
            </td>
			<form action="<?php echo U('Coin/to_download1');?>">
			 <td width="20%">
            <select class="form-control" name="status" id="droCoinType">
					<option  value="" >==选择状态==</option>
					<option  value="4" >待审核</option>
					<option  value="1" >审核通过</option>
					<option  value="-1" >驳回</option>
			</select>
			</td>
			 <td width="30%">
                <input type='submit' class="btn btn-success"  value="下载表格">
            </td>
        </tr>
    </table>
</form>
<table class="table table-striped table-bordered table-hover table-condensed">
    <tr>
        <th>用户账号</th>
        <th>用户姓名</th>
        <th>填写人账号</th>
        <th>填写人用户姓名</th>
        <th>充值银行</th>
        <th>积分金额</th>
        <th>充值凭证</th>
        <th>充值时间</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
            <td><?php echo ((isset($vo['user']['phone']) && ($vo['user']['phone'] !== ""))?($vo['user']['phone']):'暂无'); ?></td>
            <td><?php echo ((isset($vo['user']['username']) && ($vo['user']['username'] !== ""))?($vo['user']['username']):'暂无'); ?></td>
            <td><?php echo ((isset($vo['shenqing']['phone']) && ($vo['shenqing']['phone'] !== ""))?($vo['shenqing']['phone']):'暂无'); ?></td>
            <td><?php echo ((isset($vo['shenqing']['username']) && ($vo['shenqing']['username'] !== ""))?($vo['shenqing']['username']):'暂无'); ?></td>
            <td><?php echo ((isset($vo['bank_name']) && ($vo['bank_name'] !== ""))?($vo['bank_name']):'暂无'); ?></td>
            <td><?php echo ($vo['money']); ?></td>
            <td><?php if(empty($vo['image'])): ?>暂未上传打款凭证<?php else: echo ($vo['image']); endif; ?></td>
            <td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>
            <td>
				<?php switch($vo['status']): case "1": ?>通过<?php break;?>
					<?php case "-1": ?>驳回<?php break;?>
					<?php case "0": ?>审核中<?php break; endswitch;?>
			</td>
			  <td>
				<?php if(($vo['status']) == "0"): ?><a href="<?php echo U('Coin/findPingzheng',array('recharge_id'=>$vo['recharge_id']));?>" class="btn btn-success">查看凭证</a>
				<a href="<?php echo U('Coin/adoptRecharge',array('recharge_id'=>$vo['recharge_id']));?>" class="btn btn-success">通过</a>
				<a href="<?php echo U('Coin/refuseRecharge',array('recharge_id'=>$vo['recharge_id']));?>" class="btn btn-danger">拒绝</a>
				<?php else: ?>
					已审核完成<?php endif; ?>
			</td>
        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<?php echo ($page); ?>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
</body>
<script>
function adopt(id){
		$.post("<?php echo U('Coin/adoptRecharge');?>",{'recharge_id':id},function(d){
			alert(d['msg']);
		if(d['status'] == 1){
			window.location.reload();
		}
		});
	}
	function refuse(id){
		$.post("<?php echo U('Coin/refuseRecharge');?>",{'recharge_id':id},function(d){
			alert(d['msg']);
		if(d['status'] == 1){
			window.location.reload();
		}
		});
	}
</script>
</html>