<?php
/**
 * 考试-勋章,积分领取记录表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:43:157
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\MedalRecordModel;

class MedalRecordService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new MedalRecordModel();

        parent::__construct();
    }
}
