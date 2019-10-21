<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <link href="{{ url('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <title>@lang('message.orderPage')</title>
  <style type="text/css">
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
      <h3 class="text-center">@lang('message.orderPage')</h3>

      <div class="row" style="margin-top: 30px;">
        <div class="col-md-12" style="font-size:16px;">{{ $order['order_type']['name'] }}</div>
        <div class="col-md-12">@lang('message.orderPageDate'): {{ date("Y-m-d", strtotime($order['created_at'])) }}</div>
      </div>
      <table class="table  table-bordered" style="margin-top: 30px; border:1px solid #000 ">
        <tr>
          <td width="50%">
            <b>@lang('message.orderPageSenderInfo')</b><br/><br/>
            @lang('message.orderPageSenderName'): {{ $order['send_fullname'] }}<br/>
            @lang('message.orderPageSenderPhone'): {{ $order['send_phone'] }}<br/>
            @lang('message.orderPageSenderAddress'): {{ $order['send_province'] }}{{ $order['send_city'] }}{{ $order['send_district'] }}{{ $order['send_address'] }}
          </td>
          <td>
            <b>@lang('message.orderPageReceiverInfo')</b><br/><br/>
            @lang('message.orderPageReceiverName'): {{ $order['receiver_fullname'] }}<br/>
            @lang('message.orderPageReceiverPhone'): {{ $order['receiver_phone'] }}<br/>
            @lang('message.orderPageReceiverAddress'): {{ $order['receiver_province'] }}{{ $order['receiver_city'] }}{{ $order['receiver_district'] }}{{ $order['receiver_address'] }}
          </td>
        </tr>
      </table>


      
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>@lang('message.orderPageProduct')</th>
            <th>@lang('message.orderPageSku')</th>
            <th>@lang('message.orderPageOrderQty')</th>
            <th>@lang('message.orderPageOutboundQty')</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;?>
          @forelse ($order['order_items'] as $k => $item)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $item['name_cn'] }}{{ $item['spec_name_cn'] }}</td>
              <td> {{ $item['relevance_code'] }}</td>
              <td>{{ $item['amount'] }}</td>
              <td>{{ $item['pick_num'] }}</td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>
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
    </div>
  </div>
</body>
</html>
