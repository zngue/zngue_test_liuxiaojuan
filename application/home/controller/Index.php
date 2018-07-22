<?php
namespace app\home\controller;
use app\home\model\ShipModel;
use app\home\model\VoyageModel;
use app\home\model\RichWholeModel;
use app\home\model\OrderModel;
use app\home\model\DetailModel;
use think\Db;

class Index extends Base
{
    public function home(){
      //return $this->fetch('home');
	  
	  $this->home();
    }
    public function index()
    {
    	//系列名称
        $whole = new RichWholeModel();
    	$map['is_del'] = 0;
    	$field = 'r_id,name';
    	$wholes = $whole->getAll($map,$field);
    	//$tel = $info['tel'];
    	$this->assign('wholes', $wholes);
        
        $r_id1 = $wholes[0]['r_id'];//第一个豪华系列
        //豪华游轮
        $ship = new ShipModel();
        $map1['is_del'] = 0;
        $map1['s_type'] = 1;
        $map1['r_id'] = $r_id1;
        $field1 = 's_id,r_name,s_img,group_plans,tourism_day,reference_price';
        $voyagess1 = $ship->getAllData($map1,$field1,1,4);
        //$tel = $info['tel'];
        $this->assign('voyagess1', $voyagess1);

        //普通游轮
        $ship = new ShipModel();
        $maps['is_del'] = 0;
        $maps['s_type'] = 2;
        $fields = 's_id,p_name,s_img,group_plans,tourism_day,reference_price';
        $voyages = $ship->getAllData($maps,$fields,1,4);
        //$tel = $info['tel'];
        $this->assign('voyages', $voyages);
        //重庆到宜昌轮播
        $weeks= config('weeks');
        $voyage = new VoyageModel();
        $where['v.is_del'] = 0;
        $where['v.s_type'] = 1;
        $where['v.starting_place'] = '重庆';
        $where['v.end_place'] = '宜昌';
        $where['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $fields = 'v.v_id,v.s_id,s.r_name,v.starting_time,s.reference_price';
        $richs = $voyage->getAll($where,$fields);
        if (!empty($richs)) {
            $n = 0;
            $mm = date("Y-m-d",strtotime("+1 day"));
            foreach ($richs as $k => $v) {
                $tt = $v['starting_time'];
                if ($tt == $mm) {
                    if ($n>3) {
                        //continue;
                        unset($richs[$k]);
                    }else{
                        $t = strtotime($v['starting_time']);
                        $data[$k]['starting_times'] = date("m-d",$t);
                        $data[$k]['week_times'] = $weeks[date("w",$t)];
                        $data[$k]['v_id'] = $v['v_id'];
                        $data[$k]['s_id'] = $v['s_id'];
                        $data[$k]['r_name'] = $v['r_name'];
                        $data[$k]['reference_price'] = $v['reference_price'];
                        $n=$n+1;
                    }
                }else{
                    $t = strtotime($v['starting_time']);
                    $data[$k]['starting_times'] = date("m-d",$t);
                    $data[$k]['week_times'] = $weeks[date("w",$t)];
                    $data[$k]['v_id'] = $v['v_id'];
                    $data[$k]['s_id'] = $v['s_id'];
                    $data[$k]['r_name'] = $v['r_name'];
                    $data[$k]['reference_price'] = $v['reference_price'];
                    $mm = $tt;
                    $n=1;
                }
            }
            $data=array_merge($data);
            $mm = date("m-d",strtotime("+1 day"));
            $a = 0;
            $b = 0;
            foreach ($data as $k => $v) {

                $tt = $v['starting_times'];
                if ($tt == $mm) {
                    $list[$a][$b] = $data[$k];
                    $b =$b+1;
                }else{
                    $a = $a+1;
                    $b = 0;
                    $list[$a][$b] = $data[$k];
                    $b =$b+1;
                    $mm = $tt;
                }
            }
            $count = count($list);
            $allpage = intval(ceil($count / 6));

            $this->assign('list', $list);
            $this->assign('allpage', $allpage);
        }

        //宜昌到重庆轮播
        $weeks= config('weeks');
        $voyage = new VoyageModel();
        $where2['v.is_del'] = 0;
        $where2['v.s_type'] = 1;
        $where2['v.starting_place'] = '宜昌';
        $where2['v.end_place'] = '重庆';
        $where2['v.starting_time'] =  ['>=',date("Y-m-d",strtotime("+1 day"))];//从明天
        $fields2 = 'v.v_id,v.s_id,s.r_name,v.starting_time,s.reference_price';
        $richs2 = $voyage->getAll($where2,$fields2);
        //dump($richs2);exit;

        if (!empty($richs2)) {
            $n = 0;
            $mm = date("Y-m-d",strtotime("+1 day"));
            foreach ($richs2 as $k => $v) {
     
                $tt = $v['starting_time'];
                if ($tt == $mm) {

                    if ($n>3) {
                        //continue;
                        unset($richs2[$k]);
                    }else{
                        $t = strtotime($v['starting_time']);
                        $data2[$k]['starting_times'] = date("m-d",$t);
                        $data2[$k]['week_times'] = $weeks[date("w",$t)];
                        $data2[$k]['v_id'] = $v['v_id'];
                        $data2[$k]['s_id'] = $v['s_id'];
                        $data2[$k]['r_name'] = $v['r_name'];
                        $data2[$k]['reference_price'] = $v['reference_price'];
                        $n=$n+1;
                    }
                }else{
                    $t = strtotime($v['starting_time']);
                    $data2[$k]['starting_times'] = date("m-d",$t);
                    $data2[$k]['week_times'] = $weeks[date("w",$t)];
                    $data2[$k]['v_id'] = $v['v_id'];
                    $data2[$k]['s_id'] = $v['s_id'];
                    $data2[$k]['r_name'] = $v['r_name'];
                    $data2[$k]['reference_price'] = $v['reference_price'];
                    $mm = $tt;
                    $n=1;
                }
            }
            $data2=array_merge($data2);
            $mm = date("m-d",strtotime("+1 day"));
            $a = 0;
            $b = 0;
            foreach ($data2 as $k => $v) {

                $tt = $v['starting_times'];
                if ($tt == $mm) {
                    $list2[$a][$b] = $data2[$k];
                    $b =$b+1;
                }else{
                    $a = $a+1;
                    $b = 0;
                    $list2[$a][$b] = $data2[$k];
                    $b =$b+1;
                    $mm = $tt;
                }
            }
            $count2 = count($list2);
            $allpage2 = intval(ceil($count2 / 6));

            $this->assign('list2', $list2);
            $this->assign('allpage2', $allpage2);
        }

    	return $this->fetch('home');
    }

    public function richQiehuan()
    {
        $r_id = input('param.r_id');
        //豪华游轮
        $ship = new ShipModel();
        $map['is_del'] = 0;
        $map['s_type'] = 1;
        $map['r_id'] = $r_id;
        $field = 's_id,r_name,s_img,group_plans,tourism_day,reference_price';
        $info = $ship->getAllData($map,$field,1,4);
        if($info)
        {
            return json(['code' => 1, 'data' => $info, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }



}
