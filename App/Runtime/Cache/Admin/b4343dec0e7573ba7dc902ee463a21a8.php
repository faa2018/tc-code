<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
        <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/font-awesome-4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/Public/Admin/css/base.css" />
        <link rel="stylesheet" href="/Public/Admin/statics/iCheck-1.0.2/skins/all.css">
</head>
<body>

<!-- 导航栏开始 -->
<div class="bjy-admin-nav">
    <i class="fa fa-home"></i> 首页
    &gt;
充值凭证
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
        <a href="#">充值凭证</a>
    </li>
</ul>
	<table class="table table-striped table-bordered table-hover table-condensed">
		<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
			<th>图片</th>
            <td>
            	<div class="field" id="preview">
                 <a target='_blank' href='<?php echo ($vo["image"]); ?>'><img style="width: 300px;height:150px;border: 1px solid;" src="<?php echo ($vo["image"]); ?>"></a>
                 </div>
            </td>
		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
	</table>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
<script src="/Public/Admin/statics/iCheck-1.0.2/icheck.min.js"></script>
<script>
$(document).ready(function(){
    $('.xb-icheck').iCheck({
        checkboxClass: "icheckbox_minimal-blue",
        radioClass: "iradio_minimal-blue",
        increaseArea: "20%"
    });
});
</script>
</body>
</html>