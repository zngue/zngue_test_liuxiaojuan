<?php

namespace app\install\controller;

use think\Controller;
use think\Config;

class index extends Controller
{

    // 安装首页
    public function index()
    {
       return $this->fetch();
    }


    //安装完成
    public function complete(){
        $step = session('step');

        if(!$step){
            $this->redirect('index');
        } elseif($step != 3) {
            $this->redirect("Install/step{$step}");
        }

        // 写入安装锁定文件
        Storage::put('./Public/install.lock', 'lock');
        if(!session('update')){
        //创建配置文件
        $this->assign('info',session('config_file'));
        }
        session('step', null);
        session('error', null);
        session('update',null);
        $this->display();
    }


}
