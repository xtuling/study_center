<include file="Common@Frontend/Header" />

<script type="text/javascript">
<!--
<php>if (!empty($redirectUrl)) {</php>
<php>if ('android' == $os || '' != $top) {</php>
window.top.location.href = '{$redirectUrl}';
<php>} else {</php>
window.location.href = '{$redirectUrl}';
<php>}</php>
<php>}</php>
<php>if (!empty($javascript)) {</php>
{$javascript}
<php>}</php>
//-->
</script>

<include file="Common@Frontend/Footer" />