<?php

namespace app\admin\controller;
use app\admin\model\ShipModel;
use app\admin\model\VoyageModel;
use app\admin\model\RichWholeModel;
use app\admin\model\OrderModel;
use think\Db;

class Order extends Base
{

    /**
     * [index 订单列表]
     
     */
    /*public function index(){
        $key = input('key');
        $r_id = input('r_id');
        $s_type = input('s_type');
        $status = input('status');
        $map['o.is_del'] = 0;
        if($key&&$key!==""){
            $map['o.username|o.telphone|v.starting_place'] = ['like',"%" . $key . "%"];          
        } 
        if($r_id&&$r_id!==""){
            $map['s.r_id'] = $r_id;          
        } 
        if($s_type&&$s_type!==""){
            $map['v.s_type'] = $s_type;          
        }
        if($status&&$status!==""){
            $map['o.status'] = $status;          
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $order = new OrderModel();
        $count = $order->getCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $order->getOrderAll($map, $Nowpage, $limits);
        //dump($lists);exit;
        $s_types = config('s_type');
        $statuss = config('status');
        foreach ($lists as $k => $v) {
            $lists[$k]['create_times'] = date('Y-m-d H:i:s',$v['create_times']);
            $lists[$k]['status'] = $statuss[$v['status']];
            $lists[$k]['s_type'] = $s_types[$v['s_type']];
            $lists[$k]['r_name'] = $v['r_name'] == ''?'':$v['r_name'];
            $lists[$k]['p_name'] = $v['p_name'] == ''?'':$v['p_name'];
        }
        $whole = new RichWholeModel();
        $raw = $whole->getAll(array('is_del'=>'0'),'r_id,name');//所有系列
        $this->assign('raw', $raw);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        $this->assign('r_id', $r_id);
        $this->assign('s_type', $s_type);
        $this->assign('status', $status);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }*/
    public function index(){
        $key = input('key');
        $status = input('status');
        $map['is_del'] = 0;
        if($key&&$key!==""){
            $map['username|telphone'] = ['like',"%" . $key . "%"];          
        } 
        if($status&&$status!==""){
            $map['status'] = $status;          
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $order = new OrderModel();
        $count = $order->getCounts($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $order->getOrderAlls($map, $Nowpage, $limits);
        //dump($lists);exit;
        $statuss = config('status');
        foreach ($lists as $k => $v) {
            $lists[$k]['create_times'] = date('Y-m-d H:i:s',$v['create_times']);
            $lists[$k]['status'] = $statuss[$v['status']];
            
        }
       
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        $this->assign('status', $status);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [detail_order 查看]
     * @return [type] [description]
     
     */
    public function detailOrder()
    {
        $order = new OrderModel();
        $o_id = input('param.o_id');
        $field = 'o.*,s.p_name,s.p_model,s.r_wholename,s.r_name,s.s_img,
                 v.starting_place,v.end_place';
        $lists = $order->getOneOrder(array('o.o_id'=>$o_id),$field);
        $s_types = config('s_type');
        $lists['s_type'] = $s_types[$lists['s_type']];
        $statuss = config('status');
        $lists['status'] = $statuss[$lists['status']];
        if ($lists['type']) {
            $types = config('type');
            $lists['type'] = $types[$lists['type']];
        }
        $lists['create_times'] = date('Y-m-d H:i:s',$lists['create_times']);
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * [del_order 删除]
     * @return [type] [description]
     
     */
    public function delOrder()
    {
        $o_id = input('param.id');
        $order = new OrderModel();
        $flag = $order->delOrder($o_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }


    /**
     * [order_state 状态]
     * @return [type] [description]
     
     */
    public function orderState()
    {
        $o_id=input('param.id');
        $status = Db::name('order')->where(array('o_id'=>$o_id))->value('status');//判断当前状态情况
        if($status==2)
        {
            $flag = Db::name('order')->where(array('o_id'=>$o_id))->setField(['status'=>1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '未审核']);
        }
        else
        {
            $flag = Db::name('order')->where(array('o_id'=>$o_id))->setField(['status'=>2]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已审核']);
        }
    
    }


   

}