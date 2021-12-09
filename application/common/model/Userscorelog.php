<?php

namespace app\common\model;

use think\Model;

class Userscorelog extends Model
{

    // 表名
    protected $name = 'user_score_log';


    /**
     * 添加积分记录
     * @param $user_id 用户id
     * @param $memo 备注
     * @param $score 积分数量
     * @param $type 0减少积分 1增加积分
    */
 /*   public function add_data($user_id , $score , $memo , $type){

        $user_model = new User();
        $before = $user_model->getField(['id' => $user_id],'score');
        $array = [
            'user_id' => $user_id,
            'score' => $score,
            'memo' => $memo,
            'type' => $type,
            'before' => $before,
            'after' => $before + $score,
            'createtime' => time(),
            ];

        return $this->save($array);
    }*/

}