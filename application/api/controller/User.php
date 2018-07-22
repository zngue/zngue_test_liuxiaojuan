<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\api\model\UserModel;
use app\common\Api\UploadApi;
use wechat\WxAuth;
use think\Session;
use wxcrypt\WXBizDataCrypt;
class User extends Base
{
    protected $_model;
    protected $parm;
    public function __construct(){
        parent::__construct();
        $param = file_get_contents("php://input");
        $this->parm = json_decode($param,true);//接收传过来的数据
        $this->_model = new UserModel();
    }
    /**
     * @Author   lcx
     * @DateTime 2017-05-11
     * @desc    获取用户基本信息
     * @return   {json}
     * @return   [type]     [description]
     */
    public function getUserBaseInfo(){
        $map['user_id'] = $this->user_id;
         if(isset($this->parm['user_id'])&&!empty($this->parm['user_id'])){
            $map['user_id'] = $this->parm['user_id'];
        }

        $data = Db::name('member')->where($map)->field('user_money,headimg,mobile')->find();
        if(empty($data)){
            returnJson(103, '暂无相关数据');
        }else{
            $data['user_money'] = moneyFormat($data['user_money']);
            if(isset($data['headimg'])){
                $data['headimg'] = HeadImgPath($data['headimg']);
            }
            if(isset($data['mobile'])){
                $data['mobile_ori'] = $data['mobile'];
                if($data['mobile']){
                     $data['mobile'] = substr($data['mobile'],0,3).'****'.substr($data['mobile'],7,11);
                }
            }
            returnJson(100, '',$data);
        }
    }

    /**
     * 短信接口
     *@param mobile String 手机号
     *
     */
    public function sendSms(){
        if(request()->isPost()){
            if(''==$this->parm['mobile']){
                returnJson(102, '手机号不能为空');
            }
            if (!isMobile($this->parm['mobile'])) {
                returnJson(102, '手机号不合法');
            }
            //判断手机号是否已经绑定过
            $data = Db::name('member')->where('mobile',$this->parm['mobile'])->value('mini_openid');
            if($data){
               returnJson(102, '该手机号已经绑定过，不能重复绑定');
            }

            $yzm = rand(1000, 9999);
            $result_data = sendSms($this->parm['mobile'], $yzm);
            //$result_data['code'] = 0;
            if ($result_data['code'] == 0) {
                // 设置缓存的同时并且进行参数设置
                cache($this->parm['mobile'], $yzm, 300);
                returnJson(100, '短信发送成功,5分钟内有效');
            } else {
                returnJson(102, '短信发送失败');
            }
        }
    }
    /**
     * 发送邮件接口
     *@param email String 邮箱
     *@param content String 邮件内容
     */
    public function sendEmail(){
        if(request()->isPost()){
            $email = input('email');
            $content = '感谢使用'.getAppName();
            //验证邮箱是否合法
            if(!isEmail($email)){
                returnJson(102,'邮箱不合法');
            }
            //发送邮件
            if(!sendEmail($email,$content)){
                returnJson(102,'发送失败');
            }
            returnJson(100,'发送成功');
        }
    }

    //绑定信息
    public function bind(){
        if(request()->isPost()){
            //判断手机号，验证码是否为空
            if(empty($this->parm['mobile'])){
                returnJson(102,'请输入手机号');
            }

            if(empty($this->parm['code'])){
                returnJson(102,'请输入验证码');
            }
            if(!isMobile($this->parm['mobile'])){
                returnJson(102,'手机号码错误');
            }
            //判断手机号是否已经绑定过
            $my_map['mobile'] = $this->parm['mobile'];
            $data =  $this->_model->getOne($my_map,'mini_openid');
            if($data){
               returnJson(102, '该手机号已经绑定过，不能重复绑定');
            }


          /* if($this->parm['code']!=cache($this->parm['mobile'])){
                returnJson(102,'验证码错误');
            }*/
            $my_map2['mini_openid'] = $this->parm['openid'];
            $data2 =  $this->_model->getOne($my_map2,'mini_openid,user_id');
            $user_id = $data2['user_id'];
            $u_map['user_id'] = $user_id;
            //如果是被推荐
            if(!empty($this->parm['pid'])){
                //判断是否是第一次
                $pid = $this->_model->getValue($u_map,'pid');
                if(empty($pid)){//如果是第一次被推荐绑定信息，更新
                    $arr['pid'] = $this->parm['pid'];
                }
            }
            $arr['mini_openid'] = $this->parm['openid'];
            $arr['sex'] = $this->parm['sex'];
            $arr['area'] = $this->parm['area'];
            $arr['headimg'] = $this->parm['headimg'];
            $arr['nickname'] = $this->parm['nickname'];
            $arr['mobile'] = $this->parm['mobile'];
            $arr['status'] = 1;
            $arr['create_time'] = time();
            if(isset($this->parm['unionid'])&&!empty($this->parm['unionid'])){
                 $arr['unionid'] = $this->parm['unionid'];
            }

            if($data2){
                $result = $this->_model->edit($my_map2,$arr);//更新用户信息
            }else{
                $result = $this->_model->add($arr);
            }
            /***************新用户注册送优惠券********************/
            //判断是否开启
            if(getSystemConfig('site_base')['register_send_coupon']==1){
                 $cid =  Db::name('coupon_cate')->where(array('type'=>2))->find();
                if($cid['send_start_time']<time()&&time()<$cid['send_end_time']){
                    $coupon_data = array();
                    $coupon_data['code'] = $this->generate_promotion_code();
                    $coupon_data['cid'] = $cid['id'];
                    $coupon_data['type'] = $cid['type'];
                    $coupon_data['status'] = 1;
                    $coupon_data['uid'] = $user_id;
                    $coupon_data['send_time'] = time();
                    $result2 = Db::name('coupon')->insert($coupon_data);
                }
            }

            if($result){
                returnJson(100,'绑定成功');
            }else{
                returnJson(102,'绑定失败');
            }
        }

    }

    /**
     *生成优惠券码
     */
    private function generate_promotion_code($code_length = 6)
    {
        $characters = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $code = "";
        for ($i = 0; $i < $code_length; $i ++) {
            $code .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        //$code =microtime().$code;
        return $code;
    }

    //更新用户token
    public function updateToken(){
        if(!isset($this->parm['openid'])||empty($this->parm['openid'])){
            returnJson(102, '未登录');
        }
        //生成token
      do {
        $token = getToken();
        $utoken = $this->_model->getOne(array('token'=>$token),'user_id');
        if($utoken){
            continue;
        }else{
            break;
        }
      }while(true);

      $user['token'] = $token;
//      $user['expire_time'] = time()+60;
      $user['expire_time'] = time()+7200;
      $u_map['mini_openid'] = $this->parm['openid'];
      $fp = fopen('tokenlock.txt','r');
        if(flock($fp, LOCK_EX)){
            $res = $this->_model->edit($u_map,$user);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
      if($res){
          returnJson(100,'更新token成功',array('token'=>$token));
      }
        returnJson(-100,'更新token失败');
    }

    /**
     * @Author   lcx
     * @DateTime 2017-05-12
     * @desc    小程序用户信息解密
     * @return   {json}
     */
    public function aesDecrypt(){
        $wx_config = getSystemConfig('sm_config');
        $appid = $wx_config["app_id"];
        $sessionKey = $this->parm['key'];
        $encryptedData= $this->parm['data'];
        $iv = $this->parm['iv'];
        $pc = new WXBizDataCrypt($appid,$sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        $arr = json_decode($data,true);
        $a = Db::name('member')->where('unionid',$arr['unionId'])->find();

        if($a['unionid']&&empty($a['mini_openid'])){
            Db::name('member')->where('unionid',$arr['unionId'])->setField('mini_openid',$arr['openId']);
        }
        if ($errCode == 0) {

            print($data . "\n");
        } else {
            print($errCode . "\n");
        }
    }
     //判断某用户是否参过该团
    public function isJoin($team_id,$uid){
        $map['uid'] = $uid;
        $map['team_id'] = $team_id;
        $map['order_status'] = array('not in','3,5');
        $data = Db::name('order')->where($map)->find();
        if(!empty($data)){
            return 1;
        }else{
            return 0;
        }
    }
    //判断用户该团是否存在未支付订单
    public function isWaitPay($team_id,$uid){
        $map['o.uid'] = $uid;
        $map['o.team_id'] = $team_id;
        $map['o.order_status'] = 0;
        $map['o.pay_status'] = 0;
        $map['t.status'] = 1;
        $data = Db::name('order')->alias('o')->join('team_buy t','o.team_id=t.id')
                ->where($map)->find();
        if(!empty($data)){
            return 1;
        }else{
            return 0;
        }

    }
    //生成推广二维码
    public function expandQrcode(){

        if(!isset($this->parm['url'])||empty($this->parm['url'])){
             returnJson(-102,'缺少参数url');
        }
        $url = $this->parm['url'];
        $logo = WHOLE_HOST."/static/index/images/logo3.png";
        $data = createQrcodeImg($url,'qr',$logo);
         returnJson(100,'成功',$data);
    }

    /**
     *   意见反馈
     */
    public function opinionFeedback()
    {
        // 判断是否POST请求
        if (request()->isPost()) {
            // 验证规则
            $rule = [
                'content' => 'require',
            ];
            // 提示信息
            $msg = [
                'content.require' => '意见不能为空',
            ];
            checkedParm($this->parm, $rule, $msg);
            // 接收内容
            $set['content'] = $this->parm['content'];
            $set['member_id'] = $this->user_id;
            $set['add_time'] = time();
            $res = Db::name('opinion')
                        ->insertGetId($set);
            if (!$res) {
                returnJson(-100, '意见反馈失败', $res);
            }
                returnJson(100, '意见反馈成功', $res);

        } else {
                returnJson(-101, '请求不合法');
        }
    }

}
