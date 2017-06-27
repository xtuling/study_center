<?php
/**
 * AbstractModel.class.php
 * Model 层基类
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Model;

abstract class AbstractModel extends \Com\Model
{
    /**
     * 维护表名，主要用处是连表查询的时候避免表名的硬编码。
     */
    protected $_tb_depot;

    protected $_tb_topic;


    // 构造方法
    public function __construct()
    {
        parent::__construct();

        $db_prefix = cfg('DB_PREFIX');
        $this->_tb_depot = "{$db_prefix}depot";
        $this->_tb_topic = "{$db_prefix}topic";

    }
}
