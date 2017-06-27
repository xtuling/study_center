<?php
/**
 * 图片上传
 *
 * 由于UC获取临时素材是异步的，所以前端至少会请求2次。分别是上传和请求图片地址。
 * 参数atId不为空并且wxid不为空时，是轮询请求图片地址。
 * 参数atId为空并且wxid不为空时，是上传临时图片素材
 *
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:49
 */
namespace Api\Controller\Attachment;

use Com\Cookie;
use VcySDK\Service;
use VcySDK\Attach;
use Common\Common\Attach as CommonAttach;

class UploadImgController extends AbstractController
{

    protected $_require_login = false;

    /**
     * VcySDK 附件操作类
     *
     * @type Attach
     */
    protected $_attach;

    /**
     * 微信素材ID
     *
     * @var string
     */
    protected $_wxid;

    /**
     * 附件ID
     *
     * @var string
     */
    protected $_atId;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_attach = new Attach($serv);

        return true;
    }

    public function Index()
    {

        // 微信文件ID
        $this->_wxid = I('post.wxid');
        // 附件ID
        $this->_atId = I('post.atId');

        // 参数错误
        if (empty($this->_wxid)) {
            $this->_set_error('_ERR_PARAMS_UNALLOWED');
            return false;
        }

        /**
         * 根据参数判断是什么业务，进行相应的处理
         * -- 附件ID参数不为空时，是获取附件信息操作
         * -- 附件ID为空时, 是上传附件
         */
        if (empty($this->_atId)) {
            $this->_upload();
        } else {
            $this->_getDetail();
        }

        return true;
    }

    /**
     * 上传临时图片素材到UC
     *
     * @return array 返回UC返回的参数
     */
    protected function _upload()
    {

        $params = array(
            'memUid' => empty($this->_login->user) ? null : $this->_login->user['memUid'],
            'mediaId' => $this->_wxid,
            'fileType' => Attach::TYPE_IMG,
            'callbackUrl' => oaUrl('Api/Attachment/UploadCallback', array(), '', 'Public') . '?_identifier=common'
        );

        $attach = $this->_attach->getMedia($params);
        // 上传错误
        if (empty($attach)) {
            $this->_set_error('_ERR_UPLOAD_ERROR');
        }

        // 缓存标记
        CommonAttach::instance()->setWaitingFlag($attach['atId']);
        $this->_result = array(
            'wxid' => $this->_wxid,
            'atId' => $attach['atId'],
            'atMqStatus' => 0
        );
        return true;
    }

    /**
     * 获取附件详情
     *
     * @return array
     */
    protected function _getDetail()
    {

        $flag = CommonAttach::instance()->getFlag($this->_atId);
        // 如果已经通知了, 并且附件处理出错
        if (CommonAttach::FLAG_ERROR == $flag) {
            $this->_result = array(
                'atId' => $this->_atId,
                'atMqStatus' => 2
            );
            return true;
        } elseif (CommonAttach::FLAG_WAITING == $flag) { // 上传中
            $this->_result = array(
                'atId' => $this->_atId,
                'atMqStatus' => 0
            );
            return true;
        }

        // 获取附件信息
        $attach = $this->_attach->fetch(array(
            'atId' => $this->_atId
        ));

        // 非图片附件
        if (stripos($attach['atAttachment'], '/image/') === false) {
            $this->_result = array(
                'wxid' => $this->_wxid,
                'atId' => $this->_atId,
                'atMqStatus' => 0
            );
        } else {
            $this->_result = array(
                'wxid' => $this->_wxid,
                'atId' => $attach['atId'],
                'atFilename' => $attach['atFilename'],
                'atFilesize' => formatBytes($attach['atFilesize']),
                'atAttachment' => imgUrl($attach['atId']),
                'atMqStatus' => 1,
                'atCreated' => $attach['atCreated']
            );
        }

        return true;
    }

}
