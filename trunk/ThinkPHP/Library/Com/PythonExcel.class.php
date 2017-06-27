<?php
/**
 * 调用 python 处理 excel 业务
 * User: zhuxun37
 * Date: 2017/4/9
 * Time: 下午2:05
 */

namespace Com;

class PythonExcel
{

    /**
     * excel 可选版本
     * @var array
     */
    protected $_vers = array('2003', '2007');

    /**
     * 实例化
     *
     * @return PythonExcel
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {
        // do nothing.
    }

    /**
     * 读取 excel 文件, 需要注意的是, 起始行号为 0, 读取示例:
     * $filename = "/Users//*zhuxun37/python/excel/data/big-data-test.xls";
     * $ret = PythonExcel::instance()->read($filename, 0, 2);
     * var_dump($ret);
     * @param string $filename 文件名称
     * @param int    $start    起始行号, 开始行号为: 0
     * @param int    $end      结束行号, 不包括当前行
     * @param int    $sheet    sheet 序号, 第一个 sheet 序号为 0
     * @param string $ver      版本
     * @return mixed
     */
    public function read($filename, $start, $end = 0, $sheet = 0, $ver = "2003")
    {

        if (in_array($ver, $this->_vers)) {
            $ver = (string)$ver;
        } else {
            $ver = $this->_vers[0];
        }

        return callPython("excel_func::read_excel", $filename, $start, $end, $sheet, $this->_getExcelVersion($ver));
    }

    /**
     * 写单 sheet 的 excel 文件, 写示例:
     * $columns = array("工号", "姓名", array(
     *   'value' => "手机号码", // 单元格内容
     *   'pattern' => array( // 单元格样式, 关于样式, 请参考: http://www.python-excel.org
     *     'pattern' => 'solid',
     *     'fore_colour' => 'red'
     *   )
     * ));
     * $rows = array(
     *   array("1", "朱逊", "13588119714"),
     *   array("2", "周焘", "13898765234"),
     *   array("3", "杨坤", "13912344321")
     * );
     * $filename = "/Users/zhuxun37/python/excel/data/1.xls";
     * $ret = PythonExcel::instance()->write($filename, $columns, $rows);
     * var_dump($ret);
     * @param string $filename 文件名称
     * @param array  $columns  列名称
     * @param array  $rows     行信息
     * @param string $ver      版本
     * @return mixed
     */
    public function write($filename, $columns, $rows, $ver = "2003")
    {

        return callPython("excel_func::write_excel", $filename, $columns, $rows, $this->_getExcelVersion($ver));
    }

    /**
     * 写多 sheet 的 excel 文件, 示例:
     * $sheets = array("第一个sheet名称", "第二个sheet名称");
     * $data = array(
     *   array(
     *     "columns" => array("工号", "姓名", "手机号码”),
     *     "rows" => array(
     *       array("1", "朱逊", "13588119714"),
     *       array("2", "周焘", "13898765234"),
     *       array("3", "杨坤", "13912344321")
     *     )
     *   ),
     *   array(
     *     "columns" => array("工号", "姓名", "手机号码”),
     *     "rows" => array(
     *       array("1", "朱逊", "13588119714"),
     *       array("2", "周焘", "13898765234"),
     *       array("3", "杨坤", "13912344321")
     *     )
     *   )
     * );
     * @param string $filename 文件名称
     * @param array  $sheets   sheet 名称数组
     * @param array  $data     excel 数据
     * @param string $ver      版本
     * @return mixed
     */
    public function writeSheet($filename, $sheets, $data, $ver = "2003")
    {

        return callPython("excel_func::write_excel_sheet", $filename, $sheets, $data, $this->_getExcelVersion($ver));
    }

    /**
     * 获取 excel 版本
     * @param string $ver 版本号
     * @return mixed|string
     */
    protected function _getExcelVersion($ver)
    {

        if (in_array($ver, $this->_vers)) {
            return (string)$ver;
        } else {
            return $this->_vers[0];
        }
    }

}
