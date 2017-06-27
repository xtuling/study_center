<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\FileService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author liyifei
     * @desc 文件列表接口
     * @param Int file_id:true 当前文件夹ID（根目录传0）
     * @param Int file_type:true:0 文件类型（0=文件夹和文件；1=文件夹；2=文件）
     * @param String file_name 文件名称关键字
     * @return Array
                array(
                    'file_id' => 2, // 当前文件夹ID
                    'level' => 2, // 当前文件夹层级
                    'parent_id' => 2, // 父级文件夹ID
                    'paths' => array( // 文件夹路径列表
                        array(
                            'file_name' => '吃喝玩乐', // 文件夹名称
                            'file_id' => 1, // 文件夹ID
                        )
                    ),
                    'total_file' => 10, // 文件总数
                    'list' => array( // 文件列表
                        array(
                            'file_id' => 123, // 文件ID
                            'level' => 3, // 文件层级
                            'file_name' => '001.pdf', // 文件名称
                            'file_type' => 2, // 文件类型（1=文件夹；2=文件）
                            'at_id' => '8765432345', // 附件ID
                            'at_size' => '123141', // 附件尺寸（单位字节）
                            'at_suffix' => 'pdf', // 文件尾缀
                            'update_time' => 1493264288000, // 最后更新时间
                            'file_status' => 1, // 文件转码状态（1=转码中；2=转码完成）
                         )
                    ),
                );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
            'file_type' => 'require|integer|in:0,1,2',
        ];

        // 验证参数
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $postData['file_name'] = I('post.file_name', '', 'trim');

        // 初始化返回值
        $data = [
            'file_id' => $postData['file_id'],
            'level' => 0,
            'parent_id' => 0,
            'paths' => [],
            'total_file' => 0,
            'list' => [],
        ];

        $fileServ = new FileService();
        if ($postData['file_id'] != 0) {
            // 当前目录信息
            $file = $fileServ->get($postData['file_id']);
            if (empty($file)) {
                E('_ERR_FILE_PARENT_IS_NULL');
            }

            // 父文件夹ID
            $data['parent_id'] = $file['parent_id'];

            // 目录层级
            $data['level'] = $fileServ->getLevel($postData['file_id']);

            // 目录路径
            $data['paths'] = $fileServ->getPaths($postData['file_id']);
        }

        // 组合条件
        $conds = [
            // 本目录搜索
            'parent_id' => $postData['file_id'],
        ];
        if (strlen($postData['file_name']) > 0) {
            // 全局搜索
            $conds = [
                'file_name like ?' => '%' . $postData['file_name'] . '%',
            ];
        }
        if ($postData['file_type'] != 0) {
            $conds['file_type'] = $postData['file_type'];
        }

        // 排序规则
        $orders = [
            '`order`' => 'ASC',
            'file_type' => 'ASC',
            'update_time' => 'DESC',
        ];

        $data['total_file'] = $fileServ->count_by_conds($conds);
        $data['list'] = $fileServ->list_by_conds($conds, [], $orders);

        // 格式化数据库数据为树形结构
        $tree = $fileServ->formatDBData();

        // 文件后缀
        if (!empty($data['list'])) {
            foreach ($data['list'] as &$info) {
                if ($info['file_type'] == Constant::FILE_TYPE_IS_DOC) {
                    $info['at_suffix'] = end(explode('.', $info['file_name']));
                } else {
                    $info['at_suffix'] = '';
                }

                // 子文件层级
                $info['level'] = $data['level'] + 1;

                // 子文件夹内容是否包含子文件夹
                $fileId = $info['file_id'];
                $info['is_child'] = Constant::IS_CHILD_FOLDER_FALSE;
                if (isset($tree[$fileId]['child'])) {
                    foreach ($tree[$fileId]['child'] as $v) {
                        if ($v['file_type'] == Constant::FILE_TYPE_IS_FOLDER) {
                            $info['is_child'] = Constant::IS_CHILD_FOLDER_TRUE;
                        }
                    }
                }
            }
        }

        $this->_result = $data;
    }
}
