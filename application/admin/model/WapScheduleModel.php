<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class WapScheduleModel extends Model
{
    protected $name = 'wap_schedule';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getScheduleAll($map, $Nowpage, $limits)
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
    public function insertSchedule($param)
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
    public function editSchedule($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['ws_id' => $param['ws_id']]);

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
    public function getOneSchedule($ws_id)
    {
        return $this->where('ws_id', $ws_id)->find();
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
    public function delSchedule($ws_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['ws_id' => $ws_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}