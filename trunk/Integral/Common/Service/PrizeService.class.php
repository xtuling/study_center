<?php
/**
 * PrizeService.class.php
 * 奖品设置表
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Model\PrizeModel;

class PrizeService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new PrizeModel();
    }

    /**
     * Apicp 奖品列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return int
     */
    public function getPrizeList($conds, $pageOption = null, $orderOption = array())
    {

        return $this->_d->getPrizeList($conds, $pageOption, $orderOption);
    }

    /**
     * Apicp 奖品列表总数
     * @param $conds
     * @return int
     */
    public function countPrizeList($conds)
    {

        return $this->_d->countPrizeList($conds);
    }

    /**
     * 微信端查询奖品分页列表
     * @param $conds
     * @param null $pageOption
     * @param array $orderOption
     * @return array|bool
     */
    public function getWxPrizePageList($conds, $pageOption = null, $orderOption = array())
    {

        return $this->_d->getWxPrizePageList($conds, $pageOption, $orderOption);
    }

    /**
     * 微信端 查询符合条件的奖品记录总数
     * @param $conds
     * @return array
     */
    public function countWxPrize($conds)
    {
        return $this->_d->countWxPrize($conds);
    }

    /**
     * 操作奖品库存
     * $param int $id 主键
     * @param int $number 操作库存数
     * @return mixed
     */
    public function changeReserve($id, $number)
    {
        return $this->_d->changeReserve($id, $number);
    }

    /**
     * 查询奖品 无视逻辑删除
     * @param $iaId
     * @return mixed
     */
    public function getWithOutDeleted($iaId)
    {
        return $this->_d->getWithOutDeleted($iaId);
    }
}
