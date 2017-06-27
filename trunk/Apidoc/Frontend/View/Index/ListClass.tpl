<include file="./Header"/>

<div class="ui floating message">
    <h1 class="ui header left floated">接口列表</h1>
    <div id="refreshApi" class="ui primary button right floated"><i class="refresh icon"></i>刷新接口</div>
    <table class="ui black celled striped table" style="margin-top: 4rem;">
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
                <td style="width:25%;word-wrap:break-word;word-break:break-all;">{$m['shortMethod']}</td>
                <td><a href="{$docUrl}?file={$m['file']}&method={$m['method']}">{$m['service']}</a></td>
                <td>{$m['title']}</td>
                <td style="width:30%;word-wrap:break-word;word-break:break-all;">{$m['desc']}</td>
            </tr>
        </foreach>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $('#refreshApi').on('click', function () {

        loading.show();
        $.ajax({
            url: '/' + enumber + "/Apidoc/Api/ApiDoc/RefreshApiList",
            type: "GET",
            dataType: "json",
            data: {
                dir: '{$dir}'
            },
            success: function (data) {
                loading.hide();
                if (data.errcode > 0) {
                    alert("接口错误: " + data.errmsg);
                    return;
                }

                window.location.reload(true);
            },
            error: function () {
                loading.hide();
                alert("接口错误, 通讯失败");
            }
        });
    });
</script>

<div class="ui floating message">
    <h1 class="ui header">文件列表</h1>
    <table class="ui black celled striped table">
        <thead>
        <tr>
            <th>#</th>
            <th>文件名称</th>
            <th>最后修改时间</th>
        </tr>
        </thead>
        <tbody>
        <if condition="empty($files)">
            <tr>
                <td colspan="3">no class</td>
            </tr>
        </if>
        <foreach name="files" key="index" item="f">
            <tr>
                <td>{$index + 1}</td>
                <td>
                    <if condition="$f['type'] eq 'file'">
                        <i class="file icon"></i> <a href="{$methodUrl}?file={$f['name']}">{$f['name']}</a>
                        <else/>
                        <i class="folder icon"></i> <a href="{$classUrl}?dir={$f['name']}">{$f['name']}</a>
                    </if>
                </td>
                <td>{$f['time']}</td>
            </tr>
        </foreach>
        </tbody>
    </table>
</div>

<p/>

<include file="./Footer"/>