<?php

/**
 * 获取指定用户的积分排名.
 *
 * @auther gaoyaqiu
 * @date 2017/05/24
 */

namespace Api\Controller\Integral;

use Common\Common\Department;
use Com\PackageValidate;
use VcySDK\Integral;
use VcySDK\Service;

class RankingController extends AbstractController
{
    public function Index()
    {
        // 获取提交数据
        $this->getRequstParams();

        $dpId = $this->postData['dp_id'];
        // 如果部门等于-1时，表示全公司
        if (!empty($dpId) && $dpId != -1) {
            $department =  new Department();
            // 获取所有子部门，包含自己
            $deps = $department->list_childrens_by_cdid($dpId, true);
            $deps = array_keys($deps);
        }

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->getRanking([
            'memUid' => $this->_login->user['memUid'],
            'dpIds' => $deps,
            'job_id' => $this->postData['job_id']
        ]);

        return true;
    }

    /**
     * 获取提交数据
     * @return bool
     */
    protected function getRequstParams()
    {
        $validate = new PackageValidate(
            [],
            [],
            [
                'dp_id',
                'job_id'
            ]
        );

        $this->postData = $validate->postData;
    }
}
