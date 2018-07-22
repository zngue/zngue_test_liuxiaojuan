<?php

namespace app\home\model;
use think\Model;
use think\Db;

class DetailModel extends Model
{
    protected $name = 'detail';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getDetailAll($map, $Nowpage, $limits)
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
    public function insertDetail($param)
    {
        try{
            $result = $this->allowField(true)->save($param);
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
    public function editDetail($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);

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
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneDetail($map,$field='*')
    {
        return $this->field($field)->where($map)->find(); 
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
    public function delDetail($id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['id' => $id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}