<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,user-scalable=no" />
  <title>入库单</title>
  <style>
    * {
      margin: 0px;
      padding: 0px;
    }
    table {
      border-collapse: collapse;
      border-spacing: 0;
    }
    .title {
      text-align: center;
      margin: 50px;
      font-size: 30px;
      font-weight: bold;
    }
    .top-left {
      padding: 20px 60px;
      width: 400px;
    }
    .top-left p {
      line-height: 30px;
    }
    .top-left .top1 {
      padding-left: 30px;
    }
    .top-left .top2 {
      padding-left: 14px;
    }
    .top-left .top3 {
      padding-left: 44px;
    }

    .qrcode {
      position: absolute;
      right: 120px;
      top: 170px;
    }
    .qrcode img {
      height: 70px;
      width: 250px;
    }
    .qrcode p {
      text-align: center;
    }

    .goods-table p {
      margin-left: 5%;
      font-size: 18px;
      font-weight: bold;
    }

    table {
      width: 90%;
      max-width: 100%;
      margin: 20px 5%;
      background-color: #fff;
    }

    .table td,
    .table th {
      padding: 10px;
      border: 1px solid #eee;
      text-align: left;
      font-size: 14px;
      color: #555;
    }

    .bottom-right {
      padding-top: 50px;
      padding-left: 65%;
      line-height: 30px;
    }
    .goods-table .table .text_center {
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="warehouse" id="box_info">
    <p class="title">({{ $batch['warehouse']['name_cn'] }}) 入库单</p>

    <div class="top-left">
      <p>
        <span>入库单分类</span>
        <span class="top1">{{ $batch['batch_type']['name'] }}</span>
      </p>
      <p>
        <span>入库单编号</span>
        <span class="top1">{{ $batch['confirmation_number'] }}</span>
      </p>
      <p>
        <span>入库单供应商</span>
        <span class="top2">{{ $batch['distributor']['name_cn'] }}</span>
      </p>
      <p>
        <span>入库备注</span>
        <span class="top3">{{ $batch['remark'] }}</span>
      </p>

    </div>

    <div class="qrcode">
      <img src="{{ $batch['batch_code_barcode'] }}">
      <p>{{ $batch['batch_code'] }}</p>
    </div>

    <div class="goods-table">
      <p>商品列表</p>
      <table class="table">
        <tr>
          <th class="text_center">#</th>
          <th class="text_center">SKU</th>
          <th class="text_center">商品信息</th>
          <th class="text_center">采购价格</th>
          <th class="text_center">预计数量</th>
          <th class="text_center">实时数量</th>
          <th class="text_center">入库单批次号</th>
          <th class="text_center">备注</th>
        </tr>
        <tbody>
          @forelse ($batch['batch_products'] as $k => $product)
          <tr>
            <td class="text_center">{{ $k+1 }}</td>
            <td class="text_center">
              <img src="{{ $product['relevance_code_barcode'] }}">
              <p>{{ $product['relevance_code'] }}</p>
            </td>
            <td class="text_center">{{ $product['spec']['product']['name_cn'] }}({{$product['spec']['name_cn']}})</td>
            <td class="text_center">{{ $product['spec']['purchase_price'] }}</td>
            <td class="text_center">{{ $product['need_num'] }}</td>
            <td class="text_center">{{ $product['total_stockin_num'] }}</td>
            <td class="text_center">{{ $product['sku'] }}</td>
            <td class="text_center">{{ $product['remark'] }}</td>
          </tr>
          @empty
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="bottom-right">
      <!-- <p>运单号: info_waybill</p> -->
      <p>预计入库总数: {{ $batch['total_num']['total_need_num'] }}</p>
      <!-- <p>操作人: info_operator</p> -->
      <p>预期入库时间: {{ $batch['plan_time'] }}  -  {{ $batch['over_time'] }}</p>
    </div>
  </div>
</body>
</html>
