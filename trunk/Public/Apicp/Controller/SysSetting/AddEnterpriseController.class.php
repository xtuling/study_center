<?php
/**
 * 添加企业信息（此接口暂时不用）
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年8月2日13:54:51
 */

namespace Apicp\Controller\SysSetting;

use Com\Model;
use Com\Validator;
use VcySDK\Adminer;
use VcySDK\Enterprise;
use VcySDK\Service;
use VcySDK\Sms;

class AddEnterpriseController extends AbstractController
{

    /**
     * 是否必须登录
     *
     * @var string
     */
    protected $_require_login = false;

    /**
     * @type Sms
     */
    protected $_sms;

    /**
     * @type Enterprise
     */
    protected $_enterprise;

    /**
     * @type Adminer
     */
    protected $_admin;

    // 前置操作
    public function before_action($action = '')
    {

        if (parent::before_action($action) === false) {
            return false;
        }

        // 实例化SDK
        $service =& Service::instance();
        $this->_sms = new Sms($service);
        $this->_enterprise = new Enterprise($service);
        $this->_admin = new Adminer($service);

        return true;
    }

    public function Index()
    {

        $mobile = I('post.epContactmobile');
        $code = I('post.code');

        // 验证密码
        if (! $this->pwd()) {
            return false;
        }

        // 如果不为手机号
        if (! Validator::is_phone($mobile)) {
            $this->_set_error('_ERR_PHONE_FORMAT');
            return false;
        }

        // 获取验证码信息
        $sms = $this->_sms->verifyCode(array('scMobile' => $mobile, 'scCode' => $code));

        // 验证码错误
        if ($sms['code'] !== "SUCCESS") {
            $this->_set_error('_ERR_PHONE_CODE');
            return false;
        }

        // 添加企业
        if (! $this->addetr(I('post.'))) {
            return false;
        }

        // 添加管理员
        $admin = $this->addAdmin(I('post.'));
        // 添加管理员是否成功
        if (! $admin) {
            return false;
        }

        return true;
    }

    /**
     * 注册企业信息
     *
     * @param array $data post数据
     *
     * @return bool
     */

    protected function addetr($data)
    {

        $values = array(
            'domain' => $_SERVER['HTTP_HOST'] . "/" . QY_DOMAIN . "/",
            'epDomain' => $_SERVER['HTTP_HOST'] . "/" . QY_DOMAIN . "/",
            'isStandard' => 1,
            'epEnumber' => $data['epEnumber'],
            'epName' => $data['epName'],
            'epContactmobile' => $data['epContactmobile'],
            'epContactemail' => $data['epContactemail'],
            'epContacter' => $data['epContacter'],
            'epCity' => serialize(array(
                'epProvince' => $data['epProvince'],
                'epCity' => $data['epCity'],
                'epCounty' => $data['epCounty']
            )),
            'epIndustry' => $data['epIndustry'],
            'epCompanysize' => $data['epCompanysize']

        );

        // 注册企业
        $result = $this->_enterprise->register($values);

        // 企业注册失败
        if (empty($result['epId'])) {
            $this->_set_error('_ERR_RGQY_REG');
            return false;
        }

        return true;
    }


    /**
     * 注册管理员
     *
     * @param array $data post数据
     *
     * @return bool
     */
    protected function addAdmin($data)
    {

        $values = array(
            'eaMobile' => $data['epContactmobile'],
            'eaRealname' => $data['epContacter'],
            'eaPassword' => $data['pwd'],
            'eaUserstatus' => Model::ST_CREATE,
            'ea_level' => Adminer::SUPER_MANAGER,
            'eaCpmenu' => ''
        );

        // 注册管理员
        $result = $this->_admin->register($values);

        // 管理员注册失败
        if (empty($result['eaId'])) {
            $this->_set_error('_ERR_EDIT_ADMIN');
            return false;
        }

        return true;
    }


    /**
     * 密码验证
     *
     * @return bool
     */

    protected function pwd()
    {

        $pwd = I('post.pwd');
        $repeatpwd = I('post.repeatpwd');

        // 密码不能为空
        if (empty($pwd)) {
            $this->_set_error('_ERR_PWD_EMPTY');
            return false;
        }

        // 登录密码和确认密码不相等
        if ($pwd != $repeatpwd) {
            $this->_set_error('_ERR_PWD_NOT_EQ');
            return false;
        }

        return true;
    }

}
