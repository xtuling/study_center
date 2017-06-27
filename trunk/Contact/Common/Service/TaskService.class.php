<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/10/20
 * Time: 14:58
 */
namespace Common\Service;

use Common\Model\TaskModel;

class TaskService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new TaskModel();
    }
}
