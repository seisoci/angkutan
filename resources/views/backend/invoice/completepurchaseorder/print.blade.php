<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
    <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
          type="text/css"/>
  @endforeach
  <style type="text/css">
    .table-title tbody tr td {
      padding-top: 0;
      padding-bottom: 0;
      line-height: 10px;
    }

    @media print {
      .table-title tbody tr td {
        padding-top: 0;
        padding-bottom: 0;
        line-height: 10px;
      }

      table {
        page-break-after: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      td {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      thead {
        display: table-row-group;
      }

      tfoot {
        display: table-row-group;
      }

      body {
        padding: 4em;
        color: #fff;
        background-color: #000;
      }

      hr {
        border: 1px #000 solid;
        width: 100%;
        display: block;
      }

      @page {
        size: A4 portrait;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>{{ $config['page_title'] }}</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="padding-left:4rem;width:20%">No. Invoice</td>
        <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
      </tr>
      <tr>
        <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="padding-left:4rem;width:20%">Supplier</td>
        <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td class="text-left" style="width:18%"> {{ $data->supplier->name }}</td>
      </tr>
      <tr>
        <td>Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
        <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td class="text-left" style="width:18%"> {{ $data->invoice_date }}</td>
      </tr>
      <tr>
        <td>Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="padding-left:4rem;width:20%">Metode Pembayaran</td>
        <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td class="text-left" style="width:18%"> {{ $data->method_payment == 'cash' ? 'Tunai' : 'Kredit' }}</td>
      </tr>
      <tr>
        <td>Deskripsi : {{ $data->description ?? ''}}</td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="padding-left:4rem;width:20%">Tanggal Jth Tempo</td>
        <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td class="text-left" style="width:18%"> {{ $data->due_date }}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th scope="col" class="text-center">#</th>
        <th style="min-width: 150px">Tgl Buat Invoice</th>
        <th style="min-width: 150px">No. Invoice</th>
        <th style="width: 100%">Supplier</th>
        <th style="min-width:200px; text-align: right">Total Tagihan</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data->invoice_purchase as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{  $item->invoice_date }}</td>
          <td>{{ $item->prefix . '-' . $item->num_bill  }}</td>
          <td>{{ $item->supplier->name }}</td>
          <td
            class="text-right currency">{{ number_format(($item->total_bill - $item->diskon) ?? 0,2, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="4" class="text-right font-weight-bold">Diskon</td>
        <td class="text-right">{{ number_format($data->discount ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="4" class="text-right font-weight-bold">Total Tagihan</td>
        <td class="text-right">{{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
      </tr>
      </tbody>
    </table>
    <h4 class="text-dark">Pembayaran</h4>
    <table class="table">
      <thead>
      <tr>
        <th style="width:5%">#</th>
        <th style="width:65%">Tanggal</th>
        <th class="text-right" style="width:10%">Nominal</th>
        <th class="text-right" style="width:10%">Total</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data->payment_complete as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->date_payment }}</td>
          <td class="text-right">{{ number_format($item->payment, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->payment, 2, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="3" class="text-right font-weight-bold">Total Tagihan</td>
        <td class="text-right">{{ number_format($data->total_net ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bold">Total Pembayaran</td>
        <td class="text-right">{{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bold">Sisa Tagihan</td>
        <td class="text-right">{{ number_format($data->rest_payment ?? 0,2, ',', '.') }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</div>
</body>
@foreach(config('layout.resources.js') as $script)
  <script src="{{ asset($script) }}" type="text/javascript"></script>
  <script>
    window.onload = function (e) {
      window.print();
    }
    window.setTimeout(function () {
      window.close();
    }, 2000);
  </script>
@endforeach

</html>
