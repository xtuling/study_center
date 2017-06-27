<?php
/**
 * 考试-标签信息表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:44:12
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Job;
use Common\Common\Role;
use Common\Model\TagModel;

class TagService extends AbstractService
{
    /**
     * 初始化属性表
     * @var AttrService|null
     */
    protected $_d_attr = null;

    // 构造方法
    public function __construct()
    {
        $this->_d = new TagModel();

        $this->_d_attr = new AttrService();

        parent::__construct();
    }

    /**
     * 【后台】根据条件获取标签列表
     * @param array $params
     * @author 何岳龙
     * @return array
     */
    public function get_tag_list($params = array())
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
        $order_option = array('etag_id' => 'DESC');

        // 获取分页数据
        $data = $this->get_tag_data($cond, $page_option, $order_option);

        // 组装数据
        $data['page'] = intval($page);
        $data['limit'] = intval($limit);

        return $data;
    }


    /**
     * 【后台】根据条件查询列表
     * @author 何岳龙
     * @param array $cond 查询条件
     * @param array $page_option 分页参数
     * @param array $order_option 排序参数
     * @return array|bool
     */
    public function get_tag_data($cond = array(), $page_option = null, $order_option = array())
    {
        // 初始化数据
        $data = array();

        // 查询总数
        $total = $this->_d->count_by_conds($cond);

        // 获取列表
        $list = array();

        // 初始化属性数据
        $attr_data = array();

        if ($total > 0) {

            // 获取列表
            $list = $this->_d->list_by_conds($cond, $page_option, $order_option, 'etag_id,tag_name');

            // 标签IDS
            $tag_ids = array_column($list, 'etag_id');

            // 获取属性列表
            $attr_list = $this->_d_attr->list_by_conds(array('etag_id' => $tag_ids));

            // 重组属性数据
            foreach ($attr_list as $key => $v) {

                $attr_data[$v['etag_id']][] = array(
                    'attr_id' => intval($v['attr_id']),
                    'attr_name' => $v['attr_name']
                );

            }

            // 遍历标签重组数组列表
            foreach ($list as $item) {

                $data[] = array(
                    'etag_id' => intval($item['etag_id']),
                    'tag_name' => strval($item['tag_name']),
                    'attr_list' => !empty($attr_data[$item['etag_id']]) ? $attr_data[$item['etag_id']] : array()
                );
            }

        }

        return array('total' => intval($total), 'list' => $data);
    }

    /**
     * 【后台】标签详情验证
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function tag_info_validation($params = array())
    {
        // 如果标签ID不存
        if (empty($params['etag_id'])) {

            E('_EMPTY_TAG_ID');

            return false;
        }

        // 获取标签详情
        $info = $this->get($params['etag_id']);

        // 详情不存在
        if (empty($info)) {

            E('_EMPTY_TAG_INFO');

            return false;
        }

        return true;
    }

    /**
     * 【后台】获取标签详情数据
     * @param array $params POST数据
     * @author 何岳龙
     * @return array
     */
    public function get_tag_info($params = array())
    {
        // 初始化数据
        $data = array();

        // 初始化属性数据
        $attr_data = array();

        // 获取标签详情
        $info = $this->get($params['etag_id']);

        // 获取属性列表
        $attr_list = $this->_d_attr->list_by_conds(array('etag_id' => $params['etag_id']));

        // 重组属性数据
        foreach ($attr_list as $key => $v) {

            $attr_data[$v['etag_id']][] = array(
                'attr_id' => intval($v['attr_id']),
                'attr_name' => $v['attr_name']
            );

        }

        // 返回数据
        $data = array(
            'etag_id' => intval($info['etag_id']),
            'tag_name' => strval($info['tag_name']),
            'tag_type' => intval($info['tag_type']),
            'attr_list' => !empty($attr_list) ? $attr_data[$info['etag_id']] : array()
        );

        return $data;
    }

    /**
     * 【后台】添加标签数据
     * @param array $params POST数据
     * @author 何岳龙
     * @return bool
     */
    public function add_tag_validation($params = array())
    {

        // 标签名称为空
        if (empty($params['tag_name'])) {

            E('_EMPTY_TAG_NAME');

            return false;
        }

        // 标签名称不为空且长度大于20
        if (!empty($params['tag_name']) && $this->utf8_strlen($params['tag_name']) > 20) {

            E('_ERR_TAG_NAME_FONT_LENGTH');

            return false;
        }

        // 标签名称不能重复
        if (!empty($params['tag_name'])) {

            // 统计是否重复
            $total = $this->_d->count_by_conds(array('tag_name' => $params['tag_name']));

            // 如果存在重复标签名
            if (!empty($total)) {

                E('_ERR_TAG_NAME_REPEAT');

                return false;
            }
        }

        // 判断标签类型
        if (!is_numeric($params['tag_type']) || !in_array($params['tag_type'],
                array(
                    TagModel::TAG_TYPE_MANUAL,
                    TagModel::TAG_TYPE_RELATION
                )
            )
        ) {

            E('_ERR_TAG_TYPE_STATUS');

            return false;
        }

        // 获取属性个数
        $attr_count = count(array_unique(array_filter(array_column($params['attr_list'], 'attr_name'))));

        // 如果属性列表格式错误
        if (empty($attr_count)) {

            E('_ERR_TAG_ATTR_LIST');

            return false;
        }

        // 转换键名
        $attr_arr = array_combine_by_key($params['attr_list'], 'attr_name');

        // 验证是否有重复属性名
        if (count($attr_arr) != count($params['attr_list'])) {

            E('_ERR_TAG_ATTR_NAME_LIST');

            return false;
        }

        // 遍历判断属性数据
        foreach ($params['attr_list'] as $key => $v) {

            // 属性重复数
            $attr_total = $this->_d_attr->count_by_conds(array('attr_name' => $v['attr_name']));

            if (!empty($attr_total)) {

                E('属性名称"' . $v['attr_name'] . '",已在其它标签下存在，请修改', '8099000');

                return false;
            }

        }

        return true;
    }

    /**
     * 【后台】添加标签操作
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function add_tag($params = array())
    {

        try {
            $this->start_trans();

            // 整合插入标签表数据
            $tag_data = array(
                'tag_name' => $params['tag_name'],
                'tag_type' => $params['tag_type']
            );

            // 插入标签数据
            $tag_id = $this->_d->insert($tag_data);

            // 初始化标签属性列表
            $attr_data = array();

            // 遍历判断属性数据
            foreach ($params['attr_list'] as $key => $v) {

                $attr_data[] = array(
                    'etag_id' => $tag_id,
                    'attr_name' => $v['attr_name']
                );
            }

            $this->_d_attr->insert_all($attr_data);

            $this->commit();
        } catch (\Think\Exception $e) {

            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->rollback();

            return false;
        } catch (\Exception $e) {

            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->rollback();

            return false;
        }

        return true;
    }

    /**
     * 【后台】获取全部标签属性（添加题目用，不分页）
     * @author 何岳龙
     * @return array|bool
     */
    public function get_all_tag_attr()
    {
        // 获取所有标签列表
        $tag_list = $this->_d->list_all();

        // 标签IDS
        $tag_ids = array_column($tag_list, 'etag_id');

        // 获取属性列表
        $attr_list = $this->_d_attr->list_by_conds(array('etag_id' => $tag_ids));

        // 重组属性数据
        foreach ($attr_list as $key => $v) {

            $attr_data[$v['etag_id']][] = array(
                'attr_id' => $v['attr_id'],
                'attr_name' => $v['attr_name']
            );
        }

        $list = array();
        // 遍历标签重组数组列表
        foreach ($tag_list as $item) {

            $list[] = array(
                'etag_id' => intval($item['etag_id']),
                'tag_name' => strval($item['tag_name']),
                'attr_list' => !empty($attr_data[$item['etag_id']]) ? $attr_data[$item['etag_id']] : array()
            );
        }

        return $list;

    }

    /**
     * 【后台】编辑标签验证
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function save_tag_validation($params = array())
    {

        // 验证标签ID
        if (empty($params['etag_id'])) {

            E('_EMPTY_TAG_ID');

            return false;
        }

        // 标签名称为空
        if (empty($params['tag_name'])) {

            E('_EMPTY_TAG_NAME');

            return false;
        }

        // 标签名称不为空且长度大于20
        if (!empty($params['tag_name']) && $this->utf8_strlen($params['tag_name']) > 20) {

            E('_ERR_TAG_NAME_FONT_LENGTH');

            return false;
        }

        // 标签名称不能重复
        if (!empty($params['tag_name'])) {

            // 统计是否重复
            $info = $this->_d->get_by_conds(array('tag_name' => $params['tag_name']));

            // 如果存在重复标签名
            if (!empty($info) && $info['etag_id'] != $params['etag_id']) {

                E('_ERR_TAG_NAME_REPEAT');

                return false;
            }
        }

        // 判断标签类型
        if (!is_numeric($params['tag_type']) || !in_array($params['tag_type'],
                array(
                    TagModel::TAG_TYPE_MANUAL,
                    TagModel::TAG_TYPE_RELATION
                )
            )
        ) {

            E('_ERR_TAG_TYPE_STATUS');

            return false;
        }

        // 获取属性个数
        $attr_count = count(array_unique(array_filter(array_column($params['attr_list'], 'attr_name'))));

        // 如果属性列表格式错误
        if (empty($attr_count)) {

            E('_ERR_TAG_ATTR_LIST');

            return false;
        }

        // 转换键名
        $attr_arr = array_combine_by_key($params['attr_list'], 'attr_name');

        // 验证是否有重复属性名
        if (count($attr_arr) != count($params['attr_list'])) {

            E('_ERR_TAG_ATTR_NAME_LIST');

            return false;
        }

        // 遍历判断属性数据
        foreach ($params['attr_list'] as $key => $v) {

            // 获取当前属性名详情
            $info = $this->_d_attr->get_by_conds(array('attr_name' => $v['attr_name']));

            // 如果存在相同名称的属性
            if (!empty($info['etag_id']) && $info['etag_id'] != $params['etag_id']) {

                E('属性名称"' . $v['attr_name'] . '",已在其它标签下存在，请修改', '8099000');

                return false;
            }
        }

        return true;
    }

    /**
     * 【后台】编辑标签操作
     * @param array $params POST 参数
     * @author 何岳龙
     * @return bool
     */
    public function update_tag_data($params = array())
    {

        try {
            $this->start_trans();

            // 初始化标签属性待删除的IDS
            $attr_del_ids = array();

            // 初始化待更新的数据IDS
            $attr_update_ids = array();

            // 整合插入标签表数据
            $tag_data = array(
                'tag_name' => $params['tag_name'],
                'tag_type' => $params['tag_type']
            );

            // 更新标签数据
            $this->_d->update($params['etag_id'], $tag_data);

            // 获取编辑前属性数据
            $old_attr_list = $this->_d_attr->list_by_conds(array('etag_id' => $params['etag_id']));

            // 原始数据转换键名
            $old_attr_ids = array_column($old_attr_list, 'attr_id');

            // 遍历新属性数据列表
            foreach ($params['attr_list'] as $key => $v) {

                // 如果attr_id存在原始数据中
                if (in_array($v['attr_id'], $old_attr_ids)) {

                    // 写入待更新IDS
                    $attr_update_ids[] = $v['attr_id'];

                    // 更新数据
                    $this->_d_attr->update($v['attr_id'], array('attr_name' => $v['attr_name']));

                } else {

                    // 查询当前名称是否在当前标签下存在
                    $attr_info = $this->_d_attr->get_by_conds(array(
                        'etag_id' => $params['etag_id'],
                        'attr_name' => $v['attr_name']
                    ));

                    // 如果不存在则插入
                    if (empty($attr_info)) {

                        // 组装属性数据
                        $attr_add_data = array(
                            'etag_id' => $params['etag_id'],
                            'attr_name' => $v['attr_name']
                        );

                        $this->_d_attr->insert($attr_add_data);

                    } else {

                        // 写入待更新IDS
                        $attr_update_ids[] = $attr_info['attr_id'];

                    }
                }
            }

            // 获取待删除的数据
            $attr_del_ids = array_diff($old_attr_ids, $attr_update_ids);

            // 如果存在待删除数据
            if (!empty($attr_del_ids)) {

                // 删除无用数据
                $this->_d_attr->delete_by_conds(array('attr_id' => $attr_del_ids));
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
     * 【后台】删除标签操作
     * @param array $params
     * @author 何岳龙
     * @return bool
     */
    public function delete_tag($params = array())
    {
        try {
            $this->start_trans();

            // 删除标签
            $this->_d->delete($params['etag_id']);

            // 删除属性
            $this->_d_attr->delete_by_conds(array('etag_id' => $params['etag_id']));

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
     * 【后台】获取标签属性
     * @param array $params POST 参数
     * @author 何岳龙
     * @return array
     */
    public function get_attr_list($params = array())
    {

        // 初始化
        $data = array();

        // 初始化列表
        $list = array();

        // 如果是职位
        if ($params['type'] == self::JOB_TYPE_MEDAL) {

            // 初始化岗位类
            $job_class = new Job();

            // 获取岗位列表
            $list = $job_class->listAll();


        }

        // 如果是角色
        if ($params['type'] == self::ROLE_TYPE_MEDAL) {

            // 初始化角色
            $role_class = new Role();

            // 获取列表
            $list = $role_class->listAll();

        }
        
        // 遍历重组数组
        foreach ($list as $key => $v) {
            
            $data[] = array(
                'attr_name' => !empty($v['jobName']) ? strval($v['jobName']) : strval($v['roleName'])
            );

        }

        return $data;
    }

}
