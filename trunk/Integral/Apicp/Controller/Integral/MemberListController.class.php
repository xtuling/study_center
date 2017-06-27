<?php
/**
 * Created by IntelliJ IDEA.
 * 用户积分查询
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class MemberListController extends AbstractController
{

    public function Index()
    {

        $updateData = $this->getUpdateData();

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->integralMemberList($updateData);

        return true;
    }

    /**
     * 获取提交数据
     *
     * @return array|bool
     */
    protected function getUpdateData()
    {
        $memUsername = I('post.memUsername');
        $dpId = I('post.dpId');
        $miType = I('post.miType', 'mi_type0');
        $levelMaxIntegral = I('post.level_max_integral');
        $levelMinIntegral = I('post.level_min_integral');
        $upgradeType = I('post.upgrade_type');
        $page = I('post.page', 1);
        $limit = I('post.limit', 20);

        // 验证数据
        $updateData = [
            'miType' => $miType,
            'pageNum' => $page,
            'pageSize' => $limit
        ];

        if (!empty(trim($memUsername))) {
            $updateData['memUsername'] = $memUsername;
        }
        if (!empty($dpId)) {
            $updateData['dpId'] = $dpId;
        }

        if (!empty($upgradeType) && !empty($levelMaxIntegral) && !empty($levelMinIntegral)) {
            $updateData['upgradeType'] = $upgradeType;
            $updateData['levelMaxIntegral'] = $levelMaxIntegral;
            $updateData['levelMinIntegral'] = $levelMinIntegral;
        }

        return $updateData;
    }
}
