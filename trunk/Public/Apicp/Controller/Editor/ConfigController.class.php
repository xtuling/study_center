<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/5/15
 * Time: 15:02
 */

namespace Apicp\Controller\Editor;

class ConfigController extends AbstractController
{
    /**
     * Config
     * @author zhonglei
     * @desc 编辑器配置接口
     * @return array
     */
    public function Index()
    {
        $this->_result = [
            // 上传接口地址
            'imageUrl' => oaUrl('Apicp/Editor/Upload'),
            // 上传路径
            'imagePath' => '',
            'imageFieldName' => 'upfile',
            'imageMaxSize' => 2048,
            'imageAllowFiles' => ['png', 'jpg', 'jpeg', 'gif'],
        ];
    }
}
