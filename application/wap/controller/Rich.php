<?php
namespace app\wap\controller;
use app\wap\model\ShipModel;
use app\wap\model\VoyageModel;
use app\wap\model\RichWholeModel;
use app\wap\model\OrderModel;
use app\wap\model\DetailModel;
use think\Db;

class Rich extends Base
{
    public function rich()
    {
    	//已添加游轮的系列
        $whole = new RichWholeModel();
        $map['is_del'] = 0;
        $field = 'r_id,name';
        $wholes = $whole->getAll($map,$field);
        $ship = new ShipModel();
        $map['s_type'] = 1;
        $field2 = 'r_id';
        $raws = $ship->getAll($map,$field2);
        foreach ($wholes as $k => $v) {
            if (empty($raws)) {
                unset($wholes[$k]);
            }else{
                foreach ($raws as $kk => $vv) {
                    if ($v['r_id']==$vv['r_id']) {
                        unset($raws[$kk]);
                    }else{
                        break;
                    }
                }

            }
        }
        $this->assign('wholes', $wholes);

        return $this->fetch();
    }

    public function rich2()
    {
        $whname = input('param.whname');
        
        if ($whname!= '全部') {
            $map['r_wholename'] = $whname;

        }
        $map['is_del'] = 0;
        $map['s_type'] = 1;
        $Nowpage = input('param.page')? input('param.page'):1;
        $limits = 10;// 获取每页条数
        $ship = new ShipModel();
        $count = $ship->getCountCom($map);// 获取总条数
        $allpage = intval(ceil($count / $limits));//计算总页面
        
        $field = 's_id,r_name,s_img,reference_price,r_wholename';
        $data = $ship->getAllData($map,$field,$Nowpage, $limits);
        $list['total'] = $count;
        $list['pageSize'] = $limits;
        $list['allpage'] = $allpage;
        $list['data'] = $data;
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }


    public function richDetail()
    {
    	$s_id = input('param.s_id');
    	$map['s.s_id'] = $s_id;
        $ship = new ShipModel();
    	
        $info = $ship->getOneShip($map,'s.*,rw.content');
        $info['scheduling_wap1'] = unserialize($info['scheduling_wap1']);
        $info['scheduling_wap2'] = unserialize($info['scheduling_wap2']);

        $voyage = new VoyageModel();
        $maps['v.s_id'] = $s_id;
        $maps['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $raws = $voyage->getAll($maps,'v.v_id');
        if ($raws) {
            $info['v_id'] = $raws[0]['v_id'];
        }else{
            $info['v_id'] = 0;
        }

        $this->assign('info', $info);

        return $this->fetch();
    
    }


    //当前游轮的所有行程
    public function richV()
    {
        $s_id = input('param.s_id');
        $voyage = new VoyageModel();
        
        $map1['v.starting_place'] = '重庆';
        $map1['v.end_place'] = '宜昌';
        $map1['v.s_id'] = $s_id;
        $map1['v.is_del'] = 0;
        //$map1['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $data1 = $voyage->getAll($map1,'v.v_id,v.starting_time');
        foreach ($data1 as $k => $v) {
            
            $raw = explode('-', $v['starting_time']);//2018-04-28
        
            if ($raw['1']['0'] ==0) {
                    $v['yue'] = $raw['1']['1'];
            }else{
                    $v['yue'] = $raw['1'];
            }

            if ($raw['2']['0'] ==0) {
                    $v['ri'] = $raw['2']['1'];
            }else{
                    $v['ri'] = $raw['2'];
            }
            $v['nianyueri'] = $raw['0'].'-'.$v['yue'].'-'.$v['ri'];
        }
        /////////////////////////
        $map2['v.starting_place'] = '宜昌';
        $map2['v.end_place'] = '重庆';
        $map2['v.s_id'] = $s_id;
        $map2['v.is_del'] = 0;
        //$map2['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $data2 = $voyage->getAll($map2,'v.v_id,v.starting_time');
        foreach ($data2 as $k => $v) {
            
            $raw = explode('-', $v['starting_time']);//2018-04-28
        
            if ($raw['1']['0'] ==0) {
                    $v['yue'] = $raw['1']['1'];
            }else{
                    $v['yue'] = $raw['1'];
            }

            if ($raw['2']['0'] ==0) {
                    $v['ri'] = $raw['2']['1'];
            }else{
                    $v['ri'] = $raw['2'];
            }
             $v['nianyueri'] = $raw['0'].'-'.$v['yue'].'-'.$v['ri'];
        }
        $list['data1'] = $data1;
        $list['data2'] = $data2;
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    public function shipDetail()
    {
        $s_id = input('param.s_id');
        $map['s_id'] = $s_id;
        $ship = new ShipModel();
        
        $list = $ship->getOne($map);

        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //获取成人价格
    public function price() 
    {
        $s_id = input('param.s_id');
        $shu_n = input('param.shu_n');
        $shu_y = input('param.shu_y');
        $shu_r = input('param.shu_r');
        $wayshow = input('param.wayshow');
        $map['starting_time'] = $shu_n.'-'.$shu_y.'-'.$shu_r;
        $map['s_id'] = $s_id;
        if ($wayshow==1) {
            $map['starting_place'] = '重庆';
            $map['end_place'] = '宜昌';
        }else{

            $map['starting_place'] = '宜昌';
            $map['end_place'] = '重庆';

        }
        $voyage = new VoyageModel();
        
        $list = $voyage->getOne($map,$field='v_id,adult_money');

        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //获取所选的行程信息
    public function richSwitch()
    {
        $s_id = input('param.s_id');
        $riqi = input('param.riqi');
        $start = input('param.start');
        $voyage = new VoyageModel();
        if ($start=='cy') {
            $map['v.starting_place'] = '重庆';
            $map['v.end_place'] = '宜昌';
        }else{

            $map['v.starting_place'] = '宜昌';
            $map['v.end_place'] = '重庆';

        }
        $map['v.s_id'] = $s_id;
        $data1 = $voyage->getAll($map,'v.v_id,v.starting_time,v.adult_money,v.child_money');
        
        if ($data1!='') {
            foreach ($data1 as $k => $v) {
                if ($v['starting_time']!=$riqi) {
                    unset($data1[$k]);
                }
            }
        }
        if (!empty($data1)) {
            $data1 =  array_merge($data1);
            $num = intval($data1[0]['child_money']);
            if ($num==0) {
                $data1[0]['child_money']= 0;
            }
        }
        $list['data1'] = $data1;
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //提交信息
    public function richSub2()
    {
        if(request()->isAjax()){
            $param = input('post.');
            $param['order_number'] = 'SN'.order_sn();
            $param['total_money'] = $param['adult_moneys']*$param['adult_num']+$param['children_moneys']*$param['children_num'];
            $param['create_times'] = time();
            $order = new OrderModel();
            $flag = $order->insertOrder($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }

    

}