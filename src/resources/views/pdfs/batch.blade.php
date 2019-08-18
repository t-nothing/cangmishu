<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <link href="{{ url('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <title>入库单</title>
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
        <img src="{{ $batch['batch_code_barcode'] }}">
        <p>{{ $batch['batch_code'] }}</p>
      </div>
      <h3 class="text-center"> {{ $batch['warehouse']['name_cn'] }}入库单</h3>

      <div class="row" style="margin-top: 30px;">
        <div class="col-md-4">制单日期：{{ date("Y-m-d", strtotime($batch['created_at'])) }}</div>
        <div class="col-md-8 text-right">类型：{{ $batch['batch_type']['name'] }}</div>
      </div>
      <div class="row">
        <div class="col-md-12">供应商：{{ $batch['distributor']['name_cn'] }}</div>
      </div>
      <div style="margin-top: 30px;"></div>
    <h6>商品列表</h6>
      
    <table class="table  table-bordered text-center" >
        <thead>
          <tr>
            <th>#</th>
            <th>SKU</th>
            <th>商品信息</th>
            <th>进货单价（元）</th>
            <th>进货数量</th>
            <?php if($batch['download']??0){?>
            <th class="no-print">实时数量</th>
            <th class="no-print">入库单批次号</th>
            <?php }?>
            <th>备注</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0;?>
          @forelse ($batch['batch_products'] as $k => $product)
          <?php $total += $product['spec']['purchase_price'];?>
          <tr>
            <td>{{ $k+1 }}</td>
            <td>
              <img src="{{ $product['relevance_code_barcode'] }}">
              <p>{{ $product['relevance_code'] }}</p>
            </td>
            <td>
              {{ $product['spec']['product']['name_cn'] }}({{$product['spec']['name_cn']}})
            </td>
            <td>￥{{ $product['spec']['purchase_price'] }}</td>
            <td>{{ $product['need_num'] }}</td>
            <?php if($batch['download']??0){?>
            <td class="no-print">{{ $product['stockin_num'] }}</td>
            <td class="no-print">{{ $product['sku'] }}</td>
            <?php }?>
            <td>{{ $product['remark'] }}</td>
          </tr>
          @empty
          @endforelse
        </tbody>
        <tfoot class="text-left">
          <tr>
            <td colspan="3">合计：</td>
            <td class="text-center"> ￥ {{ number_format($total, 2) }}</td>
            <?php if($batch['download']??0){?>
            <td class="text-center no-print"> {{ $batch['need_num'] }}</td>
            <td class="text-center no-print"> {{ $batch['stock_num'] }}</td>
            <?php }?>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
      <div class="row">
        <div class="col-md-12">备注：{{ $batch['remark'] }}</div>
      </div>
    </div>
  </div>
</body>
</html>
