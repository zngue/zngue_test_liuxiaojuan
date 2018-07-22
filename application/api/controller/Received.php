<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\ProductCateModel;
use app\api\model\LadingCodeModel;
use app\api\model\SpecialProblemsModel;
use app\api\model\KuaiDiModel;
use app\api\model\UserModel;
use think\Db;
use think\Config;
use think\Request;

/**
 * Class Received
 * @package app\api\controller
 *      礼品领取 控制器
 */
class Received extends Base
{
    // 属性
    protected $pcate;
    protected $lcode;
    protected $sproblem;
    protected $kd;

    // 初始化构造方法，实例化模型
    public function __construct()
    {
        parent::__construct();
        $this->pcate = new ProductCateModel();//产品类表
        $this->lcode = new LadingCodeModel();//提货码表
        $this->sproblem = new SpecialProblemsModel();//特殊订单表
        $this->user_model = new UserModel();//用户表
        $this->kd = new KuaiDiModel();//快递
    }

    /**
     * 获取客服电话
     * @return   {json}
    */
    public function getPhone()
    {
        if(request()->isPost()){
            $phone = '4000031218';
            if($phone){
                returnJson('100','请求成功',$phone);
            }else{
                returnJson('103','暂无相关数据', $phone);
            }
        }else{
            returnJson('104','请求不合法','');
        }
    }

    /**
     * 获取消费者须知
     * @return   {json}
    */
    public function getNotice()
    {
        if(request()->isPost()){
            $arr['title'] = '一、消费者须知';
            $arr['notice'][0] = '1、不断提高您的消费体验度是我们平台的使命。有任何问题请拨打4000031218
';
            if($arr){
                returnJson('100','请求成功',$arr);
            }else{
                returnJson('103','暂无相关数据', $arr);
            }
        }else{
            returnJson('104','请求不合法','');
        }
    }


    /**
     * 获取产品种类API
     * @return   {json}
    */
    public function getProductType()
    {
        if(request()->isPost()){
            $map['is_del'] = 0;   //未删除
            $product_typeList = $this->pcate->getAll($map, $field='pc_id,product_number');
            $arr = ['0' => '请选择礼品种类'];
            
            foreach ($product_typeList as $k => $v)
            {
                foreach ($arr as $kk => $vv)
                {
                    $arr[$product_typeList[$k]['pc_id']] = $product_typeList[$k]['product_number'];
                }
            }
            if($arr){
                returnJson('100','请求成功',$arr);
            }else{
                returnJson('103','暂无相关数据', $arr);
            }
        }else{
            returnJson('104','请求不合法','');
        }
    }




    /**
     *     判断领取码是否正确 (链接参数：pc_id, lc_id )
     */
    public function checkCode()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'pc_id' => 'require',
                'lading_code' => 'require',
                'lading_passwd' => 'require',
            ];
            // 提示信息
            $msg = [
                'pc_id.require' => '产品种类不能为空',
                'lading_code.require' => '领取码不能为空',
                'lading_passwd.require' => '领取密码不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);
            // 接收参数
            $pc_id = $this->parm['pc_id'];
            $lading_code = $this->parm['lading_code'];
            $lading_passwd = $this->parm['lading_passwd'];
            // 查询条件
            $map['lc.pc_id'] = $pc_id;
            $map['lc.lading_code'] = $lading_code;
            //$map['lc.lading_passwd'] = $lading_passwd;
            $field = 'lc.*,pc.product_number';
            // 调用模型获取数据
            $data = $this->lcode->getOneCode($map,$field);
            if (!$data) {
                $raw['lid'] = 1;
                returnJson('103','领取码有误，请重新输入！',$raw);
            }
            if ($data['lading_passwd'] != $lading_passwd ) {
                $raw['lid'] = 2;
                returnJson('103','密码有误，请重新输入！',$raw);
            }
            if ($data['is_received'] == 2) {
                $raw['lid'] = 3;
                returnJson('103','领取码已领，请勿重复领取！',$raw);
            }
            // 返回
            returnJson('100','请求成功',$data);
        } else {

            returnJson('104', '请求不合法', '');

        }
    }


    /**
     *     提交礼品收货信息 (链接参数：pc_id, lc_id )
     */
    public function addReceipt()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'lc_id' => 'require',
                'consignee_name' => 'require',
                'consignee_phone' => 'require',
                'consignee_adress' => 'require',
            ];
            // 提示信息
            $msg = [
                'lc_id.require' => '提货码ID不能为空',
                'consignee_name.require' => '提货人姓名不能为空',
                'consignee_phone.require' => '提货人电话不能为空',
                'consignee_adress.require' => '提货人地址不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);

            // 接收参数
            $arr['lc_id'] = $this->parm['lc_id'];
            $arr['consignee_name'] = $this->parm['consignee_name'];
            $arr['consignee_phone'] = $this->parm['consignee_phone'];
            $arr['consignee_adress'] = $this->parm['consignee_adress'];
            $arr['region_0'] = $this->parm['region_0'];
            $arr['region_1'] = $this->parm['region_1'];
            $arr['region_2'] = $this->parm['region_2'];
            $arr['region_adress'] = $this->parm['region_adress'];
            $arr['update_time'] = time();
            
            // 调用模型获取数据
            $re = $this->lcode->editCode($arr);
            //unset($arr['lc_id']);
            //$res = $this->user_model->editUser($arr);
            // 返回
            if($re){
                returnJson('100','添加成功',$re);
            }else{
                returnJson('103','添加失败', $re);
            }

        } else {

            returnJson('104', '请求不合法', '');

        }
    }

    /**
     *     再次回显确认领取信息 (链接参数)
     */
    public function checkReceived()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'lc_id' => 'require',
                'pc_id' => 'require'
            ];
            // 提示信息
            $msg = [
                'lc_id.require' => '提货码ID不能为空',
                'pc_id.require' => '产品类ID不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);
            // 接收参数
            $map['lc_id'] = $this->parm['lc_id'];
            $where['pc_id'] = $this->parm['pc_id'];
            
            // 调用模型获取数据
            $re = $this->lcode->getOne($map);
            $res = $this->pcate->getOne($where);
            $request_obj = Request::instance();
            //获取当前域名
            $domain_name = $request_obj->root(true);
            // 返回
            if($re && $res){
                $info['product_number'] = $res['product_number'];
                $info['product_img'] = $domain_name.'/uploads/images/'. $res['product_img'];
                $info['product_remark'] = $res['product_remark'];
                $info['consignee_name'] = $re['consignee_name'];
                $info['consignee_phone'] = $re['consignee_phone'];
                $info['consignee_adress'] = $re['consignee_adress'];
                $info['region_0'] = $re['region_0'];
                $info['region_1'] = $re['region_1'];
                $info['region_2'] = $re['region_2'];
                $info['region_adress'] = $re['region_adress'];
                returnJson('100','获取成功',$info);
            }else{
                returnJson('103','获取失败', $info);
            }

        } else {

            returnJson('104', '请求不合法', '');

        }
    }

    /**
     *     提交领取信息 (链接参数：pc_id, lc_id )
     */
    public function subReceived()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'lc_id' => 'require'
            ];
            // 提示信息
            $msg = [
                'lc_id.require' => '提货码ID不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);
            // 接收参数
            $arr['lc_id'] = $this->parm['lc_id'];
            $arr['order_number'] = 'SN'.order_sn();//生成订单号
            $arr['is_received'] = 2;//已领取
            $arr['lc_status'] = 2;//处理中---------2：处理中，3：订单已发货，4：订单归入特殊问题 ，5：订单已签收----
            $arr['received_time'] = time();//领取时间
            $arr['m_id'] = $this->user_id;
            $arr['openid'] = $this->openid;
            
            // 调用模型获取数据
            $result = $this->lcode->editCode($arr);
            // 返回
            if($result){
                returnJson('100','领取成功',$result);
            }else{
                returnJson('103','领取失败', $result);
            }

        } else {

            returnJson('104', '请求不合法', '');

        }
    }

    /**
     *     订单查询列表 (链接参数：lc_id )
     */
    public function getList()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'm_id' => 'require',
            ];
            // 提示信息
            $msg = [
                'm_id.require' => '用户ID不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);
            // 接收参数
            $openid = $this->openid;

            // 判断是否传页码数
            $page = isset($this->parm['page']) ? $this->parm['page'] : 1;
            $pageSize = isset($this->parm['pageSize']) ? $this->parm['pageSize'] : 10;

            $request_obj = Request::instance();
            //获取当前域名
            $domain_name = $request_obj->root(true);
            
            // 调用模型获取数据
            $map['lc.openid'] = $openid;
            $map['lc.is_received'] = 2;
            $map['lc.lc_status'] = ['in',"2,3,5"];
            $info = $this->lcode->getCodeAll($map, $page, $pageSize);//正常订单
            foreach ($info as $k => $v) {
                $v['received_time'] = dateFormats($v['received_time']);//领取时间
                $v['product_img'] = $domain_name.'/uploads/images/'. $v['product_img'];//产品图片
                $v['product_remark'] = substr_cut($v['product_remark']);//产品说明
            }

            // 返回
            if($info){
                returnJson('100','获取成功',$info);
            }else{
                $raw['lid'] = 4;
                returnJson('103','暂无相关领取数据', $raw);
            }

        } else {

            returnJson('104', '请求不合法', '');

        }
    }

    /**
     *     物流信息 (链接参数： )
     */
    public function Logistics()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'lc_id' => 'require'
            ];
            // 提示信息
            $msg = [
                'lc_id.require' => '提货码ID不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);

            $where['lc.lc_id'] = $this->parm['lc_id'];
            $field = 'pc.product_img';
            $row = $this->lcode->getOneCode($where,$field);
            $request_obj = Request::instance();
            //获取当前域名
            $domain_name = $request_obj->root(true);
            $product_img = $domain_name.'/uploads/images/'. $row['product_img'];//产品图片

            $lc_statuss = Config('lc_status');
            // 接收参数
            $data = array();
            $data['product_img'] = $product_img;

            //正常订单物流查询
            $map['lc_id'] = $this->parm['lc_id'];
            // 调用模型获取数据
            $info = $this->lcode->getOne($map);
            
            $data['cate'] = 1;//1为正常订单，2为特殊订单

  ////////////////////////////////////////////////////////////////////////////////////////////////////////          
            if ($info['lc_status'] == 2) {
                $data['lc_status'] = '处理中';
                $data['received_time'] = dateFormat($info['received_time']);//处理时间
                returnJson('100','暂无信息',$data);
            }
            if ($info['is_special'] == 1) {  //是否为特殊订单（1：否，2：是）
                $express_name = $info['express_name'];
                $express_simple = $info['express_simple'];//快递简写
                $express_number = $info['express_number'];//快递单号
                $data['lc_status'] = $lc_statuss[$info['lc_status']];//物流状态
                $data['received_time'] = dateFormat($info['received_time']);//处理时间
                $data['send_time'] = dateFormat($info['send_time']);//发货时间
                $data['success_time'] = dateFormat($info['success_time']);//收货时间
                
            }else{
                //进入特殊订单
                $data['cate'] = 2;//1为正常订单，2为特殊订单
                $express_name = $info['express_name'];
                $express_simple = $info['express_simple'];//快递简写
                $express_number = $info['express_number'];//快递单号
                $data['lc_status'] = $lc_statuss[$info['lc_status']];//物流状态
                $data['received_time'] = dateFormat($info['received_time']);//处理时间
                $data['send_time'] = dateFormat($info['send_time']);//发货时间
                $data['success_time'] = dateFormat($info['success_time']);//收货时间
                
            }
/////////////////////////////////////////////////////////////////////////////////////////////////////
            //查询物流信息
            $Msg = $this->kd->getOrderTracesByJson($express_simple,$express_number);
            $Msg = json_decode($Msg, true);

            $LeuuDEFINEEDC = Config('LeuuDEFINEEDC');
            $Name = '';$Tel = '';
            foreach ($LeuuDEFINEEDC as $Vs => $Rs) {
                if ($Rs['code'] == $Msg['ShipperCode']) {
                    $Name = $Rs['name'];
                    $Tel = $Rs['tel'];
                }
            }
            $data['id'] = $Msg['LogisticCode'];//快递单号
            $data['name'] = $Name;//快递公司
            $data['tel'] = $Tel;//电话
            $data['list'] = array_reverse($Msg['Traces']);//物流信息
            // 返回
            returnJson('100','获取成功',$data);

        } else {

            returnJson('104', '请求不合法', '');

        }
    }

    /**
     *     确认收货 (链接参数：pc_id, lc_id )
     */
    public function shouhuo()
    {
        // 判断是否post请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'lc_id' => 'require'
            ];
            // 提示信息
            $msg = [
                'lc_id.require' => '提货码ID不能为空'
            ];
            // 验证参数是否为空
            checkedParm($this->parm, $rule, $msg);
            // 接收参数
            $map['lc_id'] = $this->parm['lc_id'];
            $map['lc_status'] = 5;//订单已签收
            $map['success_time'] = time();
            $re = $this->lcode->editCode($map);
            if($re){
                returnJson('100','收货成功',$re);
            }else{
                returnJson('103','收货失败');
            }

        } else {

            returnJson('104', '请求不合法', '');

        }
    }







}