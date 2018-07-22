<?php

namespace app\home\controller;
use think\Controller;
use app\home\model\DetailModel;
use think\Db;

class Base extends Controller
{
    public function _initialize()
    {
    	$detail = new DetailModel();
    	$map['id'] = 1;
    	$field = 'id,title,tel';
    	$info = $detail->getOneDetail($map,$field);
    	$title = $info['title'];
    	$tel = $info['tel'];
    	$this->assign('title', $title);
    	$this->assign('tel', $tel);
    }


}