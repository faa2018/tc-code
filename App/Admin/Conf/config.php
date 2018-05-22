<?php
return array(
	//'配置项'=>'配置值'
    'DEFAULT_CONTROLLER'    =>  'Login', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'showLogin', // 默认操作名称
	'TAGLIB_BUILD_IN'        => 'Cx,Admin\Tag\My',              // 加载自定义标签
	'DB_PATH_NAME'=> '/db',        //备份目录名称,主要是为了创建备份目录
	'DB_PATH'     => COMMON_PATH.'databases/',     //数据库备份路径必须以 / 结尾；
	'DB_PART'     => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
	'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
	'DB_LEVEL'    => '9',         //压缩级别   1:普通   4:一般   9:最高
);