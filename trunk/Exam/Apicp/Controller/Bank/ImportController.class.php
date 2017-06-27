<?php
/**
 * 解析题目导入模板
 * ImportController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Bank;

use Com\PythonExcel;
use Common\Service\TopicService;

class ImportController extends AbstractController
{
    /**
     * 初始化题目表
     * @var TopicService
     */
    protected $topic_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->topic_serv = new TopicService();

        return true;
    }

    public function Index_post()
    {
        // 获取上传文件详情
        $file = $_FILES['file'];

        // 获取题库ID
        $eb_Id = I('post.eb_id');

        // 验证数据
        if (!$this->xls_validation($eb_Id, $_FILES)) {

            return false;
        }

        // xls文件名称
        $filename = $file['tmp_name'];

        $data = PythonExcel::instance()->read($filename, 0);

        // 获取列表
        list($data, $title, $total, $headTotal) = $this->topic_serv->get_list($data);

        // 如果数据为空
        if (empty($data)) {

            E('_EMPTY_XLS_DATA');

            return false;
        }

        //返回值
        $this->_result = array(
            'total' => $total,
            'head_total' => $headTotal,
            'head' => $title,
            'list' => $data,
        );

        return true;
    }

    /**
     * 验证数据
     * @param string $ed_Id 题库ID
     * @param array $file POST数据
     * @return bool
     */
    protected function xls_validation($ed_Id = '', $file = array())
    {
        // 题库ID不能为空
        if (empty($ed_Id)) {

            $this->_set_error('_EMPTY_ED_ID');

            return false;
        }

        // 请上传文件
        if (empty($file)) {
            E('_ERR_FILE_UNDEFINED');

            return false;
        }

        // 文件上传失败
        if ($file['error'] > 0) {

            E('_ERR_UPLOAD_FILE');

            return false;
        }

        // 文件大小判断(2M以内)
        if ($file['size'] > 1024 * 2 * 1000) {

            E('_ERR_FILE_SIZE');

            return false;
        }

        return true;
    }
}
