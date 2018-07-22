<?php

namespace app\admin\controller;
use app\admin\model\MemberModel;
use app\admin\model\MemberGroupModel;
use think\Db;

class Member extends Base
{
    
    //*********************************************会员列表*********************************************//
    /**
     * 会员列表
     
     */
    public function index(){

        $key = input('key');
        $map['is_del'] = 0;//0未删除，1已删除
        if($key&&$key!=="")
        {
            $map['nickname|m_id'] = ['like',"%" . $key . "%"];          
        }
        $member = new MemberModel();       
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $count = $member->getAllCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));       
        $lists = $member->getMemberByWhere($map, $Nowpage, $limits);   
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
     * 编辑会员
     
     */
    public function edit_member()
    {
        $member = new MemberModel();
        if(request()->isAjax()){
            $param = input('post.');
            $param['update_time'] = time();
            $flag = $member->editMember($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $m_id = input('param.m_id');
        $this->assign(['member' => $member->getOneMember($m_id)]);
        return $this->fetch();
    }


    /**
     * 删除会员
     
     */
    public function del_member()
    {
        $m_id = input('param.id');
        $member = new MemberModel();
        $flag = $member->delMember($m_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }



    /**
     * 会员状态
     
     */
    public function member_status()
    {
        $id = input('param.id');
        $status = Db::name('member')->where('id',$id)->value('status');//判断当前状态情况
        if($status==1)
        {
            $flag = Db::name('member')->where('id',$id)->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        }
        else
        {
            $flag = Db::name('member')->where('id',$id)->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    
    }

}