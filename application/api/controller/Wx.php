<?php
namespace app\api\controller;
use think\Controller;
use wechat\WxPay;
use think\Session;
use wechat\WxAuth;
use app\api\model\UserModel;


/**
 * 微信板块
 * @author lcx 20170503
 *
 */
class Wx extends Base
{
   public function __construct(){
        parent::__construct();
        $this->user_model = new UserModel();
    }
   /**
   *获取微信SignPackage
   */
   public function getWxSignPackage(){
        //读取微信配置
        $wxconfig = getSystemConfig('wx_config');
        //调用微信分享接口
        $WxAuth = new WxAuth($wxconfig['app_id'], $wxconfig['app_secret']);
        $SignPackage = $WxAuth->getSignPackage();
        returnJson('100','ok',$SignPackage);
   }

   /**
     * @desc   获取openid
     * @param   code
     * @return   {json}
     */
    public function getOpenid(){
          // 实例化wx
        $wx_config = getSystemConfig('sm_config');
        $WxAuth = new WxAuth($wx_config["app_id"], $wx_config["app_secret"]);
        $json = $WxAuth->getSmallOAuth($this->parm['code']);
        if(!empty($json['openid'])){
          $map['openid'] = $json['openid'];
          $res = $this->user_model->getOne($map);
          if (!$res) {
              //添加用户
              $arr['openid'] = $json['openid'];
              $arr['create_time'] = time();
              $m_id = $this->user_model->add($arr);
              $user['m_id'] = $m_id;
              $user['openid'] = $json['openid'];
              returnJson('100','获取openid成功',$user);
          }
          //存在直接获取openid
          $user['openid'] = $res['openid'];
          $user['m_id'] = $res['m_id'];
          returnJson('100','直接获取openid成功',$user);
        }else{
          returnJson('103','获取openid失败');
        }
    }

    /**
     * @desc   更新用户
     * @param   code
     * @return   {json}
     */
    public function addUser(){
        $info = $this->parm['info'];
        $map['openid'] = $this->parm['openid'];
        $res = $this->user_model->getOne($map);
        if($res){
          //更新用户信息
          $param['nickname'] = $info['nickName'];
          $param['sex'] = $info['gender'];
          $param['head_img'] = $info['avatarUrl'];
          $param['update_time'] = time();
          $re = $this->user_model->edit($map,$param);
          $user['openid'] = $res['openid'];
          $user['m_id'] = $res['m_id'];
          returnJson('100','更新用户信息成功',$user);

        }else{
          returnJson('103','获取用户信息失败');
        }
    }


    //生成页面二维码
    public function getPageQr(){
      $wx_config = getSystemConfig('sm_config');
      $WxAuth = new WxAuth($wx_config["app_id"], $wx_config["app_secret"]);

      $scene = $this->parm['query'];
      $page = $this->parm['page'];
      $width = '100px';
      $auto_color = true;
      $line_color ="{'r':'0','g':'0','b':'0'}";
      $base = $WxAuth->getSamllPageQr($scene,$width,$auto_color,$line_color,$page);
      $base = base64_encode($base);//将二进制转base64
      $url = config('UPLOAD_PATH').convertBaseimg($base);
      returnJson(100,'获取openid成功',$url);

    }


}
