<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\config;
use wxcrypt\WXBizDataCrypt;

class Index extends Controller
{

    protected $parm;
    public function __construct(){
        parent::__construct();
        $param = file_get_contents("php://input");
        $this->parm = json_decode($param,true);//接收传过来的数据

    }

  public function index(){
    echo "sdS";exit;
    $appid = $this->parm['appid'];
    $sessionKey = $this->parm['key'];
    $encryptedData= $this->parm['data'];
    $iv = $this->parm['iv'];

    $pc = new WXBizDataCrypt($appid,$sessionKey);
   $errCode = $pc->decryptData($encryptedData, $iv, $data );


    if ($errCode == 0) {
        print($data . "\n");
    } else {
        print($errCode . "\n");
    }

  }

}
