<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/23
 * Time: 下午5:56
 */

namespace Api\Controller\Invite;


use Common\Common\Cache;
use Common\Common\Department;
use Common\Service\AttrService;
use Common\Service\InviteLinkService;
use Common\Service\InviteSettingService;
use VcySDK\Enterprise;
use VcySDK\Service;

class ReadLinkController extends AbstractController
{

    /**
     * @return bool
     */
    public function Index_post()
    {

        //$inviteLinkService = new InviteLinkService();
        //$inviteLinkService->readLink($this->_result, I('post.'), $this->_login->user);

        // 检查管理权限
        $this->checkCurrentInvitePower($this->_login->user);

        // 读取邀请配置
        $settingService = new InviteSettingService();
        $data = $settingService->get_by_conds([]);
        if (empty($data)) {
            E('1007:请通知管理员配置邀请设置');
            return false;
        }

        if (empty($data['inviter_write'])) {
            $this->_result['inviter_write'] = array();
        } else {
            $this->_result['inviter_write'] = unserialize($data['inviter_write']);
        }

        // 企业信息
        $enterpriseService = new Enterprise(Service::instance());
        $enterprise = $enterpriseService->detail();

        $departmentIds = array();
        foreach ($this->_login->user['dpName'] as $_dp) {
            $departmentIds[] = $_dp['dpId'];
        }

        // 读取字段配置
        $attrService = new AttrService();
        $fields = $attrService->getAttrList(true, array(), false, false);
        $fields = array_combine_by_key($fields, 'field_name');

        // 读取通讯录配置
        $settings = Cache::instance()->get('Common.AppSetting');
        $this->_result['jobMode'] = $settings['jobMode']['value'];
        $this->_result['roleMode'] = $settings['roleMode']['value'];
        $this->_result['qy_name'] = $enterprise['corpName'];
        $this->_result['departmentIds'] = $departmentIds;
        $this->_result['jobField'] = $fields['memJob'];
        $this->_result['roleField'] = $fields['memRole'];
        $this->_result['qy_logo'] = $enterprise['corpSquareLogo'];

        return true;
    }

}