<include file="./Header"/>

<div class="ui floating message">
    <span class='ui teal tag label'>
        {$file}
    </span>

    <br/><br/>
    <h1 class="ui header">接口列表</h1>
    <table class="ui black celled striped table">
        <thead>
        <tr>
            <th>#</th>
            <th>接口服务</th>
            <th>接口名称</th>
            <th>更多说明</th>
        </tr>
        </thead>
        <tbody>
        <if condition="empty($methods)">
            <tr>
                <td colspan="3">no method</td>
            </tr>
        </if>
        <foreach name="methods" key="index" item="m">
            <tr>
                <td>{$index}</td>
                <td><a href="{$docUrl}?file={$file}&method={$index}">{$m['service']}</a></td>
                <td>{$m['title']}</td>
                <td>{$m['desc']}</td>
            </tr>
        </foreach>
        </tbody>
    </table>
</div>

<p/>

<include file="./Footer"/>