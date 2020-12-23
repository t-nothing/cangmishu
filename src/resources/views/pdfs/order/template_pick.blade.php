<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang('message.orderPagePicking')</title>
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
      page-break-inside: avoid;
      page-break-after: avoid;
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
      width:350px;
      height: 22px;
      word-break:keep-all;/* 不换行 */
      white-space:nowrap;/* 不换行 */
      overflow:hidden;/* 内容超出宽度时隐藏超出部分的内容 */
      text-overflow:ellipsis;
    }
    .qrcode{
      float: right;
      font-size: 15px;
      position: absolute;
      right: 0px;
      top:50px;
      text-align: center;
    }

    .A4{
      background: white;
      position: relative;
    }
    .no-print{
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

<body>
  <div class="container-fluid">
    <div class="A4">

      <div class="qrcode">
        <img src="{{ $order['out_sn_barcode'] }}">
        <p>{{ $order['out_sn'] }}</p>
      </div>
      <h3 class="text-center"> @lang('message.orderPagePicking')</h3>

      <div class="row" style="margin-top: 30px;">
        <div class="col-md-6" style="font-size:16px;">{{ $order['order_type']['name'] }}</div>
      </div>
      <div class="row" >
        <div class="col-md-12" >@lang('message.orderPageWarehouse'): {{ $order['warehouse']['name_cn'] }}</div>

        <div class="col-md-12">@lang('message.orderPageDate'): {{ date("Y-m-d", strtotime($order['created_at'])) }}</div>

      </div>
      <div style="margin-top: 30px;"></div>
<?php

    $firstArr = $restArr = [];
    $line_no = 0;
    foreach ($order['order_items'] as $k => $item) {

        foreach ($item['stocks'] as $kk => $itemLocation) {
            $itemLocation["name_cn"] = $item['name_cn'];
            $itemLocation["spec_name_cn"] = $item['spec_name_cn'];
            if($line_no < 25) {
                $firstArr[] = $itemLocation;
            }  else {
                $restArr[] = $itemLocation;
            }

            $line_no++;
        }
    }


    if($firstArr) {
?>
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th width="400">@lang('message.orderPageProduct')</th>
            <th >@lang('message.batchPageBatchNo')</th>
            <th>@lang('message.orderPageSku')</th>
            <th>@lang('message.orderPageWarehouseLocation')</th>
            <th width="100">@lang('message.orderPagePickqty')</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;$index = 0;?>
            @forelse ($firstArr as $kk => $itemLocation)
            <?php $index++;?>
            <tr>
              <td>{{ ($index) }}</td>
              <td><div class="word-wrap:break-word;">{{ $itemLocation['name_cn'] }}  -  {{ $itemLocation['spec_name_cn'] }}</div></td>
              <td> {{ $itemLocation['stock_sku'] }}</td>
              <td>{{ $itemLocation['relevance_code'] }}</td>
              <td>{{ $itemLocation['warehouse_location_code'] }}</td>
              <td>{{ $itemLocation['pick_num'] }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
      </table>

      <div class="row">
        <div class="col-md-12">
          @lang('message.orderPageRemark'):
          {{ $order['remark'] }} </div>
      </div>
<?php
    }
    $restArrs = array_chunk($restArr, 30, true);
    if($restArrs) {
      foreach ($restArrs as $key => $restArr) {
?>
    <div <?php if($key<count($restArrs)){ ?> style="page-break-after:always;"<?php }?>>
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th  width="400">@lang('message.orderPageProduct')</th>
            <th>@lang('message.batchPageBatchNo')</th>
            <th>@lang('message.orderPageSku')</th>
            <th>@lang('message.orderPageWarehouseLocation')</th>
            <th width="100">@lang('message.orderPagePickqty')</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;$index = 0;?>
            @forelse ($restArr as $kk => $itemLocation)
            <?php $index++;?>
            <tr>
              <td>{{ ($index) }}</td>
              <td><div class="word-wrap:break-word;">{{ $itemLocation['name_cn'] }} - {{ $itemLocation['spec_name_cn'] }}</div> </td>
              <td> {{ $itemLocation['stock_sku'] }}</td>
              <td>{{ $itemLocation['relevance_code'] }}</td>
              <td>{{ $itemLocation['warehouse_location_code'] }}</td>
              <td>{{ $itemLocation['pick_num'] }}</td>
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
  </div>
</body>
</html>
