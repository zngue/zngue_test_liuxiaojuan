<?php

namespace app\install\controller;

use think\Controller;
use think\Config;
use think\Db;
class Install extends Controller
{

    protected function _initialize(){
     
      if(file_exists('./install.lock')){
          return  $this->error('已经成功安装了轮回，请不要重复安装!');
      }
    }

    public function step1()
    {
        
      session('error', false);
      //环境检测
      $env = check_env();
      //目录文件读写检测
      if(IS_WRITE){
        $dirfile = check_dirfile();

       
        $this->assign('dirfile', $dirfile);
      }
      //函数检测
      $func = check_func();
      session('step', 1);
      $this->assign('env', $env);
      $this->assign('func', $func);
      return $this->fetch();
    }



  //安装第二步，创建数据库
  public function step2($db = null, $admin = null)
  {

    if(request()->isPost())
    {

  
        $admin = input('param.')['admin'];

        p(input('param.'));
       
        //检测管理员信息
        if(!is_array($admin) || empty($admin[0]) || empty($admin[1]) ){
         $this->error('请填写完整管理员信息');
        } else if($admin[1] != $admin[2]){
         $this->error('确认密码和密码不一致');
        } else {
          $info = array();
          list($info['username'], $info['password'], $info['repassword']) = $admin;
          //缓存管理员信息
          session('admin_info', $info);
        }



        //检测数据库配置
        if(!is_array($db) || empty($db[0]) ||  empty($db[1]) || empty($db[2]) || empty($db[3])){
          $this->error('请填写完整的数据库配置');
        } else {
          $DB = array();
          list($DB['DB_TYPE'], $DB['DB_HOST'], $DB['DB_NAME'], $DB['DB_USER'], $DB['DB_PWD'],
             $DB['DB_PORT'], $DB['DB_PREFIX']) = $db;
          //缓存数据库配置
          session('db_config', $DB);

          //创建数据库
          $dbname = $DB['DB_NAME'];
          unset($DB['DB_NAME']);
          $db = Db::connect($DB);

          try {
                $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
                $db = Db::execute($sql);

                P($db);

            } catch (PDOException $e) {
                return $this->error($e->getError());
            }  
          }

        //跳转到数据库安装页面
        $this->redirect('step3');
      } else {

              if(session('update')){
                  session('step', 2);
                 return  $this->fetch('update'); 
              }else{
        session('error') && $this->error('环境检测没有通过，请调整环境后重试！');

        $step = session('step');
        if($step != 1 && $step != 2){
          $this->redirect('step1');
        }
        session('step', 2);
        return  $this->fetch();
      }
      
    }
  }







}
