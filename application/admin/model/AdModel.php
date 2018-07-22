<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class AdModel extends Model
{
    protected $name = 'ad';

    /**
     * 根据条件获取列表信息
     * @param $where
     * @param $Nowpage
     * @param $limits
     */
    public function getAdAll($map, $Nowpage, $limits)
    {
        return $this->alias('a')->field('a.*,name')->join('__AD_POSITION__ ap', 'a.ad_position_id = ap.id')->where($map)->page($Nowpage,$limits)->order('orderby desc')->select();     
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertAd($param)
    {
        try{
            $result = $this->validate('AdValidate')->allowField(true)->save($param);
            if(false === $result){       
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '添加广告成功'];
            }
        }catch( PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editAd($param)
    {
        try{

            $result = $this->validate('AdValidate')->allowField(true)->save($param, ['id' => $param['id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑广告成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取一条信息
     * @param $id
     */
    public function getOneAd($id)
    {
        return $this->where('id', $id)->find();
    }


    /**
     * 删除信息
     * @param $id
     */
    public function delAd($id)
    {
        try{
            $map['closed']=1;
            $this->save($map, ['id' => $id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除广告成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}