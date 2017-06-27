<?php
/**
 * 系统设置-修改密码
 * CreateBy：何岳龙
 * Date：2016年8月1日18:00:35
 */
namespace Apicp\Controller\SysSetting;

use Com\Validator;
use VcySDK\Adminer;
use VcySDK\Service;

class EditPasswordController extends AbstractController
{

    public function Index()
    {

        // 新密码
        $pwd = I('post.pwd');
        // 确认密码
        $repeatPwd = I('post.repeatPwd');

        // 密码不能为空
        if (empty($pwd)) {
            $this->_set_error('_ERR_PWD_EMPTY');
            return false;
        }

        // 如果密码格式错误
        if (! Validator::is_password($pwd)) {
            $this->_set_error('_ERR_PWD_FORMAT');
            return false;
        }

        // 登录密码和确认密码不相等
        if ($pwd != $repeatPwd) {
            $this->_set_error('_ERR_PWD_NOT_EQ');
            return false;
        }

        // 初始化管理员
        $sdk = new Adminer(Service::instance());
        $sdk->modifyPWD(array(
            'eaPassword' => $pwd,
            'eaId' => $this->_login->user['eaId']
        ));

        return true;
    }

}
