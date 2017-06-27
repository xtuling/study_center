<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/10/10
 * Time: 下午2:58
 */

namespace Frontend\Controller\Logincp;

use VcySDK\Adminer;
use VcySDK\Exception;
use VcySDK\Service;

class GotoWechatCpController extends \Common\Controller\Frontendcp\AbstractController
{

    // 是否必须登录
    protected $_require_login = true;

    /**
     * 管理员操作类
     *
     * @type Adminer
     */
    protected $_sdkAdmin = null;

    public function Index()
    {

        $serv = Service::instance();
        $this->_sdkAdmin = new Adminer($serv);

        /**
         * target 为目标调整页面, 可选值如下:
         * agent_setting: 设置页面
         * send_msg: 消息发送页
         * contact: 通讯录
         */
        try {
            $result = $this->_sdkAdmin->wechatLoginUrl(array(
                'eaId' => $this->_login->user['eaId'],
                'target' => 'contact'
            ));

            $this->assign('redirectUrl', $result['loginUrl']);
            $this->_output('Common@Frontend/Redirect');
        } catch (\Exception $e) {
            // 如果是SDK异常
            if ($e instanceof Exception) {
                return $this->_returnQrcodeUrl();
            }
        }

        $this->_output('Common@Frontend/Error');
        return true;
    }

    // 返回二维码 Url
    protected function _returnQrcodeUrl()
    {

        $url = $this->_sdkAdmin->getWechatManagerPageUrl();
        $this->assign('redirectUrl', oaUrl('/Frontend/Logincp/QrcodeLogin') . '?_identifier=' . APP_IDENTIFIER . '&ref=' . urlencode($url));
        $this->_output('Common@Frontend/Redirect');
        return true;
    }

}
