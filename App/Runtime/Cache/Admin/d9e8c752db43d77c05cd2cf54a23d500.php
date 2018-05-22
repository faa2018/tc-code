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
    <script src="/Public/Home/js/jquery-1.11.3.min.js"></script>
<script src="/Public/Home/layer/layer.js"></script>
<!-- 导航栏开始 -->
<div class="bjy-admin-nav">
    <a href="<?php echo U('Admin/Index/index');?>"><i class="fa fa-home"></i> 首页</a>
    &gt;
    服务器信息
</div>
<!-- 导航栏结束 -->
<div style="padding-top:100px;">
<div class="result-wrap">
        <div class="result-title">
            <h1></h1>
        </div>
        <div class="result-content">
            
		<table class="table table-striped table-bordered table-hover table-condensed">
                <tr>
                    <td width="20%">系统版本</td>
                    <td width="20%"><?php echo php_uname('r');?></td>
                    <td width="20%">服务器操作系统</td>
                    <td width="40%"><?php echo php_uname('s');?></td>
                </tr>
                <tr>
                    <td>运行环境</td>
                    <td><?php echo php_sapi_name();?></td>
                    <td>PHP版本</td>
                    <td><?php echo PHP_VERSION;?></td>
                </tr>
                <tr>
                    <td>MySql版本</td>
                    <td><?php echo mysqlnd;?></td>
                    <td>服务器IP</td>
                    <td><?php echo GetHostByName($_SERVER['SERVER_NAME']);?></td>
                </tr>
                <tr>
                    <td>你的IP</td>
                    <td><?php echo $_SERVER['REMOTE_ADDR'];?></td>
                    <td>服务器端口</td>
                    <td><?php echo $_SERVER['SERVER_PORT'];?></td>
                </tr>
                <tr>
                    <td>绝对路径</td>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT'];?></td>
                    <td>网站域名</td>
                    <td><?php echo $_SERVER['SERVER_NAME'];?></td>
                </tr>
                <tr>
                    <td>官网地址</td>
                    <td><?php echo $_SERVER['SERVER_NAME'];?></td>
                    <td>版权所有</td>
                    <td><?php echo $config['copyright'];?></td>
                </tr>
            </table>
			<table class="table table-striped table-bordered table-hover table-condensed">
                <tr>
                    <td width="20%">平台总业绩</td>
                    <td width="20%"><?php echo ($tongji["yeji_zong"]); ?></td>
                    <td width="20%">今日新增业绩</td>
                    <td width="40%"><?php echo ($tongji["yeji_xinzeng"]); ?></td>
                </tr>
                <tr>
                    <td>今日回馈积分份数</td>
                    <td><?php echo ($tongji["fenhong_gufen"]); ?></td>
                    <td>每份回馈积分预计金额</td>
                    <td><?php echo ($tongji["money_gufen"]); ?></td>
                </tr>
                <tr>
                    <td>今日回馈积分总金额</td>
                    <td><?php echo ($tongji["money_fenhong_day"]); ?></td>
                    <td>总回馈积分支出</td>
                    <td><?php echo ($tongji["money_febhong_zong"]); ?></td>
                </tr>
                
            </table>
        </div>
    </div>
</div>


<div class="xb-page"><?php echo ($page); ?></div>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
</body>
</html>