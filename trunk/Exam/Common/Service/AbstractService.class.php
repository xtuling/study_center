<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Service;

use Common\Common\Department;
use Common\Common\Job;
use Common\Common\Msg;
use Common\Common\Role;
use Common\Common\Tag;
use Common\Common\User;
use Common\Model\RightModel;

abstract class AbstractService extends \Com\Service
{

    // 岗位类型
    const JOB_TYPE_MEDAL = 0;

    // 角色类型
    const ROLE_TYPE_MEDAL = 1;

    // 勋章类型
    const EC_MEDAL_TYPE_MEDAL = 0;

    // 积分类型
    const EC_MEDAL_TYPE_INTEGRAL = 1;

    // 已发布
    const EXAM_STATES = 2;

    // 开启
    const EC_OPEN_STATES = 1;

    // 禁用
    const EC_CLOSE_STATES = 0;

    // 全部满足
    const SEARCH_ATTR_TYPE_ALL = 1;

    // 满足任意一个
    const SEARCH_ATTR_TYPE_NOT_ALL = 2;

    // 指定权限
    const AUTH_NOT_ALL = 0;

    // 分数值0
    const SCORE = 0;

    // 全公司权限
    const AUTH_ALL = 1;
    // 全公司权限
    const NO_AUTH_ALL = 0;

    // 手机端默认显示条数
    const DEFAULT_LIMIT = 15;
    // 手机端默认显示条数
    const DEFAULT_LIMIT_ADMIN = 10;

    // 权限表类型标识：激励权限
    const RIGHT_MEDAL = 2;
    // 权限表类型标识：分类权限
    const RIGHT_CATEGORY = 1;
    // 权限表类型标识：试卷权限
    const RIGHT_PAPER = 0;


    // 试卷综合状态 ：初始化
    const STATUS_INIT = 0;
    // 试卷综合状态 ：草稿
    const STATUS_DRAFT = 1;
    // 试卷综合状态 ：未开始
    const STATUS_NOT_START = 2;
    // 试卷综合状态 ：进行中
    const STATUS_ING = 3;
    // 试卷综合状态 ：已结束
    const STATUS_END = 4;
    // 试卷综合状态 ：已终止
    const STATUS_STOP = 5;


    // 试卷数据状态：初始化
    const PAPER_INIT = 0;
    // 试卷数据状态：草稿
    const PAPER_DRAFT = 1;
    // 试卷数据状态：已发布
    const PAPER_PUBLISH = 2;
    // 试卷数据状态：提前终止
    const PAPER_STOP = 3;


    // 试卷类型：测评试卷
    const EVALUATION_PAPER_TYPE = 0;
    // 试卷类型：模拟试卷
    const SIMULATION_PAPER_TYPE = 1;

    // 出题规则：自主选题
    const TOPIC_CUSTOMER = 1;
    // 出题规则：规则抽题
    const TOPIC_RULE = 2;
    // 出题规则：随机抽题
    const TOPIC_RANDOM = 3;


    // 试题类型：单选题
    const TOPIC_TYPE_SINGLE = 1;
    // 试题类型：判断题
    const TOPIC_TYPE_JUDGMENT = 2;
    // 试题类型：问答题
    const TOPIC_TYPE_QUESTION = 3;
    // 试题类型：多选题
    const TOPIC_TYPE_MULTIPLE = 4;
    // 试题类型：语音题
    const TOPIC_TYPE_VOICE = 5;
    //已参与
    const VISITED = 1;
    // 未参与
    const UNVISIT = 0;
    // 已点赞
    const SUCCES_STATE = 1;
    // 未点赞
    const FALSE_STATE = 0;

    // 未作答
    const DO_PASS_STATE = 0;
    // 作答
    const DO_STATE = 1;
    // 答题通过
    const MY_PASS = 1;
    // 答题不通过
    const NO_MY_PASS = 2;

    // 开启关键字匹配
    const KEYWORD_OPEN = 1;
    // 关闭关键字匹配
    const KEYWORD_CLOSE = 0;
    /** 文件类型：音频 */
    const TYPE_VOICE = 'voice';
    /** 文件类型：图片 */
    const TYPE_IMAGE = 'image';

    /** 文件是否转换完毕：是 */
    const IS_COMPLETE_YES = 1;

    /** 文件是否转换完毕：否 */
    const IS_COMPLETE_NO = 0;

    /**
     * 发消息类型
     */
    // 考试通知
    const ANSWER_COMING_MSG = 1;

    // 未读人员提醒
    const ANSWER_UN_MSG = 2;

    // 您有一门考试即将开始
    const ANSWER_START = 3;

    // 您有一门考试即将结束
    const ANSWER_COMING_END = 4;

    // 您有一门考试已提前终止
    const ANSWER_AHEAD_END = 5;
    // 发送勋章
    const  ENDOW__END = 6;
    // 发送积分
    const  INTEGRAL = 7;


    // 您有一门考试未通过可再次答卷
    const TO_ANSWER = 1;

    // 构造方法
    public function __construct()
    {
        $this->_d_right = new RightModel();

        parent::__construct();
    }

    /*
     * 即时消息通知方法 【发消息统一写这里，方便维护】
     * @author: 何岳龙
     * @param $params -通知参数
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + cd_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + id   -试卷ID
     *          + description -摘要说明
     *          + img_id -封面图片ID
     *          + name -考试名称
     *          + msg -提前终止原因
     *          + begin_time -开始时间
     *          + end_time -结束时间
     * @return bool
     */

    public function send_msg($params, $type)
    {
        // 获取应用名称
        $application_name = cfg('APPLICATION_NAME');

        // 初始化
        $data = array();

        // 转换格式
        $params['description'] = $this->DeleteHtml($params['description']);

        // 本方法根据类型区分拼接不通的文案
        switch ($type) {
            case self::ANSWER_COMING_MSG:

                $data = array(
                    'title' => '【'.$application_name.'通知】' . $params['name'],
                    'description' => $this->cutstr($params['description'], 0, 20),
                    'picUrl' => $this->format_cover($params['img_id']),
                );
                break;
            case self::ANSWER_UN_MSG:

                $data = array(
                    'title' => '【'.$application_name.'通知】' . $params['name'],
                    'description' => $this->cutstr($params['description'], 0, 20),
                    'picUrl' => $this->format_cover($params['img_id']),
                );

                break;
            case self::ANSWER_START:

                $data = array(
                    'title' => '【'.$application_name.'提醒】你有一门考试即将开始',
                    'description' => '试卷名称：' . $this->cutstr($params['name'], 0,
                            20) . "\r\n考试时间：" . rgmdate(strval($params['begin_time']),
                            'Y-m-d H:i:s') . ' ~ ' . rgmdate(strval($params['end_time']), 'Y-m-d H:i:s'),
                    'picUrl' => $this->format_cover($params['img_id']),
                );

                break;
            case self::ANSWER_COMING_END:

                $data = array(
                    'title' => '【'.$application_name.'提醒】你有一门考试即将结束',
                    'description' => '试卷名称：' . $this->cutstr($params['name'], 0,
                            20) . "\r\n考试时间：" . rgmdate(strval($params['begin_time']),
                            'Y-m-d H:i:s') . ' ~ ' . rgmdate(strval($params['end_time']), 'Y-m-d H:i:s'),
                    'picUrl' => $this->format_cover($params['img_id']),
                );

                break;

            case self::ANSWER_AHEAD_END:
                $data = array(
                    'title' => '【'.$application_name.'提醒】你有一门考试已提前终止',
                    'description' => '试卷名称：' . $this->cutstr($params['name'], 0,
                            20) . "\r\n原因：" . $params['msg'],
                    'picUrl' => $this->format_cover($params['img_id']),
                );

                break;
            case self::ENDOW__END:
                $data = array(
                    'title' => '恭喜您，获得[' . $params['name'] . ']勋章',
                    'description' => "获取渠道：考试中心-". $params['title'] ." \r\n获得时间：" . rgmdate(strval(MILLI_TIME),
                            'Y-m-d H:i:s'),
                );
                break;
            default:

                return true;
        }

        $this->send($params, $data);

        return true;
    }

    /**
     * 发送消息
     * @author: 何岳龙
     * @param $params -通知参数
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + cd_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + ac_id   -ID
     * @param array $condition 消息参数
     *          + title  -标题
     *          + description -内容
     *          + picUrl -图片URL
     * @return bool
     */
    private function send($params = array(), $condition = array())
    {
        // 发送消息接收人
        $toUser = array_unique(array_filter(array_column($params['uids'], 'memID')));
        if (empty($toUser)) {
            $toUser = $params['uids'];
        }

        $msgUser = implode('|', $toUser);

        // 发送消息部门
        $toParty = array_unique(array_filter(array_column($params['cd_ids'], 'dpID')));
        if (empty($toParty)) {
            $toParty = $params['cd_ids'];
        }
        // 发送消息标签
        $toTag = array_unique(array_filter(array_column($params['tag_ids'], 'tagID')));
        if (empty($toTag)) {
            $toTag = $params['tag_ids'];
        }

        // 实例化发消息SDK
        $sdk_msg = Msg::instance();
        $msg['toUser'] = !empty($msgUser) ? $msgUser : ''; // 接收人
        $msg['toParty'] = !empty($toParty) ? implode('|', $toParty) : '';
        $msg['toTag'] = !empty($toTag) ? implode('|', $toTag) : '';
        $msg['articles'] = array(
            array(
                'title' => $condition['title'],
                'description' => $condition['description'],
                'url' => oaUrl('Frontend/Index/Msg/Index',
                    array('ep_id' => $params['id'])
                ),
                'picUrl' => $condition['picUrl'],
            ),
        );

        // 如果是积分类型
        if ($params['power_type'] == self::INTEGRAL) {
            $msg['articles'][0]['url'] = oaUrl('Frontend/Index/Index/Index', array(), '', 'Integral');
        }

        // 如果是激励类型
        if ($params['power_type'] == self::ENDOW__END) {
            $msg['articles'][0]['url'] = oaUrl('Frontend/Index/Medal/Index', array(), '', 'Integral');
        }

        $sdk_msg->sendNews($msg['toUser'], $msg['toParty'], $msg['toTag'], $msg['articles']);

        return true;
    }

    /**
     * @author: 何岳龙
     * @param string $url 生成缩略图
     * @return string
     */
    public function pic_thumbs($url = '')
    {
        $size = '64';

        if (empty($url)) {
            return '';
        }
        if ('//' == substr($url, -2)) {
            // 如果后两个字符为 // 则重新取
            $pic_url = substr($url, 0, -1) . $size;
        } elseif ('/' == substr($url, -1)) {
            // 以 / 结尾时
            $pic_url = $url . $size;
        } elseif ('/' . $size != substr($url, -3)) {
            // 如果不是以 /64 结尾
            $pic_url = $url . '/' . $size;
        } else {
            $pic_url = $url;
        }

        return $pic_url;
    }

    /**
     * 获取已删除用户信息
     * @author: 何岳龙
     * @param array &$user_list 用户列表
     * @param array $uids 全部用户UID
     * @param array $uid_list 已查询到用户UID
     * @return array
     */
    public function user_list(&$user_list = array(), $uids = array(), $uid_list = array())
    {

        // 获取被删除的用户UID
        $un_uid = array_diff($uids, $uid_list);

        // 实例化
        $user = User::instance();

        // 遍历数据
        foreach ($un_uid as $k => $v) {

            // 如果UID不为空
            if (!empty($v)) {

                $user_list[] = $user->getByUid($v);
            }
        }

        return $uid_list;
    }

    /**
     * 试卷状态转化函数
     * @author daijun
     * @param string $exam_status 活动状态
     * @param string $begin_time 开始时间
     * @param string $end_time 结束时间
     * @return int 试卷状态 0：初始化，1：草稿，2：未开始，3：进行中，4：已结束，5：已终止
     */
    public function paper_status($exam_status = '0', $begin_time = '0', $end_time = '0')
    {
        if ($exam_status == self::PAPER_INIT) {
            // 初始化
            $status = self::STATUS_INIT;
        } elseif ($exam_status == self::PAPER_DRAFT) {
            // 草稿
            $status = self::STATUS_DRAFT;
        } elseif ($exam_status == self::PAPER_STOP) {
            // 已终止
            $status = self::STATUS_STOP;
        } else {
            // 已发布
            if ($begin_time > MILLI_TIME) {
                // 未开始
                $status = self::STATUS_NOT_START;
            } elseif ($begin_time <= MILLI_TIME && $end_time > MILLI_TIME) {
                // 进行中
                $status = self::STATUS_ING;
            } else {
                // 已结束
                $status = self::STATUS_END;
            }
        }

        return $status;
    }


    /**
     * 字符串截取，支持中文和其他编码
     * access public
     * @param string $str 需要转换的字符串
     * @param int $start 开始位置
     * @param int $length 截取长度
     * @param string $charset 编码格式
     * @param bool $suffix 截断显示字符
     * @return string
     */
    public function cutstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }

        if ($this->utf8_strlen($str) > $length && $slice) {

            $slice = $slice . '...';
        }

        return $suffix ? $slice : $slice;
    }

    /**
     * 格式化考试的封面图片地址
     * @param string $cover_id 封面 ID
     * @return string        封面 URL
     */
    public function format_cover($cover_id)
    {

        if (!empty($cover_id)) {
            $cover_url = imgUrl($cover_id);
        } else {
            $cover_url = cfg('PROTOCAL') . $_SERVER['HTTP_HOST'] . '/admincp/imgs/client/images/exam/examCover.png';

        }

        return $cover_url;
    }

    /**
     * 格式化人员信息
     * @param array $mem_uids 人员UIDS
     * @return array
     */
    public function format_user($mem_uids = array())
    {
        // 初始化
        $users = array();

        // 如果附件ID为空
        if (empty($mem_uids)) {

            return $users;
        }

        // 获取人员列表
        $user_list = User::instance()->listByConds(array('memUids' => $mem_uids), 1, count($mem_uids));

        // 获取已查到人员UID集合
        $uid_list = array_column($user_list['list'], 'memUid');

        // 获取全部用户列表
        $this->user_list($user_list['list'], $mem_uids, $uid_list);

        // 遍历数据
        foreach ($user_list['list'] as $key => $v) {

            $users[$v['memUid']] = array(
                'username' => strval($v['memUsername']),
                'avatar' => strval($v['memFace']),
                'uid' => strval($v['memUid'])
            );
        }

        return $users;
    }

    /**
     * 【微信端】去除字符串html标签方法
     * @param  string $str 字符串
     * @return string
     */
    public function DeleteHtml($str = '')
    {
        $str = trim($str); //清除字符串两边的空格

        $str = strip_tags($str, ""); //利用php自带的函数清除html格式

        $replace = array("\t", "\n", "\r");

        return trim(str_replace($replace, '', $str)); //返回字符串
    }

    /**
     * 统计字符串个数
     * @param string $string 指定字符
     * @return int
     */
    public function utf8_strlen($string = '')
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);

        // 返回单元个数
        return count($match[0]);
    }

    /**
     * 获取多个用户信息
     * @param array $uids
     * @return array|bool
     */
    public function getUser($uids = array())
    {
        if (is_array($uids)) {
            // 用户信息初始化
            $user = User::instance();
            // 查询
            $users = $user->listAll(array('memUids' => $uids));
            // 查询出来的用户UID列表
            $uid_list = array_column($users, 'memUid');
            // 获取被删除的用户
            $this->user_list($users, $uids, $uid_list);

            $user_list = array_combine_by_key($users, 'memUid');

            return $user_list;
        } else {
            return false;
        }
    }

    /**
     * 考试作答情况格式化
     * @author: 蔡建华
     * @param array $data 答卷ID
     * @param int $type 0 考试答卷情况 1 全部解析 2错题解析
     * @return array
     */
    public function get_answer_detail($data = array(), $type = 0)
    {
        $result = array();
        foreach ($data as $key => $val) {
            $value['order_num'] = intval($val['order_num']);
            // 考试答卷情况
            if ($type == 1) {

                // 0：未作答 1：答对 2 ：答错
                $value['status'] = $val['is_pass'];
                $result[] = $value;
            } else {
                // 错题解析
                if ($type == 2) {
                    if ($val['is_pass'] != 1) {
                        // 0：未作答 1：答对 2 ：答错
                        $value['status'] = $val['is_pass'];
                        $result[] = $value;
                    }
                } else {
                    // 全部解析
                    if (empty($val['my_answer'])) {
                        $value['status'] = 0;
                    } else {
                        // 0 未作答 1，已作答
                        $value['status'] = 1;
                    }
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 根据规则和题型获取题目设置的分数
     * @param int $et_type
     * @param array $rule_data
     * @return int
     */
    public function get_score_by_type($et_type = 0, $rule_data = array())
    {
        switch ($et_type) {
            //单选题
            case self::TOPIC_TYPE_SINGLE:
                return $rule_data['single_score'];
            //判断题
            case self::TOPIC_TYPE_JUDGMENT:
                return $rule_data['judgment_score'];
            //问答题
            case self::TOPIC_TYPE_QUESTION:
                return $rule_data['question_score'];
            //多选题
            case self::TOPIC_TYPE_MULTIPLE:
                return $rule_data['multiple_score'];
            //语音题
            case self::TOPIC_TYPE_VOICE:
                return $rule_data['voice_score'];
            default:
                return 0;
        }
    }


    /**
     * 获取格式化后的权限数据
     * @author 何岳龙
     * @param array $conds 权限筛选条件
     * @return array
     *          + array dp_list   部门信息
     *                    + string dp_id   部门ID
     *                    + string dp_name 部门名称
     *          + array tag_list  标签信息
     *                    + string tag_id   标签ID
     *                    + string tag_name 标签名称
     *          + array user_list 人员信息
     *                    + string uid      用户ID
     *                    + string username 用户姓名
     *                    + string face     头像
     */
    public function get_auth($conds = array())
    {
        $list = $this->_d_right->list_by_conds($conds);

        $rights_db = $this->format_db_data($list);
        $data = array(
            'user_list' => array(),
            'dp_list' => array(),
            'tag_list' => array(),
            'job_list' => array(),
            'role_list' => array(),
        );

        foreach ($rights_db as $k => $v) {
            switch ($k) {

                // 部门
                case 'dp_ids':
                    if (!empty($v)) {
                        $dpServ = &Department::instance();
                        sort($v);
                        $dps = $dpServ->listById($v);

                        foreach ($dps as $dp) {
                            $data['dp_list'][] = [
                                'dpID' => $dp['dpId'],
                                'dpName' => $dp['dpName'],
                            ];
                        }
                    }
                    break;

                // 标签
                case 'tag_ids':
                    if (!empty($v)) {
                        $tagServ = &Tag::instance();
                        sort($v);
                        $tags = $tagServ->listAll($v);
                        foreach ($tags as $tag) {
                            $data['tag_list'][] = [
                                'tagID' => $tag['tagId'],
                                'tagName' => $tag['tagName'],
                            ];
                        }
                    }
                    break;

                // 人员
                case 'uids':
                    if (!empty($v)) {
                        $userServ = &User::instance();
                        sort($v);
                        $users = $userServ->listAll(array('memUids' => $v));

                        foreach ($users as $user) {
                            $data['user_list'][] = [
                                'memID' => $user['memUid'],
                                'memUsername' => $user['memUsername'],
                                'memFace' => $this->pic_thumbs($user['memFace']),
                            ];
                        }
                    }
                    break;
                // 岗位
                case 'job_ids':
                    if (!empty($v)) {
                        $jobServ = &Job::instance();
                        $jobs = $jobServ->listById($v);

                        foreach ($jobs as $job) {
                            $data['job_list'][] = [
                                'jobID' => $job['jobId'],
                                'jobName' => $job['jobName'],
                            ];
                        }
                    }
                    break;
                // 角色
                case 'role_ids':
                    if (!empty($v)) {
                        $roleServ = &Role::instance();
                        $roles = $roleServ->listById($v);

                        foreach ($roles as $role) {
                            $data['role_list'][] = [
                                'roleID' => $role['roleId'],
                                'roleName' => $role['roleName'],
                            ];
                        }
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * 格式化数据库中的权限数据
     * @author houyingcai
     * @param array $rights 权限数据
     * @return array
     */
    public function format_db_data($rights)
    {
        $data = array();
        // 数据分组
        $data['uids'] = array_filter(array_column($rights, 'uid'));
        $data['dp_ids'] = array_filter(array_column($rights, 'cd_id'));
        $data['tag_ids'] = array_filter(array_column($rights, 'tag_id'));
        $data['job_ids'] = array_filter(array_column($rights, 'job_id'));
        $data['role_ids'] = array_filter(array_column($rights, 'role_id'));

        return $data;
    }

}
