<?php

namespace app\admin\controller;
use app\admin\model\DetailModel;
use think\Db;

class Detail extends Base
{

    /**
     * [网站标题]
     */
    public function title() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,title';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [24小时值班电话]
     */
    public function tel() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,tel';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [公司简介]
     */
    public function profile() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,company_profile_pc,company_profile_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [公司环境]
     */
    public function environment() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,company_environment_pc,company_environment_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [联系我们]
     */
    public function contact() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,contact_us_pc,contact_us_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [预定常见问题]
     */
    public function problems() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,common_problems_pc,common_problems_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [签署旅游合同]
     */
    public function contract() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,signing_contract_pc,signing_contract_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [付款问题解答]
     */
    public function question() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,answer_question_pc,answer_question_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [其他说明]
     */
    public function other() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,other_instructions_pc,other_instructions_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [取消丶改签及退款说明]
     */
    public function other1() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,other1_pc,other1_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [为什么需要提前预定]
     */
    public function other2() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,other2_pc,other2_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [如何进行三峡豪华游轮前期咨询预定]
     */
    public function other3() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,other3_pc,other3_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [三峡豪华游轮基本情况]
     */
    public function other4() { 

        $detail = new DetailModel();
        $map['id'] = 1;
        $field = 'id,other4_pc,other4_wap';
        $info = $detail->getOneDetail($map,$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * [edit_detail 编辑配置]
     
     */
    public function editDetail()
    {
        $detail = new DetailModel();
        $param = input('post.');
        $flag = $detail->editDetail($param);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        
    }

}