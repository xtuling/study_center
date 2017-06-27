<?php
/**
 * Created by IntelliJ IDEA.
 * 用户积分操作明细接口
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class DetailListController extends AbstractController
{

    public function Index()
    {
        $conds = $this->getCondition();

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->detailList($conds);

        return true;
    }

    /**
     * 获取提交的参数
     *
     * @return array|bool
     */
    protected function getCondition()
    {
        $conds = [];
        $params = ['memUid', 'milOptType', 'startTime', 'endTime'];
        foreach ($params as $name) {
            $temp = I('post.' . $name);
            if (!empty($temp)) {
                $conds[$name] = $temp;
            }
        }

        $conds['pageNum'] = I('post.page', 1);
        $conds['pageSize'] = I('post.limit', 20);

        if (empty($conds['memUid'])) {
            E('_ERR_EMPTY_UID');
            return false;
        }

        return $conds;
    }
}
