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
    protected $_tb_article = '';

    protected $_tb_attach = '';

    protected $_tb_class = '';

    protected $_tb_like = '';

    protected $_tb_read = '';

    protected $_tb_right = '';

    protected $_tb_favorite = '';


    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_tb_arctic = cfg('DB_PREFIX') . 'article';
        $this->_tb_attach = cfg('DB_PREFIX') . 'attach';
        $this->_tb_class = cfg('DB_PREFIX') . 'class';
        $this->_tb_like = cfg('DB_PREFIX') . 'like';
        $this->_tb_read = cfg('DB_PREFIX') . 'read';
        $this->_tb_right = cfg('DB_PREFIX') . 'right';
        $this->_tb_favorite = cfg('DB_PREFIX') . 'favorite';
    }
}
