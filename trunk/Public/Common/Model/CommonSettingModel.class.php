<?php
/**
 * SettingModel.class.php
 * 公共设置表 Model
 * @author Deepseath
 * @version $Id$
 */
namespace Common\Model;

class CommonSettingModel extends AbstractModel
{

    /**
     * 数值类型：数组
     */
    const TYPE_ARRAY = 1;

    /**
     * 数值类型：标量
     */
    const TYPE_SCALAR = 0;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
}
