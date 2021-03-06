<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>@lang('message.purchasePage')</title>
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
    <span style="position: absolute; right: 0px; font-size: 18px; color: red; border-bottom: 2px solid #000000"><b style="color: black">NO.</b> {{ $purchase['purchase_code'] }}</span>
    <h2 align="center">@lang('message.purchasePage')</h2>
    <table width="100%" border="0" align="center" style="margin-top: 30px;">
      <tr>
        <td width="33%">@lang('message.purchasePageInvoiceNumber')：<span style=" font-size: 18px; color: red; border-bottom: 2px solid #000000"> {{ $purchase['order_invoice_number'] }} </span></td>
        <td width="33%">@lang('message.batchPageDistributor')：{{ $purchase['distributor']['name_cn'] }}</td>
        <td>@lang('message.batchPurcharseDepartment')：{{ $purchase['warehouse']['name_cn'] }}</td>
      </tr>
    </table>

    <table class="table  table-bordered text-center" style="margin-top: 30px;">
      <thead>
        <tr>
          <th width="60px">@lang('message.batchPageNo')</th>
          <th width="120px">@lang('message.batchPageSku')</th>
          <th >@lang('message.batchPageProductName')</th>
          <th width="105px">@lang('message.batchPagePurcharseQty')</th>
          <th width="105px">@lang('message.batchPagePurcharsePrice')</th>
          <th width="105px">@lang('message.purchasePageArriveQty')</th>
        </tr>
      </thead>
      <tbody>
          <?php $total = 0; $remarks = [];?>
        @forelse ($purchase['items'] as $k => $product)
        <tr>
          <td>{{ $k+1 }}</td>
          <td>{{ $product['relevance_code'] }}</td>
          <td>{{ $product['product_spec_name'] }}</td>
          <td>{{ $product['need_num']}}</td>
          <td>{{ number_format($product['purchase_price'], 2) }}</td>
          <td>{{ $product['confirm_num'] }}</td>
        </tr>
        @empty
        @endforelse
      </tbody>
      <tfoot class="text-left">
        <tr>
          <td colspan="3">@lang('message.batchPurcharseTotal')：</td>
          <td class="text-center"> {{ $purchase['need_num'] }}</td>
          <td class="text-center">{{ number_format($purchase['sub_total'],2) }}</td>
          <td class="text-center">{{ $purchase['confirm_num'] }}</td>
        </tr>
      </tfoot>
    </table>

    <table width="100%" border="0"  align="center" class="table table-bordered ">
      <tr>
        <td width="180px">@lang('message.batchPurcharseRemark')：</td>
        <td height="100px">{{ $purchase['remark'] }}</td>
      </tr>
    </table>

    <table width="100%" border="0"  align="center">
      <tr>
        <td width="50%">@lang('message.batchPageDate'): {{ date("Y-m-d", strtotime($purchase['created_at'])) }}</td>
        <td width="50%">@lang('message.batchPurcharseInitiator'):</td>
      </tr>
    </table>
  </div>
</body>
</html>
