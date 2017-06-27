<?php
/**
 * FileConvert.class.php
 * 文件转换接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhonglei
 * @version    1.0.0
 */
namespace VcySDK;

class FileConvert
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * SERVICE 类
     *
     * @var null
     */
    private $service = null;

    /**
     * 文件转换
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CONVERT_URL = '%s/dcc/convert';

    /**
     * 获取转换信息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const GET_URL = '%s/dcc/get';

    /**
     * 获取腾讯云视频播放地址
     * %s = {apiUrl}/s/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const VOD_PLAY_URL = '%s/qcloud/getVodPlayUrl';

    /**
     * 获取签名
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const GET_SIGNATURE = '%s/qcloud/getSignature';

    /**
     * 新增视频转码文件回调数据
     * %s = {apiUrl}/{enumber}
     *
     * @var string
     */
    const ADD_TRANSCODE_URL = '%s/transcode/add';

    /**
     * 转换类型：html
     */
    const CONVERT_TYPE_HTML = 1;

    /**
     * 转换类型：图片
     */
    const CONVERT_TYPE_IMAGE = 2;

    /**
     * 转换类型：pdf
     */
    const CONVERT_TYPE_PDF = 3;

    /**
     * 转换状态：成功
     */
    const CONVERT_STATUS_SUCCESS = 0;

    /**
     * 转换清晰度：标清
     */
    const CONVERT_IS_HIGH_FALSE = 1;

    /**
     * 转换清晰度：高清
     */
    const CONVERT_IS_HIGH_TRUE = 2;

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct($serv)
    {
        $this->serv = $serv;
        $this->service = new Service();
    }

    /**
     * 文件转换
     * @param array $params 请求参数
     *        + string memUid 用户ID
     *        + array atIds 文件ID数组
     *        + int convertType 转换类型（1=html；2=图片；3=pdf）
     *        + int high 是否转高清版本(只有html才支持，默认标清); 1=标清，2=高清
     *        + string caNotifyUrl 文件转换成功之后回调地址，只有设置了url，UC才会回调(注： 回调给业务端的参数和查询接口2（根据atId获取指定的文件转换信息），返回的数据一致)
     * @return array
     */
    public function convert($params)
    {
        return $this->serv->postSDK(self::CONVERT_URL, $params, 'generateConvertUrl');
    }

    /**
     * 获取转换信息
     * @param string $at_id 文件ID
     * @return array
     */
    public function get($at_id)
    {
        return $this->serv->postSDK(self::GET_URL, ['atId' => $at_id], 'generateConvertUrl');
    }

    /**
     * 获取腾讯云视频播放地址
     * @param string $at_id 文件ID
     * @return array
     */
    public function getVodPlayUrl($at_id)
    {
        return $this->serv->postSDK(self::VOD_PLAY_URL, ['fileId' => $at_id], 'generateApiUrls');
    }

    /**
     * 获取签名
     * @param $condition
     *          + args String 必填 签名参数
     * @return array|bool
     */
    public function getSignature($condition)
    {

        return $this->serv->postSDK(self::GET_SIGNATURE, $condition, 'generateApiUrlS');
    }

    /**
     * 新增视频转码文件回调数据.
     *
     * @param $condition
     *          + args String 必填 签名参数
     * @return array|bool
     */
    public function add($condition)
    {
        // 特殊地址, 修改UC地址
        $this->serv->getApiUrl($url);
        $this->serv->setConfig(['apiUrl' => cfg('UC_REST_APIURL')]);

        $sdkResult = $this->serv->postSDK(self::ADD_TRANSCODE_URL, $condition, 'generateApiUrlA');

        // 修改回UC地址
        $this->serv->setConfig(['apiUrl' => $url]);

        return $sdkResult;
    }
}