<?php

namespace app\api\exception;

use Exception;
use think\exception\Handle;

class ExceptionHandler extends Handle
{
    private $code = 500;

    public function render(Exception $e)
    {
        $data = [
            'msg' => $e->getMessage(),
            'code' => $e->getCode(),
            'data' => ''
        ];
        return json($data, $this->code);
    }
}