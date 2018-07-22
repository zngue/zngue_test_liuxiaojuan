<?php

return [
    //模板参数替换
    'view_replace_str' => array(
        '__CSS__' => '/static/home/css',
        '__JS__'  => '/static/home/js',
        '__IMG__' => '/static/home/images',	
    ),
    'http_exception_template'    =>
        [
            //404 =>  Env::get('app_path') . 'home/view/exception/404.html',
            404 =>  APP_PATH.'home/view/exception/404.html',
        ],
	'url_html_suffix'=>'',
];
