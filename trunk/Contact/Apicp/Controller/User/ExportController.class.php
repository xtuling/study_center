<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:47
 */

namespace Apicp\Controller\User;

use Com\PythonExcel;
use Common\Common\User;
use Common\Common\Department;
use Common\Model\AttrModel;
use Common\Service\AttrService;

class ExportController extends AbstractController
{

    /**
     * 【通讯录】批量导出
     * @author zhonglei
     */
    public function Index_get()
    {

        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, array(), true);
        $titles = array();
        foreach ($attrs as $_attr) {
            if (1 == $_attr['is_required_cp'] || 1 == $_attr['is_required']) {
                $titles[] = array(
                    'value' => $_attr['attr_name'], // 单元格内容
                    'pattern' => array( // 单元格样式, 关于样式, 请参考: http://www.python-excel.org
                        'pattern' => 'solid',
                        'fore_colour' => 'red'
                    )
                );
            } else {
                $titles[] = $_attr['attr_name'];
            }
        }
        $rows = [];

        $userServ = new User();
        $deptServ = new Department();

        $page = 1;
        $pageSize = 5000;
        $pageCount = 0;

        do {
            $result = $userServ->listByConds([], $page, $pageSize);

            // 计算总页数
            if (!$pageCount) {
                $pageCount = ceil($result['total'] / $pageSize);
            }

            foreach ($result['list'] as $v) {
                $row = [];

                foreach ($attrs as $attr) {
                    $field_name = $attr['field_name'];
                    $value = isset($v[$field_name]) ? $v[$field_name] : '';

                    if (!empty($value)) {
                        // 根据属性类型格式化数据
                        switch ($attr['type']) {
                            // 日期
                            case AttrModel::ATTR_TYPE_DATE:
                                $value = rgmdate($value, 'Y-m-d');
                                break;
                            // 日期时间
                            case AttrModel::ATTR_TYPE_DATE_TIME:
                                $value = rgmdate($value, 'Y-m-d H:i:s');
                                break;
                            // 单选
                            case AttrModel::ATTR_TYPE_RADIO:
                                $default = '未知选项';
                                foreach ($attr['option'] as $val) {
                                    if ($value == $val['value']) {
                                        $default = $val['name'];
                                        break;
                                    }
                                }
                                $value = $default;
                                break;
                            // 下拉框单选
                            case AttrModel::ATTR_TYPE_DROPBOX:
                                $default = '未知选项';
                                foreach ($attr['option'] as $val) {
                                    if ($value == $val['value']) {
                                        $default = $val['name'];
                                        break;
                                    }
                                }
                                $value = $default;
                                break;
                            // 多选
                            case AttrModel::ATTR_TYPE_CHECKBOX:
                                $data = unserialize($value);
                                if (is_array($data)) {
                                    $nameArr = array_column($data, 'name');
                                    if (is_array($data)) {
                                        $value = implode(';', $nameArr);
                                    }
                                }
                                break;
                            // 图片
                            case AttrModel::ATTR_TYPE_PICTURE:
                                $images = $attrServ->formatValueByType(AttrModel::ATTR_TYPE_PICTURE, $value);
                                if (!empty($images)) {
                                    $urls = array_column($images, 'url');
                                    if (is_array($urls)) {
                                        $value = implode(';', $urls);
                                    }
                                }
                                break;
                            // 直属上级 V1.2.0版本迭代
                            case AttrModel::ATTR_TYPE_LEADER:
                                $leaders = $attrServ->formatValueByType(AttrModel::ATTR_TYPE_LEADER, $value);
                                $value = '';
                                if (!empty($leaders)) {
                                    $leaderName = '';
                                    foreach ($leaders as $leader) {
                                        $leaderName .= $leader['name'] . ';';
                                    }
                                    $value = substr($leaderName, 0, -1);
                                }
                                break;
                        }
                    }

                    // 部门
                    if ($field_name == 'dpName') {
                        $user = $userServ->getByUid($v['memUid']);

                        if ($user && $user['dpName']) {
                            $dpIds = array_column($user['dpName'], 'dpId');
                            $dpNames = [];

                            foreach ($dpIds as $dpId) {
                                $dpNames[] = $deptServ->getCdNames($dpId);
                            }

                            $value = implode(';', $dpNames);
                        }
                    }

                    $row[] = $value;
                }

                if ($row) {
                    $rows[] = $row;
                }
            }

            $page++;
        } while ($page < $pageCount);

        // 生成 Excel 并输出
        $filename = rgmdate(NOW_TIME, 'Y') . rgmdate(NOW_TIME, 'm') . rgmdate(NOW_TIME, 'd') . '_员工列表';
        $filename = $filename  . '.xls';
        PythonExcel::instance()->write(get_sitedir() . $filename, $titles, $rows);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl;charset=UTF-8");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");

        // 根据浏览器类型来判断是否需要特殊处理中文字符
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua) || preg_match("/rv:11.0/", $ua) || preg_match("/Edge/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header("Content-Transfer-Encoding:binary");
        echo file_get_contents(get_sitedir() . $filename);
        exit;
    }
}
