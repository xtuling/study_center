<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/9/28
 * Time: 下午5:47
 */

namespace Apicp\Controller\AdminManager;


use Com\Validator;
use Common\Common\Sms;

class TransferSuperAdminController extends AbstractController
{

    // 已存在管理员
    const TYPE_EXIST = 1;

    // 新管理员
    const TYPE_NEW = 2;

    // 过期时间
    const EXPIRED = 1800;

    public function Index()
    {

        $transferAdminType = I('post.transferAdminType');
        $selfCode = I('post.selfCode');
        $selfCodeSign = I('post.selfCodeSign');

        // 判断当前管理员身份是否超级管理员
        if (! $this->_login->is_super_admin()) {
            $this->_set_error('_ERR_NO_PERMISSION');
            return false;
        }

        // 验证码错误(签名错误), 理论上应该验证时间(半小时)
        list($_mobile, $_code, $_ts) = Sms::instance()->parseSign($selfCodeSign);
        if ($_mobile != $this->_login->user['eaMobile'] || $selfCode != $_code || $_ts + self::EXPIRED < NOW_TIME) {
            $this->_set_error('_ERR_ADMIN_TRANSFER_SUPER_SIGN_TIMEOUT');
            return false;
        }

        if (self::TYPE_EXIST == $transferAdminType) {
            return $this->_to_exist_admin();
        } else {
            return $this->_to_new_admin();
        }
    }

    /**
     * 把超级管理员移交给一个已存在的管理员
     *
     * @return bool
     */
    protected function _to_exist_admin()
    {

        $oldSuperEaId = $this->_login->user['eaId'];
        $newSuperEaId = I('post.newSuperEaId');
        // 目标管理员账号不存在
        if (empty($newSuperEaId)) {
            $this->_set_error('_ERR_ADMIN_TRANSFER_SUPER_NEW_EA_ID_EMPTY');
            return false;
        }

        // 目标管理员账号和当前登录账号不能重复
        if ($newSuperEaId == $oldSuperEaId) {
            $this->_set_error('_ERR_ADMIN_TRANSFER_SUPER_TO_SELF');
            return false;
        }

        $data = array(
            'transferAdminType' => self::TYPE_EXIST,
            'oldSuperEaId' => $oldSuperEaId,
            'newSuperEaId' => $newSuperEaId
        );
        $this->_result = $this->_sdkAdminer->transferSuperAdmin($data);
        return true;
    }

    /**
     * 移交给新管理员
     *
     * @return bool
     */
    protected function _to_new_admin()
    {

        $eaMobile = I('post.eaMobile');
        $eaRealname = I('post.eaRealname');
        $eaPassword = I('post.eaPassword');
        $eaEmail = I('post.eaEmail');
        $code = I('post.code');
        // 验证手机号码
        if (! Validator::is_mobile($eaMobile)) {
            $this->_set_error('_ERR_MOBILE_INVALID');
            return false;
        }

        // 验证真实姓名
        if (! Validator::is_realname($eaRealname, 3, 255)) {
            $this->_set_error('_ERR_REALNAME_INVALID');
            return false;
        }

        // 验证密码
        if (! Validator::is_password($eaPassword)) {
            $this->_set_error('_ERR_PASSWORD_INVALID');
            return false;
        }

        // 验证邮箱格式
        if (! Validator::is_email($eaEmail)) {
            $eaEmail = '';
        }

        // 验证手机验证码
        if (! Sms::instance()->verifyCodeSDK($eaMobile, $code)) {
            return false;
        }

        $data = array(
            'transferAdminType' => self::TYPE_NEW,
            'oldSuperEaId' => $this->_login->user['eaId'],
            'eaMobile' => $eaMobile,
            'eaRealname' => $eaRealname,
            'eaPassword' => $eaPassword,
            'eaEmail' => $eaEmail
        );
        $this->_result = $this->_sdkAdminer->transferSuperAdmin($data);
        return true;
    }
}
