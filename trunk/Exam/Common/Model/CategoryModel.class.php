<?php
/**
 * 考试-试卷分类表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:05:45
 * @version $Id$
 */

namespace Common\Model;

class CategoryModel extends AbstractModel
{

    // 禁用分类
    const CATEGORY_STATUS_DISABLE = 0;

    // 启用分类
    const CATEGORY_STATUS_ENABLE = 1;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
}