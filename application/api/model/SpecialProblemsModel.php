<?php

namespace app\api\model;
use think\Model;
use think\Db;

class SpecialProblemsModel extends Model
{
    protected $name = 'special_problems';
    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getSpecialAll($map, $Nowpage, $limits)
    {
        return $this->alias('sp')->field('sp.*,pc.product_img,pc.product_remark')
                ->join('__PRODUCT_CATE__ pc', 'sp.product_number = pc.product_number')
                ->where($map)->page($Nowpage,$limits)->order('sp.create_time desc')->select();    
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
    public function insertSpecial($param)
    {
        try{
            $result = $this->allowField(true)->insert($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '操作成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editSpecial($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['sp_id' => $param['sp_id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '操作成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneSpecial($map)
    {
        return $this->where($map)->find();
    }


    /**
     * 删除信息
     * @param $id
     */
    public function delSpecial($sp_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['sp_id' => $sp_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除特殊订单成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}