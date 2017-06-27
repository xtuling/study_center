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

class IndexListController extends AbstractController
{
    /** pageSize 最大值 */
    const MAX_LIMIT = 1000;

    public function Index()
    {
        $pageNum = I('post.page', 1);
        $pageSize = I('post.limit', 5);
        $pageSize = $pageSize > self::MAX_LIMIT ? self::MAX_LIMIT : $pageSize;

        // 查询参数
        $params = ['userName', 'milOptType', 'depId', 'startTime', 'endTime'];
        foreach ($params as $name) {
            $temp = I('post.' . $name);
            if (empty($temp)) {
                $condition[$name] = $temp;
            }
        }
        $condition['pageNum'] = $pageNum;
        $condition['pageSize'] = $pageSize;

        $integralSdk = new Integral(Service::instance());
        $this->_result = $integralSdk->integralList($condition);

        return true;
    }
}
