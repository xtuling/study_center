<?php
/**
 * Attachment.php
 * 附件操作类
 * @author Deepseath
 * @version $Id$
 * @copyright vchangyi.com
 */

namespace Com;

use VcySDK\Attach;
use VcySDK\Service as VcySDKService;

class Attachment
{
    /**
     * 文件类型：图片
     */
    const FILE_TYPE_IMAGE = 1;

    /**
     * 文件类型：语音
     */
    const FILE_TYPE_VOICE = 2;

    /**
     * VcySDK 附件操作类
     */
    protected $_attach = null;

    /**
     * 构造方法
     */
    public function __construct()
    {

        $serv = &VcySDKService::instance();
        $this->_attach = new Attach($serv);
    }

    /**
     * 实例化
     *
     * @return \Com\Attachment
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
     * 上传图片
     *
     * @param string $uid 用户 ID
     * @param string $wxMid 微信文件 ID
     * @return array
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
     * @param string $uid 用户 ID
     * @param string $wxMid 微信文件 ID
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
     * 根据附件 ID 获取附件 Url
     *
     * @param string $atId 附件 ID
     * @return string
     */
    public function getAttachUrl($aid)
    {

        $result = $this->_attach->fetch(array('atId' => $aid));

        return $result['atAttachment'];
    }

    /**
     * 根据附件 ID 获取附件信息
     *
     * @param string $aid 附件ID
     * @return string
     */
    public function getAttachDetail($aid)
    {

        return $this->_attach->fetch(array('atId' => $aid));
    }

    /**
     * 根据附件 ID 数组获取附件 Url
     * @param array $aids 附件ID数组
     * @return array
     */
    public function listAttachUrl($aids)
    {
        $aids = settype($aids, 'array');

        // 查询SDK
        $result = $this->_attach->listAll(['atIds' => $aids]);
        if (empty($result)) {
            return [];
        }

        return array_combine_by_key($result, 'atId');
    }

    /**
     * 下载附件文件
     * @param string $atId 附件 ID
     * @return boolean
     */
    public function downloadAttachmentByAid($atId = '')
    {
        // 获取附件的详细信息
        $attachDetail = $this->getAttachDetail($atId);
        if (empty($attachDetail)) {
            return false;
        }
        // 附件原始文件名，转换编码为 GBK
        $showname = iconv('UTF-8', 'GBK//IGNORE', $attachDetail['atFilename']);
        // 发送 Http Header 信息 开始下载
        header('Pragma: public');
        header('Cache-control: max-age=' . 180);
        // header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s', NOW_TIME + 86400 * 7) . 'GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $attachDetail['atCreated']) . 'GMT');
        header('Content-Disposition: attachment; filename=' . $showname);
        header('Content-Length: ' . $attachDetail['atFilesize']);
        header('Content-type: application/octet-stream');
        header('Content-Encoding: none');
        header('Content-Transfer-Encoding: binary');
        // 读取远程文件输出
        readfile($attachDetail['atAttachment']);

        exit();
    }
}
