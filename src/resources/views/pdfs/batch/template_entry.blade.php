<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang('message.batchPage')</title>
  <style type="text/css">
    <?php echo file_get_contents(public_path('bootstrap/css/bootstrap.min.css'));?>
    *{
      font-size: 13px;
    }
    h3{
      font-size: 16px;
    }
    .table-bordered {
      border: 2px solid #000000;
    }

    .table-bordered thead th,
    .table-bordered thead td {
      border-bottom-width: 2px;
      border: 1px solid #000000;
    }
    .table-bordered th,
    .table-bordered td {
      border: 1px solid #000000;
    }
    .table-bordered td div{
      width:330px;
      height: 22px;
      word-break:keep-all;/* 不换行 */
      white-space:nowrap;/* 不换行 */
      overflow:hidden;/* 内容超出宽度时隐藏超出部分的内容 */
      text-overflow:ellipsis;
    }
    .table-bordered td span{
      width:100px;
      height: 22px;
      display: block;
      word-break:keep-all;/* 不换行 */
      white-space:nowrap;/* 不换行 */
      overflow:hidden;/* 内容超出宽度时隐藏超出部分的内容 */
      text-overflow:ellipsis;
    }
    @page {
      size: A4;
      margin: 2mm 2mm;
      padding: 0;
    }
    @media print{
      /*隐藏不打印的元素*/
      .no-print{
          display:none;
      }
    }
  </style>
</head>
<?php
  
    $data_count = count($batch['batch_products']);
    $page_count = ceil($data_count/10+ (($data_count-10)/15));
?>
<body>
  <div class="container-fluid" style="position: relative;">
    <div class="qrcode" style="position: absolute; right: 0px; ">
        <img src="{{ $batch['batch_code_barcode'] }}">
     
        <div style="font-size: 18px; color: red; border-bottom: 2px solid #000000"><b style="color: black">NO.</b> {{ $batch['confirmation_number'] }}

      </div>
     </div>
    <h2 align="center">@lang('message.batchPage') <?php  if($page_count>1){?>1/<?php echo $page_count?> <?php }?></h2>
    <table width="100%" border="0" align="center" style="margin-top: 30px;">
      <tr>
        <td width="33%">@lang('message.batchPageDate') 
：{{ date("Y-m-d", strtotime($batch['created_at'])) }}</td>
        <td width="33%">@lang('message.batchPageWarehouse')：{{ $batch['warehouse']['name_cn'] }}</td>
        <td>@lang('message.batchPageType')：{{ $batch['batch_type']['name'] }}</td>
      </tr>
    </table>
    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">@lang('message.batchPageDistributor')：{{ $batch['distributor']['name_cn'] }}</td>
        <td></td>
      </tr>
    </table>
<?php

    $firstArr = $restArr = [];
    $line_no = 0;
    foreach ($batch['batch_products'] as $k => $product) {
        
        if($line_no < 10) {
            $firstArr[] = $product;
        }  else {
            $restArr[] = $product;
        }

        $line_no++;
    }
    if($firstArr) {
?>
    <table class="table  table-bordered text-center" style="margin-top: 30px;">
      <thead>
        <tr>
          <th width="60px">@lang('message.batchPageNo')</th>
          <th width="180px">@lang('message.batchPageSku')</th>
          <th >@lang('message.batchPageProductName')</th>
          <th width="120px">@lang('message.batchPageSpecName')</th>
          <th width="80px">@lang('message.batchPagePurcharseQty')</th>
          <?php if($showInStock??0){?>
            <th class="no-print" width="80px">@lang('message.batchPageActualQty')</th>
            <th class="no-print">@lang('message.batchPageBatchNo')</th>
          <?php }?>
          <th width="100px">@lang('message.batchPagePurcharsePrice')</th>
        </tr>
      </thead>
      <tbody>
          <?php $total = 0; $remarks = [];?>
        @forelse ($firstArr as $k => $product)
          <?php $total += $product['purchase_price'];?>
          <?php if(!empty($product['remark'])) $remarks[] = sprintf("%s : %s; ",$product['spec']['product']['name_cn'],$product['remark'] );?>
        <tr>
          <td>{{ $k+1 }}</td>
          <td><img src="{{ $product['relevance_code_barcode'] }}">
              <p>{{ $product['relevance_code'] }}</p></td>
              <td><div>{{ $product['spec']['product']['name_cn'] }}</div></td>
              <td><span>{{$product['spec']['name_cn']}}</span></td>
          <td>{{ $product['need_num'] }}</td>
            <?php if($showInStock??0){?>
            <td class="no-print">{{ $product['stockin_num'] }}</td>
            <td class="no-print">{{ $product['sku'] }}</td>
            <?php }?>
          <td>{{ $product['purchase_price'] }}</td>
        </tr>
        @empty
        @endforelse
      </tbody>
    </table>

    <table width="100%" border="0"  align="center" class="table table-bordered ">
      <tr>
        <td width="240px">@lang('message.batchPageInRemark')：</td>
        <td height="100px">{{ $batch['remark'] }} <br/><?php echo  implode("<br/>", $remarks)?></td>
      </tr>
    </table>

    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">@lang('message.batchPageTreasurer')：</td>
        <td width="33%">@lang('message.batchPageManager')：</td>
        <td >@lang('message.batchPageKeeper')：</td>
      </tr>
    </table>
<?php
    }
    $restArrs = array_chunk($restArr, 15, true);
    if($restArrs) {
      foreach ($restArrs as $key => $restArr) {
?>
    <div <?php if($key<count($restArrs)){ ?> style="page-break-before: always;"<?php }?>>
    <div class="qrcode" style="position: absolute; right: 0px; ">
        <img src="{{ $batch['batch_code_barcode'] }}">
     
        <div style="font-size: 18px; color: red; border-bottom: 2px solid #000000"><b style="color: black">NO.</b> {{ $batch['confirmation_number'] }}

      </div>
     </div>
    <h2 align="center">@lang('message.batchPage') <?php echo $key+2?>/<?php echo $page_count?> </h2>
    <table width="100%" border="0" align="center" style="margin-top: 30px;">
      <tr>
        <td width="33%">@lang('message.batchPageDate') 
：{{ date("Y-m-d", strtotime($batch['created_at'])) }}</td>
        <td width="33%">@lang('message.batchPageWarehouse')：{{ $batch['warehouse']['name_cn'] }}</td>
        <td>@lang('message.batchPageType')：{{ $batch['batch_type']['name'] }}</td>
      </tr>
    </table>
    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">@lang('message.batchPageDistributor')：{{ $batch['distributor']['name_cn'] }}</td>
        <td></td>
      </tr>
    </table>
    <table class="table  table-bordered text-center" style="margin-top: 30px;">
      <thead>
        <tr>
          <th width="60px">@lang('message.batchPageNo')</th>
          <th width="180px">@lang('message.batchPageSku')</th>
          <th >@lang('message.batchPageProductName')</th>
          <th width="120px">@lang('message.batchPageSpecName')</th>
          <th width="80px">@lang('message.batchPagePurcharseQty')</th>
          <?php if($showInStock??0){?>
            <th class="no-print" width="80px">@lang('message.batchPageActualQty')</th>
            <th class="no-print">@lang('message.batchPageBatchNo')</th>
          <?php }?>
          <th width="100px">@lang('message.batchPagePurcharsePrice')</th>
        </tr>
      </thead>
      <tbody>
          <?php $total = 0; $remarks = [];?>
        @forelse ($restArr as $k => $product)
          <?php $total += $product['purchase_price'];?>
          <?php if(!empty($product['remark'])) $remarks[] = sprintf("%s : %s; ",$product['spec']['product']['name_cn'],$product['remark'] );?>
        <tr>
          <td>{{ $k+1 }}</td>
          <td><img src="{{ $product['relevance_code_barcode'] }}">
              <p>{{ $product['relevance_code'] }}</p></td>
              <td><div>{{ $product['spec']['product']['name_cn'] }}</div></td>
              <td><span>{{$product['spec']['name_cn']}}</span></td>
          <td>{{ $product['need_num'] }}</td>
            <?php if($showInStock??0){?>
            <td class="no-print">{{ $product['stockin_num'] }}</td>
            <td class="no-print">{{ $product['sku'] }}</td>
            <?php }?>
          <td>{{ $product['purchase_price'] }}</td>
        </tr>
        @empty
        @endforelse
      </tbody>
    </table>
    </div>
<?php
      }
    }
?>  
  </div>
</body>
</html>
