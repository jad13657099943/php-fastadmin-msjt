<?php

namespace app\common\model;

use fast\Random;
use think\Model;


/**
 * 会员模型
 */
class User Extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    protected $insert = ['invitation_code'];

    //注册时自动写入邀请码
    protected function setInvitationCodeAttr()
    {
        return strtoupper(Random::alnum(8));
    }

    /**
     * 添加购买过的会员套餐
     * @param $goodsId
     */
    public function addByVipGoods($goodsId)
    {
        if ($this->buy_vip_goods) {
            $this->buy_vip_goods .= ",$goodsId";
        } else {
            $this->buy_vip_goods = $goodsId;
        }
        $this->save();
    }

    /**
     * 获取个人URL
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {

        return "/u/" . $data['id'];
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, TRUE));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int $money 余额
     * @param int $user_id 会员ID
     * @param string $memo 备注
     */
    public static function money($money, $user_id, $memo, $type,$order_id)
    {
        $user = self::get($user_id);
        if ($user) {
            $before = $user->money;
            if ($type == 70 ){
                $after = $user->money + $money;
            }elseif ($type == 20){
                $after = $user->money - $money;
            }else{
                $after = $user->money + $money;
            }
            //更新会员信息
            $user->save(['money' => $after]);
            //写入日志
            MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo, 'type' => $type,'order_id'=>$order_id]);
        }
    }

    /**
     * 变更会员积分
     * @param int $score 积分
     * @param int $user_id 会员ID
     * @param string $memo 备注
     */
    public static function score($score, $user_id, $memo, $type = 0)
    {
        $user = self::get($user_id);
        if ($user) {
            $before = $user->score;
            $after = $type == 0 ? $user->score - $score : $user->score + $score;
//            $level = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after]);
            //写入日志
            ScoreLog::create(['user_id' => $user_id, 'score' => $type == 0 ? $score : $score, 'before' => $before, 'after' => $after, 'memo' => $memo, 'type' => $type]);
        }
        return $user;
    }

    /**
     * 变更会员余额
     * @param int $money 余额
     * @param int $user_id 会员ID
     * @param string $memo 备注
     *
     */
    public static function balance($money, $user_id, $type=1)
    {
        $user = self::get($user_id);
        if ($user) {
            $user->money += $money;
            //更新会员信息
            $user->save();
            //写入日志
            $money = $money < 0 ? abs($money) : $money; //去金额的绝对值
            Commission::create(['uid' => $user_id, 'price' => $money, 'type' => $type]);
        }
        return $user;
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }

    /*
     * 获取用户信息
     */
    public function getUserInfo($where, $field = '*')
    {

        $list = $this->field($field)->where($where)->find();
        return $list;
    }


    /*
    * 获取用户信息
    */
    public function find_data($where = [], $field = '*')
    {

        $list = $this->field($field)->where($where)->find();
        return $list;
    }

    /**
     * 获取分页数据
     *
     */
    public function select_page($where = [], $field = "*", $page = 1, $pagesize = 10)
    {

        $list = $this->where($where)->field($field)->limit(($page - 1) * $pagesize, $pagesize)->select();
        return $list;
    }

    /**
     * 获取多条数据
     */
    public function select_data($where = [], $field = "*", $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }

    //修改资料
    public function edit_data($where, $data)
    {
        return $this->where($where)->update($data);
    }


    //修改资料
    public function updata_data($where, $data)
    {
        return $this->where($where)->update($data);
    }

    /**
     * 修改用户信息
     * @param $where
     * @param $data
     * @return User
     */
    public function update_data($where, $data)
    {
        dump($where);dump($data);
        return $this->where($where)->update($data);
    }

    //拼接头像路径

    public function set_img_url($data)
    {
        if ($data) {
            $str = "http";
            foreach ($data as $k => $v) {
                if (stripos($v['avatar'], "ttp") || stripos($v['avatar'], "http")) {
                    $data[$k]['avatar'] = $v['avatar'];
                } else {
                    $data[$k]['avatar'] = $v['avatar'] ? config('item_url') . $v['avatar'] : config('item_url') . '/uploads/20190903/Fpw57TZUNxDBzk3n4OVjkcN65Y1W.png';
                }
            }
        }
        return $data;
    }

    //获取单个字段
    public function getField($where = [], $data)
    {
        return $this->where($where)->value($data);
    }

    //邀请人数增加
    public function invite_num_inc($id)
    {
        return $this->where(['id' => $id])->setInc('invite_num');
    }


    /**
     * 获取头像
     * @param $uid
     * */
    public function getAvatar($uid)
    {
        $avatar = $this->getField(['id' => $uid], 'avatar');
        //检测是否包含http:
        $str = "http";
        if (stripos($avatar, "ttp") || stripos($avatar, "http")) {
            $avatar = $avatar;
        } else {
            $avatar = config('item_url') . $avatar;
        }
        return $avatar;
    }
    
    public function order()
    {
        return $this->hasMany('Litestoreorder','user_id','id');
    }
    
}