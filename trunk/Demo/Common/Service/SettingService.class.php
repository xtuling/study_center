<?php
/**
 * AbstractSettingService.class.php
 * $author$
 */
namespace Common\Service;

use Common\Model\SettingModel;

class CommonSettingService extends AbstractSettingService
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
        $this->_d = new SettingModel();
    }

}
