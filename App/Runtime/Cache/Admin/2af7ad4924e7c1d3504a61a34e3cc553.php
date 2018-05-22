<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>用户推荐结构 </title>
        <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/bootstrap-3.3.5/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="/Public/Admin/statics/font-awesome-4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/Public/Admin/css/base.css" />
	
<script type="text/javascript">

    $('.treenew li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    function getsuser(ob) {
        // alert(123)
        if($(ob).children('#adimg').attr('src') == "/Public/Admin/images/add.png"){
            $(ob).children('#adimg').attr('src',"/Public/Admin/images/minus.png")
        }else{
            $(ob).children('#adimg').attr('src',"/Public/Admin/images/add.png")
        }

        var children = $(ob).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(ob).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else if( $(ob).attr("index") == 1 ){
            children.show('fast');
            return false;
        }else {
            children.show('fast');
            $(ob).attr('index','1');
            var asd=$(ob).find("em").text();
            //alert(asd);
            var url = "<?php echo U('User/getInivt');?>"
            var ob = $(ob);
            $.post(url,{'username':asd},function(msg){
                if(msg.status==1){

                    ob.attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
                    var str = "<ul>"
                        for(var i=0;i<msg.user.length;i++){
                            str += " <li class='parent_li' path='"+msg['path']+"' ><span class='tree' onclick='getsuser(this);'  data-key='on'><img src='/Public/Admin/images/add.png' id='adimg'/><img src='/Public/Admin/images/head2.png' /><em class='account'>"+msg['user'][i]['username']+'</em>'+"----投资金额："+msg['user'][i]['touzi_money']+" ---- 注册日期："+msg['user'][i]['add_time']+"</span></li>";
                        }
                    str += "</ul>";
                    ob.after(str);
                }else{
                    alert('无下线会员');
                }
            })

        }
        // $(ob).stopPropagation();
    }

</script>
<style>
    .icon1{width:20px !important;
        height:20px !important;
        background-image:url(/Public/Admin/images/add.png);
        background-repeat:no-repeat;
        background-position:center;
        float:left;
        line-height: 35px;
        margin-top:6px;
    }
    .icon2{width:20px !important;
        height:20px !important;
        background-image:url(/Public/Admin/images/reduce.png);
        background-repeat:no-repeat;
        background-position:center;
        float:left;
        line-height: 35px;
        margin-top:6px;
    }
    .tree{ cursor:pointer;}
    .td{ height:35px; line-height:35px; text-align:left; padding-left:100px}
    .treenew {
        min-height:20px;
        padding:19px;
        margin-bottom:20px;
        background-color:#fbfbfb;
        border:1px solid #ccc;
        -webkit-border-radius:4px;
        -moz-border-radius:4px;
        border-radius:4px;
        -webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
        -moz-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
        box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05)
    }
    .treenew li {
        list-style-type:none;
        margin:0;
        padding:10px 5px 0 5px;
        position:relative
    }
    .treenew li::before, .treenew li::after {
        content:'';
        left:-20px;
        position:absolute;
        right:auto
    }
    .treenew li::before {
        border-left:1px solid #999;
        bottom:50px;
        height:100%;
        top:0;
        width:1px
    }
    .treenew li::after {
        border-top:1px solid #999;
        height:20px;
        top:25px;
        width:25px
    }
    .treenew li span {
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border:1px solid #999;
        border-radius:5px;
        display:inline-block;
        padding:3px 8px;
        text-decoration:none
    }
    .treenew li.parent_li>span {
        cursor:pointer
    }
    .treenew>ul>li::before, .treenew>ul>li::after {
        border:0
    }
    .treenew li:last-child::before {
        height:25px
    }
    .treenew li.parent_li>span:hover, .treenew li.parent_li>span:hover+ul li span {
        background:#eee;
        border:1px solid #94a0b4;
        color:#000
    }
    .treenew ul{
        display: block;
        list-style-type: disc;
    //-webkit-margin-before: 1em;
    //-webkit-margin-after: 1em;
        -webkit-margin-start: 0px;
        -webkit-margin-end: 0px;
        -webkit-padding-start: 40px;
    }
</style>
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
   <li >
         <a href="<?php echo U('Admin/User/index');?>">用户列表</a>
   </li>
   <li class="active">
        <a href="<?php echo U('Admin/User/updateUser');?>">用户推荐结构</a>
    </li>
</ul>
<form>
    <table class="table table-striped table-bordered table-hover table-condensed">
        
		<tr >
			<td colspan='5'>
				<a href="<?php echo U('User/getJiegou',array('type'=>1));?>">
					<input class="btn btn-success" type="button" value="切换图表显示模式">
				</a>
				<a href="<?php echo U('User/getJiegou',array('type'=>2));?>">
					<input class="btn btn-success" type="button" value="切换表格显示模式">
				</a>
            </td>
		</tr>
		<!--<tr>
            <th width="5%">搜索</th>
            <td width="15%">
               	<input class="form-control" type="text" name="username" value="<?php echo ($_GET['username']); ?>"  placeholder="用户名" >
            </td>
              <td width="15%">
                 <input class="form-control" type="text" name="email" value="<?php echo ($_GET['email']); ?>"  placeholder="email" >
             </td>
            <td width="15%">
                <input class="form-control" type="text" name="phone" value="<?php echo ($_GET['phone']); ?>" placeholder="手机号码">
            </td>
            <td width="80%">
                <input class="btn btn-success" type="submit" value="搜索">
				
				<span style='color:red;'>三个搜索输入一个即可，输入多个以最后一个为准</span>
            </td>
        </tr>-->
    </table>
</form>
<!-- 导航栏结束 -->
<div>
	<div class="page-content">

    <div class="container-fluid">
        
        <div class="row-fluid" style=" width: 100%; margin: 0 auto;">
            <div class="span12">
                <div class="portlet box">

                    <div class="portlet-body">

                        <!-- BEGIN xipu-->
                        <div class="result-wrap">

                            <div class="treenew well">
                                <ul>
                                    <li class="parent_li">
							<span class="tree" onclick="getsuser(this);" data-key="on">
								<img src="/Public/Admin/images/add.png" id="adimg"/>
								<img src="/Public/Admin/images/head2.png" />
								<em class="account"><?php echo ($user["username"]); ?></em> 
								----投资金额：<?php echo ((isset($user["touzi_money"]) && ($user["touzi_money"] !== ""))?($user["touzi_money"]):'0'); ?>
								---- 注册日期：<?php echo (date("Y-m-d H:i:s",$user["add_time"])); ?>
							</span>

                                    </li>

                                </ul>
                            </div>

                        </div>
                        <!-- END xipu-->

                    </div>
                </div>
            </div><!-- End span12 -->

        </div> <!-- End Row Fluid -->


    </div><!-- End span12 -->

</div>
</div>
<!-- 引入bootstrjs部分开始 -->
<script src="/Public/Admin/statics/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Admin/statics/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/Public/js/base.js"></script>
</body>
</html>