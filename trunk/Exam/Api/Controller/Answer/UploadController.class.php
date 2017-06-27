<?php
/**
 * UploadController.class.php
 * 语音题 - 媒体文件上传
 * @author: 蔡建华
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerAttachService;
use Common\Service\AnswerService;
use VcySDK\Service;
use VcySDK\Attach;

class UploadController extends AbstractController
{
    private $__file_info = '';
    /**
     * @var Attach
     */
    private $__attach = null;
    /**
     * @var AnswerService
     */
    protected $answer_serv;
    /**
     * @var AnswerService
     */
    protected $answer_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情service

        $this->answer_detail_serv = new AnswerDetailService();

        // 实例化答卷service
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        $ead_id = intval($params['ead_id']);
        $order_id = $params['order_id'];
        if (!$ead_id) {
            E('_ERR_ECT_ID_EMPTY_FOR_VOICE');
            return false;
        }
        if (!$order_id) {
            E('_ERR_ID_ORDER_FOR_VOICE');
            return false;
        }
        $data = $this->answer_detail_serv->get($ead_id);
        if (empty($data)) {
            E('_ERR_COUNT_DETAIL_EMPTY_FOR_VOICE');
            return false;
        }
        // 上传插件
        // 处理媒体文件
        if (empty($params['media_id'])) {
            // 移除媒体文件
            $rel = $this->remove($params, $data['ea_id']);
            if (!$rel) {
                return false;
            }
        } else {
            // 上传媒体文件
            $rel = $this->add_media($params, $data['ea_id']);
            if ($rel) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $params 新增媒体文件
     * @param int $ea_id 答卷ID
     * @return bool
     */
    private function add_media($params = array(), $ea_id = 0)
    {
        if (empty($params['media_id']) || !is_scalar($params['media_id'])) {
            E('_ERR_VOICE_UPLOAD_MEDIA_IS_EMPTY');
            return false;
        }
        // 初始化文件上传
        $serv = &Service::instance();
        $this->__attach = new Attach($serv);

        if ($params['type'] == AnswerAttachService::TYPE_VOICE) {
            // 音频文件
            $at_id = $this->add_voice($params);
            if (!$at_id) {
                return false;
            }
            $file_type = AnswerAttachService::TYPE_VOICE;
            $is_complete = AnswerAttachService::IS_COMPLETE_NO;
        } elseif ($params['type'] == AnswerAttachService::TYPE_IMAGE) {
            // 图片文件
            $at_id = $this->add_image($params);
            $file_type = AnswerAttachService::TYPE_IMAGE;
            $is_complete = AnswerAttachService::IS_COMPLETE_YES;
        } else {
            E('_ERR_UNKNOW_MEDIA_TYPE');
            return false;
        }

        // 写入附件表
        $count_attach_serv = new AnswerAttachService();
        $data = array(
            'ead_id' => $params['ead_id'],
            'ea_id' => $ea_id,
            'order_id' => $params['order_id'],
            'media_id' => $params['media_id'],
            'is_complete' => $is_complete,
            'at_id' => $at_id,
            'type' => $file_type,
            'file_info' => serialize($this->__file_info)
        );
        $count_attach_serv->insert($data);
        return true;
    }

    /** 移除媒体文件
     * @param array $params 参数
     * @param int $ea_id 答卷ID
     * @return bool
     */
    private function remove($params = array(), $ea_id = 0)
    {
        // 移除附件
        $count_attach_serv = new AnswerAttachService();
        $count_attach_serv->delete_by_conds([
            'ead_id' => $params['ead_id'],
            'ea_id' => $ea_id,
            'order_id' => $params['order_id']
        ]);
        return true;
    }

    /**
     * 新增语音文件
     * @return boolean|string
     */
    private function add_voice($params = array())
    {
        $this->__file_info = $params['info'];
        if (!isset($this->__file_info['length'])) {
            E('_ERR_UPLOAD_VOICE_LENGTH_ERROR');
            return false;
        }
        // 重新整理文件信息
        $this->__file_info = [
            'length' => $this->__file_info['length']
        ];

        // 转换完毕后的回调 URL
        $url = oaUrl('/Frontend/Callback/Upload/Index', [
            'ead_id' => $params['ead_id'],
            'order_id' => $params['order_id'],
        ]);
        $result = $this->__attach->getMedia([
            'memUid' => $this->uid,
            'mediaId' => $params['media_id'],
            'fileType' => Attach::TYPE_AUDIO,
            'callbackUrl' => $url
        ]);
        if (!$result || empty($result['atId'])) {
            E('_ERR_UPLOAD_VOICE_FAILED');
            return false;
        }

        return $result['atId'];
    }

    /**
     * 新增图片文件
     * @return boolean
     */
    private function add_image($params)
    {
        // 重新整理文件信息
        $this->__file_info = [];
        $result = $this->__attach->getMedia([
            'memUid' => $this->uid,
            'mediaId' => $params['media_id'],
            'fileType' => Attach::TYPE_IMG
        ]);

        if (!$result || empty($result['atId'])) {
            E('_ERR_UPLOAD_IMAGE_FAILED');
            return false;
        }

        return $result['atId'];
    }
}
