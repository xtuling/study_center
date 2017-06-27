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
     * 分页:默认页数
     */
    const PAGING_DEFAULT_PAGE = 1;

    /**
     * 分页:默认当前页数据总数
     */
    const PAGING_DEFAULT_LIMIT = 20;

    /**
     * 文件附件上传上限:5个
     */
    const UPLOAD_FILE_ATTACH_LIMIT = 5;

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

}
