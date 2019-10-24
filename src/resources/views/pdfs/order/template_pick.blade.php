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
      
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>@lang('message.orderPageProduct')</th>
            <th>@lang('message.batchPageBatchNo')</th>
            <th>@lang('message.orderPageSku')</th>
            <th>@lang('message.orderPageWarehouseLocation')</th>
            <th>@lang('message.orderPagePickqty')</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;$index = 0;?>
          @forelse ($order['order_items'] as $k => $item)
            @forelse ($item['stocks'] as $kk => $itemLocation)
            <?php $index++;?>
            <tr>
              <td>{{ ($index) }}</td>
              <td>{{ $item['name_cn'] }}{{ $item['spec_name_cn'] }}</td>
              <td> {{ $itemLocation['stock_sku'] }}</td>
              <td>{{ $itemLocation['relevance_code'] }}</td>
              <td>{{ $itemLocation['warehouse_location_code'] }}</td>
              <td>{{ $itemLocation['pick_num'] }}</td>
            </tr>
            @empty
            @endforelse
          @empty
          @endforelse
        </tbody>
      </table>
      <div class="row">
        <div class="col-md-12">
          @lang('message.orderPageRemark'): 
          {{ $order['remark'] }} </div>
      </div>
    </div>
  </div>
</body>
</html>
