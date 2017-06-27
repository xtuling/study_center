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
    protected $_tb_article;

    protected $_tb_class;

    protected $_tb_favorite;

    protected $_tb_learned;

    protected $_tb_right;


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
        parent::__construct();

        $db_prefix = cfg('DB_PREFIX');
        $this->_tb_article = "{$db_prefix}article";
        $this->_tb_class = "{$db_prefix}class";
        $this->_tb_favorite = "{$db_prefix}favorite";
        $this->_tb_learned = "{$db_prefix}learned";
        $this->_tb_right = "{$db_prefix}right";
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
