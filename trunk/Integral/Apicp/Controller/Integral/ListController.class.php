<?php
/**
 * Created by IntelliJ IDEA.
 * 用户积分操作明细接口（积分首页使用）
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class ListController extends AbstractController
{
    /** pageSize 最大值 */
    const MAX_LIMIT = 1000;

    public function Index()
    {
        $page = I('post.page', 1);
        $limit = I('post.limit', 5);
        $limit = $limit > self::MAX_LIMIT ? self::MAX_LIMIT : $limit;

        $condition = [
            'pageSize' => $limit,
            'pageNum' => $page
        ];

        $params = [
            'memUsername', 'dpId', 'milOptType', 'startTime', 'endTime'
        ];
        foreach ($params as $field) {
            $temp = I('post.' . $field);
            if (!empty($temp)) {
                $condition[$field] = $temp;
            }
        }

        $integralSdk = new Integral(Service::instance());
        $this->_result = $integralSdk->integralIndexList($condition);

        return true;
    }
}
