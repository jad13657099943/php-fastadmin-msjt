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
namespace TencentCloud\Vod\V20180717\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getWidth() 获取雪碧图中小图的宽度，取值范围： [128, 4096]，单位：px。
 * @method void setWidth(integer $Width) 设置雪碧图中小图的宽度，取值范围： [128, 4096]，单位：px。
 * @method integer getHeight() 获取雪碧图中小图的高度，取值范围： [128, 4096]，单位：px。
 * @method void setHeight(integer $Height) 设置雪碧图中小图的高度，取值范围： [128, 4096]，单位：px。
 * @method string getSampleType() 获取采样类型，取值：
<li>Percent：按百分比。</li>
<li>Time：按时间间隔。</li>
 * @method void setSampleType(string $SampleType) 设置采样类型，取值：
<li>Percent：按百分比。</li>
<li>Time：按时间间隔。</li>
 * @method integer getSampleInterval() 获取采样间隔。
<li>当 SampleType 为 Percent 时，指定采样间隔的百分比。</li>
<li>当 SampleType 为 Time 时，指定采样间隔的时间，单位为秒。</li>
 * @method void setSampleInterval(integer $SampleInterval) 设置采样间隔。
<li>当 SampleType 为 Percent 时，指定采样间隔的百分比。</li>
<li>当 SampleType 为 Time 时，指定采样间隔的时间，单位为秒。</li>
 * @method integer getRowCount() 获取雪碧图中小图的行数。
 * @method void setRowCount(integer $RowCount) 设置雪碧图中小图的行数。
 * @method integer getColumnCount() 获取雪碧图中小图的列数。
 * @method void setColumnCount(integer $ColumnCount) 设置雪碧图中小图的列数。
 * @method string getName() 获取雪碧图模板名称，长度限制：64 个字符。
 * @method void setName(string $Name) 设置雪碧图模板名称，长度限制：64 个字符。
 * @method integer getSubAppId() 获取点播[子应用](/document/product/266/14574) ID。如果要访问子应用中的资源，则将该字段填写为子应用 ID；否则无需填写该字段。
 * @method void setSubAppId(integer $SubAppId) 设置点播[子应用](/document/product/266/14574) ID。如果要访问子应用中的资源，则将该字段填写为子应用 ID；否则无需填写该字段。
 */

/**
 *CreateImageSpriteTemplate请求参数结构体
 */
class CreateImageSpriteTemplateRequest extends AbstractModel
{
    /**
     * @var integer 雪碧图中小图的宽度，取值范围： [128, 4096]，单位：px。
     */
    public $Width;

    /**
     * @var integer 雪碧图中小图的高度，取值范围： [128, 4096]，单位：px。
     */
    public $Height;

    /**
     * @var string 采样类型，取值：
<li>Percent：按百分比。</li>
<li>Time：按时间间隔。</li>
     */
    public $SampleType;

    /**
     * @var integer 采样间隔。
<li>当 SampleType 为 Percent 时，指定采样间隔的百分比。</li>
<li>当 SampleType 为 Time 时，指定采样间隔的时间，单位为秒。</li>
     */
    public $SampleInterval;

    /**
     * @var integer 雪碧图中小图的行数。
     */
    public $RowCount;

    /**
     * @var integer 雪碧图中小图的列数。
     */
    public $ColumnCount;

    /**
     * @var string 雪碧图模板名称，长度限制：64 个字符。
     */
    public $Name;

    /**
     * @var integer 点播[子应用](/document/product/266/14574) ID。如果要访问子应用中的资源，则将该字段填写为子应用 ID；否则无需填写该字段。
     */
    public $SubAppId;
    /**
     * @param integer $Width 雪碧图中小图的宽度，取值范围： [128, 4096]，单位：px。
     * @param integer $Height 雪碧图中小图的高度，取值范围： [128, 4096]，单位：px。
     * @param string $SampleType 采样类型，取值：
<li>Percent：按百分比。</li>
<li>Time：按时间间隔。</li>
     * @param integer $SampleInterval 采样间隔。
<li>当 SampleType 为 Percent 时，指定采样间隔的百分比。</li>
<li>当 SampleType 为 Time 时，指定采样间隔的时间，单位为秒。</li>
     * @param integer $RowCount 雪碧图中小图的行数。
     * @param integer $ColumnCount 雪碧图中小图的列数。
     * @param string $Name 雪碧图模板名称，长度限制：64 个字符。
     * @param integer $SubAppId 点播[子应用](/document/product/266/14574) ID。如果要访问子应用中的资源，则将该字段填写为子应用 ID；否则无需填写该字段。
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
        if (array_key_exists("Width",$param) and $param["Width"] !== null) {
            $this->Width = $param["Width"];
        }

        if (array_key_exists("Height",$param) and $param["Height"] !== null) {
            $this->Height = $param["Height"];
        }

        if (array_key_exists("SampleType",$param) and $param["SampleType"] !== null) {
            $this->SampleType = $param["SampleType"];
        }

        if (array_key_exists("SampleInterval",$param) and $param["SampleInterval"] !== null) {
            $this->SampleInterval = $param["SampleInterval"];
        }

        if (array_key_exists("RowCount",$param) and $param["RowCount"] !== null) {
            $this->RowCount = $param["RowCount"];
        }

        if (array_key_exists("ColumnCount",$param) and $param["ColumnCount"] !== null) {
            $this->ColumnCount = $param["ColumnCount"];
        }

        if (array_key_exists("Name",$param) and $param["Name"] !== null) {
            $this->Name = $param["Name"];
        }

        if (array_key_exists("SubAppId",$param) and $param["SubAppId"] !== null) {
            $this->SubAppId = $param["SubAppId"];
        }
    }
}
