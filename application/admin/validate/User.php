<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|max:50|unique:admin',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'username' => 'URL规则只能是小写字母、数字、下划线和/组成'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username'  => __('username'),
        ];
        $this->message['username'] = __('Name only supports letters, numbers, underscore and slash');
        parent::__construct($rules, $message, $field);
    }
    
}
