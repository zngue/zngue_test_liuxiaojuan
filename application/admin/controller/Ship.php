<?php

namespace app\admin\controller;
use app\admin\model\RichWholeModel;
use app\admin\model\ShipModel;
use app\admin\model\WapScheduleModel;
use think\Db;

class Ship extends Base
{

    /**
     * [index 豪华游轮系列]
     
     */
    public function wholeIndex(){
        $key = input('key');
        $map['is_del'] = 0;
        if($key&&$key!==""){
            $map['r_id|name'] = ['like',"%" . $key . "%"];          
        } 
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $whole = new RichWholeModel();
        $count = $whole->getCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $whole->getWholeAll($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_whole 添加系列]
     
     */
    public function addWhole()
    {
        if(request()->isAjax()){

            $param = input('post.');
            $whole = new RichWholeModel();
            $param['create_time'] = time();  
            $flag = $whole->insertWhole($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }

    /**
     * [edit_whole 编辑]
     * @return [type] [description]
     */
    public function editWhole()
    {
        $whole = new RichWholeModel();
        
        if(request()->isAjax()){

            $param = input('post.');      
            $flag = $whole->editWhole($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $map['r_id'] = input('param.r_id');
        $this->assign('whole',$whole->getOne($map));
        return $this->fetch();
    }

    /**
     * [del_whole 删除]
     * @return [type] [description]
     
     */
    public function delWhole()
    {
        $r_id = input('param.id');
        $whole = new RichWholeModel();
        $flag = $whole->delWhole($r_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

 
    //////////////////////////////////////////////////豪华游轮列表///////////////////////////////////////////////////

    /**
     * [rich_index 豪华游轮列表]
     
     */
    public function richIndex(){
        $key = input('key');
        $r_id = input('r_id');
        $map['s.is_del'] = 0;
        $map['s.s_type'] = 1;
        if($key&&$key!==""){
            $map['s.s_id|s.r_name'] = ['like',"%" . $key . "%"];          
        } 
        if($r_id&&$r_id!==""){
            $map['rw.r_id'] = $r_id;          
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $ship = db('ship');
		$count=$ship->alias('s')->where($map)->count();
       // $count = $ship->getCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
		
			
		 $lists=$ship->alias('s')->field('s.s_id,rw.name,s.r_name,s.s_img')
               ->join('__RICH_WHOLE__ rw', 's.r_id = rw.r_id')
                ->where($map)->page($Nowpage,$limits)->order('s.create_time desc')->select(); 
				
				
		//print_r($lists);die;
        //$lists = $ship->getShipAll($map, $Nowpage, $limits);
       
        
        $whole = new RichWholeModel();
        $raw = $whole->getAll(array('is_del'=>'0'),'r_id,name');//所有系列
        
        $this->assign('raw', $raw);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        $this->assign('r_id', $r_id);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_rich 添加豪华游轮]
     * @return [type] [description]
     */
    public function addRich()
    {
        $ship = new ShipModel();
        
        if(request()->isAjax()){

            $param = input('post.');
            $whole = explode('/', $param['whole']);
            $param['r_id'] = $whole['0'];
            $param['r_wholename'] = $whole['1'];
            unset($param['whole']);
            unset($param['file']);
            $param['create_time'] = time();
            $flag = $ship->insertShip($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $whole = new RichWholeModel();
        $raw = $whole->getAll(array('is_del'=>'0'),'r_id,name');//所有系列
        $this->assign('raw',$raw);
        return $this->fetch();
    }

    /**
     * [edit_rich 编辑]
     * @return [type] [description]
     */
    public function editRich()
    {
        $n = input('param.n');
        if (request()->isAjax()) {
            $n = input('post.n');
        }
        $map['s_id'] = input('param.s_id');
        $ship = new ShipModel();
        //基本信息
        if ($n==1) {
            
            if(request()->isAjax()){
                $param = input('post.');
                unset($param['file']);
                unset($param['n']);
                $flag = $ship->editOne($param);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }

            $info = $ship->getOne($map);
            $this->assign('info',$info);
            $this->assign('n',$n);
            return $this->fetch();

        }else{

            if(request()->isAjax()){

                $param = input('post.');
                unset($param['n']);
                $flag = $ship->editOne($param);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }

            $info = $ship->getOne($map);
            $this->assign('info',$info);
            $this->assign('n',$n);
            return $this->fetch();

        }

    }

    /**
     * [schedul_voyage 电脑行程安排]
     * @return [type] [description]
     */
    public function schedulVoyage()
    {
        $ship = new ShipModel();
        
        if(request()->isAjax()){

            $param = input('post.');      
            $flag = $ship->editOne($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $map['s_id'] = input('param.s_id');
        $info = $ship->getOne($map);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [schedul_voyage 手机行程安排]
     * @return [type] [description]
     */
    public function wapScheduls()
    {
        $ship = new ShipModel();
        if(request()->isAjax()){
            $param = input('post.');
            $all1 = [];
            $index1 = -1;
            for($i=0;$i<=$param['come_day_num'];$i++){
                if(!isset($param['the_day_'.$i])){
                    continue;
                }
                $index1++;
                $arr = [];
                $arr['the_day'] = $param['the_day_'.$i];
                $arr['visit_name'] = $param['visit_name_'.$i];
                $arr['visit_remarks'] = $param['visit_remarks_'.$i];
                $arr['day_img'] = $param['day_img_'.$i];
                $num = $param['come_day_num_sec_'.$i];

                $arr2 = [];
                $inedx2 = -1;
                for($j=0;$j<=$num;$j++){
                    if(!isset($param['clock_'.$i.'_'.$j])){
                        continue;
                    }
                    $inedx2++;
                    $time = [];
                    $time['clock'] = $param['clock_'.$i.'_'.$j];
                    $time['sche'] = $param['sche_'.$i.'_'.$j];
                    $arr2[$inedx2] = $time;
                }
                $arr['time'] = $arr2;
                $all1[$index1] = $arr;
            }
            $all2 = [];
            $index3 = -1;
            for($i=0;$i<=$param['back_day_num'];$i++){
                if(!isset($param['the_day2_'.$i])){
                    continue;
                }
                $index3++;
                $arr = [];
                $arr['the_day'] = $param['the_day2_'.$i];
                $arr['visit_name'] = $param['visit_name2_'.$i];
                $arr['visit_remarks'] = $param['visit_remarks2_'.$i];
                $arr['day_img'] = $param['day_img2_'.$i];
                $num = $param['back_day_num_sec_'.$i];

                $arr2 = [];
                $inedx2 = -1;
                for($j=0;$j<=$num;$j++){
                    if(!isset($param['clock2_'.$i.'_'.$j])){
                        continue;
                    }
                    $inedx2++;
                    $time = [];
                    $time['clock'] = $param['clock2_'.$i.'_'.$j];
                    $time['sche'] = $param['sche2_'.$i.'_'.$j];
                    $arr2[$inedx2] = $time;
                }
                $arr['time'] = $arr2;
                $all2[$index3] = $arr;
            }

            $data['s_id'] = $param['s_id'];
            if(!empty($all1[0]['the_day'])){
                $data['scheduling_wap1'] = serialize($all1);
            }
            if(!empty($all2[0]['the_day'])){
                $data['scheduling_wap2'] = serialize($all2);
            }
            $flag = $ship->editOne($data);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $map['s_id'] = input('param.s_id');
        $info = $ship->getOne($map);
       
        $info['scheduling_wap1'] = empty($info['scheduling_wap1'])?'':unserialize($info['scheduling_wap1']);
        $info['scheduling_wap2'] = empty($info['scheduling_wap2'])?'':unserialize($info['scheduling_wap2']);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [del_rich 删除]
     * @return [type] [description]
     
     */
    public function delRich()
    {
        $s_id = input('param.id');
        $ship = new ShipModel();
        $flag = $ship->delShip($s_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    //////////////////////////////////////////////////普通游轮列表///////////////////////////////////////////////////

    /**
     * [common_index 普通游轮列表]
     
     */
    public function commonIndex(){
        $key = input('key');
        $map['is_del'] = 0;
        $map['s_type'] = 2;
        if($key&&$key!==""){
            $map['p_name|p_model'] = ['like',"%" . $key . "%"];          
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $ship = new ShipModel();
        $count = $ship->getCountCom($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $ship->getAllData($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count); 
        $this->assign('val', $key);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * [add_common 添加普通游轮]
     * @return [type] [description]
     */
    public function addCommon()
    {
        $ship = new ShipModel();
        
        if(request()->isAjax()){

            $param = input('post.');
            unset($param['file']);
            $param['create_time'] = time();
            $flag = $ship->insertShip($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }

    /**
     * [edit_common 编辑]
     * @return [type] [description]
     */
    public function editCommon()
    {
        $n = input('param.n');
        if (request()->isAjax()) {
            $n = input('post.n');
        }
        $map['s_id'] = input('param.s_id');
        $ship = new ShipModel();
        //基本信息
        if ($n==1) {
            
            if(request()->isAjax()){
                $param = input('post.');
                unset($param['file']);
                unset($param['n']);
                $flag = $ship->editOne($param);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }

            $info = $ship->getOne($map);
            $this->assign('info',$info);
            $this->assign('n',$n);
            return $this->fetch();

        }else{

            if(request()->isAjax()){

                $param = input('post.');
                unset($param['n']);
                $flag = $ship->editOne($param);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }

            $info = $ship->getOne($map);
            $this->assign('info',$info);
            $this->assign('n',$n);
            return $this->fetch();

        }

    }

    /**
     * [schedul_voyage 电脑行程安排]
     * @return [type] [description]
     */
    public function schedulVoyages()
    {
        $ship = new ShipModel();
        
        if(request()->isAjax()){

            $param = input('post.');      
            $flag = $ship->editOne($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $map['s_id'] = input('param.s_id');
        $info = $ship->getOne($map);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [schedul_voyage 手机行程安排]
     * @return [type] [description]
     */
    public function wapSchedul()
    {
        $ship = new ShipModel();
        if(request()->isAjax()){
            $param = input('post.');
            $all1 = [];
            $index1 = -1;
            for($i=0;$i<=$param['come_day_num'];$i++){
                if(!isset($param['the_day_'.$i])){
                    continue;
                }
                $index1++;
                $arr = [];
                $arr['the_day'] = $param['the_day_'.$i];
                $arr['visit_name'] = $param['visit_name_'.$i];
                $arr['visit_remarks'] = $param['visit_remarks_'.$i];
                $arr['day_img'] = $param['day_img_'.$i];
                $num = $param['come_day_num_sec_'.$i];

                $arr2 = [];
                $inedx2 = -1;
                for($j=0;$j<=$num;$j++){
                    if(!isset($param['clock_'.$i.'_'.$j])){
                        continue;
                    }
                    $inedx2++;
                    $time = [];
                    $time['clock'] = $param['clock_'.$i.'_'.$j];
                    $time['sche'] = $param['sche_'.$i.'_'.$j];
                    $arr2[$inedx2] = $time;
                }
                $arr['time'] = $arr2;
                $all1[$index1] = $arr;
            }
            $all2 = [];
            $index3 = -1;
            for($i=0;$i<=$param['back_day_num'];$i++){
                if(!isset($param['the_day2_'.$i])){
                    continue;
                }
                $index3++;
                $arr = [];
                $arr['the_day'] = $param['the_day2_'.$i];
                $arr['visit_name'] = $param['visit_name2_'.$i];
                $arr['visit_remarks'] = $param['visit_remarks2_'.$i];
                $arr['day_img'] = $param['day_img2_'.$i];
                $num = $param['back_day_num_sec_'.$i];

                $arr2 = [];
                $inedx2 = -1;
                for($j=0;$j<=$num;$j++){
                    if(!isset($param['clock2_'.$i.'_'.$j])){
                        continue;
                    }
                    $inedx2++;
                    $time = [];
                    $time['clock'] = $param['clock2_'.$i.'_'.$j];
                    $time['sche'] = $param['sche2_'.$i.'_'.$j];
                    $arr2[$inedx2] = $time;
                }
                $arr['time'] = $arr2;
                $all2[$index3] = $arr;
            }

            $data['s_id'] = $param['s_id'];
            if(!empty($all1[0]['the_day'])){
                $data['scheduling_wap1'] = serialize($all1);
            }
            if(!empty($all2[0]['the_day'])){
                $data['scheduling_wap2'] = serialize($all2);
            }
            $flag = $ship->editOne($data);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $map['s_id'] = input('param.s_id');
        $info = $ship->getOne($map);
       
        $info['scheduling_wap1'] = empty($info['scheduling_wap1'])?'':unserialize($info['scheduling_wap1']);
        $info['scheduling_wap2'] = empty($info['scheduling_wap2'])?'':unserialize($info['scheduling_wap2']);
        $this->assign('info',$info);
        return $this->fetch();
    }

   

    

    /**
     * [del_common 删除]
     * @return [type] [description]
     
     */
    public function delCommon()
    {
        $s_id = input('param.id');
        $ship = new ShipModel();
        $flag = $ship->delShip($s_id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

   

}