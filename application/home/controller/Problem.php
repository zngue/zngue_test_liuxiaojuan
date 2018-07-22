<?php
namespace app\home\controller;
use app\home\model\ShipModel;
use app\home\model\VoyageModel;
use app\home\model\RichWholeModel;
use app\home\model\OrderModel;
use app\home\model\DetailModel;
use think\Db;

class Problem extends Base
{
    public function problem()
    {
    	$detail = new DetailModel();
    	$map['id'] = 1;
    	$field = 'company_profile_pc,company_environment_pc,contact_us_pc,common_problems_pc,signing_contract_pc,answer_question_pc,other1_pc,other2_pc,other3_pc,other4_pc';
    	$info = $detail->getOneDetail($map,$field);
    	$this->assign('info', $info);
        return $this->fetch();
    }



}