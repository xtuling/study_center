<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/17
 * Time: 上午11:46
 */

namespace Common\Model;


class ImportDataModel extends AbstractModel
{
    // 导入结果: 未知
    const IS_ERROR_TYPE_UNKNOWN = 0;
    // 导入结果: 已出错
    const IS_ERROR_TYPE_ERROR = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

}