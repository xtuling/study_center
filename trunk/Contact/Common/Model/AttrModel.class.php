<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 14:31
 */

namespace Common\Model;

class AttrModel extends AbstractModel
{

    /**
     * 属性类型:单行文本
     */
    const ATTR_TYPE_SINGLE_TEXT = 1;

    /**
     * 属性类型:多行文本
     */
    const ATTR_TYPE_MULTIPLE_TEXT = 2;

    /**
     * 属性类型:数字
     */
    const ATTR_TYPE_NUMBER = 3;

    /**
     * 属性类型:日期
     */
    const ATTR_TYPE_DATE = 4;

    /**
     * 属性类型:时间
     */
    const ATTR_TYPE_TIME = 5;

    /**
     * 属性类型:日期时间
     */
    const ATTR_TYPE_DATE_TIME = 6;

    /**
     * 属性类型:单选
     */
    const ATTR_TYPE_RADIO = 7;

    /**
     * 属性类型:多选
     */
    const ATTR_TYPE_CHECKBOX = 8;

    /**
     * 属性类型:地址
     */
    const ATTR_TYPE_ADDRESS = 9;

    /**
     * 属性类型:图片
     */
    const ATTR_TYPE_PICTURE = 10;

    /**
     * 属性类型:下拉框单选
     */
    const ATTR_TYPE_DROPBOX = 11;

    /**
     * 属性类型:部门
     */
    const ATTR_TYPE_SPECIAL = 999;

    /**
     * 属性类型:直属上级
     */
    const ATTR_TYPE_LEADER = 998;

    /**
     * 是否系统属性:是
     */
    const IS_SYSTEM_TRUE = 1;

    /**
     * 手机端区域:联系信息
     */
    const AREA_CONTACT = 1;

    /**
     * 手机端区域:个人信息
     */
    const AREA_PERSONAL = 2;

    /**
     * 手机端区域:身份信息
     */
    const AREA_IDENTITY = 3;

    /**
     * 管理后台区域:自定义信息
     */
    const AREA_CUSTOM = 4;

    /**
     * 属性是否开启,是
     */
    const ATTR_IS_OPEN_TRUE = 1;

    /**
     * 属性是否在前端显示,是
     */
    const ATTR_IS_SHOW_TRUE = 1;

    /**
     * 属性是否必填,是
     */
    const ATTR_IS_REQUIRED_TRUE = 1;

    /**
     * 属性是否必填,否
     */
    const ATTR_IS_REQUIRED_FALSE = 0;

    /**
     * 是否允许编辑:是
     */
    const ATTR_IS_EDIT_TRUE = 1;

    /**
     * 手机端是否允许编辑 1: 允许 0: 不允许
     */
    const ATTR_ALLOW_USER_MODIFY = 1;
    const ATTR_NOT_ALLOWED_USER_MODIFY = 0;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 获取最大顺序值
     * @author liyifei
     * @return array
     */
    public function getMaxOrder()
    {

        $sql = "SELECT MAX(`order`) AS max_order FROM __TABLE__ WHERE `domain` = ? AND status < ?";

        $param = [
            QY_DOMAIN,
            self::ST_DELETE,
        ];

        return $this->_m->fetch_row($sql, $param);
    }

    /**
     * 获取已用扩展字段
     * @author liyifei
     * @param array $fields 已开放的扩展字段
     * @return array|bool
     */
    public function getUsedField($fields = [])
    {

        $sql = "SELECT DISTINCT `field_name` FROM __TABLE__
                WHERE `field_name` IN (?) AND `domain` = ? AND status < ? ";

        $param = [
            $fields,
            QY_DOMAIN,
            self::ST_DELETE,
        ];

        return $this->_m->fetch_array($sql, $param);
    }
}
