<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang('message.orderSalePage')</title>
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
      width:550px;
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
<?php
    $pre_count = 25;
    $data_count = count($order['order_items']);
    $page_count = ceil($data_count/$pre_count+ (($data_count-$pre_count)/$pre_count));
?>
<body>
  <div class="container-fluid">
    <div class="A4"> 

      <div class="qrcode">
        <img src="{{ $order['out_sn_barcode'] }}">
        <p>{{ $order['out_sn'] }}</p>
      </div>
      <h2 class="text-center">@lang('message.orderSalePage') </h2>

      <div class="row" style="margin-top: 30px;">
        <div class="col-md-12" style="font-size:16px;"></div>
        <div class="col-md-12">@lang('message.orderPageDate'): {{ date("Y-m-d", strtotime($order['created_at'])) }}</div>
      </div>
      <table class="table  table-bordered" style="margin-top: 30px; border:1px solid #000 ">
        <tr>
          <td>
            <b>@lang('message.orderPageSaleReceiverInfo')</b><br/><br/>
            @lang('message.orderPageSaleReceiverName'): {{ $order['receiver_fullname'] }}<br/>
            @lang('message.orderPageSaleReceiverPhone'): {{ $order['receiver_phone'] }}<br/>
            @lang('message.orderPageSaleReceiverAddress'): {{ $order['receiver_province'] }}{{ $order['receiver_city'] }}{{ $order['receiver_district'] }}{{ $order['receiver_address'] }}
          </td>
        </tr>
      </table>
<?php

    $firstArr = $restArr = [];
    $line_no = 0;
    foreach ($order['order_items'] as $k => $item) {
        
        if($line_no < 20) {
            $firstArr[] = $item;
        }  else {
            $restArr[] = $item;
        }

        $line_no++;
    }

    $line_no = 0;
    if($firstArr) {
?>

      
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>@lang('message.orderPageSaleProduct')</th>
            <th>@lang('message.orderPageSaleSpec')</th>
            <th>@lang('message.orderPageOrderQty')</th>
            <th>@lang('message.orderPageSalePrice')</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($firstArr as $k => $item)
          <?php $line_no++;?>
            <tr>
              <td>{{ $line_no }}</td>
              <td><div>{{ $item['name_cn'] }}</div></td>
              <td> {{ $item['spec_name_cn'] }}</td>
              <td>{{ $item['amount'] }}</td>
              <td>{{ number_format($item['sale_price'],2 ) }}</td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>
<?php
    }

?>
      <div class="row">
        <div class="col-md-12">
          @lang('message.orderPageRemark')：
          {{ $order['remark'] }} 
         </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          @lang('message.orderPageWarehouse')：
          {{ $order['warehouse']['name_cn'] }}
         </div>
      </div>
<?php
      $restArrs = array_chunk($restArr, $pre_count, true);
      if($restArrs) {
        foreach ($restArrs as $key => $restArr) {
?>
        <div <?php if($key<count($restArrs)){ ?> style="page-break-before: always;"<?php }?>>
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>@lang('message.orderPageSaleProduct')</th>
            <th>@lang('message.orderPageSaleSpec')</th>
            <th>@lang('message.orderPageOrderQty')</th>
            <th>@lang('message.orderPageSalePrice')</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;?>
          @forelse ($restArr as $k => $item)
              <?php $line_no++;?>
            <tr>
              <td>{{ $line_no }}</td>
              <td><div>{{ $item['name_cn'] }}</div></td>
              <td> {{ $item['spec_name_cn'] }}</td>
              <td>{{ $item['amount'] }}</td>
              <td>{{ number_format($item['sale_price'],2 ) }}</td>
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
