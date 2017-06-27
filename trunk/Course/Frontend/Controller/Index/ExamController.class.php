<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/5
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

class ExamController extends AbstractController
{
    /**
     * 跳转至手机端测评页
     * @author zhonglei
     */
    public function Index()
    {
        $params = [
            'article_id' => I('get.article_id', 0, 'intval'),
            'et_ids' => I('get.et_ids', '', 'trim'),
        ];

        redirectFront('/app/page/exam/exam-going-promote', $params, 'Exam');
    }
}
