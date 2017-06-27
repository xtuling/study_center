<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 10:59
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\ArticleHelper;
use Common\Service\ClassService;
use Common\Service\SourceService;
use Common\Service\ArticleService;
use Common\Service\ArticleSourceService;
use Common\Service\RightService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author zhonglei
     * @desc 保存课程接口
     * @param Int article_id:true 课程ID（新增时传0）
     * @param Int class_id:true 分类ID
     * @param Int cm_id 能力模型ID
     * @param String article_title:true 课程名称（最长64）
     * @param Int article_type:true 课程类型（1=单课程；2=系列课程）
     * @param String cover_id:true 封面图片ID
     * @param String cover_url:true 封面图片URL（最长500）
     * @param String summary 摘要（最长120）
     * @param String content 系列课程介绍
     * @param Array source_ids:true 素材ID数组
     * @param Array et_ids 评测题目ID数组
     * @param String tags 课程标签（最长34）
     * @param Int is_secret:true 是否保密（1=不保密；2=保密）
     * @param Int is_share:true 允许分享（1=不允许；2=允许）
     * @param Int is_notice:true 消息通知（1=不开启；2=开启）
     * @param Int is_comment:true 评论功能（1=不开启；2=开启）
     * @param Int is_like:true 点赞功能（1=不开启；2=开启）
     * @param Int is_recommend:true 首页推荐（1=不开启；2=开启）
     * @param Int is_exam:true 是否需要测评（1=不评测；2=评测）
     * @param Int is_step:true 是否开启闯关（1=未开启；2=已开启）
     * @param Int article_status:true 课程状态（1=草稿；2=已发布）
     * @param Array right:true 适用范围
     * @param String right.is_all 是否全公司（1=否；2=是）
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array right.job_ids 职位ID
     * @param Array right.role_ids 角色ID
     * @return Array
     * array(
     *     'article_id' => 1 // 课程ID
     * )
     */
    public function Index_post()
    {
        // 请求数据
        $post_data = I('post.');

        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
            'class_id' => 'require|integer',
            'cm_id' => 'integer',
            'article_title' => 'require|max:64',
            'article_type' => 'require|in:1,2',
            'cover_id' => 'require',
            'cover_url' => 'require|max:500',
            'summary' => 'max:120',
            'source_ids' => 'require|array',
            'et_ids' => 'array',
            'tags' => 'max:34',
            'is_secret' => 'require|in:1,2',
            'is_share' => 'require|in:1,2',
            'is_notice' => 'require|in:1,2',
            'is_comment' => 'require|in:1,2',
            'is_like' => 'require|in:1,2',
            'is_recommend' => 'require|in:1,2',
            'is_exam' => 'require|in:1,2',
            'is_step' => 'require|in:1,2',
            'article_status' => 'require|in:1,2',
            'right' => 'require|array',
        ];

        // 验证请求数据
        $validate = new PackageValidate();
        $validate->postData = $post_data;
        $validate->validateParams($rules);

        $articleServ = new ArticleService();
        $articleSourceServ = new ArticleSourceService();
        $rightServ = new RightService();

        list($class_data, $article_data, $send_notice) = $this->_formatPostData($post_data);
        $article_id = $post_data['article_id'];

        // 新增课程
        if ($article_id == 0) {
            $article_id = $articleServ->insert($article_data);
            // RPC推送
            if ($article_data['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_TRUE) {
                $articleServ->addCourseRpc($article_id);
            }

        // 编辑课程
        } else {
            // RPC推送
            $this->_operationRpc($article_id, $article_data);

            $articleServ->update($article_id, $article_data);
        }

        // 保存课程素材关系
        $articleSourceServ->saveData($article_id, $post_data['source_ids']);

        // 保存课程适用范围
        $rightServ->saveData(['article_id' => $article_id], $post_data['right']);

        // 更新已学、未学人数
        $articleHelper = &ArticleHelper::instance();
        list(, $uids_study, $uids_unstudy) = $articleHelper->getStudyData($article_id);
        $total_data = ['study_total' => count($uids_study), 'unstudy_total' => count($uids_unstudy)];
        $articleServ->update($article_id, $total_data);

        // 消息通知
        if ($send_notice) {
            $article_data['article_id'] = $article_id;
            $article_data['class_name'] = $class_data['class_name'];
            $rights = $rightServ->formatPostData($post_data['right']);
            $articleHelper->sendNotice($article_data, $rights);
        }

        $this->_result = [
            'article_id' => $article_id,
        ];
    }

    /**
     * 格式化请求数据，并返回一级分类数据、课程数据、是否发送新课程消息通知
     * @param array $post_data 请求数据
     * @return array
     */
    private function _formatPostData($post_data)
    {
        $classServ = new ClassService();
        $articleServ = new ArticleService();
        $sourceServ = new SourceService();
        $article_id = $post_data['article_id'];
        $send_notice = false;

        // 获取分类
        $class_list = $classServ->list_all();
        $class_list = array_combine_by_key($class_list, 'class_id');
        $class_id = $post_data['class_id'];

        if (!isset($class_list[$class_id])) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }

        // 获取一级分类
        while ($class_list[$class_id]['parent_id'] > 0) {
            $class_id = $class_list[$class_id]['parent_id'];
        }

        // 新增课程
        if ($article_id == 0) {
            if ($post_data['is_notice'] == Constant::ARTICLE_IS_NOTICE_TRUE &&
                $post_data['article_status'] == Constant::ARTICLE_STATUS_SEND) {
                $send_notice = true;
            }

        // 编辑课程
        } else {
            $article = $articleServ->get($article_id);

            // 检查闯关逻辑
            $this->_checkSourceOrder($article, $post_data);

            if (empty($article)) {
                E('_ERR_ARTICLE_DATA_NOT_FOUND');
            }

            // 课程类型不能更改
            if ($post_data['article_type'] != $article['article_type']) {
                E('_ERR_ARTICLE_TYPE_CAN_NOT_CHANGE');
            }

            // 课程状态改变
            if ($post_data['article_status'] != $article['article_status']) {
                // 已发布课程状态不能更改
                if ($article['article_status'] == Constant::ARTICLE_STATUS_SEND) {
                    E('_ERR_ARTICLE_STATUS_CAN_NOT_CHANGE');
                } elseif ($post_data['is_notice'] == Constant::ARTICLE_IS_NOTICE_TRUE) {
                    $send_notice = true;
                }
            }
        }

        switch ($post_data['article_type']) {
            // 单课程
            case Constant::ARTICLE_TYPE_SINGLE:
                // 素材总数
                if (count($post_data['source_ids']) != 1) {
                    E('_ERR_ARTICLE_SOURCE_COUNT_INVALID');
                }

                $source_id = end($post_data['source_ids']);
                $source = $sourceServ->get($source_id);

                // 判断素材是否存在
                if (empty($source)) {
                    E('_ERR_ARTICLE_SOURCE_NOT_FOUND');
                }

                // 素材类型
                $post_data['source_type'] = $source['source_type'];

                // 新增课程时自动抓取摘要
                if ($article_id == 0 && (!isset($post_data['summary']) || empty($post_data['summary']))) {
                    $content = strip_tags(html_entity_decode($source['content']));
                    $post_data['summary'] = mb_substr($content, 0, Constant::SUMMARY_LENGTH, 'utf-8');
                }

                break;

            // 系列课程
            case Constant::ARTICLE_TYPE_MULTI:
                // 素材总数
                if (count($post_data['source_ids']) < 1) {
                    E('_ERR_ARTICLE_SOURCE_COUNT_INVALID');
                }

                $source_count = $sourceServ->count_by_conds(['source_id' => $post_data['source_ids']]);

                // 判断素材是否存在
                if ($source_count != count($post_data['source_ids'])) {
                    E('_ERR_ARTICLE_SOURCE_NOT_FOUND');
                }

                // 素材类型
                $post_data['source_type'] = Constant::SOURCE_TYPE_EMPTY;

                // 自动抓取摘要
                if (isset($post_data['content']) && !empty($post_data['content'])) {
                    $content = strip_tags(html_entity_decode($post_data['content']));
                    $post_data['summary'] = mb_substr($content, 0, Constant::SUMMARY_LENGTH, 'utf-8');
                }

                break;
        }

        $keys = ['class_id', 'cm_id', 'article_title', 'article_type', 'source_type', 'cover_id', 'cover_url', 'summary',
            'content', 'source_ids', 'et_ids', 'tags', 'is_secret', 'is_share', 'is_notice', 'is_comment',
            'is_like', 'is_recommend', 'is_exam', 'is_step', 'article_status'];

        $article = array_intersect_key_reserved($post_data, $keys, true);
        $article['ea_id'] = $this->_login->user['eaId'];
        $article['ea_name'] = $this->_login->user['eaRealname'];
        $article['source_ids'] = serialize($article['source_ids']);
        $article['et_ids'] = $article['is_exam'] == Constant::ARTICLE_IS_EXAM_TRUE && !empty($article['et_ids']) ? serialize($article['et_ids']) : '';
        $article['update_time'] = MILLI_TIME;

        // 新增课程，创建数据标识
        if ($article_id == 0) {
            $article['data_id'] = $articleServ->buildDataID();
        }

        return [$class_list[$class_id], $article, $send_notice];
    }

    /**
     * @desc 更新数据时，数据库与提交数据对比，判断推送接口
     * @author tangxingguo
     * @param int $article_id 课程ID
     * @param array $newInfo 更新的数据
     */
    private function _operationRpc($article_id, $newInfo)
    {
        $articleServ = new ArticleService();
        $articleDBInfo = $articleServ->get($article_id);
        if ($articleDBInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_TRUE && $newInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_FALSE) {
            // 有推送过取消推送，删除接口
            $articleServ->delCourseRpc($article_id);
        } elseif ($articleDBInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_FALSE && $newInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_TRUE) {
            // 第一次推送，添加接口
            $articleServ->addCourseRpc($article_id);
        } elseif ($articleDBInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_TRUE && $newInfo['is_recommend'] == Constant::ARTICLE_IS_RECOMMEND_TRUE) {
            // 有推送过，更新接口
            $articleServ->updateCourseRpc($article_id, $newInfo);
        }
    }

    /**
     * @desc 编辑课程时，闯关逻辑检查
     * @author tangxingguo
     * @param array $dbData 数据库课程信息
     * @param array $postData 用户提交课程信息
     * @return bool
     */
    private function _checkSourceOrder($dbData, $postData)
    {
        if (!isset($dbData['source_ids'], $postData['source_ids'], $postData['is_step'], $dbData['is_step'])) {
            return false;
        }

        // 允许关闭闯关
        if ($postData['is_step'] == Constant::ARTICLE_IS_CHECK_FALSE) {
            return true;
        }

        // 闯关关闭后不允许开启
        if ($postData['is_step'] == Constant::ARTICLE_IS_CHECK_TRUE && $dbData['is_step'] == Constant::ARTICLE_IS_CHECK_FALSE) {
            E('_ERR_FAVORITE_STEP_NOT_ALLOW');
        }

        // 不允许修改顺序，且课程素材不允许删除、变更
        $sourceIds = unserialize($dbData['source_ids']);
        if ($sourceIds !== $postData['source_ids']) {
            E('_ERR_FAVORITE_NOT_ALLOW_CHANGE_SOURCE');
        }
        return true;
    }
}
