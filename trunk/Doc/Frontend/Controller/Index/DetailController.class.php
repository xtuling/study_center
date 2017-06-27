<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/26
 * Time: 15:16
 */

namespace Frontend\Controller\Index;

class DetailController extends AbstractController
{
    /**
     * 跳转至手机端文件详情页
     * @author liyifei
     */
    public function Index()
    {
        $file_id = I('get.file_id', 0, 'intval');

        redirectFront('/app/page/doc-library/detail', ['file_id' => $file_id]);
    }
}
