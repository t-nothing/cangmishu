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
            Hi 你好,<br/>
            <br>
            欢迎使用仓秘书系统, 您的验证码为：<span style="font-size: 18px; font-weight: bolder; color: red;">{{$code}}</span>,此验证码有效期为5分钟，请尽快使用。<br/>
            <div style="padding-top: 30px">
                <span>此邮件由系统自动发送，请勿直接回复。</span>
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