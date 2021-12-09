<?php


namespace app\api\model;


use bar\baz\source_with_namespace;
use think\Model;

class Kernel extends Model
{

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    /**
     * 灵活查询
     * @param $condition
     * @return mixed
     */
    public static function flexibleSql($condition)
    {

        $model = new static();
        $model = self::getCondition($model, $condition);
        $model = self::getEnding($model, $condition);
        return $model;
    }

    /**
     * 条件
     * @param $model
     * @param array $condition
     * @return mixed
     */
    private function getCondition($model, $condition)
    {
        if ($where = $condition['where'] ?? false) {
            $model->where($where);
        }
        if ($with = $condition['with'] ?? false) {
            $model->with($with);
        }
        if ($field = $condition['field'] ?? false) {
            $model->field($field);
        }

        $model->order($condition['order']['field'] ?? 'id', $condition['order']['order'] ?? 'desc');

        return $model;
    }

    /**
     * 结尾
     * @param $model
     * @param $condition
     * @return mixed
     */
    public function getEnding($model, $condition)
    {
        $ending = $condition['ending'];
        if (empty($ending)) {
            return $model;
        }
        if ($ending['type'] == 'find') {
            $sql = $model->find();
        }
        if ($ending['type'] == 'select') {
            $sql = $model->select();
        }
        if ($ending['type'] == 'paginate') {
            $sql = $model->paginate($ending['limit'] ?? 10);
        }
        if ($ending['type'] == 'value') {
            $sql = $model->value($ending['field']);
        }
        if ($ending['type'] == 'column') {
            $sql = $model->column($ending['field']);
        }
        return $sql;
    }

    /**
     * 查询一条
     * @param array $where
     * @param string $filed
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function whereFind($where = [], $field = '*')
    {
        return self::where($where)->field($field)->order('id', 'desc')->find();
    }

    /**
     * 联合查询一条
     * @param $with
     * @param array $where
     * @param string $field
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function whereWithFind($with, $where = [], $field = '*')
    {
        return self::where($where)->with($with)->field($field)->find();
    }

    /**
     * 分页查询
     * @param array $where
     * @param string $field
     * @param int $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function wherePaginate($where = [], $field = '*', $limit = 10, $order = 'id')
    {
        return self::where($where)->field($field)->order($order, 'desc')->paginate($limit);
    }

    /**
     * 分页联合查询
     * @param $with
     * @param array $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function whereWithPaginate($with, $where = [], $field = '*', $limit = 10, $order = 'id')
    {
        return self::where($where)->with($with)->field($field)->order($order, 'desc')->paginate($limit);
    }

    /**
     * 插入
     * @param $data
     * @return int|string
     */
    public static function whereInsert($data)
    {
        $data['createtime'] = time();
        return self::insertGetId($data);
    }

    /**
     * 批量插入
     * @param array $data
     * @return bool|int|string
     */
    public static function AllInsert($data = [])
    {
        return self::insertAll($data);
    }

    /**
     * 查询单个值
     * @param $where
     * @param $value
     * @return float|mixed|string
     */
    public static function whereValue($where = [], $value = '')
    {
        return self::where($where)->value($value);
    }

    /**
     * 更新数据
     * @param array $where
     * @param array $date
     */
    public static function whereUpdate($where = [], $date = [])
    {
        return self::where($where)->update($date);
    }

    /**
     * 查询总数
     * @param array $where
     * @return int|string
     * @throws \think\Exception
     */
    public static function whereCount($where = [])
    {
        return self::where($where)->count();
    }

    /**
     * 查询统计
     * @param array $where
     * @param string $field
     * @return float|int|string
     */
    public static function whereSum($where = [], $field = 'id')
    {
        return self::where($where)->sum($field);
    }

    /**
     * 条件自增
     * @param array $where
     * @param string $field
     * @param int $num
     * @return bool|int|true
     * @throws \think\Exception
     */
    public static function whereSetInc($where = [], $field = '', $step = 1)
    {
        return self::where($where)->setInc($field, $step);
    }

    /**
     * 条件自减
     * @param array $where
     * @param string $field
     * @param int $num
     * @return bool|int|true
     * @throws \think\Exception
     */
    public static function whereSetDec($where = [], $field = '', $step = 1)
    {
        return self::where($where)->setDec($field, $step);
    }

    /**
     * 查询全部
     * @param array $where
     * @param string $field
     * @param string $order
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function whereSelect($where = [], $field = '*', $order = 'id')
    {
        return self::where($where)->field($field)->order($order, 'desc')->select();
    }

    public static function whereWithSelect($with = '', $where = [], $field = '*', $order = 'id')
    {
        return self::with($with)->where($where)->field($field)->order($order, 'desc')->select();
    }

    /**
     * 查询一列
     * @param array $where
     * @param string $field
     * @param string $order
     * @return array|false|string
     */
    public static function whereColumn($where = [], $field = '', $order = 'id')
    {
        return self::where($where)->order($order, 'desc')->column($field);
    }

    /**
     * 删除
     * @param array $where
     * @return false|int
     */
    public static function whereDel($where = [])
    {
        return self::where($where)->delete();
    }

}