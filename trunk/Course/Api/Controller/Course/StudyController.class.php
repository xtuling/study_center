<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */
namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\ArticleHelper;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ArticleSourceService;
use Common\Service\StudyService;
use Common\Service\StudyRecordService;

class StudyController extends \Api\Controller\AbstractController
{
    /**
     * Study
     * @author liyifei
     * @desc 课程学习接口
     * @param Int article_id:true 课程ID
     * @param Int source_id 素材ID（系列课程时，此参数必填）
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
            'source_id' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 登录人员
        $user = $this->_login->user;

        // 系列课程
        $all_study = true;
        if ($article['article_type'] == Constant::ARTICLE_TYPE_MULTI) {
            // 素材ID必填
            if (!isset($postData['source_id'])) {
                E('_ERR_ARTICLE_SOURCE_ID_IS_EMPTY');
            }

            // 系列课程章节（素材）未学习时，记录
            $recordServ = new StudyRecordService();
            $record = $recordServ->get_by_conds([
                'uid' => $user['memUid'],
                'article_id' => $postData['article_id'],
                'source_id' => $postData['source_id'],
            ]);
            if (empty($record)) {
                $recordServ->insert([
                    'uid' => $user['memUid'],
                    'username' => $user['memUsername'],
                    'article_id' => $postData['article_id'],
                    'source_id' => $postData['source_id'],
                ]);
            }

            // 该系列课程所有章节是否全部完成
            $studyList = $recordServ->list_by_conds([
                'uid' => $user['memUid'],
                'article_id' => $postData['article_id'],
            ]);
            $asServ = new ArticleSourceService();
            $sourceList = $asServ->list_by_conds([
                'article_id' => $postData['article_id'],
            ]);
            if (empty($studyList)) {
                $all_study = false;
            } else {
                $sources_diff = array_column($sourceList, 'source_id');
                $study_diff = array_column($studyList, 'source_id');
                if (!empty(array_diff($sources_diff, $study_diff))) {
                    $all_study = false;
                }
            }
        }

        // 单课程、系列课程均学习完成
        if ($all_study) {
            $studyServ = new StudyService();
            $study = $studyServ->get_by_conds([
                'uid' => $user['memUid'],
                'article_id' => $postData['article_id'],
            ]);
            if (empty($study)) {
                // 写入学习数据
                $studyServ->insert([
                    'uid' => $user['memUid'],
                    'username' => $user['memUsername'],
                    'article_id' => $postData['article_id'],
                ]);

                // 更新课程主表已学习总数
                $articleServ->update($postData['article_id'], ['study_total = study_total + ?' => 1]);

                // 不需要考评的情况下激励
                if ($article['is_exam'] == Constant::ARTICLE_IS_EXAM_FAIL) {
                    // 激励
                    $articleHelper = &ArticleHelper::instance();
                    $awardInfo = $articleHelper->getAwardByUser($user);
                }
            }
        }
    }
}
