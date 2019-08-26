<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <link href="{{ url('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <title>入库批次号</title>
  <style type="text/css">
    *{
      font-size: 11px;
      font-family:Arial,'宋体',simsun;
    }
    .page{
      width: 70mm;
      height: 50mm;
      margin: 0px auto;
      page-break-after: avoid;
      overflow: hidden;
    }
    .page .barcode{
      text-align: center;
    }
    .page .barcode p{
      margin-bottom: 5px;
    }
    .page th{
      width: 65px;
    }
    .page td{
      text-align: left;
    }
@media print {

    html, body {
      height:100vh; 
      margin: 0 !important; 
      padding: 0 !important;
      overflow: hidden;
    }

}
</style>
</head>
<body>
    @forelse ($batch['batch_products'] as $k => $product)
    <div class="page">
      <div class="barcode">
        <img src="{{ $product['sku_barcode'] }}" align="center">
        <p>{{ $product['sku'] }}</p>
      </div>

      <table width="100%" border="0" >
        <tr>
          <th nowrap="nowrap">货品名称：</th>
          <td>{{ $product['spec']['product']['name_cn'] }} （{{$product['spec']['name_cn']}}）</td>
        </tr>
        <tr>
          <th nowrap="nowrap">入库编号：</th>
          <td>{{ $batch['confirmation_number'] }}</td>
        </tr>
        <tr>
          <th nowrap="nowrap">入库时间：</th>
          <td>{{ $batch['created_at'] }}</td>
        </tr>
        <tr>
          <th nowrap="nowrap">SKU编码：</th>
          <td>{{ $product['relevance_code'] }}</td>
        </tr>
      </table>
      <table width="100%" border="0" >
        <tr>
          <th>E&nbsp;A&nbsp;N&nbsp;码：</th>
          <td nowrap="nowrap" style="font-size: 8px;">{{ $product['ean'] }}</td>
          <th style="width: 50px">保质期：</th>
          <td style="font-size: 8px;">-</td>
        </tr>
      </table>
    </div>
    @empty
    @endforelse
</body>
</html>