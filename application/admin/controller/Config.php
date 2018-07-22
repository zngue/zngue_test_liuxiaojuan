<?php

namespace app\admin\controller;
use app\admin\model\ConfigModel;
use think\Db;

class Config extends Base
{


    private $cfg = '';

    public function __construct()
    {
        parent::__construct();
        $this->cfg = new ConfigModel();
    }
   
    /**
     * [index 配置列表]
     
     */
    public function index(){

        $key = input('key');
        $map = [];
        if($key&&$key!=="")
        {
            $map['title'] = ['like',"%" . $key . "%"];          
        }      
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $count = $this->cfg->getAllCount($map);  //总数据
        $allpage = intval(ceil($count / $limits));       
        $lists = $this->cfg->getAllConfig($map, $Nowpage, $limits);  
        foreach($lists as $k=>$v)
        {
            $lists[$k]['type']=get_config_type($v['type']);
            $lists[$k]['group']=get_config_group($v['group']);
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }


    /**
     * [add_config 添加配置]
     
     */
    public function addConfig()
    {
        if(request()->isAjax()){

            $param = input('post.');
            $config = new ConfigModel();
            $flag = $this->cfg->insertConfig($param);
            cache('db_config_data',null);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }


    /**
     * [edit_config 编辑配置]
     
     */
    public function editConfig()
    {
        $config = new ConfigModel();

        if(request()->isAjax()){
            $param = input('post.');
            $param['status']=$param['status']?$param['status']:'0';
            $flag = $this->cfg->editConfig($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $id = input('param.id');
        $info = $config->getOneConfig($id);
        $this->assign('info', $info);
        return $this->fetch();
    }


    /**
     * [del_config 删除配置]
     
     */
    public function delConfig()
    {
        $id = input('param.id');
        $config = new ConfigModel();
        $flag = $this->cfg->delConfig($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }



    /**
     * [user_state 配置状态]
     
     */
    public function statusConfig()
    {
        $id = input('param.id');
        $status = Db::name('config')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1)
        {
            $flag = Db::name('config')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        }
        else
        {
            $flag = Db::name('config')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    
    }


    /**
     * [获取某个标签的配置参数]
     
     */
    public function group() {  
        $id   = input('id',1);
        $type = config('config_group_list'); 

        $list = Db::name("Config")->where(array('status'=>1,'group'=>$id))->field('id,name,title,extra,value,remark,type')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$id);
        return $this->fetch();
    }



    /**
     * [批量保存配置]
     
     */
    public function save($config){
        if($config && is_array($config)){
            $Config = Db::name('Config');
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $Config->where($map)->setField('value', $value);
            }
        }
        cache('db_config_data',null);
        $this->success('保存成功！');
    }


    public function extend () {

        $key = input('key');
        $map = [];
        //$map['closed'] =0;
        if($key&&$key!=="")
        {
        $map['title'] = ['like',"%" . $key . "%"];          
        }             
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $count = Db::name('extend')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
       
        $lists = $this->cfg->getAllExt($map, $Nowpage, $limits);   
        $this->assign('count',$count);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数 
        $this->assign('val', $key);
        if(input('get.page'))
        {
            return json($lists);
        }

        return $this->fetch();
    }

    /**
     * 添加新的配置项
     */
    public function addExt() {

        if(request()->isPost()){
            $param      = input('post.');
            $flag       = $this->cfg->insertExt($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
    }

    /**
     * 删除配置
     */
    public function delExt () {

        $id         = input('param.id');
        $flag       = Db::name('extend')->where('id',$id)->delete();
        $msg        = $flag ? '删除成功' : '删除失败';
        return json(['code'=>1,'msg'=>$msg,'data'=>'']);

    }

    /**
     * 编辑扩展配置
     */
    public function editExt () {
       
        if(request()->isPost()){
            $param = input('post.');
            $value = array_combine($param['value'], $param['val']);
            unset($param['val']);
            $param['value'] = json_encode($value);
            $flag = $this->cfg->editExt($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $id = input('param.id');
        $ext = $this->cfg->getOneEXT($id);
        $exts = json_decode($ext['value'],true);

        if ( is_array($exts) ){
            while(list($key, $val) = each($exts)){ 
                
            } 
         }
        $this->assign('conf',$ext);
        $this->assign('confs',$exts);
        return $this->fetch();
    } 



}