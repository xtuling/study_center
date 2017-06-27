<?php
/**
 * 附件操作类
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/6/16
 * Time: 14:49
 */
namespace Common\Common;

use VcySDK\Exception;
use VcySDK\Service;
use VcySDK\Logger;
use VcySDK\Attach as AttachSDK;

class Attach
{

    /**
     * 分页查询最大数据条数
     */
    const LIST_MAX_PAGE_SIZE = 1000;

    /**
     * 文件类型：图片
     */
    const FILE_TYPE_IMAGE = 1;

    /**
     * 文件类型：语音
     */
    const FILE_TYPE_VOICE = 2;

    /**
     * 上传附件状态: 等待中
     */
    const FLAG_WAITING = 1;

    /**
     * 上传附件状态: 失败
     */
    const FLAG_ERROR = -1;

    /**
     * VcySDK 附件操作类
     */
    protected $_attach = null;

    /**
     * 构造方法
     */
    public function __construct()
    {

        $serv = &Service::instance();
        // 类名称冲突，使用完全限定名称调用
        $this->_attach = new AttachSDK($serv);
    }

    /**
     * 实例化
     *
     * @return Attach
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 上传附件
     * @param string $memUid      用户UID (必填)
     * @param int    $atMediaType 文件类型99=普通文件(最大5M)，1=图片(2M)，2=音频(2M)，
     *                            3=视频(10M),总文件大小最大30M,支持类型 (xls,xlsx,jpg,jpeg,png,bmp,gif,mp3,amr,avi,mp4) (必填)
     * @param mixed  $file        文件，支持多个 {必填}
     * @return mixed
     */
    public function uploadAttach($memUid, $atMediaType, $file)
    {

        $params = [
            'memUid' => $memUid,
            'atMediatype' => $atMediaType,
            'file' => $file,
        ];

        return $this->_attach->upload($params, array());
    }

    /**
     * 上传图片
     *
     * @param string $uid   用户ID
     * @param string $wxMid 微信文件ID
     * @return array|bool
     */
    public function uploadImage($uid, $wxMid)
    {

        $params = array(
            'memUid' => $uid,
            'mediaId' => $wxMid,
            'fileType' => self::FILE_TYPE_IMAGE
        );

        return $this->_attach->getMedia($params);
    }

    /**
     * 上传语音
     *
     * @param string $uid      用户ID
     * @param string $wxMid    微信文件ID
     * @param string $callback 回调地址
     * @return boolean
     */
    public function uploadVoice($uid, $wxMid, $callback)
    {

        $params = array(
            'memUid' => $uid,
            'mediaId' => $wxMid,
            'fileType' => self::FILE_TYPE_VOICE,
            'callbackUrl' => $callback
        );

        return $this->_attach->getMedia($params);
    }

    /**
     * 根据附件ID获取附件Url
     *
     * @param string $aid 附件ID
     * @return string
     */
    public function getAttachUrl($aid)
    {

        try {
            $result = $this->_attach->fetch(array('atId' => $aid));
        } catch (Exception $e) {
            Logger::write('附件获取失败, atId:' . $aid);
            return '';
        }

        return $result['atAttachment'];
    }

    /**
     * 根据附件ID获取附件信息
     *
     * @param string $aid 附件ID
     * @return string
     */
    public function getAttachDetail($aid)
    {

        try {
            $result = $this->_attach->fetch(array('atId' => $aid));
        } catch (Exception $e) {
            Logger::write('附件获取失败, atId:' . $aid);
            return '';
        }

        return $result;
    }

    /**
     * 根据附件ID数组 获取附件Url
     * @param array $aids 附件ID数组
     * @return array
     */
    public function listAttachUrl($aids)
    {

        settype($aids, 'array');

        // 查询SDK
        try {
            $result = $this->_attach->listAll(['atIds' => $aids], 1, 9999);
        } catch (Exception $e) {
            Logger::write('附件获取失败, atId:' . var_export($aids, true));
            return [];
        }

        if (empty($result)) {
            return [];
        }

        return array_combine_by_key($result['list'], 'atId');
    }

    /**
     * 生成附件ID的缓存键值
     *
     * @param string $atId 附件ID
     *
     * @return string
     */
    protected function _generateAtIdKey($atId)
    {

        return 'Common.Attach.atId.' . $atId;
    }

    /**
     * 附件上传标记
     *
     * @param string $atId 附件ID
     *
     * @return bool
     */
    public function setWaitingFlag($atId)
    {

        Cache::instance()->set($this->_generateAtIdKey($atId), self::FLAG_WAITING);
        return true;
    }

    /**
     * 清除附件标记
     *
     * @param string $atId 附件ID
     *
     * @return bool
     */
    public function clearFlag($atId)
    {

        Cache::instance()->set($this->_generateAtIdKey($atId), null);
        return true;
    }

    /**
     * 错误标记
     *
     * @param string $atId 附件ID
     *
     * @return bool
     */
    public function setErrorFlag($atId)
    {

        Cache::instance()->set($this->_generateAtIdKey($atId), self::FLAG_ERROR);
        return true;
    }

    /**
     * 获取上传标记
     *
     * @param string $atId 附件ID
     *
     * @return bool
     */
    public function getFlag($atId)
    {

        return Cache::instance()->get($this->_generateAtIdKey($atId));
    }

    /**
     * 根据附件ID获取附件信息
     * @param array $at_ids 附件ID
     * @return array
     */
    public function listAll($at_ids)
    {

        if (!is_array($at_ids) || empty($at_ids)) {
            return [];
        }

        $result = $this->_attach->listAll(['atIds' => $at_ids], 1, self::LIST_MAX_PAGE_SIZE);

        if (is_array($result) && isset($result['list'])) {
            return array_combine_by_key($result['list'], 'atId');
        }

        return [];
    }

    /**
     * 删除UC服务器文件
     * @param array $at_ids 附件ID
     * @return array
     */
    public function deleteFile($at_ids)
    {
        if (!is_array($at_ids) || empty($at_ids)) {
            return [];
        }

        return $this->_attach->deleteFile(['atIds' => $at_ids]);
    }

    /**
     * 更新附件资源权限
     * @param array $conds 更新资源信息
     * @return array
     */
    public function updateAuth($conds)
    {
        if (!is_array($conds) || empty($conds)) {
            return [];
        }

        return $this->_attach->updateAuth($conds);
    }
}
