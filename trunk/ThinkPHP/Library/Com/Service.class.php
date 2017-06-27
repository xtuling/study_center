<?php

/**
 * Service.class.php
 * $author$
 */
namespace Com;

abstract class Service
{

    /**
     * 默认数据操作类实例
     *
     * @type null|Model
     */
    protected $_d = null;

    // 构造方法
    public function __construct($name = '')
    {
        // do nothing.
    }

    // 获取错误码
    public function get_errcode()
    {

        return Error::instance()->get_errcode();
    }

    // 获取错误信息
    public function get_errmsg()
    {

        return Error::instance()->get_errmsg();
    }

    /**
     * 设置错误信息
     *
     * @param mixed $message 错误信息
     * @param int $code 错误号
     *
     * @return bool
     */
    protected function _set_error($message, $code = 0)
    {

        Error::instance()->set_error($message, $code);

        return true;
    }

    // 获取表字段前缀
    public function get_prefield()
    {

        static $prefield = null;
        // 如果前缀不为 null, 则直接返回
        if (null != $prefield) {
            return $prefield;
        }
        // 如果 model 未初始化
        if (null == $this->_d) {
            E('_ERR_SERVICE_MODEL_UN_INIT');

            return false;
        }
        $prefield = $this->_d->prefield;

        return $prefield;
    }

    /**
     * 根据主键值获取单条数据
     *
     * @param string $val 值
     *
     * @return array|bool
     */
    public function get($val)
    {

        try {
            // 设置条件
            return $this->_d->get($val);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 获取数据列表
     *
     * @param int|array $page_option 分页参数
     *                                + int => limit $page_option
     *                                + array => limit $page_option[0], $page_option[1]
     * @param array $order_option 排序信息
     *
     * @return array|bool
     */
    public function list_all($page_option = null, $order_option = array())
    {

        try {
            return $this->_d->list_all($page_option, $order_option);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 获取数据列表
     *
     * @param int|array $page_option 分页参数
     *                                + int => limit $page_option
     *                                + array => limit $page_option[0], $page_option[1]
     * @param array $order_option 排序信息
     *
     * @return array|bool
     */
    public function list_all_without_domain($page_option = null, $order_option = array())
    {

        try {
            return $this->_d->list_all_without_domain($page_option, $order_option);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 删除字段
     *
     * @param mixed $vals 主键对应的值
     *
     * @return object
     */
    public function delete($vals)
    {

        try {
            return $this->_d->delete($vals);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 更新数据
     *
     * @param int $val 主键键值
     * @param array $data 待更新数据
     *
     * @return bool
     */
    public function update($val = null, $data = array())
    {

        try {
            return $this->_d->update($val, $data);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 统计总数
     *
     * @param mixed $page_options 分页选项
     *
     * @return array|bool
     */
    public function count($page_options = 0)
    {

        try {
            return $this->_d->count($page_options);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据主键值读取数据
     *
     * @param int|string|array $vals 查询条件
     * @param array $orders 排序
     *
     * @return array|bool
     */
    public function list_by_pks($vals, $orders = array())
    {

        try {
            return $this->_d->list_by_pks($vals, $orders);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件读取数据
     *
     * @param array $conds 条件数组
     *
     * @return array|bool
     */
    public function get_by_conds($conds, $order_option = array())
    {

        try {
            return $this->_d->get_by_conds($conds, $order_option);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件读取数据数组
     *
     * @param array $conds 条件数组
     * @param int|array $page_option 分页参数
     * @param array $order_option 排序
     * @param string $field 字段信息
     *
     * @return array|bool
     */
    public function list_by_conds($conds, $page_option = null, $order_option = array(), $field = '*')
    {

        try {
            return $this->_d->list_by_conds($conds, $page_option, $order_option, $field);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件更新数据
     *
     * @param array $conds 条件数组
     * @param array $data 数据数组
     *
     * @return bool
     */
    public function update_by_conds($conds, $data)
    {

        try {
            return $this->_d->update_by_conds($conds, $data);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件删除数据
     *
     * @param array $conds 删除条件数组
     *
     * @return bool
     */
    public function delete_by_conds($conds)
    {

        try {
            return $this->_d->delete_by_conds($conds);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件计算数量
     *
     * @param array $conds
     * @param string $field
     *
     * @return number
     */
    public function count_by_conds($conds, $field = '*')
    {

        try {
            return $this->_d->count_by_conds($conds, $field);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 插入单条数据
     *
     * @param array $data 待插入数据
     * @param array $options 表达式
     * @param boolean $replace 是否使用 REPLACE INTO
     *
     * @return \Think\mixed
     */
    public function insert($data, $options = array(), $replace = false)
    {

        try {
            return $this->_d->insert($data, $options, $replace);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 插入多条数据
     *
     * @param array $list 待插入数据数组
     * @param array $options 表达式
     * @param boolean $replace 是否使用 REPLACE INTO
     *
     * @return bool|Ambigous
     */
    public function insert_all($list, $options = array(), $replace = false)
    {

        try {
            return $this->_d->insert_all($list, $options, $replace);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    // 开始存储过程
    public function start_trans()
    {

        return $this->_d->start_trans();
    }

    // 回滚
    public function rollback()
    {

        return $this->_d->rollback();
    }

    // 提交
    public function commit()
    {

        return $this->_d->commit();
    }
}
