<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/3/20
 * Time: 14:14
 */

namespace Common\Common;

class Constant
{
    /**
     * 允许下载文件上限：20M
     */
    const DOWNLOAD_FILE_MAX_SIZE = 20 * 1024 * 1024;

    /**
     * 课程未读总数刷新间隔时间（2分钟）
     */
    const ARTICLE_REFRESH_TIME = 60 * 1000 * 2;

    /**
     * 分页:默认页数
     */
    const PAGING_DEFAULT_PAGE = 1;

    /**
     * 分页:默认当前页数据总数
     */
    const PAGING_DEFAULT_LIMIT = 20;

    /**
     * 启用分类:禁用
     */
    const CLASS_IS_OPEN_FALSE = 1;

    /**
     * 启用分类:启用
     */
    const CLASS_IS_OPEN_TRUE = 2;

    /**
     * 权限是否为全公司：否
     */
    const RIGHT_IS_ALL_FALSE = 1;

    /**
     * 权限是否为全公司：是
     */
    const RIGHT_IS_ALL_TRUE = 2;

    /**
     * 权限类型:全公司
     */
    const RIGHT_TYPE_ALL = 1;

    /**
     * 权限类型:部门
     */
    const RIGHT_TYPE_DEPARTMENT = 2;

    /**
     * 权限类型:标签
     */
    const RIGHT_TYPE_TAG = 3;

    /**
     * 权限类型:人员
     */
    const RIGHT_TYPE_USER = 4;

    /**
     * 权限类型:职位
     */
    const RIGHT_TYPE_JOB = 5;

    /**
     * 权限类型:角色
     */
    const RIGHT_TYPE_ROLE = 6;

    /**
     * 素材类型:无
     */
    const SOURCE_TYPE_EMPTY = 0;

    /**
     * 素材类型:图文
     */
    const SOURCE_TYPE_IMG_TEXT = 1;

    /**
     * 素材类型:音图
     */
    const SOURCE_TYPE_AUDIO_IMG = 2;

    /**
     * 素材类型:视频
     */
    const SOURCE_TYPE_VEDIO = 3;

    /**
     * 素材类型:文件
     */
    const SOURCE_TYPE_FILE = 4;

    /**
     * 素材类型:外部
     */
    const SOURCE_TYPE_LINK = 5;

    /**
     * 素材状态：转码中
     */
    const SOURCE_STATUS_CONVERT = 1;

    /**
     * 素材状态：正常
     */
    const SOURCE_STATUS_NORMAL = 2;

    /**
     * 附件类型:视频
     */
    const ATTACH_TYPE_VIDEO = 1;

    /**
     * 附件类型:文件
     */
    const ATTACH_TYPE_FILE = 2;

    /**
     * 附件是否支持下载:不支持
     */
    const ATTACH_IS_DOWNLOAD_FALSE = 1;

    /**
     * 附件是否支持下载:支持
     */
    const ATTACH_IS_DOWNLOAD_TRUE = 2;

    /**
     * 课程类型：单课程
     */
    const ARTICLE_TYPE_SINGLE = 1;

    /**
     * 课程类型：系列课程
     */
    const ARTICLE_TYPE_MULTI = 2;

    /**
     * 课程是否保密:不保密
     */
    const ARTICLE_IS_SECRET_FALSE = 1;

    /**
     * 课程是否保密:保密
     */
    const ARTICLE_IS_SECRET_TRUE = 2;

    /**
     * 课程是否允许分享:不允许
     */
    const ARTICLE_IS_SHARE_FALSE = 1;

    /**
     * 课程是否允许分享:允许
     */
    const ARTICLE_IS_SHARE_TRUE = 2;

    /**
     * 课程消息通知:不开启
     */
    const ARTICLE_IS_NOTICE_FALSE = 1;

    /**
     * 课程消息通知:开启
     */
    const ARTICLE_IS_NOTICE_TRUE = 2;

    /**
     * 课程评论功能:不开启
     */
    const ARTICLE_IS_COMMENT_FALSE = 1;

    /**
     * 课程评论功能:开启
     */
    const ARTICLE_IS_COMMENT_TRUE = 2;

    /**
     * 课程点赞功能:不开启
     */
    const ARTICLE_IS_LIKE_FALSE = 1;

    /**
     * 课程点赞功能:开启
     */
    const ARTICLE_IS_LIKE_TRUE = 2;

    /**
     * 课程首页推荐:不开启
     */
    const ARTICLE_IS_RECOMMEND_FALSE = 1;

    /**
     * 课程首页推荐:开启
     */
    const ARTICLE_IS_RECOMMEND_TRUE = 2;

    /**
     * 课程是否需要测评：不评测
     */
    const ARTICLE_IS_EXAM_FALSE = 1;

    /**
     * 课程是否需要测评：评测
     */
    const ARTICLE_IS_EXAM_TRUE = 2;

    /**
     * 课程是否开启闯关：未开启
     */
    const ARTICLE_IS_STEP_FALSE = 1;

    /**
     * 课程是否开启闯关：已开启
     */
    const ARTICLE_IS_STEP_TRUE = 2;

    /**
     * 课程状态：草稿
     */
    const ARTICLE_STATUS_DRAFT = 1;

    /**
     * 课程状态：已发布
     */
    const ARTICLE_STATUS_SEND = 2;

    /**
     * 课程是否已学:未学
     */
    const ARTICLE_IS_STUDY_FALSE = 1;

    /**
     * 课程是否已学:已学
     */
    const ARTICLE_IS_STUDY_TRUE = 2;

    /**
     * 摘要长度:54字符
     */
    const SUMMARY_LENGTH = 54;

    /**
     * 计划任务执行时间表达式（1分钟执行一次）
     */
    const TASK_CRON_TIME = '0 0/1 * * * ?';

    /**
     * 点赞操作类型:点赞
     */
    const LIKE_TYPE_ADD = 2;

    /**
     * 点赞操作类型:取消点赞
     */
    const LIKE_TYPE_DELETE = 1;

    /**
     * 我是否收藏:未收藏
     */
    const ARTICLE_IS_FAVORITE_FALSE = 1;

    /**
     * 我是否收藏:已收藏
     */
    const ARTICLE_IS_FAVORITE_TRUE = 2;

    /**
     * 章节学习状态:未学
     */
    const ARTICLE_SOURCE_STUDY_STATUS_FALSE = 1;

    /**
     * 章节学习状态:已学
     */
    const ARTICLE_SOURCE_STUDY_STATUS_TRUE = 2;
    
    /**
     * 激励类型:勋章
     */
    const AWARD_TYPE_IS_MEDAL = 1;

    /**
     * 激励类型:积分
     */
    const AWARD_TYPE_IS_INTEGRAL = 2;

    /**
     * 题目类型:1=单选题；2=判断题；3=问答题；4=多选题；5=语音题
     */
    const EXAM_TYPE_LIST = [1 => '单选题', 2 => '判断题', 3 => '问答题', 4 => '多选题', 5 => '语音题'];

    /**
     * 是否开启闯关:未开启
     */
    const ARTICLE_IS_CHECK_FALSE = 1;

    /**
     * 是否开启闯关:已开启
     */
    const ARTICLE_IS_CHECK_TRUE = 2;

    /**
     * 测评是否通过:未通过
     */
    const ARTICLE_EXAM_IS_FAIL = 1;

    /**
     * 测评是否通过:已通过
     */
    const ARTICLE_EXAM_IS_PASS = 2;

    /**
     * 测评是否开启:未开启
     */
    const ARTICLE_IS_EXAM_FAIL = 1;

    /**
     * 测评是否开启:已开启
     */
    const ARTICLE_IS_EXAM_PASS = 2;

    /**
     * 是否外部人员:不是
     */
    const RIGHT_IS_OUTSIDE_NO = 1;

    /**
     * 是否外部人员:是
     */
    const RIGHT_IS_OUTSIDE_YES = 2;

    /**
     * 是否是分享入口:不是
     */
    const RIGHT_INPUT_IS_SHARE_FALSE = 1;

    /**
     * 是否是分享入口:是
     */
    const RIGHT_INPUT_IS_SHARE_TRUE = 2;
}
