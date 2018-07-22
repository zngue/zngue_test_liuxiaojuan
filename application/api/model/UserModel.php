<?php
namespace app\api\model;
use think\Model;
use think\Db;
/**
 * User模型
 * @author p-c
 *
 */
class UserModel extends Model
{
    protected $name = 'member';
    //protected $autoWriteTimestamp = true;//开启自动识别时间戳字段 int类型
    // 定义时间戳字段名
    //protected $createTime = 'create_time';

    /**
     * 我的超级团购 列表
     * @param array $map 条件
     * @param Int $page 当前页数
     */
    public function superLeagueList($map,$page,$limit){
        $resule =  Db::name('team_buy')->alias('t')
        ->field('t.*,g.goods_name,g.goods_title,g.goods_price')
        ->join('__GOODS__ g','g.id=t.goods_id')
        ->where($map)
        ->page($page,$limit)
        ->select();
        return $resule;
    }
    //获取单条数据
    public function getOne($map=array(),$field='*'){
        return $this->where($map)->field($field)->find();
    }
    public function getValue($map=array(),$field){
        return $this->where($map)->value($field);
    }


    //添加
    public function add($data){
        return $this->insertGetId($data);
    }
    //编辑
    public function edit($map=array(),$data){
        return $this->where($map)->update($data);
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editUser($param)
    {
        try{

            $result = $this->allowField(true)->save($param, ['m_id' => $param['m_id']]);

            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}