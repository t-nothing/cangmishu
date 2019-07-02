<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<table width="800px" align="center" bgcolor="#f3f1f2" style="font-size: 14px;word-wrap:break-word">
    <tr>
        <td style="height: 80px;"><img style="margin: 0 0 0 20px;" src="{{$logo}}" alt=""></td>
    </tr>
    <tr bgcolor="white">
        <td colspan="2" style="padding: 50px 30px 10px 30px; color: #7f7f7f;  width:760px; line-height: 30px;word-wrap:break-word">
            您好,{{ $name }}<br/>

            <br>好久不见！<br/>
            <br>希望您可以给我们1-2分钟的时间读一下这封邮件。<br/>
            <br>首先谢谢您对我们的信任，成为仓秘书的用户。<br/>
            <br> 为了让仓秘书能成为您管理仓库的好助手，我们的小团队夜以继日的努力，不断对仓秘书进行优化。<br/>
            <br>新版的仓秘书集仓库管理各个流程于一体，简化传统仓库管理的复杂操作。<br/>
            <br>采用条形码打印与管理，智能上架与拣货。帮您轻松管理仓库的各个方面。<br/>
            <br>如果您有任何问题，请添加下方的微信二维码，直接询问仓秘书团队。<br/>
            <br>您可以直接点击下方网站链接，了解新版仓秘书系统。只需要1分钟，马上创建属于自己的仓库。提高您的仓库管理效率。<br/>

            <br>{{$url}}<br/>
            <div style="padding-top: 30px">
                <span> 非常谢谢您对我们的信任！</span>
            </div>
        </td>
    </tr>
    <tr>
        <td style="height: 200px; background-color: #fff;">
            <span style="display: block;  text-align: center; font-size: 1.2rem; margin: 10px auto; color: #ccc;">如有疑问请添加下方微信</span>
            <img  style="display: block; margin: 20px auto;" src="{{$qrCode}}" alt="微信二维码">
        </td>
    </tr>
</table>

</body>
</html>