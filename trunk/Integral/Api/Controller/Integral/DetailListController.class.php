<?php
/**
 * Created by IntelliJ IDEA.
 *
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:46
 */

namespace Api\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class DetailListController extends AbstractController
{

    public function Index()
    {
        $page = I('post.page', 1);
        $limit = I('post.limit', 20);

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->detailList([
            'memUid' => $this->_login->user['memUid'],
            'pageNum' => $page,
            'pageSize' => $limit
        ]);

        // 获取所需字段
        foreach ($this->_result['list'] as &$detail) {
            $integralMemberList = array_flip($sdk->integralTypeWithNumber);
            $detail = [
                'ic_id' => $detail['businessId'],
                'createTime' => $detail['milCreated'],
                'milChangeIntegral' => $detail['milChangeIntegral'],
                'milOptTypeCn' => $sdk->integralTypeWithChinese[$integralMemberList[$detail['milOptType']]],
                'milOptType' => $detail['milOptType'],
                'milChangeDesc' => $detail['milChangeDesc'],
                'plName' => $detail['plName']
            ];
        }

        return true;
    }
}
