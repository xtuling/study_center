<?php
/**
 * Created by PhpStorm.
 * User: liyifei
 * Date: 16/9/13
 * Time: 下午14:10
 */
namespace Api\Controller;


use \Common\Controller\Api\AbstractController as BaseAbstractController;
use Common\Service\SettingService;

abstract class AbstractController extends BaseAbstractController
{

    // 默认显示条数
    const DEFAULT_LIMIT = 15;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {

            return false;
        }

        // 初始化配置
        $setting = new SettingService();

        // 读取应用配置信息
        $this->_setting = $setting->Setting();

        return true;
    }

}

