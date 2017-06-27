<?php
/**
 * 考试-题库表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 18:05:10
 * @version $Id$
 */

namespace Common\Model;

class BankModel extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 给字段添加自动添加一个数
     * 
     * @author  houyingcai
     * @param string $field
     * @param array $condition
     * @param int $step
     * 
     * @return boolean
     */
    public function setIncNum($field, $condition = array(), $step = 1)
    {

        $this->where($condition);

        return $this->setInc($field, $step);
    }

    /**
     * 给字段添加自动减少一个数
     *
     * @author  houyingcai
     * @param string $field
     * @param array $condition
     * @param int $step
     * 
     * @return boolean
     */
    public function setDecNum($field, $condition = array(), $step = 1)
    {

        $this->where($condition);

        return $this->setDec($field, $step);
    }
}
