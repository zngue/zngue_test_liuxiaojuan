<?php

namespace app\admin\controller;
use app\admin\model\ShipModel;
use app\admin\model\VoyageModel;
use app\admin\model\RichWholeModel;
use think\Db;
use think\Session;

class Voyage extends Base
{

    /**
     * [index 行程列表]
     
     */
    public function index(){
        $s_id = "";
        $key = "";
        if (input('s_id')) {
            $s_id = input('s_id');
            Session::set('s_id',$s_id);
            if($s_id&&$s_id!==""){
                $map['v.s_id'] = $s_id;          
            } 
        }else{
            $key = input('key');
            if($key&&$key!==""){
                $map['v.starting_place|s.r_name|s.p_name|v.s_id'] = ['like',"%" . $key . "%"];          
            } 
        }

        $r_id = input('r_id');
        $s_type = input('s_type');
        $starting_time = input('starting_time');
        $map['v.is_del'] = 0;
        
        if($r_id&&$r_id!==""){
            $map['s.r_id'] = $r_id;          
        } 
        if($s_type&&$s_type!==""){
            $map['v.s_type'] = $s_type;          
        }
        if($starting_time&&$starting_time!==""){
            $map['v.starting_time'] = $starting_time;          
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $voyage = new VoyageModel();
        $count = $voyage->getCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $voyage->getVoyageAll($map, $Nowpage, $limits);//分页

        $info = $voyage->getAllData($map);//不分页，所有数据
        
        if (!empty($info)) {
            $a = $info[0]['s_id'];
            foreach ($info as $kk => $vv) {
                if ($a == $vv['s_id']) {
                    $b = 1;
                }else{
                    $b = 2;
                }
            }
            if ($b == 1) {
                Session::set('s_id',$a);
            }else{
                Session::set('s_id','0');
            }

        }else{
            //dump(2222);
            //Session::set('s_id','0');
        }


        $s_types = config('s_type');
        foreach ($lists as $k => $v) {
            $lists[$k]['s_type'] = $s_types[$v['s_type']];
            $lists[$k]['r_wholename'] = $v['r_wholename'] == ''?'':$v['r_wholename'];
            $lists[$k]['r_name'] = $v['r_name'] == ''?'':$v['r_name'];
            $lists[$k]['p_model'] = $v['p_model'] == ''?'':$v['p_model'];
            $lists[$k]['p_name'] = $v['p_name'] == ''?'':$v['p_name'];
            $num = intval($v['child_money']);
            if ($num==0) {
                
                if ($v['child_money'] == '') {
                    $v['child_money']='来电咨询';
                }
            }else{
                $v['child_money']=$v['child_money'].'元';
            }
        }
        $whole = new RichWholeModel();
        $raw = $whole->getAll(array('is_del'=>'0'),'r_id,name');//所有系列
        $this->assign('raw', $raw);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        $this->assign('r_id', $r_id);
        $this->assign('s_id', $s_id);
        $this->assign('s_type', $s_type);
        $this->assign('starting_time', $starting_time);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_whole 添加行程]
     
     */
    public function addVoyage()
    {
        $voyage = new VoyageModel();
        if(request()->isAjax()){

            $param = input('post.');
            $s_type = $param['s_type'];
            //豪华
            if ($s_type==1) {
                $map['s_id'] = $param['s_id_rich'];
                $map['starting_place'] = $param['starting_place'];
                $map['end_place'] = $param['end_place'];
                $map['is_del'] = 0;
                $list = $voyage->getAll($map);
                $starting_time = $param['starting_time'];
                if (!empty($list)) {
                    foreach ($starting_time as $k => $v) {
                        foreach ($list as $key => $value) {
                            if ($v == $value['starting_time']) {
                                return json(['code' => '0', 'data' => '', 'msg' => '出游时间：'.($v).'已经添加']);
                            }
                        }
                    }
                } 
                
            }
            //普通
            if ($s_type==2) {
                $maps['s_id'] = $param['s_id_common'];
                $maps['starting_place'] = $param['starting_place'];
                $maps['end_place'] = $param['end_place'];
                $maps['is_del'] = 0;
                $lists = $voyage->getAll($maps);
                $starting_times = $param['starting_time'];
                if (!empty($lists)) {
                    foreach ($starting_times as $k => $v) {
                        foreach ($lists as $key => $value) {
                            if ($v == $value['starting_time']) {
                                return json(['code' => '0', 'data' => '', 'msg' => '出游时间：'.($v).'已经添加']);
                            }
                        }
                    }
                } 
                
            }
            
            if ($s_type==1) {
                $s_id = $param['s_id_rich'];
                $starting_place = $param['starting_place'];
                $end_place = $param['end_place'];
                $s_type = $param['s_type'];
                //特殊日期验证
                $flag = false;
                if (isset($param['Special_starting_time'])){
                    foreach ($param['Special_starting_time'] as $key=>$value){
                        if (empty($value)){
                            return json(['code' => '0', 'data' => '', 'msg' => '第'.($key + 1).'个特殊日期为空']);
                        }
                    }
                    $flag = true;
                }

                $data = [];
                foreach ($param as $kk=>$vv){
                    $tmp = [
                        's_id'=>$s_id,
                        's_type'=> $s_type,
                        'create_time'=> time(),
                        'starting_place'=> $starting_place,
                        'end_place'=> $end_place,
                    ];
                    foreach ($param as $key => $value){
                        if ($key == 'starting_time'){
                            foreach ($value as $k=>$v){
                                $tmp['starting_time'] = $value[$k];
                                $tmp['yuefen'] = date("m",strtotime($value[$k]));
                                unset($param['starting_time'][$k]);
                                break;
                            }
                        }
                        if ($key == 'room'){
                            foreach ($value as $k=>$v){
                                $tmp['room'] = $value[$k];
                                unset($param['room'][$k]);
                                break;
                            }
                        }
                        if ($key == 'adult'){
                            foreach ($value as $k=>$v){
                                $tmp['adult_money'] = $value[$k];
                                unset($param['adult'][$k]);
                                break;
                            }
                        }
                        if ($key == 'child'){
                            foreach ($value as $k=>$v){
                                $tmp['child_money'] = $value[$k];
                                unset($param['child'][$k]);
                                break;
                            }
                        }
                    }
                    if (count($tmp) > 5){
                        array_push($data,$tmp);
                    }
                }
				
				 $flag = $voyage->insertAllVoyage($data);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);

                //循环周期，在本次基础上循环6次，n*6
                $tmp_data = $data;
                for ($i = 0; $i < 6 ;$i++){
                    $t_d = [];
                    foreach ($tmp_data as $dk=>$dv){
                        $d_date = date('Y-m-d',strtotime('+7 day',strtotime($dv['starting_time'])));
                        $d_tmp = [
                            's_id'=>$s_id,
                            's_type'=>$s_type,
                            'create_time'=>time(),
                            'starting_place'=> $dv['starting_place'],
                            'end_place'=> $dv['end_place'],
                            'starting_time'=>$d_date,
                            'yuefen' => date("m",strtotime($d_date)),
                            'room' => $dv['room'],
                            'adult_money' => $dv['adult_money'],
                            'child_money' => $dv['child_money'],
                        ];
                        array_push($t_d,$d_tmp);
                        array_push($data,$d_tmp);
                    }
                    $tmp_data = $t_d;
                }

                //特殊日期
                if ($flag){
                    $Special_data = [];
                    foreach ($param['Special_starting_time'] as $sk=>$sv){
                        $s_tmp = [
                            's_id'=>$s_id,
                            's_type'=> $s_type,
                            'create_time'=> time(),
                            'starting_place'=> $starting_place,
                            'end_place'=> $end_place,
                            'starting_time'=>$sv,
                            'yuefen'=>date("m",strtotime($sv)),
                        ];
                        foreach ($param['Special_room'] as $srk=>$srv){
                            $s_tmp['room'] = $srv;
                            unset($param['Special_room'][$srk]);
                            break;
                        }
                        foreach ($param['Special_adult'] as $srk=>$srv){
                            $s_tmp['adult_money'] = $srv;
                            unset($param['Special_adult'][$srk]);
                            break;
                        }
                        foreach ($param['Special_child'] as $srk=>$srv){
                            $s_tmp['child_money'] = $srv;
                            unset($param['Special_child'][$srk]);
                            break;
                        }
                        array_push($Special_data,$s_tmp);
                        unset($param['Special_starting_time'][$sk]);
                    }

                    //替换为特殊日期
                    foreach ($data as $k=>$v){
                        foreach ($Special_data as $kk=>$vv){
                            if ($v['starting_time'] == $vv['starting_time']){
                                $data[$k] = $vv;
                                unset($Special_data[$kk]);
                            }

                        }
                    }
                    if (!empty($Special_data)){
                        $data = array_merge($data,$Special_data);
                    }
                }


                $flag = $voyage->insertAllVoyage($data);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }else{
                $param['s_id'] = $param['s_id_common'];
                foreach ($param['starting_time'] as $kkkk => $vvvv) {
                    $content[$kkkk]['starting_time'] = $vvvv;

                }
                foreach ($param['room'] as $kk => $vv) {
                    $content[$kk]['room'] = $vv;

                }
                foreach ($param['adult'] as $k => $v) {
                    $content[$k]['adult'] = $v;

                }
                foreach ($param['child'] as $kkk => $vvv) {
                    $content[$kkk]['child'] = $vvv;

                }
                unset($param['file']);
                unset($param['s_id_rich']);
                unset($param['s_id_common']);
                unset($param['room']);
                unset($param['adult']);
                unset($param['child']);
                unset($param['starting_time']);
                $param['create_time'] = time();
                foreach ($content as $key => $val) {

                    $tt = strtotime($val['starting_time']);
                    $param['yuefen'] = date("m",$tt);
                    $param['starting_time'] = $val['starting_time'];
                    unset($val['starting_time']);
                    $param['room'] = $val['room'];
                    $param['adult_money'] = $val['adult'];
                    $param['child_money'] = $val['child'];
                    $flag = $voyage->insertVoyage($param);
                    unset($param['starting_time']);
                    unset($param['yuefen']);
                    unset($param['room']);
                    unset($param['adult_money']);
                    unset($param['child_money']);
                    Session::set('s_id','0');
                }
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
                

            }
        }
        $a = Session::get('s_id');
        if ($a != 0) {
            $ship = new ShipModel();
            $ma['s_id'] = $a;
            $raws = $ship->getOne($ma);
            $this->assign('raws', $raws);
        }else{
            $raws['s_id'] = 0;
            $raws['s_type'] = 0;
            $this->assign('raws', $raws);
        }

        $s_type = config('s_type');
        $this->assign('s_type', $s_type);
        return $this->fetch();
    }

    //添加价格-初始化
    public function voDe()
    {
        $s_id = input('param.s_id');
        $ship = new ShipModel();
        $map['s_id'] = $s_id;
        $raw = $ship->getOne($map);
        if($raw)
        {
            return json(['code' => 1, 'data' => $raw, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    /**
     * [user_state 豪华游轮系列名称]
     
     */
    public function ridRaw()
    {
        
        $raw = Db::name('rich_whole')->field('r_id,name')->where(array('is_del'=>'0'))->select();
        if($raw)
        {
            return json(['code' => 1, 'data' => $raw, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    /**
     * [user_state 豪华游轮名称]
     
     */
    public function rnameRaws()
    {
        $r_id = input('param.id');
        $raws = Db::name('ship')->field('s_id,r_name')->where(array('r_id'=>$r_id,'is_del'=>'0'))->select();
        if($raws)
        {
            return json(['code' => 1, 'data' => $raws, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    /**
     * [user_state 普通游轮型号]
     
     */
    public function pnameList()
    {
       
        $list = Db::name('ship')->field('s_id,p_name')->where(array('s_type'=>'2','is_del'=>'0'))->select();
        if($list)
        {
            return json(['code' => 1, 'data' => $list, 'msg' => '获取成功']);
        }
        else
        {
            return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
        }
    
    }

    /**
     * [detail_voyage 查看]
     * @return [type] [description]
     
     */
    public function detailVoyage()
    {
        $voyage = new VoyageModel();
        $v_id = input('param.v_id');
        $field = 'v.*,s.p_name,s.p_model,s.s_img,s.r_wholename,s.r_name,s.reference_price,
                    s.tourism_day,s.group_plans,s.transportation';
        $lists = $voyage->getOneVoyage(array('v.v_id'=>$v_id),$field);
        $s_types = config('s_type');
        $lists['s_type'] = $s_types[$lists['s_type']];
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * [edit_voyage 编辑基本信息]
     * @return [type] [description]
     */
    public function editVoyage()
    {
        $voyage = new VoyageModel();
        
        if(request()->isAjax()){

            $param = input('post.');
            $s_type = $param['s_type'];
            if ($s_type==1) {
                $param['s_id'] = $param['s_id_rich'];

            }else{
                $param['s_id'] = $param['s_id_common'];
            }

            unset($param['file']);
            unset($param['s_id_rich']);
            unset($param['s_id_common']);
            $flag = $voyage->editOne($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $v_id = input('param.v_id');
        $field = 'v.*,s.r_id,s.p_name,s.r_wholename,s.r_name';
        $info = $voyage->getOneVoyage(array('v.v_id'=>$v_id),$field);
        
        //系列名称
        $a = Db::name('rich_whole')->field('r_id,name')->where(array('is_del'=>'0'))->select();
        //豪华游轮名称
        $r_id = $info['r_id'];
        $b = Db::name('ship')->field('s_id,r_name')->where(array('r_id'=>$r_id,'is_del'=>'0'))->select();
        //普通游轮型号
        $c = Db::name('ship')->field('s_id,p_name')->where(array('s_type'=>'2','is_del'=>'0'))->select();

        $this->assign('a',$a);
        $this->assign('b',$b);
        $this->assign('c',$c);

        $this->assign('info',$info);
        return $this->fetch();
    }

    

    /**
     * [del_voyage 删除]
     * @return [type] [description]
     
     */
    public function delVoyage()
    {
        $v_id = input('param.id');
        $voyage = new VoyageModel();
        $flag = $voyage->delVoyage($v_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }
 

}