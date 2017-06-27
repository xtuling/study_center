<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/5/15
 * Time: 15:02
 */

namespace Apicp\Controller\Editor;

class UploadController extends AbstractController
{
    /**
     * Config
     * @author zhonglei
     * @desc 文件上传接口
     * @return array
     */
    public function Index()
    {
        $result = [
            'state' => 'SUCCESS',
            'url' => 'upload/demo.jpg',
            'title' => 'demo.jpg',
            'original' => 'demo.jpg',
        ];

        echo json_encode($result);
        exit;
    }
}
