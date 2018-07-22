<?php

return [
    //模板参数替换
    'view_replace_str' => array(
        '__CSS__' => '/static/wap/css',
        '__JS__'  => '/static/wap/js',
        '__IMG__' => '/static/wap/images',	
    ),
    'http_exception_template'    =>
        [
            //404 =>  Env::get('app_path') . 'home/view/exception/404.html',
            404 =>  APP_PATH.'wap/view/exception/404.html',
        ],
	'url_html_suffix'=>'',
];
