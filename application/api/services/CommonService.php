<?php


namespace app\api\services;

use think\Exception;
use think\Request;

class CommonService
{


    /**
     * 查询数组对象键指定值
     * @param $array
     * @param $key
     * @param $value
     * @return false|mixed
     */
    protected function getKeyValue($array, $key, $value)
    {
        if (!is_array($array)) {
            $array = json_decode($array, true);
        }
        if (count($array) < 1) {
            return false;
        }
        foreach (array_keys($array) as $item) {
            if ($array[$item][$key] == $value) {
                return json_encode($array[$item]);
            }
        }
    }

    /**
     * 随机数
     * @return int
     */
    protected function code($type = 1)
    {
        if ($type == 1) {
            return rand('100000', '999999');
        }
        if ($type == 'debug') {
            return 123456;
        }
    }

    /**
     * 验证两次是否相同
     * @param $one
     * @param $two
     * @param string $msg
     * @return bool
     * @throws Exception
     */
    protected function isSame($one, $two, $msg = '')
    {
        if ($one != $two) {
            throw new Exception($msg);
        }
        return true;
    }

    /**
     * 操作成功返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 200)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Allow-Methods: POST,GET,PUT,DELETE");
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];

        return json_encode($result);
    }


    /**
     * 操作失败返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $code = 500)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header('Access-Control-Allow-Methods: POST,GET,PUT,DELETE');
        throw new \think\Exception($msg,$code);
    }


    /**
     * 验证返回
     * @param $status
     * @param string $success
     * @param string $error
     * @param null $data
     * @return false|string
     * @throws \think\Exception
     */
    protected function statusReturn($status, $success = '', $error = '', $data = null)
    {
        if ($status) {
            return $this->success($success, $data);
        } else {
            $this->error($error);
        }
    }

    /**
     * tp分页
     */
    protected function jsonPaginate($list)
    {
        return json_encode(['list' => $list->items(), 'total' => $list->total()]);
    }


    /**
     * 加密
     */
    protected function md5Password($password)
    {
        return md5('JAD' . $password);
    }


    /**
     * 订单号
     */
    protected function orderNo($no)
    {
        $osn = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return $no . $osn;
    }


    /**
     * get
     */
    protected function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

    /**
     * 导出
     * @param array $titles
     * @param array $list
     */
    protected function export($titles = [], $list = [], $title = '')
    {
        ob_get_clean();
        ob_start();
        echo implode("\t", $titles), "\n";
        foreach ($list as $key => $item) {
            echo implode("\t", $item), "\n";
        }
        header('Content-Disposition: attachment; filename=' . $title . date('YmdHis') . '.xls');
        header('Accept-Ranges:bytes');
        header('Content-Length:' . ob_get_length());
        header('Content-Type:application/vnd.ms-excel');
        ob_end_flush();
        die;
    }

}