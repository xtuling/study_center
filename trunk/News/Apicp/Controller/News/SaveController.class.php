<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 18:33
 */
namespace Apicp\Controller\News;

use Common\Common\Attach;
use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\NewsHelper;
use Common\Service\AttachService;
use Common\Service\ClassService;
use Common\Service\RightService;
use Common\Service\ArticleService;
use Common\Service\TaskService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author liyifei
     * @desc 新闻保存(创建、修改)
     * @param Int article_id 新闻ID
     * @param String title:true 标题
     * @param Int class_id:true 分类ID
     * @param String author:true 作者
     * @param String content 内容
     * @param String cover_id:true 封面图片ID
     * @param String is_show_cover:true:2 是否正文显示封面图片（1=不显示，2=显示）
     * @param String audio_at_id 音频附件ID
     * @param Array video_at_info 视频附件详情
     * @param String video_at_info.at_id:true 视频附件ID
     * @param String video_at_info.at_name:true 视频名称
     * @param String video_at_info.at_size:true 视频大小（单位:字节）
     * @param Array file_at_ids 文件附件ID
     * @param Array is_download:true:1 附件是否支持下载（1=不支持，2=支持）
     * @param String summary:true 摘要(最多输入150字符)
     * @param Array right:true 阅读范围
     * @param Int right.is_all 是否全公司（1=否；2=是）
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array right.job_ids 职位ID
     * @param Array right.role_ids 角色ID
     * @param String link 外部链接
     * @param Int is_jump 是否直接跳转外链（1=不直接跳转，2=直接跳转）
     * @param Int is_secret:true:1 是否保密（1=不保密，2=保密）
     * @param Int is_share:true:1 允许分享（1=不允许，2=允许）
     * @param Int is_notice:true:2 消息通知（1=不开启，2=开启）
     * @param Int is_comment:true:2 评论功能（1=不开启，2=开启）
     * @param Int is_like:true:2 点赞功能（1=不开启，2=开启）
     * @param Int is_recommend:true:2 首页推荐（1=不开启，2=开启）
     * @param Int news_status:true:1 新闻状态（1=草稿，2=已发布，3=预发布）
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            // 非必填
            'article_id' => 'integer',
            'summary' => 'max:120',
            'audio_at_id' => 'max:32',
            'file_at_ids' => 'array',
            'video_at_info' => 'array',
            'link' => 'max:500',
            'is_jump' => 'integer|in:1,2',
            // 必填
            'title' => 'require|max:64',
            'class_id' => 'require|integer',
            'author' => 'require|max:20',
            'cover_id' => 'require',
            'right' => 'require|array',
            'is_show_cover' => 'require|integer|in:1,2',
            'is_download' => 'require|integer|in:1,2',
            'is_secret' => 'require|integer|in:1,2',
            'is_share' => 'require|integer|in:1,2',
            'is_notice' => 'require|integer|in:1,2',
            'is_comment' => 'require|integer|in:1,2',
            'is_like' => 'require|integer|in:1,2',
            'is_recommend' => 'require|integer|in:1,2',
            'news_status' => 'require|integer|in:1,2',
        ];

        // 验证请求数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 补全请求参数
        $postData['content'] = I('post.content', '', 'trim');
        $fixData = $this->_fixData($postData);

        // 格式化权限数据
        $article_id = $fixData['article_id'];
        $rightServ = new RightService();
        $formatRights = $rightServ->formatPostData($postData['right']);
        if (empty($formatRights)) {
            E('_ERR_ARTICLE_RIGHT_PARAM');
        }

        // 校验传参(验证器无法验证的部分)
        $this->_checkData($fixData);

        // 组合写入article表的数据
        $sqlData = $this->_fixSqlData($fixData);
        
        // 1、保存新闻
        $articleServ = new ArticleService();
        if ($article_id == 0) {
            // 新增新闻，创建数据标识
            $sqlData['data_id'] = $articleServ->buildDataID();
            // 创建新闻
            $article_id = $articleServ->insert($sqlData);
            if ($article_id) {
                // 修改新闻未读人数
                $newsHelper = &NewsHelper::instance();
                list($uids_all, $uids_read, $uids_unread) = $newsHelper->getReadData($article_id);
                $articleServ->update($article_id, ['unread_total' => count($uids_all)]);

                // 推送未读消息(草稿、预发布除外)
                if (!in_array($sqlData['news_status'], [Constant::NEWS_STATUS_DRAFT, Constant::NEWS_STATUS_READY_SEND]) && $sqlData['is_notice'] == Constant::NEWS_IS_NOTICE_TRUE) {
                    // 新闻所在顶级分类信息
                    $classServ = new ClassService();
                    $class = $classServ->getTopClass($sqlData['class_id']);
                    $sqlData['class_name'] = $class['class_name'];

                    $sqlData['article_id'] = $article_id;
                    $newsHelper->sendNotice($sqlData, $formatRights);
                }

                // RPC推送到运营中心
                if ($sqlData['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE) {
                    $articleServ->addNewsRpc($article_id);
                }
            }
        } else {
            // RPC推送到运营中心
            $this->_operationRpc($article_id, $sqlData);

            // 修改新闻
            $articleServ->update_by_conds(['article_id' => $article_id], $sqlData);
        }

        // 创建计划任务(预发布新闻:视频、文件附件)
        if ($sqlData['news_status'] == Constant::NEWS_STATUS_READY_SEND) {
            $taskServ = new TaskService();
            $taskServ->createCheckAttachTask($article_id);
        }

        // 2、保存阅读范围
        $rightServ->saveData(['article_id' => $article_id], $postData['right']);
        
        // 3、保存附件
        $attachServ = new AttachService();
        $attachServ->saveData($article_id, $fixData);
    }

    /**
     * 补全请求数据
     * @param array $postData 请求数据
     * @return array
     */
    private function _fixData($postData)
    {
        // 非必填参数,补全默认值
        $postData['article_id'] = isset($postData['article_id']) ? $postData['article_id'] : 0;
        $postData['content'] = isset($postData['content']) ? $postData['content'] : '';
        $postData['summary'] = isset($postData['summary']) ? $postData['summary'] : '';
        $postData['audio_at_id'] = isset($postData['audio_at_id']) ? $postData['audio_at_id'] : '';
        $postData['file_at_ids'] = isset($postData['file_at_ids']) ? $postData['file_at_ids'] : [];
        $postData['video_at_info'] = isset($postData['video_at_info']) ? $postData['video_at_info'] : [];
        $postData['link'] = isset($postData['link']) ? $postData['link'] : '';
        $postData['is_jump'] = isset($postData['is_jump']) ? $postData['is_jump'] : Constant::NEWS_IS_JUMP_FALSE;

        return $postData;
    }

    /**
     * 校验传参
     * @author liyifei
     * @param array $fixData 补全后的请求参数
     * @return mixed
     */
    private function _checkData($fixData)
    {
        // 检查file_at_ids
        if (isset($fixData['file_at_ids']) && count($fixData['file_at_ids']) > Constant::UPLOAD_FILE_ATTACH_LIMIT) {
            E('_ERR_ARTICLE_FILE_ATTACH_MORE');
        }

        // 检查video_at_info
        if (!empty($fixData['video_at_info'])) {
            $videos = $fixData['video_at_info'];
            if (!isset($videos['at_id'], $videos['at_name'], $videos['at_size'])) {
                E('_ERR_ARTICLE_ATTACH_PARAM');
            }
        }

        return true;
    }

    /**
     * 补全写入article表的数据
     * @author liyifei
     * @param array $fixData 补全后的请求参数
     * @return array
     */
    private function _fixSqlData($fixData)
    {
        // 补全管理员信息
        $fixData['ea_id'] = $this->_login->user['eaId'];
        $fixData['ea_name'] = $this->_login->user['eaRealname'];

        // 补全class_name
        $classServ = new ClassService();
        $class = $classServ->get($fixData['class_id']);
        if (empty($class)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }
        $fixData['class_name'] = $class['class_name'];

        // 补全summary
        if (empty($fixData['summary']) && !empty($fixData['content'])) {
            // 去除html格式,自动从内容中抓取54个字符
            $content = htmlspecialchars(strip_tags($fixData['content']));
            $fixData['summary'] = mb_substr($content, 0, Constant::AUTO_SUMMARY_LENGTH, 'UTF-8');
        }

        // 补全cover_url
        $attachServ = &Attach::instance();
        $fixData['cover_url'] = $attachServ->getAttachUrl($fixData['cover_id']);

        // 补全send_time
        $fixData['send_time'] = MILLI_TIME;

        // 补全news_status
        if ($fixData['news_status'] == Constant::NEWS_STATUS_SEND && (!empty($fixData['video_at_info']) ||!empty($fixData['file_at_ids']))) {
            // 立即发布、有视频、文件附件时,状态为"预发布"
            $fixData['news_status'] = Constant::NEWS_STATUS_READY_SEND;
        }

        // 重置以下参数之外的postData数据
        $allowKeys = [
            'class_id',
            'class_name',
            'title',
            'ea_id',
            'ea_name',
            'author',
            'content',
            'summary',
            'cover_id',
            'cover_url',
            'is_show_cover',
            'is_download',
            'is_secret',
            'is_share',
            'is_notice',
            'is_comment',
            'is_like',
            'is_recommend',
            'send_time',
            'news_status',
            'link',
            'is_jump',
        ];
        foreach ($fixData as $k => $v) {
            if (!in_array($k, $allowKeys)) {
                unset($fixData[$k]);
            }
        }

        return $fixData;
    }

    /**
     * @desc 更新数据时，数据库与提交数据对比，判断推送接口
     * @author tangxingguo
     * @param int $article_id 新闻ID
     * @param array $newInfo 更新的新闻数据
     */
    private function _operationRpc($article_id, $newInfo)
    {
        $articleServ = new ArticleService();
        $articleDBInfo = $articleServ->get($article_id);
        if (($articleDBInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE && $newInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_FALSE) || ($articleDBInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE && $newInfo['news_status'] == Constant::NEWS_STATUS_READY_SEND)) {
            // 有推送过取消推送 或 计划任务，删除接口
            $articleServ->delNewsRpc($article_id);
        } elseif ($articleDBInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_FALSE && $newInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE) {
            // 第一次推送，添加接口
            $articleServ->addNewsRpc($article_id);
        } elseif ($articleDBInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE && $newInfo['is_recommend'] == Constant::NEWS_IS_RECOMMEND_TRUE) {
            // 有推送过，更新接口
            $articleServ->updateNewsRpc($article_id, $newInfo);
        }
    }
}
