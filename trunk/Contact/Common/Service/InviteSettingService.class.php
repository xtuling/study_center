<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:09
 */

namespace Common\Service;

use Common\Model\InviteSettingModel;

class InviteSettingService extends AbstractService
{

    /**
     * 是否需要审批,不需要
     */
    const INVITE_TYPE_NO_CHECK = 1;
    /**
     * 是否需要审批,需要
     */
    const INVITE_TYPE_NEED_CHECK = 2;
    /**
     * 是否有邀请权限:否
     */
    const IS_INVITE_NO = 0;
    /**
     * 是否有邀请权限:是
     */
    const IS_INVITE_YES = 1;
    /**
     * 是否有审核权限:否
     */
    const IS_CHECK_NO = 0;
    /**
     * 是否有审核权限:是
     */
    const IS_CHECK_YES = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new InviteSettingModel();
    }

    /**
     * 获取邀请人员设置
     * @author zhonglei
     * @return array
     */
    public function getSetting()
    {

        static $setting;

        if (is_null($setting)) {
            $setting = $this->get_by_conds([]);
            $keys = ['invite_uids', 'check_uids', 'departments'];

            foreach ($setting as $k => $v) {
                if (in_array($k, $keys)) {
                    $setting[$k] = empty($v) ? [] : unserialize($v);
                }
            }
        }

        return $setting;
    }


}
