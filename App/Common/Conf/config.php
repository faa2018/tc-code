<?php
    //常用配置
	return array (
		'LOAD_EXT_CONFIG' => 'db,web_config,error_info_config,finance_config', // 加载扩展配置文件
		'DEFAULT_FILTER'  =>  'strip_tags,stripslashes,checkSql',//默认检测的方法 用于I函数过滤
		//默认设置
		'DEFAULT_MODULE'      => 'Home',//默认模块名
		'DEFAULT_CONTROLLER'  => 'Index',//默认控制器名
		'DEFAULT_ACTION'      => 'index',//默认方法名
		// 开启路由
		'URL_ROUTER_ON'   => true, 
		'URL_MODULE_MAP'   =>    array('admin'=>'admin'),
		'MODULE_ALLOW_LIST'    =>    array('Home','admin','API'),
		'DEFAULT_MODULE'       =>    'Home',  // 默认模块
		//关闭debug模式错误跳入404 
        //'TMPL_EXCEPTION_FILE' =>'./404.html',
		// 显示错误信息 开启页面trace
		'SHOW_ERROR_MSG'  => true,
        'SHOW_PAGE_TRACE' => false,
		//密码加密后缀
		'PASSWORDSUFFIX' => 'zy',
		// 模版
		'TMPL_ACTION_SUCCESS' => './App/Common/dispatch_jump.html',
		'TMPL_ACTION_ERROR' => './App/Common/dispatch_jump.html',
		'TMPL_L_DELIM' => '{', // 模板引擎普通标签开始标记
		'TMPL_R_DELIM' => '}', // 模板引擎普通标签结束标记
		// URl设置
		'URL_CASE_INSENSITIVE' => false,
		'URL_MODEL' => 2,
		'URL_HTML_SUFFIX' => 'html',
		'URL_ROUTER_ON' => true, // 是否开启URL路由
		// 加载语言包
		'LANG_AUTO_DETECT' => true, // 关闭语言的自动检测，如果你是多语言可以开启
		'LANG_SWITCH_ON' => TRUE, // 开启语言包功能，这个必须开启
		'DEFAULT_LANG' => 'zh-cn', // zh-cn文件夹名字 /lang/zh-cn/common.php
		//设置模板通用路径
		'TMPL_PARSE_STRING' => array (
			'__ADMIN_CSS__' => '/Public/Admin/css',
			'__ADMIN_JS__' => '/Public/Admin/js',
			'__ADMIN_IMG__' => '/Public/Admin/img',
			'__ADMIN_TEXT__' => '/Public/Admin/text',
			
			
			'__HOME_CSS__' => '/Public/Home/css',
			'__HOME_JS__' => '/Public/Home/js',
			'__HOME_IMG__' => '/Public/Home/img',
			'__HOME_PRIVATE__' => '/Public/Home/private_js',
			
			
			'__HOME_INDEX_CSS__' => '/Public/Home/index/css',
			'__HOME_INDEX_JS__' => '/Public/Home/index/js',
			'__HOME_INDEX_IMG__' => '/Public/Home/index/img',
			'__HOME_INDEX_FONT__' => '/Public/Home/index/fonts',
			
			
			'__ADMIN_ACEADMIN__' => '/Public/Admin/aceadmin',
			'__ADMIN_CSS__' => '/Public/Admin/css',
			'__ADMIN_INSTALL__' => '/Public/Admin/install',
			'__ADMIN_STATICS__' => '/Public/Admin/statics',
			
			'__PUBLIC__'				=> '/Public',	
			'__PUBLIC_CSS__' => '/Public/css',
			'__PUBLIC_JS__' => '/Public/js',
			'__PUBLIC_IMG__' => '/Public/img',
		),
	);
