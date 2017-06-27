<?php
/**
 * Plugin.class.php
 * 应用公共处理类
 *
 * @author    Deepseath
 * @version   $Id$
 * @copyright vchangyi.com
 */
namespace Com;

use Think\Model as ThinkPHPModel;

class Plugin
{

    /**
     * 导入默认数据
     *
     * @param array $data (引用结果)要导入的数据，为空则导入默认数据。
     *                    数据结构可见:/[APP]/Common/Sql/data.php
     *
     * @return boolean
     */
    public function importDefaultData(array &$data = array())
    {

        if (empty($data)) {
            // 未定义导入的数据，则读取默认数据
            $file = PLUGIN_PATH . 'Common' . D_S . 'Sql' . D_S . 'data.php';
            $data = load_config($file);
        }

        if (empty($data) || ! is_array($data)) {
            // 空数据则直接返回
            return true;
        }

        // 遍历数据数组，拼凑 SQL 语句
        foreach ($data as $_table => $_data) {
            $model = new ThinkPHPModel($_table);
            foreach ($_data as $__data) {
                $_field = $_val = '';
                $_sql = array();
                $_params = array();

                // 加入数据状态
                if (! isset($__data['status'])) {
                    $__data['status'] = Model::ST_CREATE;
                }
                // 加入数据创建时间
                if (! isset($__data['created'])) {
                    $__data['created'] = NOW_TIME;
                }
                // 加入数据更新时间
                if (! isset($__data['updated'])) {
                    $__data['updated'] = NOW_TIME;
                }
                // 加入企业标识
                if (! isset($__data['domain'])) {
                    $__data['domain'] = QY_DOMAIN;
                }
                // 构造查询字段数组
                foreach ($__data as $_field => $_val) {
                    $_sql[] = "`{$_field}`=?";
                    $_params[] = $_val;
                }

                $model->execsql("REPLACE INTO __TABLE__ SET " . implode(', ', $_sql), $_params);
            }
            unset($__data, $_field, $_sql, $_params);
        }
        unset($_data, $_table);

        return true;
    }
}
