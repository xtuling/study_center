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
use Common\Common\Integral;
use Common\Common\RpcFavoriteHelper;
use Common\Common\User;
use Common\Service\ArticleService;
use Common\Service\ArticleSourceService;
use Common\Service\AwardService;
use Common\Service\ClassService;
use Common\Service\ExamService;
use Common\Service\LikeService;
use Common\Service\RightService;
use Common\Service\SourceAttachService;
use Common\Service\SourceService;
use Common\Service\StudyRecordService;
use Common\Service\StudyService;

class InfoController extends \Api\Controller\AbstractController
{
    /**
     * 是否必须登录
     */
    protected $_require_login = false;
    /**
     * Info
     * @author tangxingguo
     * @desc 课程详情接口
     * @param int article_id:true 课程ID
     * @param int source_id 素材ID
     * @param int award_id 激励ID
     * @param int is_share 是否为分享入口（1=不是；2=是）
     * @return array 课程详情
                        array(
                        'article_id' => 123, // 课程ID
                        'data_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 数据标识
                        'article_type' => 1, // 课程类型（1=单课程；2=系列课程）
                        'article_title' => '好好学', // 课程标题
                        'cm_id' => 1, // 能力模型ID
                        'cm_name' => '超能力', // 能力模型名称
                        'update_time' => 1493709035000, // 课程发布时间
                        'study_total' => 100, // 学习人数
                        'who_study_total' => 100, // 谁在学人数
                        'source_total' => 10, // 章节总数
                        'study_source_total' => 10, // 已学章节数
                        'cover_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 封面图片ID
                        'cover_url' => 'http://qy.vchangyi.org', // 封面图片地址
                        'sources' => array( // 素材
                            'source_id' => 'xxx', // 素材主键ID
                            'source_type' => 1, // 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
                            'source_title' => '素材1', // 素材名称
                            'author' => '吆西', // 作者
                            'content' => 'aaa', // 内容描述
                            'link' => 'http://www.baidu.com/001.mp3', // 外部素材url(source_type=5时使用)
                            'audio_imgs' => array( // 音图内容(source_type=2时使用)
                                array(
                                'at_id' => '111', // 图片附件ID
                                'at_url' => 'http://www.vchangyi.com/001.jpg', // 图片附件地址
                                'audios' => array( // 音频附件
                                    array(
                                        'at_id' => '111', // 音频附件ID
                                        'at_name' => 'ccc', // 音频附件名称
                                        'at_time' => '123432', // 音频附件时长(毫秒)
                                        'at_url' => 'http://www.vchangyi.com/001.mp3', // 音频附件地址
                                    ),
                                )
                                ),
                            ),
                            'videos' => array( // 视频内容(source_type=3时使用)
                                array(
                                    'at_id' => '8765432345', // 视频附件ID
                                    'at_name' => 'aaa', // 视频附件名称
                                    'at_time' => '12345432', // 视频附件时长(毫秒)
                                    'at_size' => '123233', // 视频附件尺寸（单位字节）
                                    'at_url' => 'http://www.vchangyi.com/001.jpg', // 视频附件地址
                                    'at_convert_url' => 'http://www.vchangyi.com/001.mp4', // 视频附件转码后的Url(文件、视频附件转码成功后才有值)
                                    'at_suffix' => 'xml', // 附件后缀
                                ),
                            ),
                            'files' => array( // 文件内容(source_type=4时使用,只有1个附件)
                                array(
                                    'at_id' => '8765432345', // 文件附件ID
                                    'at_name' => 'aaa', // 文件附件名称
                                    'at_time' => '12345432', // 文件附件时长(毫秒)
                                    'at_size' => '123233', // 文件附件尺寸（单位字节）
                                    'at_url' => 'http://www.vchangyi.com/001.jpg', // 文件附件地址
                                    'at_convert_url' => 'http://www.vchangyi.com/001.doc', // 文件附件转码后的Url(文件、视频附件转码成功后才有值)
                                    'at_suffix' => 'xml', // 附件后缀
                                )
                            ),
                            'attachs' => array( // 附件(source_type=1、2、3时使用,最多5个附件)
                                array(
                                    'at_id' => '8765432345', // 附件ID
                                    'at_name' => 'aaa', // 附件名称
                                    'at_time' => '12345432', // 附件时长(毫秒)
                                    'at_size' => '123233', // 附件尺寸（单位字节）
                                    'at_url' => 'http://www.vchangyi.com/001.jpg', // 附件地址
                                    'at_convert_url' => 'http://www.vchangyi.com/001.xml', // 附件转码后的Url(文件、视频附件转码成功后才有值)
                                    'at_suffix' => '.xml', // 附件后缀
                                ),
                             ),
                            'is_download' => 1, // 附件是否支持下载（1=不支持；2=支持）
                            'source_status' => 2, // 素材状态（1=转码中；2=正常）
                        ),
                        'summary' => '零食增加卫龙系列', // 摘要
                        'class_id' => 2, // 分类ID
                        'class_name' => '内部课程', // 分类名称
                        'ea_name' => '张三', // 创建者
                        'content' => '系列课程介绍', // 系列课程介绍
                        'tags' => '哈哈 啊啊', // 课程标签
                        'is_secret' => 1, // 是否保密（1=不保密，2=保密）
                        'is_share' => 1, // 允许分享（1=不允许，2=允许）
                        'is_notice' => 1, // 消息通知（1=不开启，2=开启）
                        'is_comment' => 1, // 评论功能（1=不开启，2=开启）
                        'is_like' => 1, // 点赞功能（1=不开启，2=开启）
                        'is_recommend' => 1, // 首页推荐（1=不开启，2=开启）
                        'is_exam' => 1, // 是否开启测评（1=未开启；2=已开启）
                        'exam_pass' => 1, // 当前课程评测状态（1=未通过；2=已通过）
                        'exam_url' => 'http://qy.vchangyi.com/hahaha.html', // 测评地址（测评开启且为通过测评的情况下返回）
                        'is_step' => 1, // 是否开启闯关（1=未开启；2=已开启）
                        'my_is_like' => 1, // 我是否点赞（1=未点赞，2=已点赞）
                        'is_outside' => 1, // 是否为公司外部人员（1=不是，2=是）
                        'my_is_favorite' => 1, // 我是否收藏（1=未收藏，2=已收藏）
                        'like_list' => array ( // 点赞列表(头像url)
                            'total' => 1000, // 点赞总数
                            'index' => 0, // 我在点赞人员头像列表的位置下标(我未点赞时,返回空字符串)
                            'face_list' => array( // 点赞人员头像列表
                                'http://www.vchangyi.com/001.jpg',
                                'http://www.vchangyi.com/002.jpg',
                            )
                        ),
                        'award' => array( // 激励信息
                            'award_id' => 1, // 激励ID
                            'award_action' => '第一个激励', // 激励行为
                            'description' => '第一个激励描述', // 描述
                            'award_type' => 1, // 激励类型（1=勋章；2=积分）
                            'medals' => array( // 勋章
                                'im_id' => 3, // 勋章ID
                                'icon' => 'http://qy.vchangyi.com/icon.jpg', // 勋章图标URL或者前端路径
                                'icon_type' => 1, // 勋章图标来源（1=用户上传；2=系统预设）
                                'name' => '勋章1', // 勋章名称
                                'desc' => '这是一个勋章', // 勋章描述
                            ),
                            'integral' => 3, // 积分
                        ),
                        )
     */

    public function Index_post()
    {
        // 登录人信息
        $user = $this->_login->user;
        // 验证规则
        $rules = [
            'article_id' => 'require|integer|gt:0',
            'source_id' => 'integer',
            'award_id' => 'integer',
            'is_share' => 'integer|in:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $article_id = $postData['article_id'];
        $source_id = isset($postData['source_id']) ? $postData['source_id'] : 0;
        $is_share = isset($postData['is_share']) ? $postData['is_share'] : 0;

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

        // 课程分类是否已启用
        $classServ = new ClassService();
        $classInfo = $classServ->get($articleInfo['class_id']);
        if (empty($classInfo) || $classInfo['is_open'] == Constant::CLASS_IS_OPEN_FALSE) {
            E('_ERR_CLASS_IS_OPEN_FALSE');
        }
        // 获取分类层级
        $classServ->getLevel($articleInfo['class_id'], $classLevel);
        if ($classLevel == 3) {
            // 如果是三级分类向上查找二级分类是否启用
            $parentClass = $classServ->get($classInfo['parent_id']);
            if (empty($parentClass) || $parentClass['is_open'] == Constant::CLASS_IS_OPEN_FALSE) {
                E('_ERR_CLASS_IS_OPEN_FALSE');
            }
        }

        // 课程学习权限
        $rightServ = new RightService();
        $checkRes = $rightServ->checkUserRight($user, $article_id);

        // 保密课程或非分享入口
        if ($articleInfo['is_secret'] == Constant::ARTICLE_IS_SECRET_TRUE || $is_share != Constant::RIGHT_INPUT_IS_SHARE_TRUE) {
            // 没有学习权限不允许学习
            if (!$checkRes) {
                E('_ERR_ARTICLE_CAN_NOT_STUDY');
            }
        }

        // 系列课程数据
        $articleSourceServ = new ArticleSourceService();
        $studyRecordServ = new StudyRecordService();
        if ($articleInfo['article_type'] == Constant::ARTICLE_TYPE_MULTI) {
            // 章节总数
            $source_total = $articleSourceServ->count_by_conds(['article_id' => $article_id]);
            $articleInfo['source_total'] = intval($source_total);
            // 外部人员检查
            if (!empty($user)) {
                // 已学章节总数
                $study_source_total = $studyRecordServ->count_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
                $articleInfo['study_source_total'] = intval($study_source_total);

                // 闯关开启，课程学习顺序检查
                if ($articleInfo['is_step'] == Constant::ARTICLE_IS_CHECK_TRUE) {
                    if (!empty($articleInfo['source_ids']) && isset($postData['source_id'])) {
                        $orderStudy = $this->_checkStudyOrder(unserialize($articleInfo['source_ids']), $postData['source_id'], $postData['article_id']);
                        if (!$orderStudy) {
                            E('_ERR_ARTICLE_SOURCE_STUDY_NEED_ORDER');
                        }
                    }
                }
            } else {
                $articleInfo['study_source_total'] = 0;
            }
            // 谁在学人员总数
            $articleInfo['who_study_total'] = $studyRecordServ->getUserCount($article_id);
            // 素材阅读人数
            if (!empty($source_id)) {
                $articleInfo['study_total'] = intval($studyRecordServ->count_by_conds(['article_id' => $article_id, 'source_id' => $source_id]));
            }
        }

        // 单课程取素材ID
        if ($articleInfo['article_type'] == Constant::ARTICLE_TYPE_SINGLE) {
            $source = $articleSourceServ->get_by_conds(['article_id' => $article_id]);
            $source_id = $source['source_id'];

            // 有测评并且考试未通过添加测评连接
            if (!empty($user)) {
                $articleInfo['exam_url'] = '';
                if ($articleInfo['is_exam'] == Constant::ARTICLE_IS_CHECK_TRUE) {
                    // 检查是否通过测评
                    $examPass = $this->_getExamResult($article_id, $user);
                    $articleInfo['exam_pass'] = $examPass ? Constant::ARTICLE_EXAM_IS_PASS : Constant::ARTICLE_EXAM_IS_FAIL;
                    // 题目存在且未通过测评
                    if (!empty($articleInfo['et_ids']) && !$examPass) {
                        $etIds = unserialize($articleInfo['et_ids']);
                        $etIds = implode(',', $etIds);
                        $articleInfo['exam_url'] = oaUrl('Frontend/Index/Exam/Index', ['article_id' => $articleInfo['article_id'], 'et_ids' => $etIds]);
                    }
                }

                // 我是否已学习
                $studyServ = new StudyService();
                $study = $studyServ->get_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
                if (empty($study)) {
                    $articleInfo['my_is_study'] = Constant::ARTICLE_IS_STUDY_FALSE;
                } else {
                    $articleInfo['my_is_study'] = Constant::ARTICLE_IS_STUDY_TRUE;
                }
            }
        }

        // 取素材信息
        if (!empty($source_id)) {
            $sourceInfo = $this->_getSource($source_id, $article_id);
            // 外部人员禁止下载附件
            if (empty($user)) {
                $sourceInfo['is_download'] = Constant::ATTACH_IS_DOWNLOAD_FALSE;
            }
            $articleInfo['sources'] = empty($sourceInfo) ? [] : $sourceInfo;
        }

        // RPC取能力模型信息
        $url = convertUrl(QY_DOMAIN . '/Contact/Rpc/Competence/Detail');
        $data = [
            'cm_id' => $articleInfo['cm_id'],
        ];
        $detail = \Com\Rpc::phprpc($url)->invoke('index', $data);
        if (!empty($detail) && is_array($detail)) {
            $articleInfo['cm_name'] = $detail['cm_name'];
        } else {
            $articleInfo['cm_name'] = '';
        }

        // 点赞列表头像
        list($articleInfo['like_list'], $likeUids) = $this->_getLikeList($article_id, $user);

        // 外部人员判断
        if (empty($user)) {
            $articleInfo['is_notice'] = Constant::ARTICLE_IS_COMMENT_FALSE;
            $articleInfo['is_like'] = Constant::ARTICLE_IS_LIKE_FALSE;
            $articleInfo['is_outside'] = Constant::RIGHT_IS_OUTSIDE_YES;
            $articleInfo['is_comment'] = Constant::ARTICLE_IS_COMMENT_FALSE;
            $articleInfo['is_exam'] = Constant::ARTICLE_IS_EXAM_FALSE;
        } else {
            $articleInfo['is_outside'] = Constant::RIGHT_IS_OUTSIDE_NO;

            // 激励信息
            $awardInfo = [];
            if (isset($postData['award_id'])) {
                $awardServ = new AwardService();
                $awardInfo = $awardServ->get($postData['award_id']);
                // 激励类型：勋章
                if (!empty($awardInfo) && $awardInfo['award_type'] == Constant::AWARD_TYPE_IS_MEDAL) {
                    $medal_id = $awardInfo['medal_id'];
                    $IntegralServ = new Integral();
                    $integralList = $IntegralServ->listMedal();
                    if (!empty($integralList)) {
                        $integralList = array_combine_by_key($integralList, 'im_id');
                    }
                    $awardInfo['medals'] = isset($integralList[$medal_id]) ? $integralList[$medal_id] : [];
                }
            }

            // 我是否已点赞
            $articleInfo['my_is_like'] = in_array($user['memUid'], $likeUids) ? Constant::ARTICLE_IS_LIKE_TRUE : Constant::ARTICLE_IS_LIKE_FALSE;

            // RPC查询收藏结果
            $param = [
                'uid' => $user['memUid'],
                'dataId' => $article_id,
            ];
            $rpcFavorite = &RpcFavoriteHelper::instance();
            $status = $rpcFavorite->getStatus($param);
            $articleInfo['my_is_favorite'] = Constant::ARTICLE_IS_FAVORITE_FALSE;
            if (isset($status['collection']) && $status['collection'] == RpcFavoriteHelper::COLLECTION_YES) {
                $articleInfo['my_is_favorite'] = Constant::ARTICLE_IS_FAVORITE_TRUE;
            }
        }
        $articleInfo['award'] = isset($awardInfo) ? $awardInfo : [];

        $this->_result = $articleInfo;
    }

    /**
     * 根据素材ID取素材详情
     * @param int $sourceId 素材ID
     * @param int $articleId 课程ID（权限载体）
     * @return array 素材详情
     */
    private function _getSource($sourceId, $articleId)
    {
        $sourceServ = new SourceService();
        $sourceInfo = $sourceServ->get($sourceId);
        if (empty($sourceInfo)) {
            return [];
        }
        // 音频素材反序列号
        $sourceInfo['audio_imgs'] = empty($sourceInfo['audio_imgs']) ? '' : unserialize($sourceInfo['audio_imgs']);
        // 取素材附件
        $attach = [
            'videos' => [],
            'files' => [],
            'attachs' => [],
        ];
        $attachServ = new SourceAttachService();
        $list = $attachServ->list_by_conds(['source_id' => $sourceId]);
        if ($list) {
            foreach ($list as $k => $v) {
                // 取尾缀
                $v['at_suffix'] = end(explode('.', $v['at_name']));
                switch ($v['at_type']) {
                    // 视频文件
                    case Constant::ATTACH_TYPE_VIDEO:
                        $attach['videos'][] = $v;
                        break;
                    // 文件附件
                    case Constant::ATTACH_TYPE_FILE:
                        $v['at_url'] .= $list[$k]['at_url'] ? '&_id=' . $articleId : '';
                        $v['at_convert_url'] .= $list[$k]['at_convert_url'] ? '&_id=' . $articleId : '';

                        if ($sourceInfo['source_type'] == Constant::SOURCE_TYPE_FILE) {
                            $attach['files'][] = $v;
                        } else {
                            $attach['attachs'][] = $v;
                        }
                        break;
                }
            }
        }
        // 素材加入附件
        $sourceInfo = array_merge($sourceInfo, $attach);

        return $sourceInfo;
    }

    /**
     * 取点赞信息
     * @param int $articleId 课程ID
     * @param array $user 当前人员信息
     * @return array
     *          + array $like_list 点赞信息
     *          + array $uids 已点赞的人员ID
     */
    private function _getLikeList($articleId, $user)
    {
        if (empty($user)) {
            $user['memUid'] = '';
        }
        $like_list = [
            'total' => 0,
            'index' => '',
            'face_list' => [],
        ];
        $likeServ = new LikeService();
        $likeList = $likeServ->list_by_conds(['article_id' => $articleId], [], ['created' => 'desc']);
        // 已点赞的人员ID
        $uids = [];
        if ($likeList) {
            // 点赞人员头像列表
            $uids = array_column($likeList, 'uid');
            $userServ = &User::instance();
            $users = $userServ->listAll(['memUids' => $uids]);
            if (!empty($users)) {
                $users = array_combine_by_key($users, 'memUid');
            }
            foreach ($uids as $index => $uid) {
                // 我的点赞在点赞人员头像列表的位置下标
                if ($uid == $user['memUid']) {
                    $like_list['index'] = $index;
                }
                $like_list['face_list'][] = isset($users[$uid]) ? $users[$uid]['memFace'] : '';
            }

            $like_list['total'] = count($likeList);
        }
        return [$like_list, $uids];
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

    /**
     * @desc 开启闯关的情况下，检查章节学习顺序
     * @author tangxingguo
     * @param array $orderSourceIds 章节ID（顺序）
     * @param int $sourceId 当前章节
     * @param int $article_id 课程ID
     * @return bool 可以学习返回true,不可学习返回false
     */
    private function _checkStudyOrder($orderSourceIds, $sourceId, $article_id)
    {
        // 第一个章节，返回可学
        if ($orderSourceIds[0] == $sourceId) {
            return true;
        } else {
            // 取当前章节之前的章节(需要学习完成的课程)
            $needStudy = [];
            foreach ($orderSourceIds as $k => $v) {
                if ($sourceId != $v) {
                    $needStudy[] = $v;
                } else {
                    break;
                }
            }
            // 取已学章节
            $user = $this->_login->user;
            $studyRecordServ = new StudyRecordService();
            $studyList = $studyRecordServ->list_by_conds(['article_id' => $article_id, 'uid' => $user['memUid']]);
            if (empty($studyList)) {
                return false;
            }
            $studyIds = array_column($studyList, 'source_id');

            // 对比需要学与已学
            $diff = array_diff($needStudy, $studyIds);
            if (empty($diff)) {
                return true;
            }
        }
        return false;
    }
}
