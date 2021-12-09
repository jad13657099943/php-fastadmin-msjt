<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentCloud\Dbbrain\V20191016\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getInstanceId() 获取实例 ID 。
 * @method void setInstanceId(string $InstanceId) 设置实例 ID 。
 * @method integer getEventId() 获取事件 ID 。通过“获取实例诊断历史DescribeDBDiagHistory”获取。
 * @method void setEventId(integer $EventId) 设置事件 ID 。通过“获取实例诊断历史DescribeDBDiagHistory”获取。
 */

/**
 *DescribeDBDiagEvent请求参数结构体
 */
class DescribeDBDiagEventRequest extends AbstractModel
{
    /**
     * @var string 实例 ID 。
     */
    public $InstanceId;

    /**
     * @var integer 事件 ID 。通过“获取实例诊断历史DescribeDBDiagHistory”获取。
     */
    public $EventId;
    /**
     * @param string $InstanceId 实例 ID 。
     * @param integer $EventId 事件 ID 。通过“获取实例诊断历史DescribeDBDiagHistory”获取。
     */
    function __construct()
    {

    }
    /**
     * 内部实现，用户禁止调用
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("InstanceId",$param) and $param["InstanceId"] !== null) {
            $this->InstanceId = $param["InstanceId"];
        }

        if (array_key_exists("EventId",$param) and $param["EventId"] !== null) {
            $this->EventId = $param["EventId"];
        }
    }
}
