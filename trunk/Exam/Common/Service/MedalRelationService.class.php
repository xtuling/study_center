<?php
/**
 * 考试-激励试卷关联表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:43:157
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\MedalRelationModel;

class MedalRelationService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new MedalRelationModel();

        parent::__construct();
    }
}
