<?php
return [


    // +----------------------------------------------------------------------
    // | auth配置
    // +----------------------------------------------------------------------
    'auth_config'  => [
        'auth_on'           => 1, // 权限开关
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        => 'yh_auth_group', // 用户组数据不带前缀表名
        'auth_group_access' => 'yh_auth_group_access', // 用户-用户组关系不带前缀表
        'auth_rule'         => 'yh_auth_rule', // 权限规则不带前缀表
        'auth_user'         => 'yh_admin', // 用户信息不带前缀表
    ],

    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '_',
    //'url_route_on' => true,     //开启路由功能
//    'url_route_must'=>  false,
    'route_config_file' =>  ['admin'],   // 设置路由配置文件列表
//    'TMPL_FILE_DEPR' => '-', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符
//    'URL_PATHINFO_DEPR' => '-',	// PATHINFO模式下，各参数之间的分割符号

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'app_trace' =>  false,      //开启应用Trace调试
    'trace' => [
        'type' => 'html',       // 在当前Html页面显示Trace信息,显示方式console、html
    ],
    'sql_explain' => false,     // 是否需要进行SQL性能分析  
    'extra_config_list' => ['database', 'route', 'validate'],//各模块公用配置

    'app_debug' => true,

    /*'http_exception_template'    =>
        [
            //404 =>  Env::get('app_path') . 'home/view/exception/404.html',
            404 =>  APP_PATH.'home/view/exception/404.html',
        ],*/



	'default_module' => 'home',//默认模块	
    //'default_filter' => ['strip_tags', 'htmlspecialchars'],

    //默认错误跳转对应的模板文件
    'dispatch_error_tmpl' => APP_PATH.'admin/view/public/error.tpl',
    //默认成功跳转对应的模板文件
    'dispatch_success_tmpl' => APP_PATH.'admin/view/public/success.tpl',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------
    'log'       => [       
        'type'  => 'File',// 日志记录方式，内置 file socket 支持扩展      
        'path'  => LOG_PATH,// 日志保存目录      
        'level' => [],// 日志记录级别
    ],


    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' => [     
        'type'   => 'file',// 驱动方式        
        'path'   => CACHE_PATH,// 缓存保存目录        
        'prefix' => '',// 缓存前缀       
        'expire' => 0,// 缓存有效期 0表示永久缓存
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'            => [
        'id'             => '',
        'var_session_id' => '',// SESSION_ID的提交变量,解决flash上传跨域
        'prefix'         => 'think',// SESSION 前缀
        'type'           => '',// 驱动方式 支持redis memcache memcached
        'auto_start'     => true,// 是否自动开启 SESSION
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'        => [      
        'prefix'    => '',// cookie 名称前缀      
        'expire'    => 0,// cookie 保存时间      
        'path'      => '/',// cookie 保存路径      
        'domain'    => '',// cookie 有效域名      
        'secure'    => false,//  cookie 启用安全传输      
        'httponly'  => '',// httponly设置      
        'setcookie' => true,// 是否使用 setcookie
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
    


    // +----------------------------------------------------------------------
    // | 数据库设置
    // +----------------------------------------------------------------------
    'data_backup_path'     => '../data/',   //数据库备份路径必须以 / 结尾；
    'data_backup_part_size' => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
    'data_backup_compress' => '1',          //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
    'data_backup_compress_level' => '9',    //压缩级别   1:普通   4:一般   9:最高


    // +----------------------------------------------------------------------
    // | 极验验证,请到官网申请ID和KEY，http://www.geetest.com/
    // +----------------------------------------------------------------------
    'verify_type' => '1',   //验证码类型：0极验验证， 1数字验证码
    'gee_id'  => 'ca1219b1ba907a733eaadfc3f6595fad',
    'gee_key' => '9977de876b194d227b2209df142c92a0',
    'auth_key' => 'JUD6FCtZsqrmVXc2apev4TRn3O8gAhxbSlH9wfPN', //默认数据加密KEY
    'pages'    => '10',//分页数 
    'salt'     => 'wZPb~yxvA!ir38&Z',//加密串 

    //'img_path'=>'https://'.HTTP_HOST.'/uploads/images/',

    //提货码的订单状态 
    'lc_status' => ['1'=>'暂无', '2'=>'处理中', '3'=>'订单已发货', '4'=>'订单归入特殊问题', '5'=>'订单已签收'],
    //特殊订单的订单状态
    'sp_status' => ['1'=>'处理中', '2'=>'订单已发货', '3'=>'订单继续归入特殊问题', '4'=>'订单已签收'],

    //游轮类型
    's_type' => ['1'=>'豪华游轮', '2'=>'普通游轮'],

    //订单状态
    'status' => ['1'=>'未审核', '2'=>'已审核'],

    //类型
    'type' => ['1'=>'成人','2'=>'儿童'],

    //星期
    'weeks' => ['0'=>'周日', '1'=>'周一', '2'=>'周二', '3'=>'周三','4'=>'周四', '5'=>'周五', '6'=>'周六'],


];