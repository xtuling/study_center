<?php
/**
 * 考试-标签信息表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:06:47
 * @version $Id$
 */

namespace Common\Model;

class TagModel extends AbstractModel
{
    // 手动导入
    const TAG_TYPE_MANUAL = 0;

    // 关联导入
    const TAG_TYPE_RELATION = 1;


    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
}