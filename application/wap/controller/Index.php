<?php
namespace app\wap\controller;
use app\wap\model\ShipModel;
use app\wap\model\VoyageModel;
use app\wap\model\RichWholeModel;
use app\wap\model\OrderModel;
use app\wap\model\DetailModel;
use think\Db;

class Index extends Base
{
    public function index()
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

        
        $r_id1 = $wholes[0]['r_id'];//第一个豪华系列
        //豪华游轮
        $ship = new ShipModel();
        $map1['is_del'] = 0;
        $map1['s_type'] = 1;
        $map1['r_id'] = $r_id1;
        $field1 = 's_id,r_id,r_name,s_img,reference_price,r_wholename';
        $voyagess1 = $ship->getAllData($map1,$field1,1,1);
        //$tel = $info['tel'];
        $this->assign('voyagess1', $voyagess1);
        
        //普通游轮
        $ship = new ShipModel();
        $maps['is_del'] = 0;
        $maps['s_type'] = 2;
        $fields = 's_id,p_name,s_img,reference_price';
        $voyages = $ship->getAllData($maps,$fields,1,1);
        //$tel = $info['tel'];
        $this->assign('voyages', $voyages);
        
        //重庆到宜昌轮播初始化
        $voyage = new VoyageModel();
        $where['v.is_del'] = 0;
        $where['v.s_type'] = 1;
        $where['v.starting_place'] = '重庆';
        $where['v.end_place'] = '宜昌';
        $where['v.starting_time'] =  ['=',date("Y-m-d",strtotime("+0 day"))];//从今天
        $fields = 'v.s_id,s.r_name,s.reference_price';
        $richs = $voyage->getAll($where,$fields);
        if (!empty($richs)) {
            foreach ($richs as $k => $v) {
                $data[$k]['s_id'] = $v['s_id'];
                $data[$k]['r_name'] = $v['r_name'];
                $data[$k]['reference_price'] = $v['reference_price'];
            }
            
            $this->assign('list', $data);
        }

        

    	return $this->fetch('/index');
    }

    //切换日期获取豪华游轮
    public function indexSwitch()
    {
        $search1 = input('param.search1');
        $search2 = input('param.search2');
        $riqi = input('param.riqi');
        $voyage = new VoyageModel();
        $where['v.is_del'] = 0;
        $where['v.s_type'] = 1;
        $where['v.starting_place'] = $search1;
        $where['v.end_place'] = $search2;
        $where['v.starting_time'] =  $riqi;
        $fields = 'v.s_id,s.r_name,s.reference_price';
        $richs = $voyage->getAll($where,$fields);
        if (!empty($richs)) {
            foreach ($richs as $k => $v) {
                $data[$k]['s_id'] = $v['s_id'];
                $data[$k]['r_name'] = $v['r_name'];
                $data[$k]['reference_price'] = $v['reference_price'];
            }
        }else{
            $data = '';
        }
        return json(['code' => 1, 'data' => $data, 'msg' => '获取成功']);
    
    }

    //选择一次系列更新一条豪华游轮
    public function richOne()
    {
        $whname = input('param.whname');
        
        if ($whname== '全部') {
            $map['r_id'] = 1;//第一个豪华系列

        }else{
            $map['r_wholename'] = $whname;
        }
        $map['is_del'] = 0;
        $map['s_type'] = 1;
        $ship = new ShipModel();
        $field = 's_id,r_id,r_name,s_img,reference_price';
        $data = $ship->getAllData($map,$field,1,1);
        //dump($data);exit;
        if($data)
        {
            return json(['code' => 1, 'data' => $data, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //点击更多豪华游轮加载列表
    public function richClick()
    {
        $r_id = input('param.r_id');
        
        if ($r_id!= 0) {
            $map['r_id'] = $r_id;
        }
        $map['is_del'] = 0;
        $map['s_type'] = 1;
        
        $Nowpage = input('param.rpage')? input('param.rpage'):1;
        $limits = 10;// 获取每页条数
        $ship = new ShipModel();
        $count = $ship->getCountCom($map);// 获取总条数
        $allpage = intval(ceil($count / $limits));//计算总页面
        
        $field = 's_id,r_name,s_img,reference_price';
        $raws = $ship->getAllData($map,$field,$Nowpage, $limits);
        if (!empty($raws)) {
            if ($Nowpage==1) {
                unset($raws[0]);
            }
            /*foreach ($raws as $k => $v) {
                $data[$k]['s_id'] = $v['s_id'];
                $data[$k]['r_name'] = $v['r_name'];
                $data[$k]['s_img'] = $v['s_img'];
                $data[$k]['reference_price'] = $v['reference_price'];
            }*/
        }
        $list['data'] = $raws;
        //dump($data);exit;
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    //点击更多普通游轮加载列表
    public function commonClick()
    {
        $map['is_del'] = 0;
        $map['s_type'] = 2;
        
        $Nowpage = input('param.cpage')? input('param.cpage'):1;
        $limits = 10;// 获取每页条数
        $ship = new ShipModel();
        $count = $ship->getCountCom($map);// 获取总条数
        $allpage = intval(ceil($count / $limits));//计算总页面
        
        $field = 's_id,p_name,s_img,reference_price';
        $raws = $ship->getAllData($map,$field,$Nowpage, $limits);
        if (!empty($raws)) {
            if ($Nowpage==1) {
                unset($raws[0]);
            }
            /*foreach ($raws as $k => $v) {
                $data[$k]['s_id'] = $v['s_id'];
                $data[$k]['r_name'] = $v['r_name'];
                $data[$k]['s_img'] = $v['s_img'];
                $data[$k]['reference_price'] = $v['reference_price'];
            }*/
        }
        $list['data'] = $raws;
        //dump($data);exit;
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }


    //游轮查看航期当前所有行程
    public function shipSchedule()
    {
        $s_id = input('param.s_id');
        $voyage = new VoyageModel();
        
        $map1['v.starting_place'] = '重庆';
        $map1['v.end_place'] = '宜昌';
        $map1['v.s_id'] = $s_id;
        $map1['v.is_del'] = 0;
        $map1['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
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
        $map2['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
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

    //获取所选的行程信息
    public function shipSwitch()
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
    public function shipSub2()
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
