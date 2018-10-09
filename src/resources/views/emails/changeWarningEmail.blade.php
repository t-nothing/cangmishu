<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<table width="800px" align="center" bgcolor="#f3f1f2" style="font-size: 14px;word-wrap:break-word">
    <tr>
        <td colspan="2"  style="height: 46px; padding: 10px;">
            <img src="{{$imageUrl}}/images/email/email_header.png">
        </td>
    </tr>
    <tr bgcolor="white">
        <td  colspan="2" style="padding: 50px 30px 10px 30px; color: #7f7f7f;  width:760px; line-height: 30px;word-wrap:break-word">
            <!--从这里开始-->
            <span style="font-weight: bold">{{$name}}</span> 您好，EU Techne提醒您：
            <br/>
            您的{{$typeName}}报警邮箱已于{{$currentTime}}在线变更为：<span style="color:blue;font-weight: bold">{{$new_email}}</span>
            <br/>
            原邮箱为：<span style="color:blue">{{$old_email}}</span>
            <br/>
            <br/>
            如果这不是您本人的操作，请立刻前往 <a href="{{$wmsUrl}}">{{$wmsUrl}}</a> 修改您的系统密码及库存报警邮箱，防止因此给您带来的损失。
            <br />

            <div style="padding-top: 30px">
                <span>此邮件由系统自动发送，请勿直接回复。</span><br/>
                <span>如有疑问，请及时联系：</span><br/>
                <span>固话：+31(0)886681291&nbsp;&nbsp;&nbsp;&nbsp;手机：+31(0)639332560</span>
            </div>
            <br/>
            <!--到这里结束-->
        </td>
    </tr>
    <tr bgcolor="white">
        <td colspan="2" style="padding: 0px 30px 10px 30px; color: #7f7f7f;  width:760px; line-height: 30px;word-wrap:break-word">
            <div align="right" style="padding-top: 25px;">
                <span>感谢您选择EU Techne，祝您生活愉快！</span><br/>
                <span>EU Techne客户服务部</span><br/>
{{--                <span>{{$sendDate}}</span>--}}
            </div>
        </td>
    </tr>
</table>

</body>
</html>
