<?php


namespace app\api\services\msjt;


use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\Grade;
use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\model\msjt\Users;
use app\api\services\PublicService;
use app\api\services\traits\Auth;
use app\api\validate\msjt\ApplyValidate;

class UsersService extends PublicService
{
    use Auth;

    /**
     * wx授权登录
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */

    public function wxRegister($params)
    {

        $info = $this->getAccessToken($params['code']);

        $user = $this->getWxUserInfo($info->access_token, $info->openid);


        if ($id = $this->isNull($info->openid)) {

            Users::whereUpdate(['id' => $id], ['head_image' => $user->headimgurl, 'nickname' => $user->nickname]);

        } else {
            $id = Users::whereInsert(['head_image' => $user->headimgurl, 'pid' => $params['pid'] ?? 0, 'nickname' => $user->nickname, 'openid' => $info->openid, 'createtime' => time()]);
        }

        return $this->success('登录成功', ['token' => $this->getToken($id)]);
    }


    /**
     * 设置手机
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function mobile($uid, $params)
    {
        /* $info = $this->getAccessToken($params['code']);
         $data = $this->decryptData($info->session_key, $params['encrytendData'], $params['iv']);*/
        Users::whereUpdate(['id' => $uid], ['mobile' => $params['mobile']]);
        return $this->success('设置');
    }

    /**
     * 验证是否注册
     * @param $openid
     * @return float|mixed|string
     */
    public function isNull($openid)
    {
        return Users::whereValue(['openid' => $openid], 'id');
    }

    /**
     * 用户信息
     * @param $uid
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($uid)
    {
        $info = Users::whereFind(['id' => $uid]);
        $service = new ApplyService();
        $info['apply_status'] = $service->getApplyStatus($uid);
        //$info['grade_text'] = Grade::whereValue(['id' => $info->grade], 'name');
        return $this->success('用户信息', $info);
    }

    /**
     * 修改用户信息
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function set($uid, $params)
    {
        $status = Users::whereUpdate(['id' => $uid], $params);
        return $this->statusReturn($status, '修改成功', '修改失败');
    }

    /**
     * 我的订单
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function order($uid, $params)
    {
        $with = 'goods';
        $where['user_id'] = $uid;
        $where['state'] = 1;
        if (!empty($params['status'])) $where['status'] = $params['status'];
        $list = Order::whereWithPaginate($with, $where, '*', $params['limit'] ?? 10);
        return $this->success('订单', $list);
    }


    /**
     * 订单详情
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = $params['id'];
        $with = 'goods,sale';
        $info = Order::whereWithFind($with, $where);
        foreach ($info['goods'] as $item) {
            $item->info = json_decode($item->info, true);
            $item->sku = json_decode($item->sku, true);
        }
        $info['site'] = json_decode($info['site'], true);
        return $this->success('订单详情', $info);
    }

    /**
     * 取消订单
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = $params['id'];
        $info = Order::whereFind($where);
        if ($info['status'] == 3) $this->error('订单已取消');
        if ($info['status'] != 1) $this->error('已支付,无法取消,请申请售后');
        $status = Order::whereUpdate($where, ['status' => 3]);
        return $this->statusReturn($status, '取消成功', '取消失败');
    }

    /**
     * 我的课程
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function curriculum($uid, $params)
    {
        $where['user_id'] = $uid;
        if (!empty($params['status'])) $where['status'] = $params['status'];
        $where['del_time'] = null;
        $list = CurriculumOrder::wherePaginate($where, '*', $params['limit'] ?? 10);
        foreach ($list as $item) {
            $item->info = json_decode($item->info, true);
        }
        return $this->success('我的课程', $list);
    }

    /**
     * 删除课程订单
     * @param $uid
     * @param $params
     * @return false|string
     */
    public function delCurriculum($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = $params['id'];
        $info = CurriculumOrder::whereFind($where);
        if ($info->status == 1) {
            $status = CurriculumOrder::whereDel($where);
            return $this->statusReturn($status, '删除成功', '删除失败');
        }
        CurriculumOrder::whereUpdate($where, ['del_time' => time()]);
        return $this->success('删除成功');
    }

    /**
     * 取消订单
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelCurriculum($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = $params['id'];
        $info = CurriculumOrder::whereFind($where);
        if ($info->status != 1) $this->error('已支付,无法取消');
        $status = CurriculumOrder::whereDel($where);
        return $this->statusReturn($status, '取消成功', '取消失败');
    }

}