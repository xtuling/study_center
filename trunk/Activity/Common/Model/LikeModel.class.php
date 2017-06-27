<?php

namespace Common\Model;

class LikeModel extends AbstractModel
{

    // 评论点赞
    const TYPE_COMMENT = 2;

    // 活动点赞
    const TYPE_ACTIVITY = 1;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
}
