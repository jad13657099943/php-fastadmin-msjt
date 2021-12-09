<?php

namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\School as SchoolModel;

/**
 * 校园专区接口
 */
class School extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];



    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 学校专区首页
     */
    public function school_home(){
        $params = $this->request->request();
        !$params['page'] && $this->error('page不能为空');
        !$params['pagesize'] && $this->error('pagesize不能为空');

        $field = 'id ,school_name ,school_image ,school_desc ,school_freight';
        $list = SchoolModel::getPageList(['status' => 10] ,$field, 'weigh' , $params['page'] , $params['pagesize']);

        $images = model('Cmsblock')->where('id',99)->value('images');
        $top_images = explode(',',$images);
        $this->success("成功", compact('top_images','list'));

    }
}