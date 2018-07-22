<?php
namespace app\wap\controller;
use app\wap\model\ShipModel;
use app\wap\model\VoyageModel;
use app\wap\model\RichWholeModel;
use app\wap\model\OrderModel;
use app\wap\model\DetailModel;
use think\Db;

class Problem extends Base
{
	//关于我们
    public function aboutuss()
    {
    	$detail = new DetailModel();
    	$map['id'] = 1;
    	$field = 'contact_us_wap,company_profile_wap,company_environment_wap';
    	$info = $detail->getOneDetail($map,$field);
    	$this->assign('info', $info);
        return $this->fetch();
    }

    //常见问题
    public function question()
    {
    	$detail = new DetailModel();
    	$map['id'] = 1;
    	$field = 'contact_us_wap,common_problems_wap,signing_contract_wap,answer_question_wap,other1_wap,other2_wap,other3_wap,other4_wap';
    	$info = $detail->getOneDetail($map,$field);
    	$this->assign('info', $info);
        return $this->fetch();
    }



}