<?php

namespace app\api\controller;

use addons\Qiniumg\Qiniumg;
use app\common\controller\Api;
use Qiniu\Storage\BucketManager;
use think\Request;
use Qiniu\Auth;
use Yansongda\Pay\Pay;
use addons\epay\library\Service;


class Qiniu extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    private $bucket = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        require ADDON_PATH . 'qiniumg/library/qiniu-sdk/autoload.php';
        $config = get_addon_config('qiniumg'); //dump($config); exit;

        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        $this->bucket = $config['bucket'];
        $this->Qiniu = new Auth($accessKey, $secretKey);
        $this->BucketManager = new BucketManager($this->Qiniu ,$config);
    }

    public function test(){
        $this->getVideoCover();
    }


    function getVideoCover() {
        $file = 'http://yanyuqiniu.0791jr.com/uploads/20191030/lqFYnjSRWP4oVm-UPCEI98hr0Bb2.mp4';
        $time = $time ? $time : '1'; 		//默认截取第一秒第一帧
        $size = $size ? $size : '348*470';
        $fileName = '11';

        //临时视频路径，生成截图后删除
        $dir = '/public/';
        $tempfiles = $dir.$fileName.'.mp4';
        $bool = move_uploaded_file($file, $tempfiles);
        $str = "ffmpeg -i ".$tempfiles." -y -f mjpeg -ss ".$time." -t 0.001 -s $size ".$dir.$fileName.'.jpg';
        exec($str,$out,$status);
         dump($fileName);exit;
    }


    public function index()
    {

        $pay = new Service();
        dump($pay::getConfig('wechat')); exit;
        /*
         * @param $amount 订单金额 单位：元
         * @param $orderid 订单号
         * @param $type  类型  支付宝：alipay  微信:wechat
         * @param $title  商品名称
         * @param $notifyurl 回调地址
         * @param $returnurl 网页支付回调
         * @param $method 支付方式：web、wap、app、scan、pos、mp，miniapp
         * */
        \addons\epay\library\Service::submitOrder("99.9", "1111323", "wechat", "订单标题", "回调地址", "返回地址", "app");

    }




    /*
     * 上传单张图片
     * */
    public function uploadSinglePicture()
    {
        $base_path = "./uploads/head/"; //接收文件目录
        $target_path = $base_path . ($_FILES['uploadedfile']['name']);
        if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
            ob_end_clean(); //清空缓冲区内容

            header("Content-type: application/json"); //设置json响应头
            $this->success('获取成功',str_replace('./', '/', $target_path));
        } else {
            $this->error('上传失败');
        }
    }


    //批量上传图片
    public function uploadMultiplePicture()
    {
        $base_path = "./uploads/pics/"; //接收文件目录
        $counts = count($_FILES);
        $paths = NULL;
        $errors = NULL;
        for ($i = 0; $i < $counts; $i++) {
            $target_path = $base_path . ($_FILES['uploadedfile' . $i]['name']);
            if (move_uploaded_file($_FILES['uploadedfile' . $i]['tmp_name'], $target_path)) {
                $paths[] = $target_path;
            } else {
                $errors[] = 'uploadedfile' . $i;
            }
        }
        ob_end_clean();  //清空缓冲区内容
        header("Content-type: application/json"); //设置json响应头
        if ($paths != '') {
            $this->success('获取成功',$paths);
        } else {
            $this->error('获取失败');
        }
    }


    /**
     * 获取七牛上传的token
     */
    public function get_qiniu_token()
    {
        $result = $this->BucketManager->rename('yanyu' , 'uploads/20191207/lim70rmP5UgI4zVaT7vgm0OLiTIm.apk' ,'uploads/20191207/yanyu.apk');
        dump($result); exit;
        //生成要上传的token
        $token = $this->Qiniu->uploadToken($this->bucket);
        if ($token) {
            $this->success('获取成功',$token);
        } else {
            $this->error('获取失败');
        }
    }
}