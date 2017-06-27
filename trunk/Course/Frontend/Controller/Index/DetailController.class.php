<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/2/22
 * Time: 09:59
 */
namespace Frontend\Controller\Index;

use Common\Common\Constant;

class DetailController extends AbstractController
{
    /**
     * 跳转至手机端课程详情页
     */
    public function Index()
    {
        $article_id = I('get.article_id', 0, 'intval');
        $data_id = I('get.data_id', '', 'trim');
        $article_type = I('get.article_type', Constant::ARTICLE_TYPE_SINGLE, 'intval');

        if ($article_type == Constant::ARTICLE_TYPE_SINGLE) {
            // 单课程详情
            redirectFront('/app/page/course/detail/detail', ['article_id' => $article_id, 'data_id' => $data_id]);

        } else {
            // 系列课程详情
            redirectFront('/app/page/course/detail/course-detail', ['article_id' => $article_id, 'data_id' => $data_id]);
        }
    }
}
