<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Common\Constant;
use Common\Model\SourceAttachModel;

class SourceAttachService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new SourceAttachModel();
    }

    /**
     * 保存附件数据，并返回新增数据总数
     * @author zhonglei
     * @param int $source_id 素材ID
     * @param array $data 附件数据
     * @return mixed
     */
    public function saveData($source_id, $data)
    {
        if (!is_array($data)) {
            return 0;
        }

        // 计算将要删除的数据
        $attachs = $this->list_by_conds(['source_id' => $source_id]);
        if (!empty($attachs)) {
            if (empty($data)) {
                // 删除已有的全部附件
                $ids_del = array_column($attachs, 'source_attach_id');

            } else {
                // 对比找出差异附件
                $ids_db = array_column($attachs, 'source_attach_id');
                $ids_post = array_column($data, 'source_attach_id');
                $ids_del = array_diff($ids_db, $ids_post);
            }

            // 删除数据
            if (!empty($ids_del)) {
                $this->delete($ids_del);
            }
        }

        $insert_data = [];
        $at_ids = [];

        // 新增数据
        foreach ($data as $v) {
            if ($v['source_attach_id'] == 0) {
                unset($v['source_attach_id']);
                $v['source_id'] = $source_id;
                $insert_data[] = $v;

                // 文件附件
                if ($v['at_type'] == Constant::ATTACH_TYPE_FILE) {
                    $at_ids[] = $v['at_id'];
                }
            }
        }

        if (!empty($insert_data)) {
            foreach ($insert_data as $v) {
                $this->insert($v);
            }
        }

        return [count($insert_data), $at_ids];
    }
}
