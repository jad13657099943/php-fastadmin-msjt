<?php

namespace app\admin\controller\msjt\curriculum;

use app\api\model\msjt\Users;
use app\common\controller\Backend;

/**
 * 课程订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\msjt\goods\curriculum\Order
     */
    protected $model = null;
    protected $user = null;
    protected $status=[
      1=>'待支付',
      2=>'已支付'
    ];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\msjt\goods\curriculum\Order;
        $this->user = new Users();
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $filter = $this->request->get("filter", '');
            $filter = (array)json_decode($filter, TRUE);
          //  $wheres['status'] = 2;
            if (!empty($filter['user.nickname'])) {
                $nameId = $this->user->where('nickname', 'like', '%' . $filter['user.nickname'] . '%')->column('id');
                $wheres['user_id'] = ['in', $nameId];
            }
            if (!empty($filter['user.mobile'])) {
                $mobileId = $this->user->where('mobile', 'like', '%' . $filter['user.mobile'] . '%')->column('id');
                $wheres['user_id'] = ['in', $mobileId];
                if (!empty($nameId)) {
                    $wheres['user_id'] = ['in', array_intersect($nameId, $mobileId)];
                }
            }
            if (empty($filter['status'])){
                $wheres['status']=2;
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($wheres)
                ->with('user')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    public function info($ids=''){
       $info= $this->model->where('id',$ids)->find();
       $info['info']=json_decode($info['info'],true);
       $info['status']=$this->status[$info['status']];
       $info['createtime']=date('Y-m-d H:i:s',$info['createtime']);
       $info['pay_time']=$info['pay_time']?date('Y-m-d H:i:s',$info['pay_time']):'';
       $this->view->assign('info',$info);
       return $this->view->fetch();
    }

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed $searchfields 快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @param array $fieldwhere 自定义查询条件
     * @return array
     */
    protected function buildparams($fieldwhere = '', $searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", "id");
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $filter = (array)json_decode($filter, TRUE);
        unset($filter['user.nickname'], $filter['user.mobile']);
        $op = (array)json_decode($op, TRUE);//
        unset($op['user.nickname']);
        if ($filter['vip_type'] == 3) {
            $filter['distributor'] = 1;
            $op['distributor'] = "=";
            unset($filter['vip_type'], $op['vip_type']);
        } elseif ($filter['vip_type'] == 1 || $filter['vip_type'] == 2) {
            $filter['distributor'] = 0;
            $op['distributor'] = "=";
        }
        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];

        }


        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);


            switch ($sym) {
                case '=':
                case '!=':
                    if ($k == 'litestoregoods.category_id' && $v == 0) {
                    } else
                        $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue 2;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue 2;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }  //dump($where); exit;

        //自定义查询条件
        //$fieldwhere['field'] 查询字段
        //$fieldwhere['value'] 查询值
        if ($fieldwhere) {
            $where[] = $tableName ? [$tableName . $fieldwhere['field'], '=', $fieldwhere['value']] : [$fieldwhere['field'], '=', $fieldwhere['value']];
        }


        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }

}
