<?php

namespace app\admin\model;

use app\admin\model\user\Level;
use app\common\model\MoneyLog;
use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

//    protected $jointime = 'jointime';

    protected $withdraw = null;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->withdraw = new \app\common\model\Withdraw();
    }

    public function agent()
    {
        return $this->hasOne('Useragentapply', 'uid', 'id')->where(['store_status' => 1])->order('id desc')->setEagerlyType(0);
    }

    public function withdraw()
    {
        return $this->hasMany('Withdraw', 'uid', 'id', null, 'left');
    }

    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text',
        'total_withdraw',
    ];

    public function getTotalWithdrawAttr($value, $data)
    {
        return number_format($this->withdraw->where(['uid' => $data['id'], 'status' => 2])->sum('money'), 2);
    }

    public function getOriginData()
    {
        return $this->origin;
    }

    protected static function init()
    {
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();
            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
        });


        self::beforeUpdate(function ($row) {
            $changedata = $row->getChangedData();
            if (isset($changedata['balance'])) {
                $origin = $row->getOriginData();
                MoneyLog::create(['user_id' => $row['id'], 'money' => $changedata['balance'] - $origin['balance'], 'before' => $origin['balance'], 'after' => $changedata['balance'], 'memo' => '管理员变更金额']);
            }
        });
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function UserAgentApply()
    {
        return $this->hasOne('Useragentapply', 'uid')->setEagerlyType(0);
    }

    public function searchRecord()
    {
        return $this->hasMany(Searchrecord::class, 'uid');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


    public function adduser($params)
    {

        return $this->allowField(true)->save($params);

    }


    public function select_data($where, $field = '*', $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }

    public function find_data($where, $field = '*', $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }


    /**
     * 用户统计
     * @param $where
     */
    public function userCount($where = [])
    {
        return $this->where($where)->count();
    }


    public function apply()
    {
        return $this->hasOne('Useragentapply', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    

}
