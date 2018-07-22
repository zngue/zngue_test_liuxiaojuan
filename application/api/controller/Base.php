<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\cache\driver\Redis;
use app\api\model\UserModel;
use wechat\WxPay;
use think\Session;
use wechat\WxAuth;
class Base extends Controller
{
	protected $parm;
    protected $user_id;
    protected $openid;

    public function _initialize()
    {
        if(!request()->isPost()){
            returnJson(104, '请求不合法');
        }
        $param = file_get_contents("php://input");
        $this->parm = paramFilter(json_decode($param,true));//接收传过来的数据
        
        //接口时间相差大于15分钟
        if($this->parm['timestamp']>time()+15*60||$this->parm['timestamp']<time()-15*60){
            returnJson(104, '请求不合法');
        }
        
        if(!empty($this->parm['openid'])){
            $user_model = new UserModel();
            $map['openid'] = $this->parm['openid'];
            $userinfo = $user_model->getOne($map,'m_id');
            $this->user_id = $userinfo['m_id'];
            $this->openid = $this->parm['openid'];
        }   

        /*$wx_config = getSystemConfig('sm_config');
        $WxAuth = new WxAuth($wx_config["app_id"], $wx_config["app_secret"]);
        $json = $WxAuth->getSmallOAuth($this->parm['code']);

        $user_model = new UserModel();
        $map['openid'] = $this->parm['openid'];
        $userinfo = $user_model->getOne($map,'m_id');
        $this->user_id = $userinfo['m_id'];*/

        

    }
    private function filter()
    {
        $interceptor = config('interceptor');
        $module = request()->module();
        $controller = request()->controller();
        $action = strtolower(request()->action());
        if (key_exists($module, $interceptor)) {
            if (key_exists($controller, $interceptor[$module])) {
                if (in_array($action, $interceptor[$module][$controller])) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    //添加订单操作记录
    public function addOrderLog($order_sn_total,$op){
        $log['order_sn_total'] = $order_sn_total;
        $log['operation'] =$op;
        $log['user_id'] = $this->user_id;
        $log['user_name'] = "用户";
        $log['remark'] = "小程序端操作";
        $log['operate_time'] = time();
        return Db::name('order_operate_log')->insert($log);//添加订单操作记录
    }
    /**
    * 微信支付
    */
    public function Pay($order_sn,$url="/pay/Callback/smallPay"){
        //$order_sn = 'Y327772547647442';
        //查询订单信息
        $order_data = Db::name('order')->where(array('order_sn'=>$order_sn))->find();
        if($order_data['pay_way'] == 1){
            $order_money = $order_data['total_amount']; //全款支付
        }else if($order_data['pay_way'] == 2){
            $order_money = $order_data['part_price']; //部分支付
        }

        $order_sn = $order_data['order_sn'];
        //微信配置项
        $config = getSystemConfig('sm_config');

        //读取用户信息 openid
        $openid = Db::name('member')->where(array('user_id'=>$this->user_id))->value('mini_openid');
        $wxpay=new WxPay($config);
        $order['openid'] = $openid;
        $order['body'] = $config['app_name'];//C('app_name');
        $order['out_trade_no']=$order_sn;
        if(DEBUG){
            $order_money = 0.01;
        }
        $order['total_fee']=$order_money*100;
        $url = WHOLE_HOST.$url;
        $order['notify_url']=$url;//微信回调地址
        $order['trade_type']='JSAPI';
        $wxpay->setParameter($order);
        $timeStamp = time();
        $jsApiObj['appId'] = $config['app_id'];
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $wxpay->createNoncestr();
        $prepay_id = $wxpay->getPrepayId();
        $jsApiObj["package"] = "prepay_id=".$prepay_id;
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $wxpay->getSign($jsApiObj);
        $this->savePrepayId($prepay_id,$openid);
        return $jsApiObj;
    }

    /**
     * 多商品微信支付
     */
    public function Pay_total($order_sn,$url="/pay/Callback/backPay"){
        //$order_sn = 'Y327772547647442';
        //查询订单信息
        $order_data = Db::name('order')->where(array('order_sn_total'=>$order_sn))->select();
        $order_money = 0;
        foreach ($order_data as $key=>$value){
            $order_money+=$value['total_amount'];
        }
        //微信配置项
        $config = getSystemConfig('sm_config');
        //读取用户信息 openid
        $openid = Db::name('member')->where(array('user_id'=>$this->user_id))->value('mini_openid');
        $wxpay=new WxPay($config);
        $order['openid'] = $openid;
        $order['body'] = $config['app_name'];//C('app_name');
        $order['out_trade_no']=$order_sn;
        if(DEBUG){
            $order_money = 0.01;
        }
        $order['total_fee']=$order_money*100;
        $url = WHOLE_HOST.$url;
        $order['notify_url']=$url;//微信回调地址
        $order['trade_type']='JSAPI';
        $wxpay->setParameter($order);
        $timeStamp = time();
        $jsApiObj['appId'] = $config['app_id'];
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $wxpay->createNoncestr();
        $jsApiObj["package"] = "prepay_id=".$wxpay->getPrepayId();
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $wxpay->getSign($jsApiObj);
        return $jsApiObj;
    }

     //保存formID
    public function saveFormId(){
        $arr['form_id'] = $this->parm['form_id'];
        $user_model = new UserModel();
        $map['user_id'] = $this->user_id;
        $openid = $user_model->getValue($map,'mini_openid');
        $arr['expire_time'] = time()+7*24*3600;
        $redis = new Redis();
        $key = 'mallFormId:'.$openid;
        $data = json_encode($arr);
        $len = $redis->lPush($key,$data);
        if($len>0){
            return returnJson(100,'保存成功');
        }
        return returnJson(-100,'保存失败');

    }
    //保存prepay_id
    public function savePrepayId($prepay_id,$openid){
        $arr['form_id'] = $prepay_id;
        $user_model = new UserModel();
        $arr['expire_time'] = time()+7*24*3600;
        $redis = new Redis();
        $key = 'mallFormId:'.$openid;
        $data = json_encode($arr);
        $len = 0;
        for($i=0;$i<3;$i++){
            $len = $redis->lPush($key,$data);
        }



    }





}
