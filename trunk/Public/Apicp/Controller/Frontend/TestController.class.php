<?php
/**
 * 新创建企业初始化
 * $author$
 */
namespace Apicp\Controller\Frontend;

use Common\Controller\Api\AbstractController;
use VcySDK\Enterprise;
use VcySDK\Service;
use VcySDK\Member;

class TestController extends AbstractController
{

    /**
     * SDK Member对象
     *
     * @var Member
     */
    protected $_mem;

    /**
     * SDK Enterprise对象
     *
     * @var Enterprise
     */
    protected $_enter;

    public function before_action($action)
    {

        if (! parent::before_action($action)) {
            return false;
        }

        $_serv = &Service::instance();

        $this->_mem = new Member($_serv);

        $this->_enter = new Enterprise($_serv);

        return true;
    }

    public function Index()
    {

    }

    /**
     * 注册信息
     */
    public function Register()
    {

        try {
            // 只传入了必填信息，其他见wiki
            $this->_enter->register(array(
                'domain' => 'tb8.vchangyi.com', // 域名信息
                'isStandard' => '1', // 企业是否使用标准产品, 0:非标准, 1:标准产品
                'epEnumber' => 'wxd271727eb7d089d6', // 企业账号
                'epDomain' => 'tb8.vchangyi.com', // 域名
                'epName' => 'T2测试' // 企业名称
            ));
        } catch (\Think\Exception $e) {
            print_r($e);
        } catch (\VcySDK\Exception $e) {
            print_r($e);
        }
    }

    /**
     * 设置企业信息
     */
    public function Setting()
    {

        try {
            $this->_enter->modifySetting(array(
                'wxqyCorpid' => 'wxd271727eb7d089d6',
                // 微信企业号唯一标示
                'wxqyCorpsecret' => 'npwGUS8W3Iu3rpeMPF_VAWWymROsnLcGZFEn_KgHVeAwPSxyBTGZkHA_ce9XD0ub',
                // 微信企业号Corpsecret
                'sitename' => 'T2测试'
                // 站点名称
            ));
        } catch (\Think\Exception $e) {
            print_r($e);
        } catch (\VcySDK\Exception $e) {
            print_r($e);
        }
    }

    /**
     * 同步人员
     */
    public function SyncMember()
    {

        try {
            $this->_mem->sync();
        } catch (\Think\Exception $e) {
            print_r($e);
        } catch (\VcySDK\Exception $e) {
            print_r($e);
        }
    }
}
