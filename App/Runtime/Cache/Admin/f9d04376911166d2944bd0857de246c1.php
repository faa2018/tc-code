<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title><?php echo ($geshi); ?>用户</title>
        <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/font-awesome-4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/Public/Admin/css/base.css" />
        <link rel="stylesheet" href="/Public/Admin/statics/iCheck-1.0.2/skins/all.css">
	<style>
		.form-control{
		min-width:500px
		}
	</style>
</head>
<body>

<!-- 导航栏开始 -->
<div class="bjy-admin-nav">
    <i class="fa fa-home"></i> 首页
    &gt;
    后台管理
    &gt;
    添加用户资料
</div>
<!-- 导航栏结束 -->
<ul id="myTab" class="nav nav-tabs">
   <li class="active">
        <a href="#">添加用户资料</a>
    </li>
</ul>
<form class="form-inline" method="post" action="<?php echo U('User/addOutUser');?>">
    <table class="table table-striped table-bordered table-hover table-condensed">
		<input type="hidden" name="id"  value="<?php echo ($list['uid']); ?>"  />
		<tr>
            <th>用户账号</th>
            <td>
                <input class="form-control" disabled type="text"  value="<?php echo ($list['phone']); ?>">
            </td>
        </tr>		
		<tr>
            <th>真实姓名</th> 
            <td> 
                <input class="form-control" disabled  type="text" name='real_name'  value="<?php echo ($list['real_name']); ?>"> 
             </td>
        </tr> 
        
        <!-- <tr> -->
            <!-- <th>手机号码</th> -->
            <!-- <td> -->
                <!-- <input class="form-control" disabled  type="text" name="phone"  value="<?php echo ($list['phone']); ?>"> -->
            <!-- </td> -->
        <!-- </tr> -->
		 <tr>
            <th>收件地址</th>
            <td>
                <input class="form-control" disabled type="text" name="weixin"  value="<?php echo ($list['areaid1']); ?>-<?php echo ($list['areaid1']); ?>-<?php echo ($list['areaid1']); ?>-<?php echo ($list['address']); ?>">
            </td>
        </tr>
		 <tr>
            <th>收件人</th>
            <td>
                <input class="form-control" disabled type="text" name="weixin"  value="<?php echo ($list['addressee']); ?>">
            </td>
        </tr>
		<tr>
            <th>收件人手机号</th>
            <td>
                <input class="form-control" disabled type="text" name="weixin"  value="<?php echo ($list['addressee_phone']); ?>">
            </td>
        </tr>
		<tr>
            <th>身份证号</th>
            <td>
                <input class="form-control" disabled type="text" name="idcard" value="<?php echo ($list['idcard']); ?>">
            </td>
        </tr>
		<tr>
            <th>云联惠ID</th>
            <td>
                <input class="form-control" disabled type="text" name="yunlianhui_id"  value="<?php echo ($list['yunlianhui_id']); ?>">
            </td>
        </tr>
		<tr>
            <th>云联惠手机号</th>
            <td>
                <input class="form-control" disabled type="text" name="yunlianhui_phone" value="<?php echo ($list['yunlianhui_phone']); ?>">
            </td>
        </tr>
		<tr>
            <th>推荐人</th>
            <td>
                <input class="form-control" disabled type="text" name="yunlianhui_phone" value="<?php echo ($list['tuijian']); ?>">
            </td>
        </tr>
		<tr>
            <th>推荐人姓名</th>
            <td>
                <input class="form-control" disabled type="text" name="yunlianhui_phone" value="<?php echo ($list['tuijian_name']); ?>">
            </td>
        </tr>
        <?php if(is_array($pingzheng)): $i = 0; $__LIST__ = $pingzheng;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
			<th>图片</th>
            <td>
            	<div class="field" id="preview">
                 <a target='_blank' href='<?php echo ($vo["image"]); ?>'><img style="width: 300px;height:150px;border: 1px solid;" src="<?php echo ($vo["image"]); ?>"></a>
                 </div>
            </td>
		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		<tr>
            <th></th>
            <td>
                
<a class="btn btn-success" href="javascript:history.back(-1)">点击返回</a>				
            </td>
        </tr>
		
    </table>
</form>
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
<script type="text/javascript">    
 function preview(file)  
 {  
 var prevDiv = document.getElementById('preview');  
 if (file.files && file.files[0])  
 {  
 var reader = new FileReader();  
 reader.onload = function(evt){  
 prevDiv.innerHTML = '<img src="' + evt.target.result + '" />';  
}    
 reader.readAsDataURL(file.files[0]);  
}   
}
</script>
</body>
</html>