<?php
/**
 * 考试-属性信息表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:44:51
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\AttrModel;

class AttrService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new AttrModel();

        parent::__construct();
    }

}
