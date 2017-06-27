<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:22
 */

namespace Apicp\Controller\User;

use Common\Common\Department;
use Common\Common\Integral;
use Common\Common\User;
use Common\Service\UserService;

class InfoController extends AbstractController
{

    /**
     * 【通讯录】人员详情
     * @author liyifei
     */
    public function Index_post()
    {

        $uid = I('post.uid', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        $userServ = new UserService();
        $list = $userServ->userInfo($uid);

        // 用户信息
        $newUser = new User();
        $userInfo = $newUser->getByUid($uid);

        // 获取带层级信息的部门名称
        $dpInfo = array();
        foreach ($userInfo['dpName'] as $_dp) {
            $dp = Department::instance()->getById($_dp['dpId']);
            $dpInfo[] = $dp['departmentPath'];
        }

        // 用户标签信息
        if (isset($userInfo['tagName']) && is_array($userInfo['tagName'])) {
            $tag = array_column($userInfo['tagName'], 'tagName');
        }

        // 读取积分信息
        $integralList = Integral::instance()->listByUid(array($uid));
        $integral = current($integralList);
        if (empty($integral['total'])) {
            $integral['total'] = 0;
        }
        if (empty($integral['available'])) {
            $integral['available'] = 0;
        }

        // 用户全公司下的排名
        $userRanking = 0;

        try {
            $userRank = Integral::instance()->getUserIntegralRank($uid, [], "");
            $userRanking = $userRank['ranking'];
        } catch (\Exception $e) {
            // 获取用户积分排名异常不影响主流程
            \Think\Log::record($e);
        }

        $integral['ranking'] = $userRanking;

        // 初始化返回值
        $result = [
            'username' => $userInfo['memUsername'],
            'dp_name' => $userInfo['dpName'],
            'dp_info' => $dpInfo,
            'face' => $userInfo['memFace'],
            'tag' => isset($tag) ? $tag : [],
            'status' => $userInfo['memSubscribeStatus'],
            'list' => $list,
            'integral' => $integral
        ];

        $this->_result = $result;
    }
}
