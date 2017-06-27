<include file="Common@Frontend/Header" />

<h1 class="failure">{$title}</h1>
<if condition="empty($url)">
    <h2>{$message}</h2>
<else />
    <h2><a href="{$url}">{$message}</a></h2>
</if>

<include file="Common@Frontend/Footer" />