<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang("message.batchPageBatchNo")</title>
  <style type="text/css">
    <?php echo file_get_contents(public_path('bootstrap/css/bootstrap.min.css'));?>
    *{
      font-size: 11px;
      font-family:Arial,'Source Han Sans SC','Source Han Sans CN','noto sans','宋体',simsun;
    }
    .page{
      width: 70mm;
      height: 50mm;
      margin: 0px auto;
      page-break-after: always;
      overflow: hidden;
    }
    .page.preview{
      border:1px solid #ccc;
      margin-bottom: 20px;
      padding: 15px 5px 5px 5px;
      width: 84mm;
      height: 60mm;
    }
    .page .barcode{
      text-align: center;
    }
    .page .barcode p{
      margin-bottom: 5px;
    }
    .page table{
      display: -webkit-box;display: -ms-flexbox;display: -webkit-flex;display: flex;
      font-size: 11px;
    }
    .page th{
      width: 80px;
      text-align: right;
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
    <div class="page<?php if($showInStock) {?> preview<?php }?>">
      <div class="barcode">
        <img src="{{ $product['sku_barcode'] }}" align="center" width="90%" >
        <p>{{ $product['sku'] }}</p>
      </div>

      <table width="100%" border="0" >
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageProductName')：</th>
          <td>{{ $product['spec']['product']['name_cn'] }} （{{$product['spec']['name_cn']}}）</td>
        </tr>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageBatchNo')：</th>
          <td>{{ $batch['confirmation_number'] }}</td>
        </tr>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageInDate')：</th>
          <td>{{ $batch['created_at'] }}</td>
        </tr>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageSku')：</th>
          <td>{{ $product['relevance_code'] }}</td>
        </tr>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageEan')：</th>
          <td>{{ $product['ean'] }}</td>
        </tr>
        <?php if(!is_null($product['expiration_date'])){?>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageExpireAt')：</th>
          <td>{{ date("Y-m-d", $product['expiration_date'])}}</td>
        </tr>
        <?php }?>
        <?php if(!is_null($product['best_before_date'])){?>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageExpireBbd')：</th>
          <td>{{ date("Y-m-d", $product['best_before_date'])}}</td>
        </tr>
        <?php }?>
        <?php if($product['production_batch_number']){?>
        <tr>
          <th nowrap="nowrap">@lang('message.batchPageExpireMfd')：</th>
          <td>{{ $product['production_batch_number']}}</td>
        </tr>
        <?php }?>
      </table>
    </div>

    @empty
    @endforelse
</body>
</html>