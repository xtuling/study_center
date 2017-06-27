<?php
/**
 * PrizeProcessService.class.php
 * 奖品申请进度表
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Model\ConvertProcessModel;

class ConvertProcessService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new ConvertProcessModel();
    }
}
