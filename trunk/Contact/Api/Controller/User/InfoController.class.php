<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:22
 */

namespace Api\Controller\User;

use Common\Common\Department;
use Common\Service\UserService;
use Common\Common\User;
use VcySDK\Enterprise;
use VcySDK\Service;

class InfoController extends AbstractController
{

    /**
     * 【通讯录】个人详情
     * @author liyifei
     * @time   2016-09-18 11:32:34
     */
    public function Index_post()
    {

        $uid = I('post.uid', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        // 企业信息
        $enterpriseService = new Enterprise(Service::instance());
        $enterprise = $enterpriseService->detail();

        // 属性列表
        $userService = new UserService();
        $list = $userService->getUserInfoByUid($uid);

        // 用户信息(从缓存架构用户信息表中的数据获取用户信息,参数设为true越过缓存)
        $commUser = new User();
        $userInfo = $commUser->getByUid($uid);

        // 组织路径
        $departmentPath = '';
        if (!empty($userInfo['dpName'])) {
            $departmentPath = Department::instance()->getCdNames($userInfo['dpName'][0]['dpId']);
        }

        // 初始化返回值
        $result = [
            // 是否本人
            'oneself' => $this->uid == $uid,
            'qy_name' => $enterprise['corpName'],
            'name' => $userInfo['memUsername'],
            'sex' => $userInfo['memGender'],
            'title' => $userInfo['memJob'],
            'face' => $userInfo['memFace'],
            'qr_code' => oaUrl('Frontend/Index/ContactQrcode/index', ['uid' => $uid]),
            'departmentPath' => $departmentPath,
            'list' => $list
        ];

        $this->_result = $result;
    }
}
