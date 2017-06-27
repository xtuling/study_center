<?php

/**
 * 获取所有用户的积分排名.
 *
 * @auther gaoyaqiu
 * @date 2017/05/25
 */
namespace Api\Controller\Integral;

use Common\Common\Department;
use Com\PackageValidate;
use Common\Common\User;
use VcySDK\Integral;
use VcySDK\Service;

class GetAllRankingController extends AbstractController
{
    public function Index()
    {
        // 获取提交数据
        $this->getRequstParams();

        $page = I('post.page', 1);
        $limit = I('post.limit', 10);

        $dpId = $this->postData['dp_id'];
        // 如果部门等于-1时，表示全公司
        if (!empty($dpId) && $dpId != -1) {
            $department =  new Department();
            // 获取所有子部门，包含自己
            $deps = $department->list_childrens_by_cdid($dpId, true);
            $deps = array_keys($deps);
        }

        $sdk = new Integral(Service::instance());

        // 获取所有用户的排名
        $this->_result = $sdk->getAllRanking([
            'memUid' => $this->_login->user['memUid'],
            'dpIds' => $deps,
            'job_id' => $this->postData['job_id'],
            'pageNum' => $page,
            'pageSize' => $limit
        ]);

        $this->dealResult();

        return true;
    }

    /**
     * 处理返回数据
     * @return bool
     */
    protected function dealResult()
    {
        $uidArr = [];
        foreach ($this->_result['list'] as &$item) {
            // 获取人员头像
            $uidArr[] = $item['memUid'];
        }

        // 获取人员信息
        $this->getUidInfo($uidArr);

        return true;
    }


    /**
     * 获取人员信息
     * @param $uidArr
     * @return bool
     */
    protected function getUidInfo($uidArr)
    {
        if (empty($uidArr)) {
            return true;
        }

        $user = new User();
        $userList = $user->listByUid($uidArr);
        $currMemUid = $this->_login->user['memUid'];
        foreach ($this->_result['list'] as &$item) {
            $face = $userList[$item['memUid']]['memFace'];
            if (empty($face)) {
                $face = '';
            }

            $memUsername = $userList[$item['memUid']]['memUsername'];
            if (empty($memUsername)) {
                $memUsername = '';
            }

            $item['memFace'] = $face;
            $item['memUsername'] = $memUsername;

            // 是我自己
            if ($currMemUid == $item['memUid']) {
                $item['isMy'] = true;
            }
        }

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
