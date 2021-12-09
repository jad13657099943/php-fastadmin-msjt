<?php

/**
 * 记入有效访问人数
 */

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo dump($content, true);
    $is_die && die();
}

// 公共助手函数
if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }

}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time 时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }

}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string $url 资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $url = preg_match("/^https?:\/\/(.*)/i", $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
            if (is_bool($domain)) {
                $public = \think\Config::get('view_replace_str.__PUBLIC__');
                $url = rtrim($public, '/') . $url;
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
                    $url = request()->domain() . $url;
                }
            } else {
                $url = $domain . $url;
            }
        }
        return $url;
    }

}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }

}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

}

if (!function_exists('mb_ucfirst')) {

    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items 数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields)
            return $items;
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }

}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var 数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, TRUE);
        }
    }


    function http_attach($image)
    {
        return config('site.iimages_domain') . $image;
    }

//获取订单编号
    function order_sn($type)
    {
        $str = '';
        if ($type == 1) {
            $str = 'A';
        }
        if ($type == 2 || $type == 3) {
            $str = 'D';
        }
        if ($type == 4 || $type == 5 || $type == 6) {
            $str = 'Y';
        }
        if ($type == 7) {
            $str = 'T';
        }
        if ($type == 8) {
            $str = 'Z';
        }
        if ($type == 9) {
            $str = 'C';
        }
        $ordcode = $str . date('ymd') . substr(time(), -3) . substr(microtime(), 2, 5);
        return $ordcode;

    }

    //PHP stdClass Object转array
    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }

    //验证手机号
    function check_mobile($Argv)
    {
        $RegExp = '/^(?:13|15|16|17|18|19)[0-9]{9}$/';
        return preg_match($RegExp, $Argv) ? $Argv : false;
    }

    function str_replace_bank_card($bank_card)
    {
        if (strlen($bank_card) >= 16) {
            return substr($bank_card, 0, 4) . ' **** **** **** ' . substr($bank_card, -4);
        }
    }


    function admin_list($model, $where, $sort, $order, $offset, $limit, $wheres = [])
    {
        $total = $model
            ->where($where)
            ->where($wheres)
            ->order($sort, $order)
            ->count();

        $list = $model
            ->where($where)
            ->where($wheres)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();

        $list = collection($list)->toArray();
        return array("total" => $total, "rows" => $list);
    }


    if (!function_exists('getImgs')) {
        /*
       * 解析图片
       * */
        function getImgs($images)
        {
            $images = explode(',', $images);
            $png = explode('.', $images[0]);
            $string = 'jpg,png,JPG,PNG,gif,GIF';

            if (strstr($string, $png[1])) {
                return ['video' => '', 'image' => \think\Image::setthumb($images[0])];
            } else {
                return ['video' => $images[0], 'image' =>  getVideoCover($images[0])];
            }
        }
    }

    if (!function_exists('getVideoCover')) {
        /*
         * 截取视频第一帧
         * @param  $file   视频文件
         * @param  $time    第几帧
         * @param  $dir     临时目录
         * @param  $size    截图尺寸
         * @param  $fileName 生成文件名称
         * @return url
         */

        function getVideoCover($files, $time = '', $size = '')
        {
            $time = $time ? $time : '1';      //默认截取第一秒第一帧
            $size = $size ? $size : '640*320';
            $dir = ROOT_PATH . 'public';
            $date = date('Ymd');

            //判断是否有路径 没有就创建
            $file = ROOT_PATH . 'public/uploads/' . $date;
            if (!is_dir($file)) {
                mkdir($file, 0755);
            }

            $fileName = '/uploads/' . $date . '/' . time() . rand(111111, 9999999);

            $ffmpeg_file = "/home/www/ffmpeg-4.2.2/ffmpeg";

            $str = $ffmpeg_file . " -i " . $dir . $files . " -ss " . $time . " -t 0.001 -s $size " . $dir . $fileName . '.jpg';
            exec($str);
            return $fileName . '.jpg';
        }
    }


    /*
     * 获取model 根据活动状态
     * limit_discount_id
        cut_down_id
        groupbuy_id
     */
    function getModel($type, $goods_id, $marketing_id, $field)
    {
        $where['goods_id'] = $goods_id;
        switch ($type) {
            case 2:
                $where['limit_discount_id'] = $marketing_id;
                $info = model('Limitdiscountgoods')->find_data($where, $field);//限时抢购
                break;
            case 6:
                $where['cut_down_id'] = $marketing_id;
                $info = model('Cutdowngoods')->find_data($where, $field);;//砍价
                break;
            case 4:
                $where['groupbuy_id'] = $marketing_id;
                $info = model('Groupbuygoods')->find_data($where, $field);//团购
                break;
            default:
                return false;
                break;
        }
        return $info;
    }

    /**
     * 修改版本管理 api文件名称
     * @param downloadurl 老文件名称
     * @param newversion 新版本号
     * use Qiniu\Auth;
     * use Qiniu\Storage\BucketManager;
     * */
    function renames($downloadurl, $newversion)
    {
        $config = get_addon_config('qiniumg');
        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        if ($accessKey && $secretKey) {
            require ADDON_PATH . 'qiniumg/library/qiniu-sdk/autoload.php';
            $Qiniu = new Qiniu\Auth($accessKey, $secretKey);
            $BucketManager = new Qiniu\Storage\BucketManager($Qiniu, $config);
            $newurl = $config['bucket'] . '.' . $newversion . '.apk';
            $BucketManager->rename($config['bucket'], substr($downloadurl, 1), $newurl);
        } else {
            return $downloadurl;
        }
        return $newurl;
    }

    /**
     * 获取随机字符串
     *
     * @param int $length
     * @param string $type
     * @param int $convert
     * @return string
     */
    function random($length = 6, $type = 'string', $convert = 0)
    {
        $config = array(
            'number' => '1234567890',
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if (!isset($config[$type]))
            $type = 'string';
        $string = $config[$type];

        $code = '';
        $strlen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string{mt_rand(0, $strlen)};
        }
        if (!empty($convert)) {
            $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
        }
        return $code;
    }


    //******************************************迅搜搜索封装--start*********************************************//

    /***
     * 第一步配置
     * http://www.xunsearch.com/tools/iniconfig  根据自己需要查询字段 设置ini 内容
     * 第二步 /vendor/hightman/xunsearch/app/ 目录下新建一个 .ini文件   例如 yanyu.ini  这个你这个项目名称就是yanyu
     * 第三步根据需要对接下面函数
     * 一个项目可以新建多个搜索 自己定义
     * */


    /**
     * 添加修改数据导入迅搜文档
     * @param $data     需要导入的数据 字段根据自己配置ini一致  单条数据添加修改
     * @param string $file 配置文件名称
     * @return bool|string
     */
    function xunUpdate($data, $file = 'yanyu')
    {
        try {
            $xs = new \XS($file);
            $doc = new \XSDocument();
            $doc->setFields($data);
            $index = $xs->index;
            $index->update($doc);

            $result = $index->flushIndex();//刷新数据
            return $result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * 搜索 获取搜索提示文字
     * @param $title
     * @param $file
     * return array
     * */
    function prompt_text($title, $file = 'yanyu')
    {
        $xs = new \XS($file);
        $search = $xs->getSearch();
        return $search->getExpandedQuery($title);
    }


    /**
     * 中文分词搜索
     * @param string $title 搜索名称
     * @param bool $is_scws 是否开启中文分词（例如：口袋新世代，拆分成：口袋、新、世代）
     * @param int $limit 搜索结果条数
     * @param string $file ini文件名
     * @return array 返回结果
     * @throws \XSException
     */
    function xunsearchGame($title, $file = 'yanyu', $is_scws = 'true', $limit = 100)
    {

        $xs = new \XS($file);
        if ($is_scws === true) {
            //中文分词
            $tokenizer = new \XSTokenizerScws;
            //词语拆分
            $words = $tokenizer->getTokens($title);
            $where = '';
            //拼接成查询条件（OR）
            foreach ($words as $key => $val) {
                if ($key == 0) {
                    $where = $val;
                } else {
                    $where .= ' OR ' . $val;
                }
            }
        } else {
            $where = $title;
        }
        $result = $xs->search->setQuery($where)
//           ->setSort('id','asc') #按索引排序
            ->setDocOrder(true)#按添加索引排序（升序）
            ->setLimit($limit)
            ->search();
        $xs->search->close();

        /*
         * 下面就是你返回前端数据字段 都是跟配置文件ini 你数据库字段一致
         * */
        foreach ($result as $doc) {
            $arr = array(); //这里字段跟 yanyu.ini 字段一致  /vendor/hightman/xunsearch/app/yanyu.ini
            $arr['goods_id'] = $doc->goods_id;
            $arr['goods_name'] = $doc->goods_name;
            $arr['content'] = $doc->content;
            $arr['image'] = $doc->image;
            $arr['goods_price'] = $doc->goods_price;
            $arr['line_price'] = $doc->line_price;
            $results[] = $arr;
        }
        return $results;
    }

    //**********************************************迅搜搜索封装--end*****************************************//


    //******************************************订单导出--start*********************************************//

    /**
     * 订单导出
     * @param $model
     * @param $where
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    function out($model, $where, $filename)
    {
//        $field = 'id,order_no,total_price,user_id,zf_type,pay_status,pay_time,coupon_price
//                  ,shipper_code,express_no,express_company,freight_price,activity_id,createtime
//                  ,total_num,pay_price,is_status,consignee,reserved_telephone,apply_id
//                  ,order_status,freight_status,freight_time,receipt_status,receipt_time
//                  ,refund_status,apply_after_sale_time,examine_time,refund_money,ship_time
//                  ,total_frequency,current_frequency,remark';
//        $result = $this->model->where($where)->field($field)->order('id desc')->select();
        $result = $model
            ->with(['address', 'goods', 'user'])
            ->where($where)
            ->order('id desc')
            ->select();

//        dump($result[0]['goods']->toArray());
//        die;

//        $filename = "订单数据";
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置表头
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单编号');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', '商品金额');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', '用户昵称');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1', '支付方式');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1', '支付状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1', '支付时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1', '优惠金额');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1', '物流公司编码');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1', '快递单号');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1', '快递公司');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1', '邮费');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', '活动');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1', '下单时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N1', '商品数量');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O1', '订单总支付金额');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P1', '配送方式');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q1', '收货人');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R1', '预留电话');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S1', '提货地区');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T1', '订单状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U1', '发货状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V1', '发货时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W1', '收货状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X1', '收货时间');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y1', '售后');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z1', '申请售后时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AA1', '商家审核时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AB1', '已退款金额');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AC1', '下次发货时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD1', '总发货次数');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE1', '已发货次数');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AF1', '备注');

        //改变此处设置的字体
//        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A2:Q2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A:AF')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中

        //改变此处设置的长度数值
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(75);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(50);

        //输出表格
        $myRow = 1;
        foreach ($result as $key => &$val) {
            $i = $key + 2;//表格是从2开始的
            $myRow += 1;//从1开始的改变样式

            //时间转换
            empty($val['pay_time']) ? $pay_time = '' : $pay_time = date('Y-m-d h:i:s', $val['pay_time']);
            empty($val['createtime']) ? $createtime = '' : $createtime = date('Y-m-d h:i:s', $val['createtime']);
            empty($val['freight_time']) ? $freight_time = '' : $freight_time = date('Y-m-d h:i:s', $val['freight_time']);
            empty($val['receipt_time']) ? $receipt_time = '' : $receipt_time = date('Y-m-d h:i:s', $val['receipt_time']);
            empty($val['examine_time']) ? $examine_time = '' : $examine_time = date('Y-m-d h:i:s', $val['examine_time']);
            empty($val['ship_time']) ? $ship_time = '' : $ship_time = date('Y-m-d h:i:s', $val['ship_time']);
            empty($val['apply_after_sale_time']) ? $apply_after_sale_time = '' : $apply_after_sale_time = date('Y-m-d h:i:s', $val['apply_after_sale_time']);


            //活动
            //配送方式 配送方式1=商品配送 2=商品自提
            if ($val['is_status'] == 1) {
                $val['is_status'] = '商品配送';
                $consignee = $val['address']['name'];
                $reserved_telephone = $val['address']['phone'];
            } else {
                $val['is_status'] = '商品自提';
                $consignee = $val['consignee'];
                $reserved_telephone = $val['reserved_telephone'];
            }

            //支付方式
            switch ($val['zf_type']) {
                case 10:
                    $val['zf_type'] = '支付宝';
                    break;
                case 20:
                    $val['zf_type'] = '微信';
                    break;
                default :
                    $val['zf_type'] = '';
            }

            //支付状态:10=未支付,20=已支付
            switch ($val['pay_status']) {
                case 10:
                    $val['pay_status'] = '未支付';
                    break;
                case 20:
                    $val['pay_status'] = '已支付';
                    break;
                default :
                    $val['pay_status'] = '';
            }

            // 订单状态:0=已取消,10=待付款,20=待发货，30待收货，40待评价，50交易完成 ,60 待分享
            switch ($val['order_status']) {
                case 0:
                    $val['order_status'] = '已取消';
                    break;
                case 10:
                    $val['order_status'] = '待付款';
                    break;
                case 20:
                    $val['order_status'] = '待发货';
                    break;
                case 30:
                    $val['order_status'] = '待收货';
                    break;
                case 40:
                    $val['order_status'] = '待评价';
                    break;
                case 50:
                    $val['order_status'] = '交易完成';
                    break;
                case 60:
                    $val['order_status'] = '待分享';
                    break;
                default :
                    $val['order_status'] = '';
            }

            // 发货状态:10=未发货,20=已发货
            switch ($val['freight_status']) {
                case 10:
                    $val['freight_status'] = '未发货';
                    break;
                case 20:
                    $val['freight_status'] = '已发货';
                    break;
                default :
                    $val['freight_status'] = '';
            }

            // 收货状态:10=未收货,20=已收货
            switch ($val['receipt_status']) {
                case 10:
                    $val['receipt_status'] = '未收货';
                    break;
                case 20:
                    $val['receipt_status'] = '已收货';
                    break;
                default :
                    $val['receipt_status'] = '';
            }

            //售后 10）申请中 20）同意 30)不同意 40)不是售后订单
            switch ($val['refund_status']) {
                case 10:
                    $val['refund_status'] = '申请中';
                    break;
                case 20:
                    $val['refund_status'] = '同意';
                    break;
                case 30:
                    $val['refund_status'] = '不同意';
                    break;
                case 40:
                    $val['refund_status'] = '不是售后订单';
                    break;
                default :
                    $val['order_status'] = '';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['order_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val['total_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['user']['nickname']);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $val['zf_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $val['pay_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $pay_time);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $val['coupon_price']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $val['shipper_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $val['express_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $val['express_company']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $val['freight_price']);

            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $val['activity_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $createtime);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $val['total_num']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, $val['pay_price']);

            $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, $val['is_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, $consignee);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, $reserved_telephone);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, $val['address']['site']);

            $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, $val['order_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, $val['freight_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, $freight_time);
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, $val['receipt_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, $receipt_time);

            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, $val['refund_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, $apply_after_sale_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, $examine_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, $val['refund_money']);

            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, $ship_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i, $val['total_frequency']);
            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $i, $val['current_frequency']);
            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $i, $val['remark']);
        }

        //改变此处设置表格样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ' . $myRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);/*垂直居中*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('A1:Az' . $myRow)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*水平居中*/

        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setBold(true);//表头字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setName('微软雅黑');//表头改变字体

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=' . $filename . '.xls');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }


    /**
     * 订单导出
     * @param $model
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    function out2($model, $filename)
    {
        //订单
        $result = $model;
//            ->with(['address', 'goods', 'user'])
//            ->where($where)
//            ->order('id desc')
//            ->select();


//        $filename = "订单数据";
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //合并单元
        $objPHPExcel->getActiveSheet()->mergeCells('A1:AF1');
        $objPHPExcel->getActiveSheet()->mergeCells('AG1:AL1');

        //设置表头
        //订单
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单订单信息');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', '订单编号');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', '商品金额');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', '用户昵称');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', '支付方式');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', '支付状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', '支付时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2', '代理商');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', '优惠券金额');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', '快递单号');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2', '快递公司');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2', '邮费');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2', '活动');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2', '下单时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2', '商品数量');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O2', '订单总支付金额');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P2', '配送方式');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q2', '收货人');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R2', '预留电话');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S2', '提货地区');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T2', '订单状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U2', '发货状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V2', '发货时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W2', '收货状态');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X2', '收货时间');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y2', '售后');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z2', '申请售后时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AA2', '商家审核时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AB2', '已退款金额');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AC2', '下次发货时间');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD2', '总发货次数');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE2', '已发货次数');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AF2', '备注');

        //商品
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG1', '商品信息');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG2', '商品名称');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AH2', '规格');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AI2', '单价');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AJ2', '数量');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AK2', '状态');

        //改变此处设置的长度数值
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A:AZ')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(75);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(15);

        //输出表格
//        $myRow = 2;
        $i = 3;
        $user = model('User');
        foreach ($result as $key => &$val) {

            //时间转换
            empty($val['pay_time']) ? $pay_time = '' : $pay_time = date('Y-m-d h:i:s', $val['pay_time']);
            empty($val['createtime']) ? $createtime = '' : $createtime = date('Y-m-d h:i:s', $val['createtime']);
            empty($val['freight_time']) ? $freight_time = '' : $freight_time = date('Y-m-d h:i:s', $val['freight_time']);
            empty($val['receipt_time']) ? $receipt_time = '' : $receipt_time = date('Y-m-d h:i:s', $val['receipt_time']);
            empty($val['examine_time']) ? $examine_time = '' : $examine_time = date('Y-m-d h:i:s', $val['examine_time']);
            empty($val['ship_time']) ? $ship_time = '' : $ship_time = date('Y-m-d h:i:s', $val['ship_time']);
            empty($val['apply_after_sale_time']) ? $apply_after_sale_time = '' : $apply_after_sale_time = date('Y-m-d h:i:s', $val['apply_after_sale_time']);


            //活动
            //配送方式 配送方式1=商品配送 2=商品自提
            if ($val['is_status'] == 1) {
                $val['is_status'] = '商品配送';
                $consignee = $val['address']['name'];
                $reserved_telephone = $val['address']['phone'];
            } else {
                $val['is_status'] = '商品自提';
                $consignee = $val['consignee'];
                $reserved_telephone = $val['reserved_telephone'];
            }

            //快递单号
            $val['express_no'] = ' ' . $val['express_no'];

            //支付方式
            switch ($val['zf_type']) {
                case 10:
                    $val['zf_type'] = '支付宝';
                    break;
                case 20:
                    $val['zf_type'] = '微信';
                    break;
                default :
                    $val['zf_type'] = '';
            }

            //支付状态:10=未支付,20=已支付
            switch ($val['pay_status']) {
                case 10:
                    $val['pay_status'] = '未支付';
                    break;
                case 20:
                    $val['pay_status'] = '已支付';
                    break;
                default :
                    $val['pay_status'] = '';
            }

            // 订单状态:0=已取消,10=待付款,20=待发货，30待收货，40待评价，50交易完成 ,60 待分享
            switch ($val['order_status']) {
                case 0:
                    $val['order_status'] = '已取消';
                    break;
                case 10:
                    $val['order_status'] = '待付款';
                    break;
                case 20:
                    $val['order_status'] = '待发货';
                    break;
                case 30:
                    $val['order_status'] = '待收货';
                    break;
                case 40:
                    $val['order_status'] = '待评价';
                    break;
                case 50:
                    $val['order_status'] = '交易完成';
                    break;
                case 60:
                    $val['order_status'] = '待分享';
                    break;
                default :
                    $val['order_status'] = '';
            }

            // 发货状态:10=未发货,20=已发货
            switch ($val['freight_status']) {
                case 10:
                    $val['freight_status'] = '未发货';
                    break;
                case 20:
                    $val['freight_status'] = '已发货';
                    break;
                default :
                    $val['freight_status'] = '';
            }

            // 收货状态:10=未收货,20=已收货
            switch ($val['receipt_status']) {
                case 10:
                    $val['receipt_status'] = '未收货';
                    break;
                case 20:
                    $val['receipt_status'] = '已收货';
                    break;
                default :
                    $val['receipt_status'] = '';
            }

            //售后 10）申请中 20）同意 30)不同意 40)不是售后订单
            switch ($val['refund_status']) {
                case 10:
                    $val['refund_status'] = '申请中';
                    break;
                case 20:
                    $val['refund_status'] = '同意';
                    break;
                case 30:
                    $val['refund_status'] = '不同意';
                    break;
                case 40:
                    $val['refund_status'] = '不是售后订单';
                    break;
                default :
                    $val['order_status'] = '';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['order_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val['total_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['user']['nickname']);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $val['zf_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $val['pay_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $pay_time);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $user->where(['id' => $val['user']['pid']])->value('username'));

            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $val['coupon_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $val['express_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $val['express_company']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $val['freight_price']);

            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $val['activity_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $createtime);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $val['total_num']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, $val['pay_price']);

            $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, $val['is_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, $consignee);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, $reserved_telephone);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, $val['address']['site']);

            $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, $val['order_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, $val['freight_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, $freight_time);
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, $val['receipt_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, $receipt_time);

            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, $val['refund_status']);
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, $apply_after_sale_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, $examine_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, $val['refund_money']);

            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, $ship_time);
            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i, $val['total_frequency']);
            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $i, $val['current_frequency']);
            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $i, $val['remark']);

            //商品
            $count = count($val['goods'], 1);
            if ($count > 0) {
                $j = $i - 1;
                foreach ($val['goods'] as $k => $v) {

                    //状态 0）正常 1）退款中 2)退款完成 3)退款失败
                    switch ($v['is_refund']) {
                        case 0:
                            $v['is_refund'] = '正常';
                            break;
                        case 1:
                            $v['is_refund'] = '退款中';
                            break;
                        case 2:
                            $v['is_refund'] = '退款完成';
                            break;
                        case 3:
                            $v['is_refund'] = '退款失败';
                            break;
                    }

                    $j += 1;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG' . $j, $v['goods_name']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AH' . $j, $v['key_name']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AI' . $j, $v['goods_price']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AJ' . $j, $v['total_num']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AK' . $j, $v['is_refund']);
                }

                //合并订单表格
                if ($count > 1) {
                    $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF');
                    foreach ($array as $v)
                        $objPHPExcel->getActiveSheet()->mergeCells($v . $i . ':' . $v . $j);
                }

//                //设置下边框
//                $style_array = array(
//                    'borders' => array(
//                        'bottom' => array(
//                            'style' => \PHPExcel_Style_Border::BORDER_THIN
//                        )
//                    ));
//                $objPHPExcel->getActiveSheet()->getStyle('A2:AK2')->applyFromArray($style_array);
//                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':AK' . $j)->applyFromArray($style_array);
            }
            $i = $j + 1;
        }

//        //设置右边框
//        $style_array = array(
//            'borders' => array(
//                'right' => array(
//                    'style' => \PHPExcel_Style_Border::BORDER_THIN
//                )
//            ));
//        $objPHPExcel->getActiveSheet()->getStyle('AF1:AF' . $j)->applyFromArray($style_array);

        //改变此处设置表格样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ' . $i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);/*垂直居中*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('A2:Az' . $i)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*水平居中*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('AG3:AG' . $i)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_LEFT);/*水平左对齐*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('AG1')->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*水平居中*/

        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setSize(14);//表头字体大小
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ2')->getFont()->setBold(true);//表头字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ2')->getFont()->setName('微软雅黑');//表头改变字体

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=' . $filename . '.xls');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    //**********************************************订单导出--end*****************************************//


    /**
     * 科目段位 课程分类 教师列表
     * @return array
     */
    function courseAdnSubject_list()
    {
        $Tree = new fast\Tree();
        $teacher_model = model('Teacher');
        $course_category_model = model('Coursecategory');
        $subject_category_model = model('Dansubjectcategory');

        $tree = $Tree::instance();
        $subject_category_list = $course_category_list = [0 => ['type' => 'all', 'name' => __('None')]];
        $teacher_list = [0 => ['type' => 'all', 'nickname' => __('None')]];

        //科目段位
        $tree->init(collection($subject_category_model->order('weigh desc,id desc')->select())->toArray(), 'pid');

        $category_list = $tree->getTreeList($tree->getTreeArray(0), 'name');
        foreach ($category_list as $k => $v) {
            $subject_category_list[$v['id']] = $v;
        }

        //课程分类
        $tree->init(collection($course_category_model->order('weigh desc,id desc')->select())->toArray(), 'pid');
        $category_list = $tree->getTreeList($tree->getTreeArray(0), 'name');
        foreach ($category_list as $k => $v) {
            $course_category_list[$v['id']] = $v;
        }
        //教师列表
        $category_list = $teacher_model->where(['status' => '10'])->field('id,nickname')->select()->toArray();
        foreach ($category_list as $k => $v) {
            $teacher_list[$v['id']] = $v;
        }
        return [
            'subject' => $subject_category_list,
            'course' => $course_category_list,
            'teacher_list' => $teacher_list
        ];
    }
}

if (!function_exists('getAccessToken')) {
    /**
     * 获取accessToken
     */
    function getAccessToken()
    {
        $accessToken = \think\Cache::get('accessToken');
        if (!$accessToken) {
            $config = \addons\epay\library\Service::getConfig()['wechat'];
            $params = [
                'grant_type' => 'client_credential',
                'appid' => $config['miniapp_id'],
                'secret' => $config['app_secret'],
            ];
            $result = json_decode(\fast\Http::get('https://api.weixin.qq.com/cgi-bin/token', $params), true);

            if ($result['errcode'] == 0) {
                $accessToken = $result['access_token'];
                \think\Cache::set('accessToken', $accessToken, $result['expires_in']);
            } else {
                return false;
            }
        }
        return $accessToken;
    }
}