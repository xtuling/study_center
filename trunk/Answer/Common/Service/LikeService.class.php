<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/21
 * Time: 17:14
 */
namespace Common\Service;

use Common\Model\LikeModel;

class LikeService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new LikeModel();
    }
}
