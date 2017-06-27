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
     * 分页:默认页数
     */
    const PAGING_DEFAULT_PAGE = 1;

    /**
     * 分页:默认当前页数据总数
     */
    const PAGING_DEFAULT_LIMIT = 20;

    /**
     * 新闻未读总数刷新时间（2分钟）
     */
    const NEWS_UNREAD_TIME = 60 * 1000 * 2;

    /**
     * 自动截取摘要长度:54字符
     */
    const AUTO_SUMMARY_LENGTH = 54;

    /**
     * 文件附件上传上限:5个
     */
    const UPLOAD_FILE_ATTACH_LIMIT = 5;

    /**
     * 启用分类:禁用
     */
    const CLASS_IS_OPEN_FALSE = 1;

    /**
     * 启用分类:启用
     */
    const CLASS_IS_OPEN_TRUE = 2;

    /**
     * 适用范围类型:分类
     */
    const RIGHT_CLASS_TYPE_CLASS = 1;

    /**
     * 适用范围类型:新闻
     */
    const RIGHT_CLASS_TYPE_NEWS = 2;

    /**
     * 适用范围是否全公司:否
     */
    const RIGHT_IS_ALL_FALSE = 1;

    /**
     * 适用范围是否全公司:是
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
     * 是否正文显示封面图片:不显示
     */
    const NEWS_IS_SHOW_COVER_FALSE = 1;

    /**
     * 是否正文显示封面图片:显示
     */
    const NEWS_IS_SHOW_COVER_TRUE = 2;

    /**
     * 附件是否支持下载:不支持
     */
    const NEWS_IS_DOWNLOAD_FALSE = 1;

    /**
     * 附件是否支持下载:支持
     */
    const NEWS_IS_DOWNLOAD_TRUE = 2;

    /**
     * 是否保密:不保密
     */
    const NEWS_IS_SECRET_FALSE = 1;

    /**
     * 是否保密:保密
     */
    const NEWS_IS_SECRET_TRUE = 2;

    /**
     * 是否允许分享:不允许
     */
    const NEWS_IS_SHARE_FALSE = 1;

    /**
     * 是否允许分享:允许
     */
    const NEWS_IS_SHARE_TRUE = 2;

    /**
     * 消息通知:不开启
     */
    const NEWS_IS_NOTICE_FALSE = 1;

    /**
     * 消息通知:开启
     */
    const NEWS_IS_NOTICE_TRUE = 2;

    /**
     * 评论功能:不开启
     */
    const NEWS_IS_COMMENT_FALSE = 1;

    /**
     * 评论功能:开启
     */
    const NEWS_IS_COMMENT_TRUE = 2;

    /**
     * 点赞功能:不开启
     */
    const NEWS_IS_LIKE_FALSE = 1;

    /**
     * 点赞功能:开启
     */
    const NEWS_IS_LIKE_TRUE = 2;

    /**
     * 首页推荐:不开启
     */
    const NEWS_IS_RECOMMEND_FALSE = 1;

    /**
     * 首页推荐:开启
     */
    const NEWS_IS_RECOMMEND_TRUE = 2;

    /**
     * 新闻状态:草稿
     */
    const NEWS_STATUS_DRAFT = 1;

    /**
     * 新闻状态:已发布
     */
    const NEWS_STATUS_SEND = 2;

    /**
     * 新闻状态:预发布
     */
    const NEWS_STATUS_READY_SEND = 3;

    /**
     * 附件类型:视频
     */
    const ATTACH_TYPE_VIDEO = 1;

    /**
     * 附件类型:音频
     */
    const ATTACH_TYPE_AUDIO = 2;

    /**
     * 附件类型:其他
     */
    const ATTACH_TYPE_FILE = 3;

    /**
     * 阅读状态:已读
     */
    const READ_STATUS_IS_YES = 2;

    /**
     * 阅读状态:未读
     */
    const READ_STATUS_IS_NO = 1;

    /**
     * 点赞:对于新闻的点赞
     */
    const LIKE_TYPE_ARTICLE = 1;

    /**
     * 点赞:对于评论的点赞
     */
    const LIKE_TYPE_COMMENT = 2;

    /**
     * 评论操作类型:发表评论
     */
    const COMMENT_TYPE_ADD = 1;

    /**
     * 评论操作类型:删除评论
     */
    const COMMENT_TYPE_DELETE = 2;

    /**
     * 收藏操作类型:收藏
     */
    const FAVORITE_TYPE_ADD = 2;

    /**
     * 收藏操作类型:取消收藏
     */
    const FAVORITE_TYPE_DELETE = 1;

    /**
     * 是否外部人员:不是
     */
    const RIGHT_IS_OUTSIDE_NO = 1;

    /**
     * 是否外部人员:是
     */
    const RIGHT_IS_OUTSIDE_YES = 2;

    /**
     * 是否直接跳转外链:不直接跳转
     */
    const NEWS_IS_JUMP_FALSE = 1;

    /**
     * 是否直接跳转外链:直接跳转
     */
    const NEWS_IS_JUMP_TRUE = 2;

    /**
     * 是否是分享入口:不是
     */
    const RIGHT_INPUT_IS_SHARE_FALSE = 1;

    /**
     * 是否是分享入口:是
     */
    const RIGHT_INPUT_IS_SHARE_TRUE = 2;
}
