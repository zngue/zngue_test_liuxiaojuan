<?php

namespace app\api\model;
use think\Model;
use think\Db;

class LadingCodeModel extends Model
{
    protected $name = 'lading_code';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCodeAll($map, $Nowpage, $limits)
    {
        return $this->alias('lc')->field('lc.*,pc.product_number,pc.product_img,pc.product_remark')
                ->join('__PRODUCT_CATE__ pc', 'lc.pc_id = pc.pc_id')
                ->where($map)->page($Nowpage,$limits)->order('lc.received_time desc')->select();     
    }

    /**
     * 根据条件获取总数
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getCount($map)
    {
        return $this->alias('lc')
                ->join('__PRODUCT_CATE__ pc', 'lc.pc_id = pc.pc_id')
                ->where($map)->count();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertCode($param)
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
    public function editCode($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['lc_id' => $param['lc_id']]);

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
    public function getOneCode($map,$field='*')
    {
        return $this->alias('lc')->field($field)
                ->join('__PRODUCT_CATE__ pc', 'lc.pc_id = pc.pc_id')
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
    public function delCode($lc_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['lc_id' => $lc_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}