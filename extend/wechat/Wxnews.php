<?php
namespace Org\WeChat;

class Wxnews
{
    private $TOKEN = '';
	
	public function __construct($token){
		$this->TOKEN = $token;
	}
	
    public function responseMsg()
    {
        // get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        // extract post data
        if (! empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //寰楀埌娑堟伅绫诲瀷
            $RX_TYPE = trim($postObj->MsgType);
            
            //鏍规嵁娑堟伅绫诲瀷杩斿洖璋冪敤涓嶅悓鏂规硶
            switch ($RX_TYPE){
                case "event":
                    $resultStr = $this->handleEvent($postObj);
                    break;
            }            
            echo $resultStr;
        } else {
            echo "";
            exit();
        }
    }
    
    //浜嬩欢娑堟伅澶勭悊
    private function handleEvent($object){
        $contentStr = "";
        switch ($object->Event) {
            case "subscribe":
                if($object->Ticket){
                    //浜岀淮鐮佹帹骞縤d
                    $id=substr($object->EventKey,8);
					//鍏虫敞浜簅penid
					$openid=$object->FromUserName;
					
					$userModel=D('user');
					
					//鏌ユ壘鍏虫敞浜烘槸鍚﹀凡缁忔槸璇ョ郴缁熺殑浼氬憳
					$userinfo = $userModel->where("openid='{$openid}'")->find();
					if(!$userinfo || $userinfo['guanzhu'] == 0){//涓嶆槸宸茬粡瀛樺湪鐨勪細鍛�
						//鑾峰彇绯荤粺閰嶇疆
						$config_data=getSystemConfig(array('app_id','app_secret','app_name','score'),false);
						//缁欐帹鑽愪汉澧炲姞绉垎
						//$userModel->where("id={$id}")->save(array('score'=>'score+='.$config_data['score']));
						$sql="update ".C('DB_PREFIX')."user set score=score+".$config_data['score']." where id=".$id;
						$userModel->execute($sql);

						//娣诲姞绉垎璁板綍
						$scoreModel= D('score');
						$scoreModel->add(array('user_id'=>$id,'count'=>$config_data['score'],'mode'=>1));
						
						
						//鑾峰彇鍏充富浜虹殑寰俊淇℃伅
						$WeChat = new WxAuth($config_data['app_id'],$config_data['app_secret']);
						$newJson=$WeChat->getWxUserInfo($openid);
						
						$userinfo['nickname'] = $newJson['nickname'];
						$userinfo['head_imgurl'] = $newJson['headimgurl'];
						$userinfo['sex'] = $newJson['sex'];
						$userinfo['create_time'] = date('Y-m-d H:i:s');

						if(isset($userinfo['guanzhu']) && $userinfo['guanzhu'] == 0){
							$sql="update ".C('DB_PREFIX')."user set nickname='".$newJson['nickname']."',head_imgurl='".$newJson['headimgurl']."',sex=".$newJson['sex'].",create_time='".date('Y-m-d H:i:s')."',guanzhu=1 where openid='".$newJson['openid']."'";
							$userModel->execute($sql);
						}else{
							$userinfo['openid'] = $newJson['openid'];
							$userinfo['guanzhu'] = 1;
							//娣诲姞浼氬憳璁板綍
							$userinfo['id'] = $userModel->add($userinfo);
						}
						
						$frindsModel = D('friends');
						//鎺ㄨ崘浜轰笌鍏虫敞浜虹敓鎴愭湅鍙嬪叧绯�
						$frindsModel->add(array('user_id'=>$id,'friends_id'=>$userinfo['id'],'type'=>1));
						//缁欐帹鑽愪汉鍙戦�侀�氱煡娑堟伅
						
						$tjuserInfo = $userModel->where("id={$id}")->find();
						
						$data=array(
						    'first'=>array("value"=>"鎮ㄥソ锛屼互涓嬩細鍛樻槸閫氳繃鎮ㄧ殑浜岀淮鐮佸叧娉ㄦ垜浠殑锛�","color"=>"#173177"),
						    'keyword1'=>array("value"=>$userinfo['nickname'],"color"=>"#173177"),
						    'keyword2'=>array("value"=>date('Y-m-d H:i:s'),"color"=>"#173177"),
						    'keyword3'=>array("value"=>$tjuserInfo['nickname'],"color"=>"#173177"),
						    'remark'=>array("value"=>'濡傛湁鐤戦棶锛岃鎾墦鎴戜滑鐨�400鍙风爜',"color"=>"#173177")
						);
						$WeChat->sendTemplateMessage($tjuserInfo['openid'], 'hCBHN8IiQTsTLow1pLCxUBnhWH5lgomLpf9SCBo5pwI', $data);
					}
                }else{
					//鍏虫敞浜簅penid
					$openid=$object->FromUserName;
					$userModel=D('user');
					//鏌ユ壘鍏虫敞浜烘槸鍚﹀凡缁忔槸璇ョ郴缁熺殑浼氬憳
					$userinfo = $userModel->where("openid='{$openid}'")->find();
					if(!$userinfo || $userinfo['guanzhu'] == 0){//涓嶆槸宸茬粡瀛樺湪鐨勪細鍛�
						//鑾峰彇绯荤粺閰嶇疆
						$config_data=getSystemConfig(array('app_id','app_secret','app_name','score'),false);
						//鑾峰彇鍏充富浜虹殑寰俊淇℃伅
						$WeChat = new WxAuth($config_data['app_id'],$config_data['app_secret']);
						$newJson=$WeChat->getWxUserInfo($openid);
						
						$userinfo['nickname'] = $newJson['nickname'];
						$userinfo['head_imgurl'] = $newJson['headimgurl'];
						$userinfo['sex'] = $newJson['sex'];
						$userinfo['create_time'] = date('Y-m-d H:i:s');

						if(isset($userinfo['guanzhu']) && $userinfo['guanzhu'] == 0){
							$sql="update ".C('DB_PREFIX')."user set nickname='".$newJson['nickname']."',head_imgurl='".$newJson['headimgurl']."',sex=".$newJson['sex'].",create_time='".date('Y-m-d H:i:s')."',guanzhu=1 where openid='".$newJson['openid']."'";
							$userModel->execute($sql);
						}else{
							$userinfo['openid'] = $newJson['openid'];
							$userinfo['guanzhu'] = 1;
							//娣诲姞浼氬憳璁板綍
							$userinfo['id'] = $userModel->add($userinfo);
						}
					}
				}

                $contentStr="娆㈣繋鎮�,".$userinfo['nickname']."鍏変复".$config_data['app_name'];
                break;
            
            case "unsubscribe":
                //鍙栨秷鍏虫敞浜簅penid
                $openid=$object->FromUserName;
                
                $userModel=D('user');
                //鍙栨秷鍏虫敞浜轰俊鎭�
                $userinfo = $userModel->where("openid='{$openid}'")->find();
                $friends_id=$userinfo['id'];
                //淇敼鍏虫敞浜虹殑鍏虫敞鐘舵��
				$sql="update ".C('DB_PREFIX')."user set guanzhu=0 where openid='".$openid."'";
				$userModel->execute($sql);
				
                $frindsModel = D('friends');
                //鎺ㄨ崘浜轰笌鍏虫敞浜虹敓鎴愭湅鍙嬪叧绯�
                $friends_data=$frindsModel->where("friends_id={$friends_id} and type=1")->find();
                //鍙栨秷鍏虫敞浜虹殑鎺ㄨ崘浜篿d
                $tuijainren_id=$friends_data['user_id'];
                $frindsModel->where("friends_id={$friends_id} and type=1 and user_id={$tuijainren_id}")->delete();
                //鑾峰彇绯荤粺閰嶇疆
                $config_data=getSystemConfig(array('score'),false);
                
                $userModel->where("id={$tuijainren_id}")->setDec('score',$config_data['score']);
                
                $scoreModel= D('score');
                $scoreModel->add(array('user_id'=>$tuijainren_id,'count'=>$config_data['score'],'mode'=>5));
                
                break;
            default:
                $contentStr ='';
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    private function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
    
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        // valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit();
        }
    }
    
    //楠岃瘉寰俊鏈嶅姟鍣�
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		
        $token = $this->TOKEN;
        $tmpArr = array(
            $token,
            $timestamp,
            $nonce
        );
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}