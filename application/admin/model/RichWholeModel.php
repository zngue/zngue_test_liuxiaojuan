<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class RichWholeModel extends Model
{
    protected $name = 'rich_whole';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getWholeAll($map, $Nowpage, $limits)
    {
        return $this->where($map)->page($Nowpage,$limits)->order('create_time asc')->select();     
    }

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAll($map,$field='*')
    {
        return $this->field($field)->where($map)->select();     
    }

    /**
     * 根据条件获取总数
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCount($map)
    {
        return $this->where($map)->count();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertWhole($param)
    {
        try{
            $result = $this->allowField(true)->save($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '添加系列成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editWhole($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['r_id' => $param['r_id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑系列成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneWhole($r_id)
    {
        return $this->where('r_id', $r_id)->find();
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
    public function delWhole($r_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['r_id' => $r_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除系列成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}