<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/5/9
 * Time: 10:59
 */
namespace Apicp\Controller\Course;

use Com\PythonExcel;
use Common\Common\User;
use Common\Common\ArticleHelper;
use Common\Service\ArticleService;

class ExportController extends \Apicp\Controller\AbstractController
{
    /**
     * Export
     * @author zhonglei
     * @desc 导出未学习人员数据接口
     * @param Int article_id:true 课程ID
     */
    public function Index_get()
    {
        $article_id = I('get.article_id', 0, 'intval');

        if (empty($article_id)) {
            E('_ERR_ARTICLE_ID_EMPTY');
        }

        $articleServ = new ArticleService();
        $article = $articleServ->get($article_id);

        if (empty($article)) {
            E('_ERR_ARTICLE_DATA_NOT_FOUND');
        }

        // 获取未学人员ID
        $articleHelper = &ArticleHelper::instance();
        list(, , $uids_unstudy) = $articleHelper->getStudyData($article['article_id']);

        // 获取未学人员数据
        $userServ = &User::instance();
        $list = $userServ->listAll(['memUids' => $uids_unstudy]);

        $columns = ['姓名', '部门', '职位', '手机号'];
        $rows = [];

        foreach ($list as $v) {
            $dp_names = array_column($v['dpName'], 'dpName');
            $dp_name = implode(';', $dp_names);

            $rows[] = [
                // 姓名
                $v['memUsername'],
                // 部门
                $dp_name,
                // 职位
                $v['memJob'],
                // 手机号
                $v['memMobile'],
            ];
        }

        $filename = TEMP_PATH . QY_DOMAIN  . rgmdate(MILLI_TIME, '_ymdHis') . '.xls';
        $result = PythonExcel::instance()->write($filename, $columns, $rows);

        if ($result === true) {
            $file_size = filesize($filename);
            $file_name = '未学人员.xls';
            $content = file_get_contents($filename);

            header('Content-type: application/octet-stream;charset=utf-8');
            header('Accept-Ranges: bytes');
            header("Accept-Length: {$file_size}");
            header("Content-Disposition: attachment; filename={$file_name}");
            exit($content);
        }
    }
}
