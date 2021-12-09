<?php

namespace app\admin\model\litestore;

use app\admin\model\coupon\Category;
use app\admin\model\user\Level;
use think\Model;

class Coupon extends Model
{
    // 表名
    protected $name = 'litestore_coupon';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'limit_type_text',
        'receive_start_time_text',
        'receive_end_time_text',
        'use_start_time_text',
        'use_end_time_text',
        'coupon_type_text',
        'get_type_text',
        'is_limit_level_text',
        'user_level_data_text',
        'limit_goods_category_text',
        'limit_goods_text',
        'discount_text'
    ];


    protected static function init()
    {
        self::beforeInsert(function ($data) {

            return $data;
        });

        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            // $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getLimitTypeList()
    {
        return ['timedays' => __('Timedays'), 'timelimit' => __('Timelimit')];
    }

    public function getCouponTypeList()
    {
        return ['deduct' => __('Deduct'), 'discount' => __('Discount')];
    }

    public function getGetTypeList()
    {
        return ['1' => __('Get_type 1'), '0' => __('Get_type 0')];
    }

    public function getIsLimitLevelList()
    {
        return ['0' => __('Is_limit_level 0'), '1' => __('Is_limit_level 1')];
    }

    public function getUserLevelDataList()
    {
        return Level::where(['status' => 'normal'])->column('id,name');
    }

    public function getLimitGoodsCategoryList()
    {
        return ['0' => __('Limit_goods_category 0'), '1' => __('Limit_goods_category 1')];
    }

    public function getLimitGoodsList()
    {
        return ['0' => __('Limit_goods 0'), '1' => __('Limit_goods 1')];
    }

    public function getTypeDataList()
    {
        // return ['newperson' => __('Type_data newperson')];
        return ['general' => __('Type_data general'), 'newperson' => '绑定手机号'];
    }

    //获取器

    public function getDiscountTextAttr($value, $data)
    {
        switch ($data['coupon_type']) {
            case 'discount':
                return '打 ' . $data['discount'] . ' 折';
                break;
            case 'deduct';
                return '优惠 ' . $data['deduct'] . ' 元';
                break;
        }
    }

    public function getLimitTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['limit_type']) ? $data['limit_type'] : '');
        $list = $this->getLimitTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReceiveStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['receive_start_time']) ? $data['receive_start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReceiveEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['receive_end_time']) ? $data['receive_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['use_start_time']) ? $data['use_start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['use_end_time']) ? $data['use_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCouponTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['coupon_type']) ? $data['coupon_type'] : '');
        $list = $this->getCouponTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGetTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['get_type']) ? $data['get_type'] : '');
        $list = $this->getGetTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsLimitLevelTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_limit_level']) ? $data['is_limit_level'] : '');
        $list = $this->getIsLimitLevelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getUserLevelDataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['user_level_data']) ? $data['user_level_data'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getUserLevelDataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getLimitGoodsCategoryTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['limit_goods_category']) ? $data['limit_goods_category'] : '');
        $list = $this->getLimitGoodsCategoryList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getLimitGoodsTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['limit_goods']) ? $data['limit_goods'] : '');
        $list = $this->getLimitGoodsList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setRemainderNumAttr($value, $data)
    {
        return $data['total'];
    }


    protected function setReceiveStartTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    protected function setReceiveEndTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setUseStartTimeAttr($value, $data)
    {
        // TODO 该方案是否合理, 是否可通过时间组件直接处理？
        $use_time_text_arr = explode(' - ', $data['use_time_range']);

        if ($use_time_text_arr[0]) {
            return strtotime($use_time_text_arr[0]);
        }
        return 0;
//        return $value ? strtotime($value) : $value;
    }

    protected function setUseEndTimeAttr($value, $data)
    {
        $use_time_text_arr = explode(' - ', $data['use_time_range']);
        if ($use_time_text_arr[1]) {
            return strtotime($use_time_text_arr[1]);
        }
        return 0;
//        return $value ? strtotime($value) : $value;
    }

    protected function setUserLevelDataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setTypeDataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getCanUseTime($filed = '')
    {
        $times = [
            'use_start_time' => null,
            'use_end_time' => null,
        ];
        if ($this->limit_type == 'timedays') {
            if ($this->timedays) {
                $times['use_start_time'] = time();
                $times['use_end_time'] = time() + $this->timedays * 24 * 3600;
            }
        } else if ($this->limit_type == 'timelimit') {
            if ($this->use_start_time) $times['use_start_time'] = $this->use_start_time;
            if ($this->use_end_time) $times['use_end_time'] = $this->use_end_time;
        }
        if ($filed) {
            return $times[$filed];
        }
        return $times;
    }

    public function addcoupon($params)
    {

        return $this->allowField(true)->save($params);

    }


    public function getIndex($couponId = null)
    {
        $where['is_index'] = 1;
        $couponId && $where['id'] = ['neq', $couponId];
        return self::get($where);
    }
}
