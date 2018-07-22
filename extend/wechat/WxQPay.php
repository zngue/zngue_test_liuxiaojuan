<?php
// +----------------------------------------------------------------------
// | 接口基类
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http:/www.phpceo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Liting <346586799@qq.com>
// +----------------------------------------------------------------------
// | Date:  2015-1-22 下午11:22:21
// +----------------------------------------------------------------------
namespace wechat;

use think\Exception;

/**
 * 所有接口的基类
 */
class WxQPay{

	var $parameters;//请求参数，类型为关联数组
	var $response;//微信返回的响应
	var $result;//返回参数，类型为关联数组
	var $url; //接口链接
	var $curl_timeout;//curl超时时间
	var $appid;
	var $secret;
	var $key;
	var $mchid;

	function __construct($config) {

		$this->appid= $config['app_id'];
		$this->secret= $config['app_secret'];
		$this->key= $config['pay_key'];
		$this->mchid= $config['mchid'];
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
		//设置curl超时时间
		$this->curl_timeout = 30;

	}

	function trimString($value)
	{
		$ret = null;
		if (null != $value)
		{
			$ret = $value;
			if (strlen($ret) == 0)
			{
				$ret = null;
			}
		}
		return trim($ret);
	}

	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 )
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}

	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
			if($urlencode)
			{
				$v = urlencode($v);
			}

			$buff .= $k . "=" . $v . "&";
		}
		$reqPar = '';
		if (strlen($buff) > 0)
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}

	/**
	 * 	作用：生成签名
	 */
	public function getSign($Obj)
	{
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);

		$String = $String."&key=".$this->key;

		$String = md5($String);

		$result_ = strtoupper($String);

		return $result_;
	}
	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr)
	{
		$xml = "<xml>";
		foreach ($arr as $key=>$val)
		{
			if (is_numeric($val))
			{
				$xml.="<".$key.">".$val."</".$key.">";

			}
			else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml.="</xml>";

		return $xml;
	}

	/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml)
	{
		$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $array_data;
	}

	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml,$url,$second=30)
	{
		if($url == false || !strpos($xml, 'xml')){

			throw new Exception('传入参数不符合要求');
		}
		//初始化curl
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if(!strpos($data, 'xml')){
			throw new Exception('没有返回xml格式数据');
		}
		if($data != false){
			return $data;
		}
		else
		{	echo "curl出错，错误码:$error"."<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			return false;
		}
	}

	/**
	 * 	作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml,$url,$second=30)
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


	/**
	 * 	作用：设置请求参数
	 */
	function setParameter($parameterData){
		//需要传入的支付信息
		$parameterArray = array('openid',
								'check_name',
								'amount',
								'desc',
								'partner_trade_no'
					);
		if(empty($parameterData)){
			throw new Exception('请传入您要支付的信息');
		}

		foreach($parameterArray as $val){
			if($parameterData[$val] == false){
				throw new Exception('支付参数'.$val.' 不能为空');
			}
			$this->parameters[$this->trimString($val)] = $this->trimString($parameterData[$val]);
		}
		return $this->parameters;		//返回支付参数
	}


	/**
	 * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
	 */
	function createXml()
	{
		$this->parameters["mch_appid"] =$this->appid;//公众账号ID
		$this->parameters["mchid"] = $this->mchid;//商户号
		$this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		$this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
		$this->parameters["sign"] = $this->getSign($this->parameters);//签名
		return $this->arrayToXml($this->parameters);
	}


	/**
	 * 	作用：post请求xml
	 */
	function postXml()
	{
		$xml = $this->createXml();
		$this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	/**
	 * 	作用：使用证书post请求xml
	 */
	function postXmlSSL()
	{
		$xml = $this->createXml();
		$this->response = $this->postXmlSSLCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	/**
	 * 	作用：获取结果，默认不使用证书
	 */
	function getResult()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
}
