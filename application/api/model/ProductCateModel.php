<?php

namespace app\api\model;
use think\Model;
use think\Db;

class ProductCateModel extends Model
{
    protected $name = 'product_cate';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCateAll($map, $Nowpage, $limits)
    {
        return $this->where($map)->page($Nowpage,$limits)->order('create_time desc')->select();     
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
     * 插入信息
     * @param $param
     */
    public function insertCate($param)
    {
        try{
            $result = $this->allowField(true)->save($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '添加产品类成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editCate($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['pc_id' => $param['pc_id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑产品类成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneCate($pc_id)
    {
        return $this->where('pc_id', $pc_id)->find();
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
    public function delCate($pc_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['pc_id' => $pc_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除产品类成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}