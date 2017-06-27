<?php
/**
 * CommonChooseService.class.php
 * 选人记录表
 * @author: 原习斌
 * @date  :2016-09-02
 */
namespace Common\Service;

use Common\Model\CommonChooselogModel;

class CommonChooseService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new CommonChooselogModel();
    }

    /**
     * 获取常用人员
     *
     * @param string    $eaID        管理员ID
     * @param int|array $page_option 分页参数
     * @param array     $condition   查询参数
     * @return array
     */
    public function get_often_used($eaID, $condition = array(), $page_option = null)
    {

        return $this->_d->get_often_used($eaID, $condition, $page_option);
    }

    /**
     * 获取常用人员的数量
     * @param array  $condition 查询参数
     * @param string $eaID      管理员ID
     * @return int
     */
    public function count_for_often_used($eaID, $condition = array())
    {

        return $this->_d->count_for_often_used($eaID, $condition);
    }
}

