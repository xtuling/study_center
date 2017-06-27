<?php
/**
 * @author: houyingcai 
 * @email: 	594609175@qq.com
 * @date :  2017-04-25 17:22:25
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\RecordModel;

class RecordService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new RecordModel();

        parent::__construct();
    }

}