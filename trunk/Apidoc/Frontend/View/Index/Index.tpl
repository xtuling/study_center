<include file="./Header"/>

<div class="ui floating message">
    <span class='ui teal tag label'>方法注释</span>

    <div class="ui feed">
        <div class="event">
            <div class="label"><i class="pencil icon"></i></div>
            <div class="content">
                <div class="summary">
                    [@param]简单类型数据的注释
                    <div class="date">
                        完整注释
                    </div>
                </div>
                <div class="extra text">
                    * @param int mode:true:1 投票类别, 1=实名; 2=匿名<br />
                    * @param string begintime:true:'2017-04-01 01:01' 投票类别, 1=实名; 2=匿名
                </div>
                <div class="meta">
                    其中, mode: 变量名; true: 是否必填, true=必填, false=选填; 1: 默认值; 字段的注释后面不能有空格, 当默认值中有空格时, 则默认值前后需要加 ' 或 "
                </div>
            </div>
        </div>
        <div class="event">
            <div class="label"><i class="pencil icon"></i></div>
            <div class="content">
                <div class="summary">
                    [@param]简单类型数据的注释
                    <div class="date">
                        简要注释
                    </div>
                </div>
                <div class="extra text">
                    * @param int mode 投票类别, 1=实名; 2=匿名
                </div>
                <div class="meta">
                    其中, mode: 变量名; 表示该数据非必填, 也没有默认值
                </div>
            </div>
        </div>
        <div class="event">
            <div class="label"><i class="pencil icon"></i></div>
            <div class="content">
                <div class="summary">
                    [@param]简单类型数据的注释
                    <div class="date">
                        部分注释
                    </div>
                </div>
                <div class="extra text">
                    * @param int mode:true 投票类别, 1=实名; 2=匿名<br />
                    * @param int ismulti:0 是否多选, 1=多选; 0=单选
                </div>
                <div class="meta">
                    其中, mode: 变量名; true: 该数据必填; 0: 默认值; 字段后面只跟了一个冒号时, 如果值为true/false, 说明是是否必填的说明, 否则当做默认值
                </div>
            </div>
        </div>
        <div class="event">
            <div class="label"><i class="pencil icon"></i></div>
            <div class="content">
                <div class="summary">
                    [@param]对象/数组数据的聚合注释
                    <div class="date">
                        对象/数组
                    </div>
                </div>
                <div class="extra text">
                    * 写法一:<br />
                    * @param array thread 投票主题相关信息<br />
                    * array(<br />
                    * &nbsp;&nbsp;'thread' => array(<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;'subject:true' => '主题', // 投票主题信息<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;'message' => '详情' // 投票详情信息<br />
                    * &nbsp;&nbsp;)<br />
                    * )<br />
                    * @param array options 投票选项信息<br />
                    * array(<br />
                    * &nbsp;&nbsp;'options' => array(<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;array(<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'option:true' => '选A', // 投票选项名称<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'at_id' => 'at***' // 投票选项对应的图片附件ID<br />
                    * &nbsp;&nbsp;&nbsp;&nbsp;)<br />
                    * &nbsp;&nbsp;)<br />
                    * )<br />
                </div>
                <div class="extra text">
                    * 写法二:<br />
                    * @param array thread 投票主题相关信息<br />
                    * @param array thread.subject:true:主题 投票主题信息<br />
                    * @param array thread.message:详情 投票详情信息<br />
                    * @param array options 投票选项信息<br />
                    * @param string options[].option:true:选A 投票选项名称<br />
                    * @param string options[].at_id:at*** 投票选项对应的图片附件ID
                </div>
                <div class="meta">
                    上面两种写法等价, 没有区别
                </div>
            </div>
        </div>
        <div class="event">
            <div class="label"><i class="pencil icon"></i></div>
            <div class="content">
                <div class="summary">
                    [@return]返回值的注释写法
                    <div class="date">
                        返回值注释
                    </div>
                </div>
                <div class="extra text">
                    返回值的注释和传入参数注释规则一样
                </div>
            </div>
        </div>
    </div>

    <div class="ui segment form">
        <a class="ui blue ribbon label">完整示例代码</a>
        <pre style="margin: 0;">
            <code class="syntax brush-javascript">
&lt;?php
/**
 * Add
 * @desc 新增投票
 * @param string thread.subject:true 投票主题，限制 81 个字符
 * @param string thread.at_id 标题附件ID
 * @param string thread.at_auth_url 标题附件Url
 * @param string thread.message 投票详情描述
 * @param int    thread.begin_time:true 投票开始时间，格式为：yyyy-mm-dd HH:ii:ss
 * @param int    thread.end_time:true 投票截止时间，格式为：yyyy-mm-dd HH:ii:ss
 * @param int    thread.ismulti:true:0 是否多选，1=多选，0=单选
 * @param int    thread.minchoices:true:1 最小选项数
 * @param int    thread.maxchoices:true:2 最大选项数
 * @param int    thread.isopen:true:1 是否开启，1=开启，0=关闭
 * @param int    thread.mode:true:1 投票类别，1=实名，2=匿名
 * @param int    thread.repeat_vote:true:0 是否允许重复投票，1=允许，0=不允许
 * @param int    thread.repeat_ip_limit:true:0 单IP投票数限制, 0: 不限制重复投票数
 * @param int    thread.repeat_user_limit:true:0 单用户投票数限制, 0: 不限制
 * @param int    thread.repeat_interval:true:0 投票时间间隔
 * @param int    thread.view_result:true:1 投票后是否可查看投票结果，1=可查看，0=不可查看
 * @param array  options:true 投票选项集
 * @param string options[].option:true 投票选项
 * @param string options[].at_id 投票选项附件id
 * @param string options[].at_auth_url 附件Url
 * @param string chooseList[].flag 标识, 1: 部门; 2: 标签; 3: 人员
 * @param string chooseList[].id 部门/标签/人员ID
 * @param string chooseList[].name 部门/标签/人员名称
 * @return array 投票信息
 *               array(
 *               'v_id' => 1 // 投票ID
 *               )
 */
public function Index()
{

    return [];
}
            </code>
        </pre>
    </div>

</div>

<p/>

<include file="./Footer"/>