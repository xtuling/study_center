<?php
/**
 * 考试-试卷分类表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:45:03
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Department;
use Common\Common\Tag;
use Common\Common\User;
use Common\Model\CategoryModel;
use Common\Model\PaperModel;
use Common\Model\RightModel;

class CategoryService extends AbstractService
{
    /**
     * @var PaperModel
     */
    protected $_d_right;

    /**
     * @var PaperModel
     */
    protected $_d_paper;

    // 构造方法
    public function __construct()
    {
        $this->_d = new CategoryModel();

        // 初始化权限
        $this->_d_right = new RightModel();

        // 初始化试卷表
        $this->_d_paper = new PaperModel();

        parent::__construct();
    }

    /**
     * 【后台】添加分类验证
     * @author: 何岳龙
     * @param array $params POST请求参数
     * @return bool
     */
    public function add_cate_validation($params = array())
    {
        // 如果分类名称为空
        if (empty($params['ec_name'])) {

            E('_EMPTY_CATE_NAME');

            return false;
        }

        // 如果分类名长度小于2或者大于20
        if ($this->utf8_strlen($params['ec_name']) < 2 || $this->utf8_strlen($params['ec_name']) > 20) {

            E('_ERR_CATE_NAME_FONT_LENGTH');

            return false;
        }

        // 如果分类名不能重复
        if (!empty($params['ec_name'])) {

            // 获取当前分类名称个数
            $total = $this->_d->count_by_conds(array('ec_name' => $params['ec_name']));

            // 如果存在相同名称的分类
            if (!empty($total)) {

                E('_ERR_CATE_NAME_REPEAT');

                return false;
            }

        }

        // 判断状态判断
        if (!is_numeric($params['ec_status']) || !in_array($params['ec_status'],
                array(
                    CategoryModel::CATEGORY_STATUS_DISABLE,
                    CategoryModel::CATEGORY_STATUS_ENABLE
                )
            )
        ) {

            E('_ERR_CATE_STATUS');

            return false;
        }

        // 分类描述为空
        if (empty($params['ec_desc'])) {

            E('_EMPTY_CATE_DESC');

            return false;
        }

        // 分类描述长度小于2或者大于120
        if ($this->utf8_strlen($params['ec_desc']) < 2 || $this->utf8_strlen($params['ec_desc']) > 120) {

            E('_ERR_CATE_DESC_FONT_LENGTH');

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

            // 获取岗位IDS
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
     * 【后台】添加分类操作
     * @author: 何岳龙
     * @param array $params POST 数据
     * @return bool
     */
    public function insert_cate($params = array())
    {

        try {
            $this->start_trans();

            // 组装分类表数据
            $cate_data = array(
                'ec_name' => $params['ec_name'],
                'ec_desc' => $params['ec_desc'],
                'ec_status' => $params['ec_status'],
                'is_all' => $params['is_all'],
                'order_num' => 0,
            );

            // 添加分类
            $cate_id = $this->insert($cate_data);

            // 指定权限数组
            $data = array();

            // 如果是指定人员
            if ($params['is_all'] == self::AUTH_NOT_ALL) {

                // 遍历人员权限
                foreach ($params['right']['user_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $cate_id,
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $cate_id,
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $cate_id,
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $cate_id,
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $cate_id,
                        'er_type' => self::RIGHT_CATEGORY,
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
     * 【后台】编辑分类验证
     * @author: 何岳龙
     * @param array $params POST请求参数
     * @return bool
     */
    public function save_cate_validation($params = array())
    {
        // 验证分类ID是否为空
        if (empty($params['ec_id'])) {

            E('_EMPTY_CATE_ID');

            return false;
        }

        // 如果分类名称为空
        if (empty($params['ec_name'])) {

            E('_EMPTY_CATE_NAME');

            return false;
        }

        // 如果分类名长度小于2或者大于20
        if ($this->utf8_strlen($params['ec_name']) < 2 || $this->utf8_strlen($params['ec_name']) > 20) {

            E('_ERR_CATE_NAME_FONT_LENGTH');

            return false;
        }

        // 如果分类名不能重复
        if (!empty($params['ec_name'])) {

            // 获取当前分类名称个数
            $info = $this->_d->get_by_conds(array('ec_name' => $params['ec_name']));

            // 如果存在相同名称的分类
            if (!empty($info['ec_id']) && $info['ec_id'] != $params['ec_id']) {

                E('_ERR_CATE_NAME_REPEAT');

                return false;
            }

        }

        // 判断状态判断
        if (!is_numeric($params['ec_status']) || !in_array($params['ec_status'],
                array(
                    CategoryModel::CATEGORY_STATUS_DISABLE,
                    CategoryModel::CATEGORY_STATUS_ENABLE
                )
            )
        ) {

            E('_ERR_CATE_STATUS');

            return false;
        }

        // 分类描述为空
        if (empty($params['ec_desc'])) {

            E('_EMPTY_CATE_DESC');

            return false;
        }

        // 分类描述长度小于2或者大于120
        if ($this->utf8_strlen($params['ec_desc']) < 2 || $this->utf8_strlen($params['ec_desc']) > 120) {

            E('_ERR_CATE_DESC_FONT_LENGTH');

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

            // 获取角色IDS
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
     * 【后台】编辑分类操作
     * @author: 何岳龙
     * @param array $params POST 数据
     * @return bool
     */
    public function update_cate($params = array())
    {

        try {
            $this->start_trans();

            // 组装分类表数据
            $cate_data = array(
                'ec_name' => $params['ec_name'],
                'ec_desc' => $params['ec_desc'],
                'ec_status' => $params['ec_status'],
                'is_all' => $params['is_all']
            );

            // 添加分类
            $this->update($params['ec_id'], $cate_data);

            // 删除权限表
            $this->_d_right->delete_by_conds(
                array(
                    'er_type' => self::RIGHT_CATEGORY,
                    'epc_id' => $params['ec_id']
                )
            );

            // 统计当前分类下试卷个数
            $count_paper = $this->_d_paper->count_by_conds(array('ec_id' => $params['ec_id']));

            // 如果存在试卷则更新试卷分类状态
            if (!empty($count_paper)) {

                $this->_d_paper->update_by_paper(array('ec_id' => $params['ec_id']),
                    array('cate_status' => $params['ec_status']));
            }

            // 指定权限数组
            $data = array();

            // 如果是指定人员
            if ($params['is_all'] == self::AUTH_NOT_ALL) {

                // 遍历人员权限
                foreach ($params['right']['user_list'] as $v) {

                    $data[] = array(
                        'epc_id' => $params['ec_id'],
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $params['ec_id'],
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $params['ec_id'],
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $params['ec_id'],
                        'er_type' => self::RIGHT_CATEGORY,
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
                        'epc_id' => $params['ec_id'],
                        'er_type' => self::RIGHT_CATEGORY,
                        'uid' => '',
                        'cd_id' => '',
                        'tag_id' => '',
                        'job_id' => '',
                        'role_id' => $v['roleID'],
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


    /*
    * 分类数据格式化
    * @author: 蔡建华
    * @param array $data
    * @return array
    */
    public function format_category_all($data = array())
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $value['ec_id'] = intval($value['ec_id']);
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * 【后台】批量排序分类验证 何岳龙
     * @param array $params POST请求参数
     * @return bool
     */
    public function order_cate_validation($params = array())
    {
        // 参数不能为空
        if (empty($params['list']) || !is_array($params['list'])) {

            E('_EMPTY_CATE_DATA');

            return false;
        }

        // 遍历验证数据
        foreach ($params['list'] as $key => $v) {

            // 如果ID为空或者不是数据
            if (empty($v['ec_id']) || !is_numeric($v['ec_id'])) {

                E('_ERR_CATE_ORDER_ID');

                return false;
            }

            // 如果order_num为空或者不是数据
            if (empty($v['order_num']) || !is_numeric($v['order_num'])) {

                E('_ERR_CATE_ORDER_NUM');

                return false;
            }

        }

        return true;
    }

    /**
     * 【后台】分类批量排序
     * @param array $params POST请求参数
     * @return bool
     */
    public function order_cate($params = array())
    {

        try {
            $this->start_trans();

            // 遍历验证数据
            foreach ($params['list'] as $key => $v) {

                $this->update_by_conds(
                    array(
                        'ec_id' => $v['ec_id']
                    ),
                    array(
                        'order_num' => $v['order_num']
                    )
                );
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
     * 【后台】验证删除分类
     * @author: 何岳龙
     * @param array $params POST请求参数
     * @return bool
     */
    public function delete_cate_validation($params = array())
    {

        // 分类ID不能为空
        if (empty($params['ec_id'])) {

            E('_EMPTY_CATE_ID');

            return false;
        }

        // 查看当前分类是否使用
        $total = $this->_d_paper->count_by_conds(array('ec_id' => $params['ec_id']));

        // 如果有数据
        if (!empty($total)) {

            E('_ERR_CATE_ID_PAPER');

            return false;
        }

        try {
            $this->start_trans();

            // 删除分类
            $this->_d->delete($params['ec_id']);

            // 删除权限
            $this->_d_right->delete_by_conds(array('epc_id' => $params['ec_id']));

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
     * 【后台】详情验证
     * @author: 何岳龙
     * @param array $params POST 请求参数
     * @return bool
     */
    public function info_cate_validation($params = array())
    {
        // 分类ID不能为空
        if (empty($params['ec_id'])) {

            E('_EMPTY_CATE_ID');

            return false;
        }

        // 查看当前分类是否使用
        $info = $this->_d->get($params['ec_id']);

        // 如果没有数据
        if (empty($info)) {

            E('_EMPTY_CATE_INFO');

            return false;
        }

        return true;
    }

    /**
     * 【后台】详情验证
     * @author: 何岳龙
     * @param array $params POST请求参数
     * @return array
     */
    public function get_cate_info($params = array())
    {
        // 初始化数据
        $auth = array();

        // 获取详情
        $info = $this->_d->get($params['ec_id']);

        // 如果不是全公司
        if ($info['is_all'] == self::AUTH_NOT_ALL) {

            // 获取权限信息
            $auth = $this->get_auth(
                array(
                    'epc_id' => $params['ec_id'],
                    'er_type' => self::RIGHT_CATEGORY
                )
            );
        }

        // 获取权限信息
        $data = array(
            'ec_name' => strval($info['ec_name']),
            'ec_status' => intval($info['ec_status']),
            'ec_desc' => strval($info['ec_desc']),
            'is_all' => intval($info['is_all']),
            'right' => $auth,
        );

        return $data;
    }
}
