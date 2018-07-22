<?php
namespace wechat;
use think\Db;
class WxAuth
{

    public $appId;

    public $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 获取微信回传code值
     *
     * @param string $url:code回传到页面
     * @param string $snsapi:是否弹出微信授权页面。snsapi_userinfo：弹出，snsapi_base：不弹出
     *
     * @return code
     */
    public function getCode($url, $snsapi = 'snsapi_base')
    {
        $url = urlencode($url);
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . $url . '&response_type=code&scope=' . $snsapi . '&state=1&connect_redirect=1#wechat_redirect';
    }

    /**
     * 通过code获取用户基本信息
     *
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOAuth($code = '')
    {
        if(empty($code)){
            $code = isset($_GET['code']) ? $_GET['code'] : '';
        }

        if (! $code) {
            return false;
        }
        // GET请求连接
        $get_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appId . "&secret=" . $this->appSecret . "&code=" . $code . "&grant_type=authorization_code";
        $result = $this->http_get($get_token_url);
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['access_token'])) {
                return $json;
            } else {
                return false;
            }
        }
        return false;
    }
    //小程序通过code 获取session_key,oepnid
    public function getSmallOAuth($code=''){
        if(empty($code)){
            $code = isset($_GET['code']) ? $_GET['code'] : '';
        }
        if (! $code) {
            return false;
        }
        // GET请求连接
        $get_token_url ="https://api.weixin.qq.com/sns/jscode2session?appid=". $this->appId ."&secret=" . $this->appSecret . "&js_code=". $code . "&grant_type=authorization_code";
        $result = $this->http_get($get_token_url);
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['openid'])) {
                return $json;
            } else {
                return false;
            }
        }
        return false;
    }
    public function getSamllPageQr($scene,$width,$auto_color=false,$line_color="{'r':'0','g':'0','b':'0'}",$page='pages/home/home'){
        $accesstoken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$accesstoken;
        $params = array(
            'scene'=>$scene,
            'width'=>$width

        );
        $json_str = json_encode($params);
        $result = $this->http_post($url,$json_str);

        if ($result) {

            return $result;
        }
        return false;
    }

    /**
     * 获取用户详细信息
     *
     * @param string $openid:微信用户openid
     * @return mixed|boolean {
     *         "subscribe": 1,
     *         "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
     *         "nickname": "Band",
     *         "sex": 1,
     *         "language": "zh_CN",
     *         "city": "广州",
     *         "province": "广东",
     *         "country": "中国",
     *         "headimgurl":"http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
     *         "subscribe_time": 1382694957,
     *         "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
     *         "remark": "",
     *         "groupid": 0
     *         }
     */
    public function getWxUserInfo($openid)
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN';
        $result = $this->http_get($url);
        if ($result) {
            $json = json_decode($result, true);
            if ($json) {
                return $json;
            } else {
                return false;
            }
        }
    }

    public function getWxUserInfo2($openid, $accessToken)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN';

        $result = $this->http_get($url);
        if ($result) {
            $json = json_decode($result, true);
            if ($json) {
                return $json;
            } else {
                return false;
            }
        }
    }

    /**
     * 生成二维码
     */
    public function getLimitQRCode($scene_id)
    {
        $params = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => $scene_id
                )
            )
        );
        $accessToken = $this->getAccessToken();
        $json_str = json_encode($params);
        $result = $this->http_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?' . 'access_token=' . $accessToken, $json_str);
        $ticket = json_decode($result, true);
        if (isset($ticket['ticket'])) {
            return $ticket;
        }
        exit('system error');
    }

    /**
     * 发送模板消息
     *
     * @param string $openid
     *            接受者openid
     * @param string $template_id
     *            消息模板id
     * @param array $data
     *            参数
     * @param string $url
     *            点击模板消息，跳转页面
     * @param string $topcolor
     *            模板消息背景颜色
     * @return boolean
     */
    public function sendTemplateMessage($openid, $template_id, $data, $url = '', $topcolor = '#FF0000')
    {
        $params = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $accessToken = $this->getAccessToken();
        $json_str = json_encode($params);
        $result = $this->http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?' . 'access_token=' . $accessToken, $json_str);
        $message = json_decode($result, true);
        if ($message['errcode'] == 0) {
            return true;
        }
        return false;
    }

    public function wxCustomMenu($json_str)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->http_post('https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $accessToken, $json_str);
        $ticket = json_decode($result, true);
        return $ticket;
    }

    /**
     * 获取微信JS-SDK 配置信息
     *
     * @return multitype:string number unknown NULL
     */
    public function getSignPackage($url = '')
    {
        $jsapiTicket = $this->getJsApiTicket();


        // 注意 URL 一定要动态获取，不能 hardcode.
        if (! $url) {
            $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }

        $timestamp = time();
        $nonceStr = $this->createNonceStr();


        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "signature" => $signature,
            "url" => $url
        );

        return $signPackage;
    }


    /**
     * 获取getaddrSign
     *
     * @return multitype:string number unknown NULL
     */
    public function getaddrSign($url, $token)
    {
        $timestamp = strval(time());
        $nonceStr = $this->createNonceStr();

        $obj['appid'] = $this->appId;
        $obj['accesstoken'] = $token;
        $obj['timestamp'] = $timestamp;
        $obj['noncestr'] = $nonceStr;
        $obj['url'] = $url;

        //参数小写
        foreach ($obj as $k => $v){
            $bizParameters[strtolower($k)] = $v;
        }
        //字典序排序
        ksort($bizParameters);
        //URL键值对拼成字符串
        $buff = "";
        foreach ($bizParameters as $k => $v){
            $buff .= $k."=".$v."&";
        }
        //去掉最后一个多余的&
        $buff2 = substr($buff, 0, strlen($buff) - 1);
        //sha1签名
        $addrSign = sha1($buff2);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "addrSign" => $addrSign
        );
        return $signPackage;
    }
    /***************公众号******************/

    /**
     * 获取AccessToken
     *
     * @return boolean|unknown
     */
    public function getAccessToken()
    {
        // 读取缓存
        $atoken = Db::name('wxtoken')->where(array('id'=>1))->find();

        if (time() > $atoken['time']) {
            // GET请求连接
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appId . '&secret=' . $this->appSecret;
            $result = $this->http_get($url);
            // 判断是否获取access_token
            if ($result) {
                $json = json_decode($result, true);
                $AccessToken = $json['access_token'];
                if ($AccessToken) {
                    Db::name('wxtoken')->where(array('id'=>1))->update(array('accesstoken'=>$AccessToken,'time'=>time()+1800));
                    return $AccessToken;
                } else {
                    return false;
                }
            }
        }else{
            return $atoken['accesstoken'];
        }

    }

    /**
     * 获取微信JS-SDK ticket
     *
     * @return boolean|unknown
     */
    private function getJsApiTicket()
    {
        // 读取缓存
        $atoken = Db::name('wxtoken')->where(array('id'=>2))->find();

        if (time() > $atoken['time']) {
            // GET请求连接
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $result = $this->http_get($url);
            // 判断是否获取access_token
            if ($result) {
                $json = json_decode($result, true);
                $JsApiTicket = $json['ticket'];
                if ($JsApiTicket) {
                    Db::name('wxtoken')->where(array('id'=>2))->update(array('accesstoken'=>$JsApiTicket,'time'=>time()+3600));
                    return $JsApiTicket;
                } else {
                    return false;
                }
            }
        }else{
            return $atoken['accesstoken'];
        }

    }


    /**
     * 随机字符串
     *
     * @param number $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /***************************小程序********************************/
    /**
     * 获取AccessToken
     *
     * @return boolean|unknown
     */
    public function getSamllAccessToken()
    {
        // 读取缓存
        $atoken = cache('small_access_token');

        if (!$atoken) {
            // GET请求连接
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appId . '&secret=' . $this->appSecret;
            $result = $this->http_get($url);
            // 判断是否获取access_token
            if ($result) {
                $json = json_decode($result, true);

                $AccessToken = $json['access_token'];
                if ($AccessToken) {
                    cache('small_access_token',$AccessToken,7200);
                    return $AccessToken;
                } else {
                    return false;
                }
            }
        }else{
            return $atoken;
        }


    }

    /**
     * 获取微信JS-SDK ticket
     *
     * @return boolean|unknown
     */
    private function getSamllJsApiTicket()
    {
        // 读取缓存
        $atoken = cache('small_jsapi_ticket');
        if (!$atoken) {
            // GET请求连接
            $accessToken = $this->getSamllAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $result = $this->http_get($url);
            // 判断是否获取access_token
            if ($result) {
                $json = json_decode($result, true);
                $JsApiTicket = $json['ticket'];
                if ($JsApiTicket) {
                    cache('small_jsapi_ticket',$JsApiTicket,7200);
                    return $JsApiTicket;
                } else {
                    return false;
                }
            }
        }else{
            return $atoken;
        }

    }
    /**
     * 小程序发送模板消息
     * @param string $openid 接受者openid
     * @param string $template_id 消息模板id
     * @param array $data 参数
     * @param string $page 点击模板消息，跳转页面
     * @param string $topcolor 模板消息背景颜色
     * @return boolean
     */
    function sendSmallTemplateMessage($openid,$template_id,$form_id,$data,$page='',$color='#000000',$emphasis_keyword=''){

        $params=array(
            'touser'=>$openid,
            'template_id'=>$template_id,
            'form_id'=>$form_id,
            'page'=>$page,
            'color'=>$color,
            'data'=>$data,
            'emphasis_keyword'=>$emphasis_keyword
        );
        $accessToken=$this->getSamllAccessToken();
        $json_str=json_encode($params);
        $result=$this->http_post('https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$accessToken,$json_str);
        $message=json_decode($result,true);
        if($message['errcode']==0){
            return true;
        }
        return false;
    }

    /**
     * GET 请求
     *
     * @param string $url
     */
    private function http_get($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * POST 请求
     *
     * @param string $url
     * @param array $param
     * @param boolean $post_file
     *            是否文件上传
     * @return string content
     */
    public function http_post($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
    public function isSubscribe($openid){
        $access_token= $this->getAccessToken();
        $subscribe_msg = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid;
        $subscribe = json_decode($this->http_get($subscribe_msg));
        $res = $subscribe->subscribe;
        return $res;
    }

}