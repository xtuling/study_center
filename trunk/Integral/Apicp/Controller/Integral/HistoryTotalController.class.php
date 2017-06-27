<?php
/**
 * Created by IntelliJ IDEA.
 * 积分统计历史数据
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class HistoryTotalController extends AbstractController
{

    public function Index()
    {
        $mi_type = I('post.mi_type', 'mi_type0');
        $beginMitdTime = I('post.beginMitdTime');
        $endMitdTime = I('post.endMitdTime');

        $cond = [
            'mi_type' => $mi_type
        ];
        if ($beginMitdTime > 0) {
            $cond['beginMitdTime'] = $beginMitdTime;
        }
        if ($endMitdTime > 0) {
            $cond['endMitdTime'] = $endMitdTime;
        }

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->historyTotal($cond);

        return true;
    }
}
