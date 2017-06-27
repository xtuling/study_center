<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/11/1
 * Time: 下午7:57
 */

namespace Apicp\Controller\AppCenter;

class ContactDetailController extends AbstractController
{

    public function Index()
    {

        // 获取应用列表(临时从列表取)
        $pluginList = $this->_pluginSDK->listAll(array(), 1, self::PERPAGE);
        foreach ($pluginList as $_plugin) {
            if (cfg('APP_CONTACT_IDENTIFIER') == $_plugin['plIdentifier']) {
                $this->_result = $this->_generatePlugin($_plugin);
                break;
            }
        }

        return true;
    }

}
