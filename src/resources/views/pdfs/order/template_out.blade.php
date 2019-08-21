<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <link href="{{ url('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <title>拣货单</title>
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
      <h3 class="text-center">出库单</h3>

      <div class="row" style="margin-top: 30px;">
        <div class="col-md-12" style="font-size:16px;">{{ $order['order_type']['name'] }}</div>
        <div class="col-md-12">制单日期：{{ date("Y-m-d", strtotime($order['created_at'])) }}</div>
      </div>
      <div style="margin-top: 30px;"></div>
      
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>商品名称及规格</th>
            <th>SKU编码</th>
            <th>出库数量</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;?>
          @forelse ($order['order_items'] as $k => $item)
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $item['name_cn'] }}</td>
              <td> {{ $item['relevance_code'] }}</td>
              <td>{{ $item['pick_num'] }}</td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>
      <div class="row">
        <div class="col-md-12">
          备注：
          {{ $order['remark'] }} 
         </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          仓库：
          {{ $order['warehouse']['name_cn'] }}
         </div>
      </div>
    </div>
  </div>
</body>
</html>
