<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang("message.recountPage")</title>
  <style type="text/css">
    <?php echo file_get_contents(public_path('bootstrap/css/bootstrap.min.css'));?>
    *{
      font-size: 13px;
    }
    h3{
      font-size: 16px;
    }
    .header-table th{
      /*text-align: right;*/
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
        <img src="{{ $data['recount_no_barcode'] }}">
        <p>{{ $data['recount_no'] }}</p>
      </div>
      <h3 class="text-center"> @lang("message.recountPage") </h3>

      <table style="margin-top: 30px;" width="100%" class="header-table">
        <tr>
          <th width="100px">@lang("message.recountPageWarehouse") :  </th>
          <td>{{ $data['warehouse']['name_cn'] }}</td>
        </tr>
        <tr>
          <th>@lang("message.recountPageDate"): </th>
          <td>{{ date("Y-m-d", strtotime($data['created_at'])) }}</td>
        </tr>
        <tr>
          <th>@lang("message.recountPageCreator"): </th>
          <td>{{ $data['operator_user']['nickname'] }}</td>
        </tr>

      </table>
      <div style="margin-top: 30px;"></div>

    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th> @lang("message.recountPageProductSpecName") </th>
            <th> @lang("message.recountPageProductSpecSku") </th>
            <th> @lang("message.recountPageInboundBatch") </th>
            <th> @lang("message.recountPageOrginStock") </th>
            <th> @lang("message.recountPageInventoryCount") </th>
            <th> @lang("message.recountPageInventoryLoss") </th>
            <th> @lang("message.recountPageInventoryLossMoney") </th>
            <th> @lang("message.recountPageInventoryProfit") </th>
            <th> @lang("message.recountPageInventoryProfitMoney") </th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;?>
          @forelse ($data['stocks'] as $k => $item)
          <?php
            $pk = ($item['shelf_num_now'] > $item['shelf_num_orgin']) ? 0 : ($item['shelf_num_now'] - $item['shelf_num_orgin']) ;
            $pk = abs($pk);
            $py = ($item['shelf_num_now'] < $item['shelf_num_orgin']) ? 0 : ($item['shelf_num_now'] - $item['shelf_num_orgin']);
            $py = abs($py);

            $pk_money = $item['shelf_num_orgin'] > 0 ? number_format($pk * $item['total_purcharse_orgin']/$item['shelf_num_orgin'], 2) : 0;

            $py_money = $item['shelf_num_now'] > 0 ? number_format($py * $item['total_purcharse_now']/$item['shelf_num_now'],2) :0 ;
          ?>
            <tr>
              <td>{{ $k+1 }}</td>
              <td>{{ $item['name_cn'] }}</td>
              <td>{{ $item['relevance_code'] }}</td>
              <td>{{ $item['stock_sku'] }}</td>
              <td>{{ $item['shelf_num_orgin'] }}</td>
              <td>{{ $item['shelf_num_now'] }}</td>
              <td>{{ $pk }}</td>
              <td>{{ $pk_money  }}</td>
              <td>{{ $py }}</td>
              <td>{{ $py_money }}</td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>
      <div class="row">
        <div class="col-md-12">
          @lang("message.recountPageRemark") :
          {{ $data['remark'] }} </div>
      </div>
    </div>
  </div>
</body>
</html>
