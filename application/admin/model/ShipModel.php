<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class ShipModel extends Model
{
    protected $name = 'ship';

    /**
     * 根据条件获取列表信息(豪华游轮列表)
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getShipAll($map, $Nowpage, $limits)
    {
        return $this->alias('s')->field('s.*,rw.name')
                ->join('__RICH_WHOLE__ rw', 's.r_id = rw.r_id')
                ->where($map)->page($Nowpage,$limits)->order('s.create_time desc')->select();     
    }

    /**
     * 根据条件获取列表信息(普通游轮列表)
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAllData($map, $Nowpage, $limits)
    {
        return $this->alias('s')->where($map)->field('s.s_id,s.p_name,s.p_model,s.r_name,s.s_img')->page($Nowpage,$limits)->order('create_time desc')->select(); 
    }

    /**
     * 根据条件获取总数(豪华游轮总数)
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCount($map)
    {
        return $this->alias('s')
                ->join('__RICH_WHOLE__ rw', 's.r_id = rw.r_id')
                ->where($map)->count();     
    }

    /**
     * 根据条件获取总数(普通游轮总数)
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCountCom($map)
    {
        return $this->where($map)->count();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertShip($param)
    {
        try{
            $result = $this->allowField(true)->insertGetId($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '添加成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editOne($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['s_id' => $param['s_id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editOnes($map,$param)
    {
        try{

            $result = $this->allowField(true)->where($map)->update($param);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 修改信息
     * @param $id
     */
    public function editShip($map,$param)
    {
        $result = $this->where($map)->update($param);
        if ($result) {
            return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
        }else{
            return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
        }
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneShip($map,$field='*')
    {
        return $this->alias('s')->field($field)
                ->join('__RICH_WHOLE__ rw', 's.r_id = rw.r_id')
                ->where($map)->find(); 
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOne($map)
    {
        return $this->where($map)->find();
    }


    /**
     * 删除信息
     * @param $id
     */
    public function delShip($s_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['s_id' => $s_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}