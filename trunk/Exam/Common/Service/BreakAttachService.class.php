<?php
/**
 * 闯关-答题文件表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:53:39
 * @version $Id$
 */

namespace Common\Service;

class BreakAttachService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 通过 order_id(文件标识)获取附件信息
     * @author: 蔡建华
     * @param string $order_id 文件标识、顺序号
     * @param int $ebd_id 答卷详情ID
     * @return array
     */
    public function getByOrderid($order_id, $ebd_id)
    {
        return $this->_d->get_by_conds([
            'ebd_id' => $ebd_id,
            'order_id' => $order_id
        ]);
    }
}
