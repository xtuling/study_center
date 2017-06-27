<?php
/**
 * 附件上传
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:49
 */

namespace Apicp\Controller\Attachment;

use VcySDK\Service;
use VcySDK\Attach;

class UploadAttaController extends AbstractController
{

    public function Index()
    {

        /*
         * 文件类型
         * 99=普通文件(最大5M)，
         * 1=图片(2M)，
         * 2=音频(2M)，
         * 3=视频(10M),
         * 总文件大小最大30M,
         * 支持类型 (xls,xlsx,jpg,jpeg,png,bmp,gif,mp3,amr,avi,mp4) (必填)
         */
        $types = array(
            Attach::TYPE_AUDIO,
            Attach::TYPE_IMG,
            Attach::TYPE_NORMAL,
            Attach::TYPE_VIDEO
        );

        $atMediatype = I('post.atMediatype', 0, 'intval');
        $res_auth = I('post.res_auth', '', 'trim');

        // 检查参数
        if (! in_array($atMediatype, $types) || empty($_FILES)) {
            $this->_set_error('_ERR_PARAMS_UNALLOWED');
            return false;
        }

        $params = [
            'atMediatype' => $atMediatype
        ];

        // 获取资源鉴权配置
        if (!empty($res_auth)) {
            $config = getResAuthConfig($res_auth);

            // 合并资源鉴权参数
            if (!empty($config)) {
                $params = array_merge($params, $config);
            }
        }

        $attach = new Attach(Service::instance());
        $result = $attach->upload($params, $_FILES);
        $this->_result = $result;

        return true;
    }
}
