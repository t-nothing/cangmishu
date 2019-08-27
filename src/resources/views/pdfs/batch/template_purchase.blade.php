<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <link href="{{ url('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <title>采购单申请单</title>
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
    <span style="position: absolute; right: 0px; font-size: 18px; color: red; border-bottom: 2px solid #000000"><b style="color: black">NO.</b> {{ $batch['confirmation_number'] }}</span>
    <h2 align="center">采&nbsp;&nbsp;购&nbsp;&nbsp;单</h2>
    <table width="100%" border="0" align="center" style="margin-top: 30px;">
      <tr>
        <td width="33%">供应商：{{ $batch['distributor']['name_cn'] }}</td>
        <td width="33%">采购部门：{{ $batch['warehouse']['name_cn'] }}</td>
        <td>类型：{{ $batch['batch_type']['name'] }}</td>
      </tr>
    </table>
    
    <table class="table  table-bordered text-center" style="margin-top: 30px;">
      <thead>
        <tr>
          <th width="60px">序号</th>
          <th width="120px">货品编号</th>
          <th >货品名称</th>
          <th width="120px">规格型号</th>
          <th width="60px">数量</th>
          <th width="105px">进货单价(元)</th>
          <th width="105px">金额(元)</th>
          <!-- <th width="100px">备注</th> -->
        </tr>
      </thead>
      <tbody>
          <?php $total = 0; $remarks = [];?>
        @forelse ($batch['batch_products'] as $k => $product)
          <?php $total += $product['purchase_price'] * $product['need_num'];?>
          <?php if(!empty($product['remark'])) $remarks[] = sprintf("%s : %s;<br/>",$product['spec']['product']['name_cn'],$product['remark'] );?>
        <tr>
          <td>{{ $k+1 }}</td>
          <td>{{ $product['relevance_code'] }}</td>
          <td>{{ $product['spec']['product']['name_cn'] }}</td>
          <td>{{$product['spec']['name_cn']}}</td>
          <td>{{ $product['need_num'] }}</td>
          <td>{{ $product['purchase_price'] }}</td>
          <td>{{ number_format($product['purchase_price'] * $product['need_num'], 2) }}</td>
          <!-- <td>{{ $product['remark'] }}</td> -->
        </tr>
        @empty
        @endforelse
      </tbody>
      <tfoot class="text-left">
        <tr>
          <td colspan="4">合计：</td>
          <td class="text-center"> {{ $batch['need_num'] }}</td>
          <td></td>
          <td class="text-center">￥ {{ number_format($total, 2) }}</td>
        </tr>
      </tfoot>
    </table>

    <table width="100%" border="0"  align="center" class="table table-bordered ">
      <tr>
        <td width="180px">采购备注：</td>
        <td height="100px">{{ implode(",", $remarks)}}</td>
      </tr>
    </table>

    <table width="100%" border="0"  align="center">
      <tr>
        <td width="33%">制单日期：{{ date("Y-m-d", strtotime($batch['created_at'])) }}</td>
        <td width="33%">采购人签名：</td>
        <td >负责人签名：</td>
      </tr>
    </table>
  </div>
</body>
</html>
