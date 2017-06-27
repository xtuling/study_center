<?php
/**
 * AbstractController.class.php
 * 基类
 * @author   : zhoutao
 * @version  : $Id$
 * @copyright: vchangyi.com
 */

namespace Api\Controller\Mall;

use Common\Common\Department;
use Common\Model\PrizeModel;

abstract class AbstractController extends \Api\Controller\AbstractController
{

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    public function after_action($action = '')
    {

        return parent::after_action();
    }

    /**
     * 判断可见范围
     * @param array $prizeData 奖品数据
     * @return bool
     */
    protected function verifyArea($prizeData)
    {
        // 如果是全公司
        if ($prizeData['is_all'] == PrizeModel::IS_ALL) {
            return true;
        }

        // 在人员所属部门
        if (!empty($prizeData['range_mem']) && in_array($this->uid, explode(',', $prizeData['range_mem']))) {
            return true;
        }

        if (!empty($prizeData['range_dep'])) {
            // 部门范围限制
            $depUtil = new Department();
            $depArr = explode(',', $prizeData['range_dep']);
            $depArea = $depUtil->list_childrens_by_cdid($depArr, true);
            // 当前人员的所属部门
            $userDep = $depUtil->list_dpId_by_uid($this->uid, true);
            $userDepArr = [];
            foreach ($userDep as $dep) {
                $userDepArr = array_merge($userDepArr, $dep);
            }
            // 是否有交集
            $intersect = array_intersect($depArea, array_values($userDepArr));
            if (!empty($intersect)) {
                return true;
            }
        }

        return false;
    }
}
