<?php
/**
 * CommonChooseService.class.php
 * 选人记录表
 * @author: 原习斌
 * @date  :2016-09-02
 */
namespace Common\Service;

use Common\Model\CommonHidemenuModel;

class CommonHidemenuService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new CommonHidemenuModel();
    }

    /**
     * 获取指定企业的菜单信息
     * @param string $epEnumber 企业标识
     * @return array
     */
    public function getMenus($epEnumber)
    {

        $result = $this->_d->getMenus($epEnumber);
        return $result;
    }
}

