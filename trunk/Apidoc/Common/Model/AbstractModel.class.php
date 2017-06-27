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
    protected $_tb_auth;

    // 构造方法
    public function __construct()
    {

        parent::__construct();

        $db_prefix = cfg('DB_PREFIX');
        $this->_tb_auth = "{$db_prefix}auth";
    }

    /**
     * 封装基础SQL where条件
     * @return array
     */
    public function getBaseWhere()
    {

        $where = ' WHERE `status`<? AND `domain`=?';
        $params = array(
            self::ST_DELETE,
            QY_DOMAIN,
        );

        return array($where, $params);
    }

}
