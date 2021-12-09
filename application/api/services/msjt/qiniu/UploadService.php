<?php

namespace app\api\services\msjt\qiniu;

use app\api\services\CommonService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Env;

class UploadService extends CommonService
{
    public function save($file)
    {
        return $file->move(ROOT_PATH . 'public' . DS . 'uploads/qny');
    }

    public function upload($key, $path)
    {
        $accessKey = Env::get('accessKey');
        $secretKey = Env::get('secretKey');
        $bucket = Env::get('bucket');
        $auth = new Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);
        $uploadMgr = new UploadManager();
        $info = $uploadMgr->putFile($token, $key, $path);
        if (!empty($info[0]['key'])) {
            return $info[0]['key'];
        } else {
            return false;

        }

    }
}