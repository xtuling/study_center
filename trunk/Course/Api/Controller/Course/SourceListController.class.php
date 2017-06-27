<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */
namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ArticleSourceService;
use Common\Service\ExamService;
use Common\Service\SourceService;
use Common\Service\StudyRecordService;
use Common\Service\StudyService;

class SourceListController extends \Api\Controller\AbstractController
{
    /**
     * 是否必须登录
     */
    protected $_require_login = false;
    /**
     * SourceList
     * @author tangxingguo
     * @desc 素材章节列表
     * @param article_id 课程ID
     * @return array 素材列表
                    array(
                        'list' => array( // 素材列表
                            'source_id' => 1, // 素材ID
                            'source_title' => '八荣八耻', // 素材标题
                            'source_key' => 'P12313131', // 素材标识
                            'study_status' => 1, // 学习状态（1=未学，2=已学）
                        ),
                        'exam' => array(
                            'is_exam' => 1, // 是否开启测评（1=未开启；2=已开启）
                            'exam_pass' => 1, // 当前课程评测状态（1=未通过；2=已通过）
                            'exam_url' => 'http://qy.vchangyi.com', // 测评地址（测评开启且为通过测评的情况下返回）
                        )
                    );
     */
    public function Index_post()
    {
        $user = $this->_login->user;

        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $article_id = $validate->postData['article_id'];
        // 测评
        $examInfo = [];

        // 课程状态检查
        $articleServ = new ArticleService();
        $articleInfo = $articleServ->get_by_conds([
            'article_id' => $article_id,
            'article_status' => Constant::ARTICLE_STATUS_SEND
        ]);
        if (empty($articleInfo)) {
            // 课程不存在
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 取素材章节列表
        $sourceList = [];
        $articleSourceServ = new ArticleSourceService();
        $sourceServ = new SourceService();
        $articleSources = $articleSourceServ->list_by_conds(['article_id' => $article_id]);
        if (!empty($articleSources)) {
            $sourceIds = array_column($articleSources, 'source_id');
            $sourceList = $sourceServ->list_by_conds(['source_id in (?)' => $sourceIds]);
        }

        // 非外部人员
        if (!empty($user)) {
            // 当前课程已读的素材章节
            $studyRecordServ = new StudyRecordService();
            $studyList = $studyRecordServ->list_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
            if (!empty($studyList)) {
                $studyList = array_combine_by_key($studyList, 'source_id');
            }

            // 组合数据
            foreach ($sourceList as $k => $v) {
                $sourceList[$k]['study_status'] = isset($studyList[$v['source_id']]) ? Constant::ARTICLE_SOURCE_STUDY_STATUS_TRUE : Constant::ARTICLE_SOURCE_STUDY_STATUS_FALSE;
            }

            // 课程全部学习完成，检查评测
            if (!empty($sourceList) && !empty($studyList)) {
                $sources_diff = array_column($sourceList, 'source_id');
                $study_diff = array_column($studyList, 'source_id');
                if (empty(array_diff($sources_diff, $study_diff))) {
                    // 有测评并且考试未通过添加测评连接
                    if ($articleInfo['is_exam'] == Constant::ARTICLE_IS_CHECK_TRUE) {
                        $examInfo['is_exam'] = Constant::ARTICLE_IS_CHECK_TRUE;
                        // 检查是否通过测评
                        $examPass = $this->_getExamResult($article_id, $user);
                        $examInfo['exam_pass'] = $examPass ? Constant::ARTICLE_EXAM_IS_PASS : Constant::ARTICLE_EXAM_IS_FAIL;
                        // 题目存在且未通过测评
                        if (!empty($articleInfo['et_ids']) && !$examPass) {
                            $etIds = unserialize($articleInfo['et_ids']);
                            $etIds = implode(',', $etIds);
                            $examInfo['exam_url'] = oaUrl('Frontend/Index/Exam/Index', ['article_id' => $articleInfo['article_id'], 'et_ids' => $etIds]);
                        }
                    }

                    // 完成学习修改状态
                    $studyServ = new StudyService();
                    $study = $studyServ->get_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
                    if (empty($study)) {
                        // 写入学习数据
                        $studyServ->insert([
                            'uid' => $user['memUid'],
                            'username' => $user['memUsername'],
                            'article_id' => $article_id,
                        ]);

                        // 更新课程主表已学习总数
                        $articleServ->update($article_id, ['study_total = study_total + ?' => 1]);
                    }
                }
            }
            $articleInfo['is_outside'] = Constant::RIGHT_IS_OUTSIDE_NO;
        } else {
            $articleInfo['is_outside'] = Constant::RIGHT_IS_OUTSIDE_YES;
        }

        // 排序
        if (!empty($articleInfo['source_ids'])) {
            $source_ids = unserialize($articleInfo['source_ids']);
            $sourceList = array_combine_by_key($sourceList, 'source_id');
            $orderSources = [];
            foreach ($source_ids as $source_id) {
                $orderSources[] = isset($sourceList[$source_id]) ? $sourceList[$source_id] : [];
            }
            $sourceList = array_values($orderSources);
        }

        $this->_result = [
            'list' => $sourceList,
            'exam' => $examInfo,
        ];
    }

    /**
     * @desc 取当前课程测评结果
     * @author tangxingguo
     * @param int $article_id 课程ID
     * @param array $user 用户信息
     * @return bool 通过测评返回true,未通过返回false
     */
    private function _getExamResult($article_id, $user)
    {
        $conds = [
            'article_id' => $article_id,
            'uid' => $user['memUid'],
            'is_pass' => Constant::ARTICLE_EXAM_IS_PASS,
        ];
        $examServ = new ExamService();
        $examPass = $examServ->count_by_conds($conds);
        if (empty($examPass)) {
            return false;
        } else {
            return true;
        }
    }
}
