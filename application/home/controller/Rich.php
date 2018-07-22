<?php
namespace app\home\controller;
use app\home\model\ShipModel;
use app\home\model\VoyageModel;
use app\home\model\RichWholeModel;
use app\home\model\OrderModel;
use app\home\model\DetailModel;
use think\Db;

class Rich extends Base
{
    public function rich()
    {

    	//全部
        $whole = new RichWholeModel();
        $map1['is_del'] = 0;
        $field1 = 'r_id,name';
        $wholes = $whole->getAll($map1,$field1);
        $this->assign('wholes', $wholes);

        return $this->fetch();
    }

    public function rich2()
    {
        $p_id = input('param.p_id');
        $r_id = input('param.r_id');

        if ($p_id==1) {
            $map['v.starting_place'] = '重庆';
            $map['v.end_place'] = '宜昌';
        }

        if ($p_id==2) {
            $map['v.starting_place'] = '宜昌';
            $map['v.end_place'] = '重庆';
        }

        if ($r_id!=0) {
            $map['s.r_id'] = $r_id;
        }

        $map['v.is_del'] = 0;
        $map['v.s_type'] = 1;
        $Nowpage = input('param.page')? input('param.page'):1;
        $limits = 8;// 获取每页条数
        $voyage = new VoyageModel();
        $count = $voyage->getCount($map);// 获取总条数
        $allpage = intval(ceil($count / $limits));//计算总页面
        
        $field = 'v.v_id,v.s_id,s.r_name,s.s_img,v.starting_time,s.tourism_day,s.reference_price';
        $data = $voyage->getAllData($map,$field,$Nowpage, $limits);
        $list['total'] = $count;
        $list['pageSize'] = $limits;
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
    	$map['s_id'] = $s_id;
        $ship = new ShipModel();
    	
        $info = $ship->getOne($map);

        $voyage = new VoyageModel();
        $maps['v.s_id'] = $s_id;
        $maps['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $raws = $voyage->getAll($maps,'v.v_id');

        if ($raws) {
            $info['v_id'] = $raws[0]['v_id'];
        }else{
            $info['v_id'] = 0;
        }
		
		$info['wap1']=unserialize($info['scheduling_wap1']);
		$info['wap2']=unserialize($info['scheduling_wap2']);
		
		//print_r( $info  );die;

        $this->assign('info', $info);

        return $this->fetch();
    
    }


    //当前游轮的重庆到宜昌所有行程
    public function richV()
    {
        $s_id = input('param.s_id');
        $yue = input('param.yue');
        //dump($s_id);exit;
        $voyage = new VoyageModel();
        
        $map1['v.starting_place'] = '重庆';
        $map1['v.end_place'] = '宜昌';
        $map1['v.s_id'] = $s_id;
        $map1['v.is_del'] = 0;
        //$map1['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $map1['v.yuefen'] =  $yue;//月份
        $data1 = $voyage->getAll($map1,'v.v_id,s.reference_price,v.starting_time,v.room,v.adult_money,v.child_money');
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
            $v['yueri'] = $v['yue'].'-'.$v['ri'];
        }
        /////////////////////////
        $map2['v.starting_place'] = '宜昌';
        $map2['v.end_place'] = '重庆';
        $map2['v.s_id'] = $s_id;
        $map2['v.is_del'] = 0;
        //$map2['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $map2['v.yuefen'] =  $yue;//月份
        $data2 = $voyage->getAll($map2,'v.v_id,s.reference_price,v.starting_time,v.room,v.adult_money,v.child_money');
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
            $v['yueri'] = $v['yue'].'-'.$v['ri'];
        }
        $list['data1'] = $data1;
        $list['data2'] = $data2;
        //dump($list);exit;

        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    public function richRoom()
    {
        $v_id = input('param.v_id');
        //dump($v_id);exit;
        $map['v_id'] = $v_id;
        $voyage = new VoyageModel();
        
        $info = $voyage->getOne($map,'v_id,room,adult_money,child_money');
        $num = intval($info['child_money']);
        if ($num==0) {
            
            if ($info['child_money'] == '') {
                $info['child_money']='来电咨询';
            }
        }else{
            $info['child_money']=$info['child_money'].'/人';
        }
        if($info)
        {
            return json(['code' => 1, 'data' => $info, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //确认信息
    public function richCheck()
    {
        $v_id = input('param.v_id');
        //dump($v_id);exit;
        $map['v.v_id'] = $v_id;
        $voyage = new VoyageModel();
        
        $info = $voyage->getOneVoyage($map,'v.v_id,v.s_id,v.starting_time,v.yuefen,s.r_name');
        $raw = explode('-', $info['starting_time']);//2018-04-28
        
        if ($raw['1']['0'] ==0) {
                $info['yue'] = $raw['1']['1'];
        }else{
                $info['yue'] = $raw['1'];
        }

        if ($raw['2']['0'] ==0) {
                $info['ri'] = $raw['2']['1'];
        }else{
                $info['ri'] = $raw['2'];
        }
        $info['yueri'] = $info['yue'].'-'.$info['ri'];
        $info['nian'] = $raw['0'];


        //dump($info);exit;
        $this->assign('info', $info);
        return $this->fetch();
    
    }

    //提交回显信息
    public function richSub()
    {
        $v_id = input('param.v_id');
        
        $map['v.v_id'] = $v_id;
        //dump($v_id);exit;
        $voyage = new VoyageModel();
        
        $info = $voyage->getOneVoyage($map,'v.v_id,s.r_name,v.starting_place,v.end_place,
            v.starting_time,v.room,v.adult_money,v.child_money,s.tourism_day');
        $num = intval($info['child_money']);
        if ($num==0) {
            $info['child_money']=0;
        }
        //dump($info);exit;
        $this->assign('info', $info);
        return $this->fetch();
    
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