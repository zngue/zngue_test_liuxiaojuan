<?php

namespace app\wap\model;
use think\Model;
use think\Db;

class VoyageModel extends Model
{
    protected $name = 'voyage';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getVoyageAll($map, $Nowpage, $limits)
    {
        return $this->alias('v')->field('v.*,s.*')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->page($Nowpage,$limits)->order('v.create_time desc')->select();     
    }

    /**
     * 根据条件获取列表信息,不分页
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAllData($map, $field, $Nowpage, $limits)
    {
        return $this->alias('v')->field($field)
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->page($Nowpage,$limits)->order('v.create_time desc')->select(); 
    }

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAll($map, $field)
    {
        return $this->alias('v')->field($field)
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->order('v.starting_time asc')->order('v.create_time desc')->select();
                //echo $this->alias('v')->getLastSql();
    }

    /**
     * 根据条件获取总数
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCount($map)
    {
        return $this->alias('v')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->count();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertVoyage($param)
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

            $result = $this->allowField(true)->save($param, ['v_id' => $param['v_id']]);

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
    public function editVoyage($map,$param)
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
    public function getOneVoyage($map,$field='*')
    {
        return $this->alias('v')->field($field)
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->find(); 
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOne($map,$field='*')
    {
        return $this->field($field)->where($map)->find();
    }


    /**
     * 删除信息
     * @param $id
     */
    public function delVoyage($v_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['v_id' => $v_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}