<?php
/**
 * CommonChooselogModel.class.php
 * 选人记录表 Model
 * @author: 原习斌
 * @date  :2016-09-02
 */
namespace Common\Model;

class CommonChooselogModel extends AbstractModel
{

    /**
     * 最少选择3次，才能算常用人员
     * @var unknown
     */
    const MIN_CHOOSIE_TIME = 3;

    /**
     * 选择人员的数据库类型值
     * @var int
     */
    const CHOOSE_MEM = 1;

    /**
     * 选择部门的数据库类型值
     * @var int
     */
    const CHOOSE_DEP = 2;

    /**
     * 选择标签的数据库类型值
     * @var int
     */
    const CHOOSE_TAG = 3;

    // 选人组件 flag类型: 部门
    const CHOOSE_FLAG_DEP = 1;

    // 选人组件 flag类型: 人员
    const CHOOSE_FLAG_MEMBER = 3;

    // 选人组件 flag类型: 职位
    const CHOOSE_FLAG_JOB = 5;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 获取常用人员
     *
     * @param string    $eaID        管理员ID
     * @param array     $condition   查询参数
     * @param int|array $page_option 分页参数
     * @return array|bool
     */
    public function get_often_used($eaID, $condition = array(), $page_option = null)
    {

        $limit = '';
        if (!$this->_limit($limit, $page_option)) {
            return false;
        }

        // 次数子查询
        $ct_sql = '(
                        SELECT
                            COUNT(*)
                        FROM
                            `oa_common_chooselog`
                        WHERE
                            `domain`=`main_tb`.`domain`
                            AND `status`<' . self::ST_DELETE . '
                            AND `chooseId`=`main_tb`.`chooseId`
                            AND `choose_type`=`main_tb`.`choose_type`
                    ) AS `ct` ';

        // 组建sql语句
        list($sql, $params) = $this->_sql_for_often_used($eaID, $condition);
        $sql = 'SELECT `cid`,`choose_type`,`chooseId`,' . $ct_sql . $sql . $limit;

        return $this->_m->fetch_array($sql, $params);
    }

    /**
     * 获取常用人员的数量
     * @param array  $condition 查询参数
     * @param string $eaID      管理员ID
     * @return int
     */
    public function count_for_often_used($eaID, $condition = array())
    {

        // 次数子查询
        $ct_sql = '(
                        SELECT
                            COUNT(*)
                        FROM
                            `oa_common_chooselog`
                        WHERE
                            `domain`=`main_tb`.`domain`
                            AND `status`<' . self::ST_DELETE . '
                            AND `chooseId`=`main_tb`.`chooseId`
                            AND `choose_type`=`main_tb`.`choose_type`
                    ) AS `ct` ';
        // 组建sql语句
        list($sql, $params) = $this->_sql_for_often_used($eaID, $condition);
        $sql = 'SELECT COUNT(*) AS `ct` FROM (SELECT ' . $ct_sql . $sql . ') AS tmp';

        $res = $this->_m->fetch_row($sql, $params);

        return isset($res['ct']) ? intval($res['ct']) : 0;
    }

    /**
     * 获取常用人员的sql和param，
     * 可以在获取列表和获取总数的时候用，
     * 从from语句开始，
     * 例如：FROM xxx WHERE xxx GROUP BY xxx ....... ORDER BY xxx
     *
     * @param string $eaID      管理员ID
     * @param array  $condition 查询参数
     * @return array array(sql语句, param参数数组)
     */
    protected function _sql_for_often_used($eaID, $condition = array())
    {

        $params = array(
            $eaID,
            QY_DOMAIN,
            self::ST_DELETE,
        );

        // 初始化
        $wheres = "";

        // 如果查询人员条件存在
        if (!empty($condition['uids'])) {

            $wheres[] = 'chooseId IN (?)';
            $params[] = $condition['uids'];
        }

        // sql语句
        $sql = 'FROM __TABLE__ AS `main_tb`
                WHERE `eaId`=? AND `domain`=? AND `status`<? ';

        // 拼sql语句
        $sql = empty($wheres) ? $sql : $sql . ' AND ' . implode(' AND ', $wheres);

        $sql .= " GROUP BY `chooseId`,`choose_type`
                HAVING `ct` > ?
                ORDER BY `ct` DESC";


        $params[] = self::MIN_CHOOSIE_TIME;

        return array($sql, $params);
    }
}

