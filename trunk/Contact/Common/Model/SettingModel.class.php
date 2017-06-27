<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 14:45
 */

namespace Common\Model;

use Com\AbstractSettingModel;

class SettingModel extends AbstractSettingModel
{

    // 构造方法
    public function __construct()
    {

        $this->_table_prefix = cfg('DB_PREFIX') . 'contact_';
        parent::__construct();
    }
}
