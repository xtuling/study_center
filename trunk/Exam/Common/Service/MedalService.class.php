<?php
/**
 * 考试-激励表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:43:157
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Integral;
use Common\Model\MedalModel;
use Common\Model\MedalRelationModel;
use Common\Model\PaperModel;
use Common\Model\RightModel;

class MedalService extends AbstractService
{

    /**
     * @var RightModel
     */
    protected $_d_right;

    /**
     * @var MedalRelationModel
     */
    protected $_d_relation;

    /**
     * @var PaperModel
     */
    protected $_d_paper;

    // 构造方法
    public function __construct()
    {
        $this->_d = new MedalModel();

        // 初始化权限表
        $this->_d_right = new RightModel();

        // 初始化试卷激励关系表
        $this->_d_relation = new MedalRelationModel();

        // 初始化试卷表
        $this->_d_paper = new PaperModel();

        parent::__construct();
    }

    /**
     * 【后台】 添加激励验证
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function add_medal_validation($params = array())
    {

        // 激励行为为空
        if (empty($params['title'])) {

            E('_EMPTY_MEDAL_TITLE');

            return false;
        }

        // 激励行为超过字符限制
        if ($this->utf8_strlen($params['title']) > 20) {

            E('_ERR_MEDAL_TITLE_LENGTH');

            return false;
        }

        // 激励描述超过字符限制
        if (!empty($params['em_desc'])) {

            if ($this->utf8_strlen($params['em_desc']) > 120) {

                E('_ERR_MEDAL_DESC_LENGTH');

                return false;
            }

        }

        // 激励类型
        if (!is_numeric($params['em_type']) &&
            !in_array($params['em_type'],
               array(
                   self::EC_MEDAL_TYPE_INTEGRAL,
                   self::EC_MEDAL_TYPE_MEDAL
               )
            )
        ) {

            E('_ERR_MEDAL_TYPE');

            return false;
        }


        // 如果是勋章
        if ($params['em_type'] == self::EC_MEDAL_TYPE_MEDAL) {

            // 勋章ID不能为空
            if (empty($params['im_id'])) {

                E('_EMPTY_MEDAL_ID');

                return false;
            }
        }

        // 如果是积分
        if ($params['em_type'] == self::EC_MEDAL_TYPE_INTEGRAL) {

            // 积分格式不正确
            if (empty($params['em_integral']) || intval($params['em_integral']) < 1) {

                E('_ERR_MEDAL_INTEGRAL');

                return false;
            }
        }

        // 规则为空或者不是数组
        if (empty($params['em_rule']) || !is_array($params['em_rule'])) {

            E('_ERR_MEDAL_RULE');

            return false;
        }


        // 遍历规则
        foreach ($params['em_rule'] as $v) {

            if (empty($v['ep_id']) || empty($v['ep_name'])) {

                E('_ERR_MEDAL_RULE_PARAMS');

                return false;
            }

        }

        // 次数为空或者不是数字
        if (empty($params['em_number']) || !is_numeric($params['em_number']) || intval($params['em_number']) < 1) {

            E('_ERR_MEDAL_NUMBER');

            return false;
        }

        // 分数为空或者不是数字
        if (empty($params['em_score']) || !is_numeric($params['em_score']) || intval($params['em_score']) < 1) {

            E('_ERR_MEDAL_SCORE');

            return false;
        }


        // 如果考试次数大于考试题目数
        if (intval($params['em_number']) > count($params['em_rule'])) {

            E('_ERR_NUMBER');

            return false;

        }

        // 考试题目IDS
        $ep_ids = array_column($params['em_rule'], 'ep_id');

        // 获取所有总分
        $scores = $this->_d_paper->list_by_conds(array('ep_id' => $ep_ids), null, array(), 'total_score');

        // 所有总分集合
        $total_scores = array_column($scores, 'total_score');

        // 获取最小值
        $min_total = min($total_scores);

        // 最大分数不能大于试卷最小分
        if (intval($params['em_score']) > intval($min_total)) {

            E('分数不得大于' . intval($min_total) . '分', '8099000');

            return false;
        }


        // 判断是否全公司
        if (!is_numeric($params['is_all']) || !in_array($params['is_all'],
                array(
                    self::AUTH_NOT_ALL,
                    self::AUTH_ALL
                )
            )
        ) {

            E('_ERR_AUTH_STATUS');

            return false;
        }

        // 如果用户权限为指定权限
        if ($params['is_all'] == self::AUTH_NOT_ALL) {

            // 指定权限不能为空
            if (empty($params['right'])) {

                E('_EMPTY_AUTH');

                return false;
            }


            // 获取全部人员权限
            $mem_ids = array_filter(array_column($params['right']['user_list'], 'memID'));

            // 获取部门IDS
            $dp_ids = array_filter(array_column($params['right']['dp_list'], 'dpID'));

            // 获取标签IDS
            $tag_ids = array_filter(array_column($params['right']['tag_list'], 'tagID'));

            // 获取岗位IDS
            $jod_ids = array_filter(array_column($params['right']['job_list'], 'jobID'));

            // 角色IDS
            $role_ids = array_filter(array_column($params['right']['role_list'], 'roleID'));

            // 有权限总数
            $right_total = count($mem_ids) + count($dp_ids) + count($tag_ids) + count($jod_ids) + count($role_ids);


            if (empty($right_total)) {

                E('_ERR_AUTH_NUM');

                return false;
            }

        }

        return true;
    }

    /**
     * 【后台】添加激励行为
     * @param array $params POST参数
     * @author 何岳龙
     */
    public function add_medal($params = array())
    {
        try {
            $this->start_trans();

            // 格式化规则
            $em_rule = serialize($params['em_rule']);

            // 组装数据
            $medal_data = array(
                'title' => $params['title'],
                'em_desc' => $params['em_desc'],
                'em_type' => $params['em_type'],
                'im_id' => !empty($params['im_id']) ? $params['im_id'] : '',
                'em_integral' => intval($params['em_integral']),
                'is_all' => $params['is_all'],
                'em_number' => $params['em_number'],
                'em_score' => $params['em_score'],
                'em_rule' => $em_rule,
                'icon_type' => intval($params['icon_type'])
            );

            // 写入数据
            $id = $this->_d->insert($medal_data);

            // 初始化激励规则
            $insert_data = array();

            // 遍历激励规则
            foreach ($params['em_rule'] as $key => $v) {

                $insert_data[] = array(
                    'ep_id' => $v['ep_id'],
                    'em_id' => $id
                );

            }

            // 如果激励规则存在
            if (!empty($insert_data)) {

                $this->_d_relation->insert_all($insert_data);
            }

            // 指定权限数组
            $data = array();

            // 如果是指定人员
            if ($params['is_all'] == self::AUTH_NOT_ALL) {

                // 遍历人员权限
                foreach ($params['right']['user_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $id,
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => $v['memID'],
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => ''
                    );

                }

                // 遍历部门权限
                foreach ($params['right']['dp_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $id,
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => $v['dpID'],
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => ''
                    );

                }

                // 遍历标签权限
                foreach ($params['right']['tag_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $id,
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => $v['tagID'],
                        'job_id' => '',
                        'role_id' => ''
                    );

                }
                // 遍历岗位权限
                foreach ($params['right']['job_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $id,
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => $v['jobID'],
                        'role_id' => ''
                    );
                }

                // 遍历角色权限
                foreach ($params['right']['role_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $id,
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => $v['roleID']
                    );
                }

            }

            // 如果指定有权限数据
            if (!empty($data)) {

                $this->_d_right->insert_all($data);
            }

            $this->commit();
        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->rollback();

            return false;
        } catch (\Exception $e) {

            \Think\Log::record($e);
            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->rollback();

            return false;
        }

        return true;
    }

    /**
     * 【后台】编辑激励验证
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function save_medal_validation($params = array())
    {

        // 激励行为ID为空
        if (empty($params['em_id'])) {

            E('_EMPTY_MEDAL_EM_ID');

            return false;
        }

        // 激励行为为空
        if (empty($params['title'])) {

            E('_EMPTY_MEDAL_TITLE');

            return false;
        }

        // 激励行为超过字符限制
        if ($this->utf8_strlen($params['title']) > 20) {

            E('_ERR_MEDAL_TITLE_LENGTH');

            return false;
        }

        // 激励描述超过字符限制
        if (!empty($params['em_desc'])) {

            if ($this->utf8_strlen($params['em_desc']) > 120) {

                E('_ERR_MEDAL_DESC_LENGTH');

                return false;
            }

        }

        // 激励类型
        if (!is_numeric($params['em_type']) &&
            !in_array($params['em_type'],
                array(
                    self::EC_MEDAL_TYPE_INTEGRAL,
                    self::EC_MEDAL_TYPE_MEDAL
                )
            )
        ) {

            E('_ERR_MEDAL_TYPE');

            return false;
        }

        // 如果是勋章
        if ($params['em_type'] == self::EC_MEDAL_TYPE_MEDAL) {

            // 勋章ID不能为空
            if (empty($params['im_id'])) {

                E('_EMPTY_MEDAL_ID');

                return false;
            }
        }

        // 积分格式不正确
        if ($params['em_type'] == self::EC_MEDAL_TYPE_INTEGRAL) {

            // 积分不能为空
            if (empty($params['em_integral']) || intval($params['em_integral']) < 1) {

                E('_ERR_MEDAL_INTEGRAL');

                return false;
            }
        }

        // 规则为空或者不是数组
        if (empty($params['em_rule']) || !is_array($params['em_rule'])) {

            E('_ERR_MEDAL_RULE');

            return false;
        }

        // 遍历规则
        foreach ($params['em_rule'] as $v) {

            if (empty($v['ep_id']) || empty($v['ep_name'])) {

                E('_ERR_MEDAL_RULE_PARAMS');

                return false;
            }

        }

        // 分数为空或者不是数字
        if (empty($params['em_score']) || !is_numeric($params['em_score']) || intval($params['em_score']) < 1) {

            E('_ERR_MEDAL_SCORE');

            return false;
        }


        // 如果考试次数大于考试题目数
        if (intval($params['em_number']) > count($params['em_rule'])) {

            E('_ERR_NUMBER');

            return false;

        }

        // 考试题目IDS
        $ep_ids = array_column($params['em_rule'], 'ep_id');

        // 获取所有总分
        $scores = $this->_d_paper->list_by_conds(array('ep_id' => $ep_ids), null, array(), 'total_score');

        // 所有总分集合
        $total_scores = array_column($scores, 'total_score');

        // 获取最小值
        $min_total = min($total_scores);

        // 最大分数不能大于试卷最小分
        if (intval($params['em_score']) > intval($min_total)) {

            E('分数不得大于' . intval($min_total) . '分', '8099009');

            return false;
        }


        // 判断是否全公司
        if (!is_numeric($params['is_all']) || !in_array($params['is_all'],
                array(
                    self::AUTH_NOT_ALL,
                    self::AUTH_ALL
                )
            )
        ) {

            E('_ERR_AUTH_STATUS');

            return false;
        }

        // 如果用户权限为指定权限
        if ($params['is_all'] == self::AUTH_NOT_ALL) {

            // 指定权限不能为空
            if (empty($params['right'])) {

                E('_EMPTY_AUTH');

                return false;
            }

            // 获取全部人员权限
            $mem_ids = array_filter(array_column($params['right']['user_list'], 'memID'));

            // 获取部门IDS
            $dp_ids = array_filter(array_column($params['right']['dp_list'], 'dpID'));

            // 获取标签IDS
            $tag_ids = array_filter(array_column($params['right']['tag_list'], 'tagID'));

            // 获取岗位IDS
            $jod_ids = array_filter(array_column($params['right']['job_list'], 'jobID'));

            // 角色IDS
            $role_ids = array_filter(array_column($params['right']['role_list'], 'roleID'));

            // 有权限总数
            $right_total = count($mem_ids) + count($dp_ids) + count($tag_ids) + count($jod_ids) + count($role_ids);

            if (empty($right_total)) {

                E('_ERR_AUTH_NUM');

                return false;
            }

        }

        return true;
    }

    /**
     * 【后台】编辑激励
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function update_medal_data($params = array())
    {

        try {
            $this->start_trans();

            // 格式化规则
            $em_rule = serialize($params['em_rule']);

            // 组装数据
            $medal_data = array(
                'title' => $params['title'],
                'em_desc' => $params['em_desc'],
                'em_type' => $params['em_type'],
                'im_id' => strval($params['im_id']),
                'em_integral' => intval($params['em_integral']),
                'is_all' => $params['is_all'],
                'em_number' => $params['em_number'],
                'em_score' => $params['em_score'],
                'em_rule' => $em_rule,
                'icon_type' => intval($params['icon_type'])
            );

            // 更新数据
            $this->_d->update($params['em_id'], $medal_data);

            // 删除激励权限
            $this->_d_right->delete_by_conds(array('epc_id' => $params['em_id']));

            // 删除规则
            $this->_d_relation->delete_by_conds(array('em_id' => $params['em_id']));

            // 初始化激励规则
            $insert_data = array();

            // 遍历激励规则
            foreach ($params['em_rule'] as $key => $v) {

                $insert_data[] = array(
                    'ep_id' => $v['ep_id'],
                    'em_id' => $params['em_id']
                );

            }

            // 如果激励规则存在
            if (!empty($insert_data)) {

                $this->_d_relation->insert_all($insert_data);
            }

            // 指定权限数组
            $data = array();

            // 如果是指定人员
            if ($params['is_all'] == self::AUTH_NOT_ALL) {

                // 遍历人员权限
                foreach ($params['right']['user_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['em_id'],
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => $v['memID'],
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => ''
                    );

                }

                // 遍历部门权限
                foreach ($params['right']['dp_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['em_id'],
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => $v['dpID'],
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => ''
                    );

                }

                // 遍历标签权限
                foreach ($params['right']['tag_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['em_id'],
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => $v['tagID'],
                        'job_id' => '',
                        'role_id' => ''
                    );

                }

                // 遍历岗位权限
                foreach ($params['right']['job_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['em_id'],
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => $v['jobID'],
                        'role_id' => ''
                    );
                }

                // 遍历角色权限
                foreach ($params['right']['role_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['em_id'],
                        'er_type' => self::RIGHT_MEDAL,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => $v['roleID']
                    );
                }

            }

            // 如果指定有权限数据
            if (!empty($data)) {

                $this->_d_right->insert_all($data);
            }

            $this->commit();
        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->rollback();

            return false;
        } catch (\Exception $e) {

            \Think\Log::record($e);
            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->rollback();

            return false;
        }

        return true;
    }


    /**
     * 【后台】获取激励详情
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function medal_info_validation($params = array())
    {

        // 如果激励ID为空
        if (empty($params['em_id'])) {

            E('_EMPTY_MEDAL_EM_ID');

            return false;
        }

        // 获取详情
        $info = $this->_d->get($params['em_id']);

        if (empty($info)) {

            E('_EMPTY_MEDAL_INFO');

            return false;

        }

        return true;
    }

    /**
     * 【后台】获取激励详情
     * @param array $params POST 参数
     * @author 何岳龙
     * @return array
     */
    public function get_medal_info($params = array())
    {
        // 初始化数据
        $auth = array(
            'user_list' => [],
            'dp_list' => [],
            'tag_list' => [],
            'job_list' => [],
            'role_list' => [],
        );

        // 获取详情
        $info = $this->_d->get($params['em_id']);

        // 如果不是全公司
        if ($info['is_all'] == self::AUTH_NOT_ALL) {

            // 获取权限信息
            $auth = $this->get_auth(
                array(
                    'epc_id' => $params['em_id'],
                    'er_type' => self::RIGHT_MEDAL
                )
            );
        }

        // 初始化勋章数据
        $integral_data = array();

        // 如果是勋章类型
        if ($info['em_type'] == self::EC_MEDAL_TYPE_MEDAL) {

            // 实例化勋章
            $integral = new Integral();

            // 勋章数据
            $integral_data = $integral->listMedal($info['im_id']);
        }

        // 反序列化
        $em_rule = unserialize($info['em_rule']);

        // 初始化规则
        $rule_data = array();

        // 格式化序列话数据
        foreach ($em_rule as $key => $v) {

            // 获取数据是否存在
            $total = $this->_d_paper->count_by_conds(array(
                'ep_id' => $v['ep_id'],
                'cate_status' => self::EC_OPEN_STATES,
                'exam_status' => self::EXAM_STATES,
                'end_time > ?' => MILLI_TIME
            ));

            // 如果数据存在
            if (!empty($total)) {

                $rule_data[] = array(
                    'ep_id' => intval($v['ep_id']),
                    'ep_name' => strval($v['ep_name'])
                );
            }

        }

        // 获取权限信息
        $data = array(
            'em_id' => intval($info['em_id']),
            'icon_type' => intval($info['icon_type']),
            'title' => strval($info['title']),
            'em_desc' => strval($info['em_desc']),
            'em_type' => intval($info['em_type']),
            'im_id' => intval($info['im_id']),
            'em_number' => intval($info['em_number']),
            'em_score' => intval($info['em_score']),
            'em_name' => $info['em_type'] == self::EC_MEDAL_TYPE_MEDAL ? $integral_data[0]['name'] : '',
            'em_integral' => intval($info['em_integral']),
            'is_all' => intval($info['is_all']),
            'right' => $auth,
            'em_rule' => $rule_data
        );

        return $data;
    }

    /**
     * 【后台】获取激励列表
     * @param array $params POST参数
     * @author 何岳龙
     * @return array
     */
    public function get_medal_list($params = array())
    {

        // 每页条数
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT_ADMIN : intval($params['limit']);
        $page = empty($params['page']) ? 1 : $params['page'];

        list($start, $limit, $page) = page_limit($page, $limit);

        // 查询条件
        $cond = array();

        // 分页参数
        $page_option = array($start, $limit);

        // 标签ID升序
        $order_option = array('created' => 'DESC');

        // 获取总数
        $total = $this->_d->count_by_conds($cond);

        // 获取分页数据
        $list = $this->_d->list_by_conds($cond, $page_option, $order_option);

        // 初始化数据表
        $data = array();

        // 遍历数据
        foreach ($list as $key => $v) {

            // 初始化权限
            $auth = array();

            // 初始化勋章数据
            $integral_data = array();

            // 如果是勋章类型
            if ($v['em_type'] == self::EC_MEDAL_TYPE_MEDAL) {

                // 实例化勋章
                $integral = new Integral();

                // 勋章数据
                $integral_data = $integral->listMedal($v['im_id']);

            }

            // 如果不是全公司
            if ($v['is_all'] == self::AUTH_NOT_ALL) {

                // 获取权限信息
                $auth = $this->get_auth(
                    array(
                        'epc_id' => $v['em_id'],
                        'er_type' => self::RIGHT_MEDAL
                    )
                );
            }

            $data[] = array(
                'em_id' => intval($v['em_id']),
                'title' => strval($v['title']),
                'em_desc' => strval($v['em_desc']),
                'em_type' => intval($v['em_type']),
                'em_name' => $v['em_type'] == self::EC_MEDAL_TYPE_MEDAL ? strval($integral_data[0]['name']) : '',
                'icon' => $v['em_type'] == self::EC_MEDAL_TYPE_MEDAL ? strval($integral_data[0]['icon']) : '',
                'em_integral' => intval($v['em_integral']),
                'is_all' => intval($v['is_all']),
                'right' => $auth,
                'em_rule' => unserialize($v['em_rule'])
            );
        }

        // 返回数据
        return array(
            'total' => intval($total),
            'page' => intval($page),
            'limit' => intval($limit),
            'list' => $data
        );
    }


}
