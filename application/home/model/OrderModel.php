<?php

namespace app\home\model;
use think\Model;
use think\Db;

class OrderModel extends Model
{
    protected $name = 'order';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getOrderAll($map, $Nowpage, $limits)
    {
        return $this->alias('o')->field('o.*,v.*,s.*')
        		->join('__VOYAGE__ v', 'o.v_id = v.v_id')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->page($Nowpage,$limits)->order('o.create_times desc')->select();     
    }

    /**
     * 根据条件获取列表信息,不分页
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAllData($map)
    {
        return $this->alias('o')->field('o.*,v.*,s.*')
                ->join('__VOYAGE__ v', 'o.v_id = v.v_id')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->order('o.create_times desc')->select(); 
    }

    /**
     * 根据条件获取总数
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCount($map)
    {
        return $this->alias('o')
                ->join('__VOYAGE__ v', 'o.v_id = v.v_id')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
                ->where($map)->count();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertOrder($param)
    {
        try{
            $result = $this->allowField(true)->insertGetId($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '提交成功'];
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

            $result = $this->allowField(true)->save($param, ['o_id' => $param['o_id']]);

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
    public function editOrder($map,$param)
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
    public function getOneOrder($map,$field='*')
    {
        return $this->alias('o')->field('o.*,v.*,s.*')
                ->join('__VOYAGE__ v', 'o.v_id = v.v_id')
                ->join('__SHIP__ s', 'v.s_id = s.s_id')
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
    public function delOrder($o_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['o_id' => $o_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}