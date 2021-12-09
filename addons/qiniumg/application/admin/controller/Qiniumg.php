<?php
/**
 * Created by FlyAdmin www.flyadmin.net
 * User: 君君要上天
 * Date: 2018/12/18
 * Time: 13:40
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Request;
use Qiniu\Auth;

class Qiniumg extends Backend
{

    private $qiniuAuth = null;
    private $bucketManager = null;
    private $bucket = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        require ADDON_PATH . 'qiniumg/library/qiniu-sdk/autoload.php';
        $config = get_addon_config('qiniumg');

        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        $this->bucket = $config['bucket'];

        $this->qiniuAuth = new Auth($accessKey, $secretKey);
        $this->bucketManager = new BucketManager($this->qiniuAuth);

        $this->assignconfig('qiniu_cdn', $config['cdn']);
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $marker = $this->request->request('marker', '');
            $prefix = $this->request->request('prefix', '');
            list($ret, $err) = $this->bucketManager->listFiles($this->bucket, $prefix, $marker, 30, '');
            if ($err === null) {
                foreach ($ret['items'] as &$item) {
                    $item['id'] = base64_encode($item['key']);
                    $item['putTime'] = datetime($item['putTime'] / 10000000);
                    $item['fsize'] = round($item['fsize'] / 1024, 2) . ' KB';
                }
                $this->success('', '', $ret);
            } else {
                $this->error($err->message());
            }
        }

        return $this->fetch();
    }

    /**
     * @param string $ids
     * 删除
     */
    public function del($ids = "")
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $keys = [];
        foreach ($ids as $id) {
            $keys[] = base64_decode($id);
        }
        $ops = $this->bucketManager->buildBatchDelete($this->bucket, $keys);
        $result = $this->bucketManager->batch($ops);

        $this->success('删除成功', '', ['ids' => $ids]);
    }

    /**
     * 上传
     */
    public function upload()
    {
        $path_prefix = $this->request->param('path_prefix', '');
        $namerule = $this->request->param('namerule', '');

        $file = $this->request->file('file');

        $path = ROOT_PATH . 'public' . DS . 'uploads';

        if ($namerule) {
            $file->rule($namerule);
        }

        $info = $file->move($path, $namerule ? true : '');
        if ($info) {
            $filepath = $path . DS . $info->getSaveName();

            $upToken = $this->qiniuAuth->uploadToken($this->bucket);

            $uploadMgr = new UploadManager();

            $url = $path_prefix . $info->getSaveName();

            list($ret, $err) = $uploadMgr->putFile($upToken, str_replace('\\', '/', $url), $filepath);
            if ($err !== null) {
                return json([
                    'code' => 0,
                    'msg' => $err
                ]);
            }
            @unlink($filepath);

            list($fileInfo, $err) = $this->bucketManager->stat($this->bucket, $ret['key']);

            return json([
                'code' => 1,
                'msg' => '上传成功',
                'data' => [
                    'key' => $ret['key'],
                    'id' => base64_encode($ret['key']),
                    'putTime' => datetime(time()),
                    'fsize' => round($fileInfo['fsize'] / 1024, 2) . ' KB',
                    'mimeType' => $fileInfo['mimeType'],
                    'type' => $fileInfo['type'],
                    'status' => 0
                ]
            ]);
        } else {
            return json([
                'code' => 0,
                'msg' => $info->getError()
            ]);
        }
    }

    /**
     * @param $ids
     * 切换存储类型
     */
    public function changetype($ids)
    {
        $key = base64_decode($ids);
        $type = $this->request->param('type');
        if (!in_array($type, [0, 1])) {
            $this->error('参数错误');
        }
        $err = $this->bucketManager->changeType($this->bucket, $key, $type);
        if ($err) {
            $this->error($err->message());
        } else {
            $this->success('操作成功', '', ['key' => $ids]);
        }

    }

    public function rename($id)
    {
        $srckey = base64_decode($id);
        $deskey = $this->request->param('key');
        if (!$srckey || !$deskey) {
            $this->error('参数错误');
        }
        $err = $this->bucketManager->move($this->bucket, $srckey, $this->bucket, $deskey);
        if ($err) {
            $this->error($err->message());
        } else {
            $this->success('重命名成功', '', ['id' => base64_encode($deskey)]);
        }
    }
}