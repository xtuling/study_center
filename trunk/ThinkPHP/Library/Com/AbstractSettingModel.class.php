<?php
/**
 * AbstractSettingModel.class.php
 * 应用 setting 表的 model 基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
namespace Com;

abstract class AbstractSettingModel extends Model
{

    /** 数据类型: 数组 */
    const TYPE_ARRAY = 1;
    /** 数据类型: 字串 */
    const TYPE_NORMAL = 0;

    /**
     * 构造方法
     */
    public function __construct($name = '', $table_prefix = '', $connection = '')
    {
        parent::__construct($name, $table_prefix, $connection);
        $this->prefield = '';
    }

    /**
     * 获取数组类型标识
     * @return string
     */
    public function get_type_array()
    {
        return self::TYPE_ARRAY;
    }

    /**
     * 获取字串类型标识
     * @return string
     */
    public function get_type_normal()
    {
        return self::TYPE_NORMAL;
    }

}
