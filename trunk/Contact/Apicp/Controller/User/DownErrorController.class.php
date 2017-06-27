<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/10/24
 * Time: 11:36
 */
namespace Apicp\Controller\User;

use Com\Excel;

class DownErrorController extends AbstractController
{

    /**
     * 下载错误人员信息
     * @author liyifei
     */
    public function Index_get()
    {
        $head = I('post.head');
        $list = I('post.list');
        if (empty($head) || empty($list)) {
            E('_ERR_PARAM_UNDEFINED');
        }

        $data = [];
        foreach ($list as $key => $info) {
            foreach ($info as $v) {
                $data[$key][] = $v['attr_value'];
            }
        }

        $filename = '通讯录成员批量导入错误人员信息';

        $excel = new Excel();
        $excel->make_excel_download($filename, $head, [], $data);
    }
}
