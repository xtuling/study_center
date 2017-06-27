<?php
/**
 * 更新缓存
 * User: zhuxun37
 * Date: 2017/5/12
 * Time: 上午10:32
 */

namespace Apicp\Controller\Setting;


use Common\Common\Cache;
use Common\Model\SettingModel;
use Common\Service\SettingService;

class UpdateController extends AbstractController
{

    public function Index_post()
    {

        $post = I('post.');
        $settings = Cache::instance()->get('Common.AppSetting');
        $settingService = new SettingService();
        foreach ($settings as $_key => $_setting) {
            if (empty($post[$_key])) {
                continue;
            }

            $val = $post[$_key];
            // 特殊处理
            $action = '_do' . ucfirst($_key);
            if (method_exists($this, $action)) {
                $this->$action($val);
            }

            // 如果是数组, 则需要序列化
            if (SettingModel::TYPE_ARRAY == $_setting['type']) {
                $val = serialize($val);
            }

            $settingService->update_by_conds(array('key' => $_setting['key']), array('value' => $val));
        }

        Cache::instance()->set('Common.AppSetting', null);
        return true;
    }

    /**
     * 整理管理权限数据
     * @param $setting
     * @return bool
     */
    public function _doManageAuths(&$setting)
    {

        $setting['auths'] = array();
        foreach ($setting['selectedList'] as $_selected) {
            $setting['auths'][] = $_selected['id'];
        }

        return true;
    }

}