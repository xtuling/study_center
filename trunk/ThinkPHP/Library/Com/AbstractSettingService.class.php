<?php
/**
 * AbstractSettingService.class.php
 * 应用 setting 表的 Service 基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

namespace Com;

abstract class AbstractSettingService extends Service
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 读取所有
     *
     * @see \Common\Service\AbstractSettingService::list_kv()
     * @return array
     */
    public function list_kv()
    {

        // 查询
        $list = $this->_d->list_all();
        // 重新整合, 改成 key-value 键值对
        $sets = array();
        foreach ($list as $_set) {
            if ($this->_d->get_type_array() == $_set['type']) {
                $sets[$_set['key']] = unserialize($_set['value']);
            } else {
                $sets[$_set['key']] = $_set['value'];
            }
        }

        return $sets;
    }

}
