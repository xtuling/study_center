<?php
/**
 * 试卷-答题文件表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:53:39
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\AnswerAttachModel;

class AnswerAttachService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new AnswerAttachModel();

        parent::__construct();
    }

    /**
     * 通过 order_id(文件标识)获取附件信息
     * @author: 蔡建华
     * @param string $order_id 文件标识、顺序号
     * @param int $ead_id 答卷详情ID
     * @return array
     */
    public function getByOrderid($order_id, $ead_id)
    {
        return $this->_d->get_by_conds([
            'ead_id' => $ead_id,
            'order_id' => $order_id
        ]);
    }

}
