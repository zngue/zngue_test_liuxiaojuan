<?php
namespace wechat;


class WxPay
{

    var $parameters;

    var $response;

    var $result;

    var $url;

    var $curl_timeout;

    var $appid;

    var $secret;

    var $key;

    var $mchid;

    function __construct($config)
    {
        $this->appid = $config['app_id'];
        $this->secret = $config['app_secret'];
        $this->key = $config['pay_key'];
        $this->mchid = $config['mchid'];

        $this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $this->curl_timeout = 30;
    }

    function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return trim($ret);
    }

    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }

            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);

        $String = $String . "&key=" . $this->key;

        $String = md5($String);

        $result_ = strtoupper($String);

        return $result_;
    }

    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    public function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @throws WxPayException
     */
    public function postXmlCurl($xml, $url)
    {
        $ch = curl_init();
		//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    /**
	 * 	作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml,$url,$second=300)
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch,CURLOPT_HEADER,FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		//设置证书
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, __DIR__.'/cert/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, __DIR__.'/cert/apiclient_key.pem');
		//post提交方式
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		}
		else {
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

    function setParameter($parameterData)
    {
        // 需要传入的支付信息
        $parameterArray = array(
            'openid',
            'body',
            'out_trade_no',
            'total_fee',
            'notify_url',
            'trade_type'
        );
        if (empty($parameterData)) {
            throw new Exception('请传入您要支付的信息');
        }

        foreach ($parameterArray as $val) {
            if ($parameterData[$val] == false) {
                throw new \Think\Exception('支付参数' . $val . ' 不能为空');
            }
            $this->parameters[$this->trimString($val)] = $this->trimString($parameterData[$val]);
        }

        return $this->parameters; // 返回支付参数
    }

    function createXml()
    {
        $this->parameters["appid"] = $this->appid;
        $this->parameters["mch_id"] = $this->mchid;
        $this->parameters["nonce_str"] = $this->createNoncestr();
        $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];
        $this->parameters["sign"] = $this->getSign($this->parameters);
        return $this->arrayToXml($this->parameters);
    }

    function getPrepayId()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        $prepay_id = $this->result["prepay_id"];
        return $prepay_id;
    }

    function postXml()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    function postXmlSSL()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlSSLCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }
}
