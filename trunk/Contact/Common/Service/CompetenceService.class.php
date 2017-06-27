<?php
/**
 * 能力模型
 * User: zhuxun37
 * Date: 2017/5/12
 * Time: 下午6:27
 */

namespace Common\Service;


use Com\Validate;
use Common\Model\CompetenceModel;

class CompetenceService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new CompetenceModel();
    }

    /**
     * 读取能力模型列表
     * @param $result
     * @param $request
     * @return bool
     */
    public function listCompetence(&$result, $request)
    {

        $page = (int)$request['page'];
        $limit = (int)$result['limit'];
        list($start, $limit, $page) = page_limit($page, $limit);

        $condition = array();
        $total = $this->_d->count_by_conds($condition);
        $list = $this->_d->list_by_conds($condition, array($start, $limit));

        $result = array(
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'list' => $list
        );
        return true;
    }

    /**
     * 新增能力模型数据
     * @param $result
     * @param $request
     * @return bool
     */
    public function addCompetence(&$result, $request)
    {

        $competence = $this->_fetchCompetence($request);
        $this->_validateCompetence($competence);

        $competence['cm_id'] = $this->_d->insert($competence);

        $result = $competence;
        return true;
    }

    /**
     * 删除指定能力模型
     * @param array $request
     * @return bool
     */
    public function delete($request)
    {

        $cm_id = (array)$request['cm_id'];
        if (empty($cm_id)) {
            E('1003:能力模型ID错误');
            return false;
        }

        $this->_d->delete($cm_id);

        return true;
    }

    /**
     * 获取指定能力模型信息详情
     * @param $result
     * @param $request
     * @return bool
     */
    public function detail(&$result, $request)
    {

        $cm_id = (string)$request['cm_id'];
        if (empty($cm_id)) {
            E('1003:能力模型ID错误');
            return false;
        }

        $result = $this->_d->get($cm_id);

        return true;
    }

    /**
     * 编辑指定能力模型信息
     * @param $result
     * @param $request
     * @return bool
     */
    public function edit(&$result, $request)
    {

        $cm_id = (string)$request['cm_id'];
        if (empty($cm_id)) {
            E('1003:能力模型ID错误');
            return false;
        }

        $competence = $this->_fetchCompetence($request);
        $this->_validateCompetence($competence);

        $result = $this->_d->update($cm_id, $competence);

        return true;
    }

    /**
     * 获取能力模型信息
     * @param $request
     * @return array
     */
    protected function _fetchCompetence($request)
    {

        return array(
            'cm_name' => (string)$request['cm_name'],
            'cm_displayorder' => (int)$request['cm_displayorder'],
            'cm_desc' => (string)$request['cm_desc'],
            'cm_level' => (int)$request['cm_level']
        );
    }

    /**
     * 检查能力模型信息合法性
     * @param $competence
     * @return bool
     */
    protected function _validateCompetence(&$competence)
    {

        $rules = array(
            'cm_name' => 'require|length:2,80'
        );
        $msgs = array(
            'cm_name.require' => L('1001:能力模型名称不能为空'),
            'cm_name.length' => L('1002:能力模型名称长度不合法')
        );
        // 开始验证
        $validate = new Validate($rules, $msgs);
        if (!$validate->check($competence)) {
            E($validate->getError());
            return false;
        }

        return true;
    }

}