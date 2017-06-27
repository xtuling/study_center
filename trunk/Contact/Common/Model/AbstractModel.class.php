<?php
/**
 * AbstractModel.class.php
 * Model 层基类
 * @author   : zhuxun37
 * @version  : $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Model;

abstract class AbstractModel extends \Com\Model
{

    /**
     * 维护表名，主要用处是连表查询的时候避免表名的硬编码。
     */
    protected $_tb_attr = '';

    protected $_tb_import_record = '';

    protected $_tb_rightTable = '';

    /**
     * 消息状态：保密
     */
    const IS_SECRET = 1;
    /**
     * 消息状态：不保密
     */
    const NO_SECRET = 0;

    // 构造方法
    public function __construct()
    {

        $this->_table_prefix = cfg('DB_PREFIX') . 'contact_';
        parent::__construct();
        $this->_tb_attr = cfg('DB_PREFIX') . 'contact_attr';
        $this->_tb_import_record = cfg('DB_PREFIX') . 'import_record';
        $this->_tb_rightTable = cfg('DB_PREFIX') . 'contact_invite_user_right';
    }

}
