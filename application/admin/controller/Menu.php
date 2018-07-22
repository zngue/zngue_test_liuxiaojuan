<?php

namespace app\admin\controller;
use app\admin\model\MenuModel;
use think\Db;

class Menu extends Base
{	
    /**
     * [index 菜单列表]
     * @return [type] [description]
     
     */
    public function index()
    {
        $nav = new \org\Leftnav;
        $menu = new MenuModel();
        $admin_rule = $menu->getAllMenu();
        $arr = $nav::rule($admin_rule);
        $this->assign('admin_rule',$arr);
        return $this->fetch();
    }

	
    /**
     * [add_rule 添加菜单]
     * @return [type] [description]
     
     */
	public function addRule()
    {
        if(request()->isAjax()){
            $param = input('post.'); 
            $param['status']=$param['status']?$param['status']:'0';          
            $menu = new MenuModel();
            $flag = $menu->insertMenu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }


    /**
     * [edit_rule 编辑菜单]
     * @return [type] [description]
     
     */
    public function editRule()
    {
        $menu = new MenuModel();
        if(request()->isPost()){
            $param = input('post.');
            $param['status']=$param['status']?$param['status']:'0';
            $flag = $menu->editMenu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign('menu',$menu->getOneMenu($id));
        return $this->fetch();
    }


    /**
     * [roleDel 删除角色]
     * @return [type] [description]
     
     */
    public function delRule()
    {
        $id = input('param.id');
        $menu = new MenuModel();
        $flag = $menu->delMenu($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }



    /**
     * [ruleorder 排序]
     * @return [type] [description]
     
     */
    public function ruleOrder()
    {
        if (request()->isAjax()){
            $param = input('post.');     
            $auth_rule = Db::name('auth_rule');
            foreach ($param as $id => $sort){
                $auth_rule->where(array('id' => $id ))->setField('sort' , $sort);
            }
            return json(['code' => 1, 'msg' => '排序更新成功']);
        }
    }


    /**
     * [rule_state 菜单状态]
     * @return [type] [description]
     
     */
    public function ruleState()
    {
        $id = input('param.id');
        $status = Db::name('auth_rule')->where('id',$id)->value('status');//判断当前状态
        if($status==1)
        {
            $flag = Db::name('auth_rule')->where('id',$id)->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        }
        else
        {
            $flag = Db::name('auth_rule')->where('id',$id)->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    
    }



}