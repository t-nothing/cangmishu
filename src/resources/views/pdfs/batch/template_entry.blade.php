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
  <div class="container-fluid" style="position: relative;">
    <div class="qrcode" style="position: absolute; right: 0px; ">
        <img src="{{ $batch['batch_code_barcode'] }}">
     
        <div style="font-size: 18px; color: red; border-bottom: 2px solid #000000"><b style="color: black">NO.</b> {{ $batch['confirmation_number'] }}

      </div>
     </div>
    <h2 align="center">入&nbsp;&nbsp;库&nbsp;&nbsp;单</h2>
    <table width="100%" border="0" align="center" style="margin-top: 30px;">
      <tr>
        <td width="33%">制单日期：{{ date("Y-m-d", strtotime($batch['created_at'])) }}</td>
        <td width="33%">仓库：{{ $batch['warehouse']['name_cn'] }}</td>
        <td>类型：{{ $batch['batch_type']['name'] }}</td>
      </tr>
    </table>
    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">供应商：{{ $batch['distributor']['name_cn'] }}</td>
        <td>备注：{{ $batch['remark'] }}</td>
      </tr>
    </table>
    
    <table class="table  table-bordered text-center" style="margin-top: 30px;">
      <thead>
        <tr>
          <th width="60px">序号</th>
          <th width="120px">货品编号</th>
          <th >货品名称</th>
          <th width="120px">规格型号</th>
          <th width="80px">进货数量</th>
          <?php if($showInStock??0){?>
            <th class="no-print" width="80px">实际数量</th>
            <th class="no-print">入库单批次号</th>
          <?php }?>
          <th width="100px">进货单价</th>
          <th width="100px">备注</th>
        </tr>
      </thead>
      <tbody>
          <?php $total = 0;?>
        @forelse ($batch['batch_products'] as $k => $product)
          <?php $total += $product['spec']['purchase_price'];?>
        <tr>
          <td>{{ $k+1 }}</td>
          <td>{{ $product['relevance_code'] }}</td>
          <td>{{ $product['spec']['product']['name_cn'] }}</td>
          <td>{{$product['spec']['name_cn']}}</td>
          <td>{{ $product['need_num'] }}</td>
            <?php if($showInStock??0){?>
            <td class="no-print">{{ $product['stockin_num'] }}</td>
            <td class="no-print">{{ $product['sku'] }}</td>
            <?php }?>
          <td>￥{{ $product['spec']['purchase_price'] }}</td>
          <td>{{ $product['remark'] }}</td>
        </tr>
        @empty
        @endforelse
      </tbody>
    </table>

    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">财务主管：</td>
        <td width="33%">部门经理：</td>
        <td >仓库保管人：</td>
      </tr>
    </table>
  </div>
</body>
</html>
