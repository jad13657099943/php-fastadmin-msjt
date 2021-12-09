<?php


namespace app\api\controller\msjt;


use app\api\services\CommonService;
use app\api\services\msjt\qiniu\UploadService;
use think\Request;


class Upload extends CommonService
{
    public function upload(Request $request, UploadService $service)
    {
        $file = $request->file('file');
        $info = $service->save($file);
        $key = $info->getFilename();
        $path = ROOT_PATH . 'public' . '/uploads/qny/' . $info->getSaveName();
        $qny = $service->upload($key, $path);
        unlink($path);
        if ($qny) {
            return $this->success('上传成功', ['url' => $qny], 1);
        } else {
            $this->error('上传失败');
        }
    }
}