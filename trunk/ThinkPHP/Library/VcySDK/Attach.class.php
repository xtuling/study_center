<?php

/**
 * Attach.class.php
 * 附件接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;
use VcySDK\Error;
use VcySDK\Exception;

class Attach
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
     * 根据MediaID读取附件
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const GET_MEDIA_URL = '%s/media/get';

    /**
     * 根据附件ID获取附件
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const LIST_URL = '%s/attach/list';

    /**
     * 获取指定附件
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const GET_URL = '%s/attach/get';

    /**
     * 上传附件
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const UPLOAD_URL = '%s/upload';

    /**
     * 附件资源权限令牌
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const TOKEN_URL = '%s/attach/token';

    /**
     * 更新附件资源权限
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const UPDATE_AUTH_URL = '%s/attach/update_auth';

    /**
     * 删除文件
     * %s = {apiDomain}/b/{enumber}
     *
     * @var string
     */
    const DELETE_FILE_URL = '%s/attach/deleteFile';

    /**
     * 文件类型-普通文件
     *
     * @var int
     */
    const TYPE_NORMAL = 99;
    
    /**
     * 文件类型-图片
     *
     * @var int
     */
    const TYPE_IMG = 1;
    
    /**
     * 文件类型-音频
     *
     * @var int
     */
    const TYPE_AUDIO = 2;
    
    /**
     * 文件类型-视频
     *
     * @var int
     */
    const TYPE_VIDEO = 3;

    /**
     * 是否需要权限认证：不需要
     *
     * @var int
     */
    const AUTH_REQUIRED_FALSE = 0;

    /**
     * 是否需要权限认证：需要
     *
     * @var int
     */
    const AUTH_REQUIRED_TRUE = 1;

    /**
     * 默认附件资源权限：不可见
     *
     * @var int
     */
    const DEFAULT_AUTH_HIDDEN = 0;

    /**
     * 默认附件资源权限：可见
     *
     * @var int
     */
    const DEFAULT_AUTH_VISIBLE = 1;

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
        $this->service = new Service();
    }

    /**
     * 上传附件
     *
     * @param array $params 请求参数;
     *                     + memUid string 用户UID (必填)
     *                     + atMediatype Integer 文件类型99=普通文件(最大5M)，1=图片(2M)，2=音频(2M)，
     *                     3=视频(10M),总文件大小最大30M,支持类型 (xls,xlsx,jpg,jpeg,png,bmp,gif,mp3,amr,avi,mp4) (必填)
     *                     + file File 文件，支持多个 {必填}
     * @param mixed $files 文件
     *
     * @return mixed
     */
    public function upload($params, $files)
    {

        return $this->serv->postSDK(self::UPLOAD_URL, $params, 'generateApiUrlAtt', array(), $files);
    }

    /**
     * 获取微信服务号多媒体信息
     *
     * @param array $params 多媒体文件相关信息
     *                      + memUid string 用户UID(必填)
     *                      + mediaId string 微信公众号媒体文件ID(必填)
     *                      + fileType string 文件类型1=图片，2=音频(必填)
     *                      + callbackUrl string amr语音异步转成mp3，回调地址(文件类型为语音时必填, method: POST)
     *
     * @return boolean
     */
    public function getMedia($params)
    {

        return $this->serv->postSDK(self::GET_MEDIA_URL, $params, 'generateApiUrlA');
    }

    /**
     * 获取指定附件信息
     *
     * @param array $condition 查询条件
     */
    public function fetch($condition)
    {
        return $this->serv->postSDK(self::GET_URL, $condition, 'generateApiUrlAtt');
    }

    /**
     * 获取附件列表
     *
     * @param array $condition 查询条件
     * @param int   $page      页码
     * @param int   $perpage   每页记录数
     * @param array $orders    排序字段
     */
    public function listAll($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {
        // 查询参数
        $this->service->getValue($condition, ['atIds']);
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlAtt');
    }

    /**
     * 获取附件列表
     *
     * @param array $condition 查询条件
     *              + string atId 附件ID
     */
    public function getToken($condition = array())
    {

        return $this->serv->postSDK(self::TOKEN_URL, $condition, 'generateApiUrlAtt');
    }

    /**
     * 更新附件资源权限
     *
     * @param array $condition 查询条件
     *              + string atId 附件ID
     *              + integr atAuthRequired 是否需要权限认证， 0-不需要,1-需要
     *              + string atAuthUrl 业务平台附件资源权限认证地址
     *              + string atDefaultAuth 默认附件资源权限，0-不可见,1-可见; 当atAuthUrl请求不通、超时(例:100ms)等等情况，使用默认权限
     */
    public function updateAuth($condition = array())
    {

        return $this->serv->postSDK(self::UPDATE_AUTH_URL, $condition, 'generateApiUrlAtt');
    }

    /**
     * 删除文件
     *
     * @param array $condition 查询条件
     *              + string atId 附件ID
     */
    public function deleteFile($condition = array())
    {

        return $this->serv->postSDK(self::DELETE_FILE_URL, $condition, 'generateApiUrlAtt');
    }
}
