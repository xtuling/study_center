<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/5
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

use Common\Common\ArticleHelper;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ExamService;

class ExamFinishController extends AbstractController
{
    /**
     * 测评完成
     * @author tangxingguo
     */
    public function Index()
    {
        $article_id = I('get.article_id', 0, 'intval');
        $user = $this->_login->user;
        $param = ['article_id' => $article_id];

        // 课程考试通过
        $examServ = new ExamService();
        $examInfo = $examServ->get_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
        if (!empty($examInfo) && $examInfo['is_pass'] == Constant::ARTICLE_EXAM_IS_PASS) {
            // 取激励
            $articleHelper = &ArticleHelper::instance();
            $awardInfo = $articleHelper->getAwardByUser($user);
            if (!empty($awardInfo)) {
                $param['award_id'] = $awardInfo['award_id'];
            }
        }

        // 课程
        $articleServ = new ArticleService();
        $articleInfo = $articleServ->get($article_id);
        $param['data_id'] = isset($articleInfo['data_id']) ? $articleInfo['data_id'] : '';
        if (isset($articleInfo['article_type']) && $articleInfo['article_type'] == Constant::ARTICLE_TYPE_MULTI) {
            // 系列课程
            redirectFront('app/page/course/detail/course-detail', $param);
        } else {
            // 单课程
            redirectFront('app/page/course/detail/detail', $param);
        }
    }
}
