<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class MemberModel extends Model
{
    protected $name = 'member';  
    //protected $autoWriteTimestamp = true;   // 开启自动写入时间戳

    /**
     * 根据搜索条件获取用户列表信息
     */
    public function getMemberByWhere($map, $Nowpage, $limits)
    {
        return $this->where($map)->page($Nowpage,$limits)->order('create_time desc')->select();
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getAllCount($map)
    {
        return $this->where($map)->count();
    }


    /**
     * 插入信息
     */
    public function insertMember($param)
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
    public function editMember($param)
    {
        try{
            $result =  $this->allowField(true)->save($param, ['m_id' => $param['m_id']]);
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
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneMember($m_id)
    {
        return $this->where('m_id', $m_id)->find();
    }


    /**
     * 删除管理员
     * @param $id
     */
    public function delUser($id)
    {
        try{

            $this->where('id', $id)->delete();
            Db::name('auth_group_access')->where('uid', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];

        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    public function delMember($m_id)
    {
        try{
            $map['is_del']=1;
            $this->save($map, ['m_id' => $m_id]);
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}