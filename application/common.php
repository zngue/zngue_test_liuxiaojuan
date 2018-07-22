<?php
use think\Db;
use taobao\AliSms;
use think\Validate;
use think\Cookie;
use think\Config;
use wechat\WxAuth;

    /***********************************API参数过滤--开始**************************************/
    function getSign($obj) {
        //字典序排序
        ksort($obj);
        //URL键值对拼成字符串
        $buff = "";
        $buff = recursionString($obj,$buff);
        $sign = strtoupper(md5(config('apiSecret').$buff));
        return $sign;
    }
    //递归处理字符串拼接
    function recursionString($param,&$buff){
        foreach ($param as $k => $v) {
            if(is_array($v)){
                recursionString($v,$buff);
            }else{
                $buff .= $k.$v;
            }
        }
        return $buff;
    }
    function paramFilter($param){
        $arr = array();
        foreach ($param as $k => $v) {
             if(is_array($v)){
                $arr2 = array();
                foreach ($v as $kk => $vv) {
                        if(is_array($vv)){
                            $arr3 = array();
                            foreach ($vv as $kkk => $vvv) {
                                $arr3[$kkk] = remove_xss(safe_replace(new_stripslashes(new_addslashes($vvv))));
                            }
                            $arr2[$kk] = $arr3;
                        }else{
                            $arr2[$kk] = remove_xss(safe_replace(new_stripslashes(new_addslashes($vv))));
                        }

                }
                $arr[$k] = $arr2;
            }else{
                $arr[$k] = remove_xss(safe_replace(new_stripslashes(new_addslashes($v))));
            }

        }
        return $arr;
    }
    /**
     * 返回经addslashes处理过的字符串或数组
     * @param $string 需要处理的字符串或数组
     * @return mixed
     */
    function new_addslashes($string){
        if(!is_array($string)) return addslashes($string);
        foreach($string as $key => $val) $string[$key] = new_addslashes($val);
        return $string;
    }

    /**
     * 返回经stripslashes处理过的字符串或数组
     * @param $string 需要处理的字符串或数组
     * @return mixed
     */
    function new_stripslashes($string) {
        if(!is_array($string)) return stripslashes($string);
        foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
        return $string;
    }

    /**
     * 安全过滤函数
     *
     * @param $string
     * @return string
     */
    function safe_replace($string) {
        $string = str_replace('%20','',$string);
        $string = str_replace('%27','',$string);
        $string = str_replace('%2527','',$string);
        $string = str_replace('*','',$string);
        $string = str_replace('"','&quot;',$string);
        $string = str_replace("'",'',$string);
        $string = str_replace('"','',$string);
        $string = str_replace(';','',$string);
        $string = str_replace('<','&lt;',$string);
        $string = str_replace('>','&gt;',$string);
        $string = str_replace("{",'',$string);
        $string = str_replace('}','',$string);
        $string = str_replace('\\','',$string);
        return $string;
    }

    /**
     * xss过滤函数
     *
     * @param $string
     * @return string
     */
    function remove_xss($string) {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

        $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');

        $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

        $parm = array_merge($parm1, $parm2);

        for ($i = 0; $i < sizeof($parm); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($parm[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $parm[$i][$j];
            }
            $pattern .= '/i';
            $string = preg_replace($pattern, ' ', $string);
        }
        return $string;
    }
    //删除空格--待优化
    function trimAll($str){
        $str = trim($str);
        $str = explode(' ', $str);
        $str = implode('', $str);
        return $str;
    }

    /***********************************API参数过滤--结束**************************************/


    /***********************************字符串函数--开始**************************************/
    /**
     * 字符串截取，支持中文和其他编码
    */
    function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
      if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
      elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
          $slice = '';
        }
      } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
      }
      return $suffix ? $slice . '...' : $slice;
    }

    /***********************************字符串函数--结束**************************************/




    /****************************************二维数组操作*************************************/
     //二维数组分页
    function arrPage($arr, $page, $indexinpage) {
        $page = is_int($page) != 0 ? $page : 1; //当前页数
        $indexinpage = is_int($indexinpage) != 0 ? $indexinpage : 5; //每页显示几条
        $newarr = array_slice($arr, ($page - 1) * $indexinpage, $indexinpage);
        return $newarr;
    }
    //二维数组去掉重复值
    function array_unique_fb($array2D){
     foreach ($array2D as $v){
      $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
      $temp[]=$v;
     }
     $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
     foreach ($temp as $k => $v){
      $temp[$k]=explode(',',$v); //再将拆开的数组重新组装
     }
     return $temp;
    }
    //二维数组查找某一个值，并返回
    function searchArray($array,$key1,$value1,$key2="",$value2=""){
        $arr = array();
        foreach($array as $keyp=>$valuep){
            if(empty($key2)||empty($value2)){
                if($valuep[$key1]==$value1){
                    array_push($arr,$valuep);
                }
            }else if(empty($key1)||empty($value1)){
                if($valuep[$key2]==$value2){
                     array_push($arr,$valuep);
                }
            }else {
                if($valuep[$key1]==$value1 && $valuep[$key2]==$value2){
                     array_push($arr,$valuep);
                }
            }

        }
        return $arr;
    }
     /**
     * 根据指定字段排序二维数组，保留原有键值(降序)
     * @param $arr @输入二维数组
     * @param $var @要排序的字段名
     * return array
     */
    function mymArrsort($arr, $var){
        $tmp=array();
        $rst=array();
        foreach($arr as $key=>$trim){
            $tmp[$key] = $trim[$var];
        }
        arsort($tmp);
        $i=0;
        foreach($tmp as $key1=>$trim1){
            $rst[$i] = $arr[$key1];
            $i++;
        }
        return $rst;
    }

    /**
    * @desc从数组随机取几条数据
    * @param $data array数组
    * @param $limit Int条数
    * @return $arr array随机取出来的数据重组的数组
    */
    function getRandInArray($data,$limit){
        // 如果查询条数小于总条数，随机取出查询条数
        if (count($data)>$limit) {
            $temp = array_rand($data, $limit);
            // 如果$this->parm['num']==1,返回$temp非数组，故此处需要判断处理
            if (is_array($temp)) {
                // 重组数组
                foreach ($temp as $val) {
                    $arr[] = $data[$val];
                }
            } else {
                $arr[] = $data[$temp];
            }
        } else {
            $arr = $data;
        }
        return $arr;
    }
    //获取二维数组中某一列的值集合,PHP版本大于5.5
    function getArrayColumn($arr,$field){
        return array_column($arr,$field);
    }
    //两个数组的差集,返回出现在第一个数组$arr1中但其他输入数组$arr2中没有的值
    function getArrayDiff($arr1,$arr2){
      return array_diff($arr1, $arr2);
    }

     /***************************************TP5 Excel ******************************************************/
  /**
   * 导出excel
   * @param $strTable 表格内容
   * @param $filename 文件名
   */
 function writer($header, $data,$name=false,$type = 0) {
   $result = import("PHPExcel",EXTEND_PATH.'PHPExcel');
        //导出
        if(!$name){$name=date("Y-m-d-H-i-s",time());}

        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        //设置表头
        $key = ord("A");
        foreach($header as $v){
            $colum = chr($key);
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth(15);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
            $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray(
            array(
                'alignment' => array(

                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT

                )

            )

        );
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->getRowDimension(1)->setRowHeight(20);
        if(!empty($data)){
           foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value) {// 列写入
                $j = chr($span);
                $objActSheet->getRowDimension($column)->setRowHeight(20);

                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
          }
        }

        $objPHPExcel->getActiveSheet()->setTitle('chen.data');
        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = iconv("utf-8", "gb2312", './static/excel/'.date('Y-m-d_', time()).time().'.xls');
        $saveName = iconv("utf-8", "gb2312", $name.'.xls');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        if ($type == 0) {
            ob_end_clean();
            ob_start();
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$saveName\"");
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        } else {
            $objWriter->save($fileName);
            return $fileName;
        }
    }
    //导入
   function reader($file) {
      $result = import("PHPExcel",EXTEND_PATH.'PHPExcel');
        if (pathinfo($file, PATHINFO_EXTENSION) == 'xls') {
            import("Tools.Excel.PHPExcel.Reader.Excel5");
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } elseif (pathinfo($file, PATHINFO_EXTENSION) == 'xlsx') {
            import("Tools.Excel.PHPExcel.Reader.Excel2007");
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        } else {
            return '路径出错';
        }

        $PHPExcel     = $PHPReader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $allColumn    = $currentSheet->getHighestColumn();
        $allRow       = $currentSheet->getHighestRow();
        for($currentRow = 1; $currentRow <= $allRow; $currentRow++){
            for($currentColumn='A'; $currentColumn <= $allColumn; $currentColumn++){
                $address = $currentColumn.$currentRow;
                $arr[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getCalculatedValue();
                //解决富文本
                if(is_object( $arr[$currentRow][$currentColumn]))
                  $arr[$currentRow][$currentColumn]=  $arr[$currentRow][$currentColumn]->__toString();

            }
        }
        return $arr;
    }





/***************************************TP5 Excel ******************************************************/

    /***********************************其他函数--开始**************************************/
    /**
     * 获取扩展配置
     *
     * @param string $key
     * @param string $flag
     * @return mixed|\think\cache\Driver|boolean|unknown|NULL[]
     *
     */
    function getSystemConfig($key = '', $flag = true)
    {
       $cache_key = 'system-config-' . json_encode($key);
         if (cache($cache_key) && $flag) {
            return cache($cache_key);
        }
        $c = Config::get('database');
        $sql = "SELECT * FROM " . $c['prefix'] . "extend";//表名
        if (is_array($key) && $key) {
            $sql .= " WHERE `key` in " . '(\'' . implode('\',\'', $key) . '\')';
        } else {
            $sql .= $key ? " WHERE `key`='$key'" : '';
        }

        $config_data = db('extend',array(),false)->query($sql);//表名
        if ($key && ! is_array($key)) {
            $temp = json_decode($config_data[0]['value'], true);
            cache($cache_key, $temp);
            return $temp;
        } else {
            $result = array();
            foreach ($config_data as $key => $val) {
                $result[$val['key']] = json_decode($val['value'], true);
            }
           cache($cache_key, $result);
            return $result;
        }
    }
    /**
     * 系统配置
     * @Author   lcx
     * @DateTime 2018-03-09
     * @desc     {string}
     * @param               {string}
     * @param    string     $key     [description]
     * @param    boolean    $flag    [description]
     * @return   [type]              [description]
     */
    function getWebsiteConfig($key = '', $flag = true)
    {
       $cache_key = 'system-conf-' . json_encode($key);
         if (cache($cache_key) && $flag) {
            return cache($cache_key);
        }
        $c = Config::get('database');
        $sql = "SELECT * FROM " . $c['prefix'] . "config";
        if (is_array($key) && $key) {
            $sql .= " WHERE `name` in " . '(\'' . implode('\',\'', $key) . '\')';
        } else {
            $sql .= $key ? " WHERE `name`='$key'" : '';
        }

        $config_data = db('config',array(),false)->query($sql);
        if ($key && ! is_array($key)) {
            $temp = json_decode($config_data[0]['value'], true);
            cache($cache_key, $temp);
            return $temp;
        } else {
            $result = array();
            foreach ($config_data as $key => $val) {
                $result[$val['key']] = json_decode($val['value'], true);
            }
           cache($cache_key, $result);
            return $result;
        }
    }


    /**
     * URL拼接上传图片
     * @param String $url
     */
    function UploadImgPath($url,$type=1){
        $str = "http://";
        $strs = "https://";
        if(strpos($url,$str) === false&&strpos($url,$strs) === false){
            $prefix = config('UPLOAD_PATH');
            //如果是七牛云上传
            if(getSystemConfig('web_conf')['file_upload_type']==1&&$type==1){
                $prefix = getSystemConfig('qiliuyun')['url_prefix'];
            }
            $url = $prefix.$url;
        }
        return $url;
    }
    /**
     * URL拼接头像
     * @param String $url
     */
    function HeadImgPath($url){
        $str = "http://";
        $strs = "https://";
        if(strpos($url,$str) === false&&strpos($url,$strs) === false){
            $url = config('IMAGE_PATH').$url;
        }
        return $url;
    }





















/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'common';
    $callback  = 'app\\'.$module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}


/**
 * 获取配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group=0){
    $list = config('config_group_list');
    return $group?$list[$group]:'';
}

/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type=0){
    $list = config('config_type_list');
    return $list[$type];
}


/**
 * 发送短信(参数：签名,模板（数组）,模板ID，手机号)
 */
function sms($signname='',$param=[],$code='',$phone)
{
    $alisms = new AliSms();
    $result = $alisms->sign($signname)->data($param)->code($code)->send($phone);
    return $result['info'];
}
/**
 * 阿里大于发送短信(手机号码,验证码)
 */
function alisendSms($phone, $yzm)
{
    require EXTEND_PATH.'/alitaobao/TopSdk.php';
    $c = new \TopClient;
    $c->appkey = '23706520';
    $c->secretKey = '3cfe542a3a946e6980fa92e3a1f1fe3f';
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend("123456");
    $req->setSmsType("normal");
    $req->setSmsFreeSignName('乐点');
    $req->setSmsParam("{\"code\":\"".$yzm."\",\"product\":\"乐点验证码\"}");
    $req->setRecNum($phone);
    $req->setSmsTemplateCode("SMS_56555298");
    $resp = $c->execute($req);
    return $resp;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name) {
    $result = false;
    if(is_dir($dir_name)){
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}



//时间格式化1
function formatTime($time) {
    if(!is_numeric($time)){
        $time=strtotime($time);
    }

    $t=time()-$time;
    $f=array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return '<span class="pink">'.$c.'&nbsp;</span>'.$v.'前';
        }
    }
}

//时间格式化2
function pincheTime($time) {
     $today  =  strtotime(date('Y-m-d')); //今天零点
      $here   =  (int)(($time - $today)/86400) ;
      if($here==1){
          return '明天';
      }
      if($here==2) {
          return '后天';
      }
      if($here>=3 && $here<7){
          return $here.'天后';
      }
      if($here>=7 && $here<30){
          return '一周后';
      }
      if($here>=30 && $here<365){
          return '一个月后';
      }
      if($here>=365){
          $r = (int)($here/365).'年后';
          return   $r;
      }
     return '今天';
}

//时间格式化3
function formatTimes($time) {
    $now_time = time();
    $t = $now_time - $time;
    $mon = (int) ($t / (86400 * 30));
    if ($mon >= 1) {
        return '一个月前';
    }
    $day = (int) ($t / 86400);
    if ($day >= 1) {
        return $day . '天前';
    }
    $h = (int) ($t / 3600);
    if ($h >= 1) {
        return $h . '小时前';
    }
    $min = (int) ($t / 60);
    if ($min >= 1) {
        return $min . '分钟前';
    }
    return '刚刚';
}

/**
 * 判断一个字符串是否含有中文
 */
function  isChinese  ($str) {
  
  return preg_match("/[\x7f-\xff]/", $str);
}

/**
 * 打印变量
 * @param  $var 变量名称
 * @return null
 */
 function p($var) {
    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)){
        var_dump(NULL);
    }else {
        echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;opacity:0.9;line-height:18px'>".print_r($var,true).'</pre>';
    }
}
/**
 * @desc 接口数据返回格式
 * @param $data 数值 返回数据
 * @param $code Int  返回码
 * @param $message Sting 文字说明
 * @return json
 * @author lcx 2017/03/13
 */
 function returnJson($code,$message='',$data=''){
    exit(json_encode(array('code' =>$code,'message' =>$message,'data' =>$data)));

 }

 /**
 * @desc 利用guid生成唯一标识,token
 * @return string
 */
function getToken()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
        return $uuid;
    }
}
/**
 * 验证数据
 *
 * @param  $data array 验证数据
 * @param  $rule array 规则
 * @param  $msg  array 提示消息
 */
function checkedParm($data, $rule,$msg=array())
{
    $validate = new Validate($rule,$msg);
    $result = $validate->check($data);
    if (! $result) {
        returnJson(101, $validate->getError());
    }
}
/**
 * 发送验证码
 *
 * @param int $phone 手机号
 * @param int $yzm 验证码
 * @return mixed
 */
function sendSms($phone, $yzm)
{
     $sms_config = getSystemConfig('sms');
    //配置参数
    /*$sms_config['uid']='1036';
    $sms_config['content']='冰侠';
    $sms_config['passwd']='weipaitan123456';*/
    $content = "验证码" . $yzm . "。【" . $sms_config['content'] . "】";
    $gateway = "http://sms.bamikeji.com:8890/mtPort/mt/normal/send?uid=" . $sms_config['uid'] . "&passwd=" . md5($sms_config['passwd']) . "&phonelist=" . $phone . "&content=" . $content;
    $result = file_get_contents($gateway);
    return json_decode($result, true);
}
//发送短信通知
function  sendSmsNotify($phone,$con){
    $sms_config = getSystemConfig('sms');
    $con = trimAll($con);
    $content = $con . "。【" . $sms_config['content'] . "】";
    $gateway = "http://sms.bamikeji.com:8890/mtPort/mt/normal/send?uid=" . $sms_config['uid'] . "&passwd=" . md5($sms_config['passwd']) . "&phonelist=" . $phone . "&content=" . $content;
    $result = file_get_contents($gateway);
    return json_decode($result, true);
}

/**
 * 验证手机号是否正确
 *
 * @author
 *
 * @param INT $mobile
 */
function isMobile($mobile)
{
    return preg_match('#^13[\d]{9}$|^17[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}


/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名
 */
function convert_arr_key($arr, $key_name)
{
  $arr2 = array();
  foreach($arr as $key => $val){
    $arr2[$val[$key_name]] = $val;
  }
  return $arr2;
}

/**
 * 密码加密
 * @param String $password
 * @param String $salt
 */
function toPassword($password, $salt)
{
    $password_code = md5(md5($password . '_' . $salt) . $salt);
    return $password_code;
}
/**
 * 手机归属地查询
 */
function attribution($phone){
    $url = "http://apis.juhe.cn/mobile/get?phone=".$phone."&key=d87d7e35d6ffc35dfb0fc4ac7ae12d9a";
    $result = file_get_contents($url);
    $result = json_decode($result,true);
    return $result['result']['province'];
}


/**
 * 验证邮箱
 * @param String email
 */
function isEmail($email){
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    if(preg_match($pattern,$email)){
        return true;
    }
    return false;
}



/**
 * 发送邮件
 * @param String email
 */
function sendEmail($email = '1010562912@qq.com',$content){
    //发送邮件
    ini_set("magic_quotes_runtime",0);
    $result = import('class',EXTEND_PATH.'/phpmailer/phpmailer','.phpmailer.php');
    $config = config('mail');
    $mail = new \PHPMailer(true);
    $mail->isSMTP();
    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth=true;
    //链接qq域名邮箱的服务器地址
    $mail->Host = $config['host'];
    //设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = $config['security'];
    //设置ssl连接smtp服务器的远程服务器端口号 可选465或587
    $mail->Port = $config['port'];
    //设置smtp的helo消息头 这个可有可无 内容任意
    $mail->Helo = "感谢使用冰侠来了";
    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    $mail->Hostname = '';
    //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
    $mail->CharSet = $config['charset'];
    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = $config['name'];
    //smtp登录的账号 这里填入字符串格式的qq号即可
    $mail->Username = $config['addr'];
    //smtp登录的密码 这里填入“独立密码” 若为设置“独立密码”则填入登录qq的密码 建议设置“独立密码”
    $mail->Password = $config['pass'];
    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $mail->From = $config['addr'];
    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML(true);
    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    $mail->addAddress($email,'冰侠来了');//第一个参数是收件人邮箱地址
    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','晶晶在线用户');
    //添加该邮件的主题
    $mail->Subject = '感谢使用冰侠来了';
    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $mail->Body = $content;
    //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    //$mail->addAttachment($tempname,$name.'.xls');
    //同样该方法可以多次调用 上传多个附件
    // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

    //发送命令 返回布尔值
    //PS：经过测试，要是收件人不存在，若不出现错误依然返回true 也就是说在发送之前 自己需要些方法实现检测该邮箱是否真实有效
    $status = $mail->send();
    return $status;
}

/*
 * 获取地区列表
 */
function get_region_list(){
    //获取地址列表 缓存读取
   if(!Cookie::has('region_list')){
      $region_list = Db::name('region')->where(array('status'=>1))->select();
       Cookie::set('region_list',$region_list,3600);
    }
   return empty($region_list) ?  Cookie::get('region_list'):$region_list;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @access public
 * @param array $list
 *            要转换的数据集
 * @param string $pid
 *            parent标记字段
 * @param string $level
 *            level标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = & $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = & $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    $parent[$child][] = & $list[$key];
                }
            }
        }
    }
    return $tree;
}




    /**
     * 生成订单号
     */

    function order_sn(){
        return strtoupper(dechex(date('m'))).date('d').substr(time(), - 5).substr(microtime(), 2, 5).sprintf('%02d',rand(0, 99));
    }




    /**
     * 生成团编号
     */
    function team_sn(){
        $team_sn = date('YmdHis').rand(10000000,99999999);
        $team_buy = Db::name('team_buy')->field('id')->where(array('team_sn'=>$team_sn))->find();
        if($team_buy){
           team_sn();
        }else{
           return  $team_sn;
        }
    }

    /**
    * 生成提货码
    */
    function code_cn(){
        $code_sn = rand(10000,99999).rand(10000000,99999999);
        $team_buy = Db::name('verifiers_record')->field('id')->where(array('code'=>$code_sn))->find();
        if($team_buy){
            code_cn();
        }else{
            return  $code_sn;
        }
    }

    //随机截取字符串
    function randStr($str,$num=8){
        //1.获取字符串的长度
        $length = strlen($str)-1;
        //2.字符串截取开始位置
        $start=rand(0,$length-$num);
        //4.随机截取字符串，取其中的一部分字符串
        $data=substr($str, $start,$num);
        return $data;
    }

    /**
     * 写入日志
     * @param unknown $path 生成文件目录
     * @param unknown $lognam 文件名称
     * @param unknown $content 内容
     */

   function insertLog($path,$lognam,$content){
        //创建支付日志目录
        $path = $path;//"../runtime/pay_log/";
        //dump($path);exit;
        if(!is_dir($path)){
            mkdir($path);
        }
        file_put_contents($path."/".date('Y-m-d',time()).'_'.$lognam,date('Y-m-d H:i:s',time())."\t".$content."\r\n",FILE_APPEND);
    }


     /**
     * 递归无限级分类【先序遍历算】，获取任意节点下所有子孩子
     * @param array $arrCate 待排序的数组
     * @param int $parent_id 父级节点
     * @param int $level 层级数
     * @return array $arrTree 排序后的数组
     */
    function getMenuTree($arrCat, $parent_id = 0, $level = 0)
    {
        static  $arrTree = array(); //使用static代替global
        if( empty($arrCat)) return FALSE;
        $level++;
        foreach($arrCat as $key => $value)
        {
            if($value['pid' ] == $parent_id)
            {
                $value[ 'level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); //注销当前节点数据，减少已无用的遍历
                getMenuTree($arrCat, $value[ 'id'], $level);
            }
        }

        return $arrTree;
    }



    //获取商城名称
    function getAppName(){
        $config =getSystemConfig('wx_config');
        return $config['app_name'];
    }
    //钱的格式，两位小数
    function moneyFormat($money){
       return sprintf('%.2f',$money);
    }
    //获取商品二维码
    function getQrcode(){
        //二维码
        $pic = Db::name('ad')->where(array('status'=>1,'type'=>3))->limit(1)->order('create_time desc')->value('path');
        if($pic){
            $pic = config('SN_SRC').$pic;
        }

        return $pic;
    }
    //获取微信SignPackage
    function getWxSignPackage(){
        //读取微信配置
        $wxconfig =getSystemConfig('wx_config');
        //调用微信分享接口
        $WxAuth = new WxAuth($wxconfig['app_id'], $wxconfig['app_secret']);
        $SignPackage = $WxAuth->getSignPackage();
        return $SignPackage;
    }
    //获取客服电话
    function getServiceTel(){
        $config =getSystemConfig('service_tel');
        return $config['tel'];
    }
    //日期时间格式
    function dateFormat($time){
        return date('Y-m-d H:i:s',$time);
    }
    // 日期格式
    function dateFormats($time){
        return date('Y-m-d',$time);
    }
    //获取IP
    function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');

        }
        elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
     /**
     *生成优惠券码
     */
    function generate_promotion_code($code_length = 6)
    {
        $characters = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $code = "";
        for ($i = 0; $i < $code_length; $i ++) {
            $code .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        //$code =microtime().$code;
        return $code;
    }
    /**
     * 数组数据加密并编译为字符串
     * @param array $Arr
     */
    function EncodeArr($Arr) {
        $String = "";
        if (is_array($Arr)) {
            $String = base64_encode(serialize($Arr));
        } else {
            $String = "ST:" . base64_encode($Arr);
        }
        return $String;
    }

    /**
     * 数据数据解密并还原为数组
     * @param string $String
     */
    function DecodeArr($String) {
        $Arr = array();
        if (!empty($String)) {

            if (preg_match('/^ST\:/', $String)) {
                $Arr = base64_decode(substr($String, 2));
            } else {
                $Row = @unserialize(base64_decode($String));
                if (!empty($Row) && is_array($Row)) {
                    $Arr = $Row;
                }
            }
        }
        return $Arr;
    }

    //model select返回数组格式数据
   function selectArray($array)
   {
       if (empty($array) || !count($array)) {
           return false;
       }
       foreach ($array as $value) {
           $datarray[] = $value->toArray();
       }
       return $datarray;
   }
   //model find返回数组格式数据
   function findArray($array){
       if(!empty($array)){
            return $array->toArray();
       }
       return false;
   }
    /**
   * 生成二维码图片
   * @Author   cqh
   * @DateTime 2017-10-12
   * @param  [type] $url N
   * @param  filename_prefix 文件夹前缀 N
   * @return [type]
   */
  function createQrcodeImg($url="",$filename_prefix="",$logo=false)
  {
    require (EXTEND_PATH.'/PHPQrcode/phpqrcode/phpqrcode.php');
      $errorCorrectionLevel = 'L';    //容错级别
      $matrixPointSize = 5;           //生成图片大小
      //生成二维码图片
      $date = date('Ymd') . '/';
      $path = './uploads/qrcode/' . $date;
      file_exists($path) || mkdir($path, 0777);
      $file = $filename_prefix.uniqid().mt_rand(100000, 999999) . '.png';
      $filename = $path.$file;
      QRcode::png($url,$filename , $errorCorrectionLevel, $matrixPointSize, 2);
      $logo = $logo;//准备好的logo图片
      $QR = $filename;//已经生成的原始二维码图
      $QR = imagecreatefromstring(file_get_contents($QR));
      if($logo !== false){
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片
        imagepng($QR,$filename);
        $filename = trim($filename,'.');
      return WHOLE_HOST.$filename;

  }
/*
   * 图片处理 base64转url 上传图片
   */
  function convertBaseimg($base)
  {
      $date = date('Ymd') . '/';
      $img_path = './uploads/images/' . $date;
      file_exists($img_path) || mkdir($img_path, 0777);
      $img_file = uniqid().mt_rand(100000, 999999) . '.jpg';
      // 图片数据
     $base = str_replace('data:image/png;base64,', '', $base);
      $base = str_replace('data:image/jpeg;base64,', '', $base);
      $base = str_replace('data:image/jpg;base64,', '', $base);
      $base = str_replace('data:image/gif;base64,', '', $base);
      $base = str_replace(' ', '+', $base);
      $data = base64_decode($base);

      // 保存图片
      $success = file_put_contents($img_path . $img_file, $data);
      // 图片路径
     // $url = "http://" . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) . substr($img_path, 2) . $img_file;
      $url = $date.$img_file;
      return $url;
  }

  /**
  *生成随机字符串
  *@param $codeLen 设置生成的随机数个数
  */
function  str_rand($codeLen='8'){
   // $str="abcdefghijkmnpqrstuvwxyz0123456789ABCDEFGHIGKLMNPQRSTUVWXYZ";//设置被随机采集的字符串
   //$str="0123456789";//设置被随机采集的字符串
  $str="123456789ABCDEFGHIGKLMNPQRSTUVWXYZ";//设置被随机采集的字符串
    $rand="";
    for($i=0; $i<$codeLen-1; $i++){
        $rand .= $str[mt_rand(0, strlen($str)-1)];  //如：随机数为30  则：$str[30]
    }
   return $rand;
}

/**
 * 只保留字符串前6个字符，后用...代替
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    if ($strlen > 6) {
        $firstStr     = mb_substr($user_name, 0, 8, 'utf-8');
        $lastStr     = '...';
        return  $firstStr.$lastStr;
    }else{
        return $user_name;
    }
    
}













