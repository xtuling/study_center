<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/12
 * Time: 10:33
 */
namespace Common\Model;

class SourceModel extends AbstractModel
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 处理条件语句及其值
     * @author liyifei
     * @param array $postData 请求参数
     * @return array
     */
    public function buildSql($postData)
    {
        // 条件
        $where = '`domain` = ? AND `status` < ?';

        // 条件的值
        $params = [
            QY_DOMAIN,
            $this->get_st_delete(),
        ];

        // 组合搜索条件及值
        if (isset($postData['source_title'])) {
            $where .= ' AND `source_title` like ?';
            $params[] = '%' . $postData['source_title'] . '%';
        }
        if (strlen($postData['source_key']) > 0) {
            $where .= ' AND `source_key` like ?';
            $params[] = '%' . $postData['source_key'] . '%';
        }
        if (isset($postData['source_status'])) {
            $where .= ' AND `source_status` = ?';
            $params[] = $postData['source_status'];
        }
        if (isset($postData['source_type'])) {
            $where .= ' AND `source_type` = ?';
            $params[] = $postData['source_type'];
        }
        if (strlen($postData['keyword']) > 0) {
            $where .= ' AND (`source_title` like ? OR `source_key` like ?)';
            $params[] = '%' . $postData['keyword'] . '%';
            $params[] = '%' . $postData['keyword'] . '%';
        }
        if (isset($postData['ea_name'])) {
            $where .= ' AND `ea_name` like ?';
            $params[] = '%' . $postData['ea_name'] . '%';
        }
        if (isset($postData['start_time'])) {
            $where .= ' AND `update_time` > ?';
            $params[] = $postData['start_time'];
        }
        if (isset($postData['end_time'])) {
            $where .= ' AND `update_time` < ?';
            $params[] = $postData['end_time'];
        }

        return [
            'where' => $where,
            'params' => $params,
        ];
    }

    /**
     * 根据条件，获取素材列表
     * @author liyifei
     * @param array $postData 请求参数
     * @param array $pages 分页参数
     * @return array
     */
    public function listSource($postData, $pages)
    {
        $data = $this->buildSql($postData);

        $sql = "SELECT * FROM __TABLE__ WHERE {$data['where']} ORDER BY `update_time` DESC";

        return $this->_m->fetch_array($sql, $data['params'], $pages);
    }

    /**
     * 根据条件，获取素材总数
     * @author liyifei
     * @param array $postData 请求参数
     * @return array
     */
    public function countSource($postData)
    {
        $data = $this->buildSql($postData);

        $sql = "SELECT COUNT(*) FROM __TABLE__ WHERE {$data['where']}";

        $count = $this->_m->result($sql, $data['params']);
        return intval($count);
    }
}
