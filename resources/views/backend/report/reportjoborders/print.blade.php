<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
    <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
          type="text/css"/>
  @endforeach

  <style>
    @media print {

      .table-title td,
      th {
        padding: 0;
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
        size: A4 landscape;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>LAPORAN TAGIHAN JOB ORDER</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td class="font-weight-normal" style="width:30%">Printed: {{ $config['current_time'] }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:25%">{{ $cooperationDefault['nickname'] ?? '' }}</td>
      </tr>
      <tr>
        <td class="font-weight-normal" style="width:30%">Priode: {{ $date ?? 'All' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:25%">{{ $cooperationDefault['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td class="font-weight-normal" style="width:30%">Costumer: {{ !empty($costumer) ? $costumer->name : 'All' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:25%">Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
      </tr>
      <tr>
        <td class="font-weight-normal" style="width:30%">No. Polisi: {{ !empty($transport) ? $transport->num_pol : 'All' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:25%">Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>No. Polisi</th>
        <th>No. Prefix</th>
        <th>No. SJ</th>
        <th>No. Shipment</th>
        <th>Nama Pelanggan</th>
        <th>Rute Dari</th>
        <th>Rute Tujuan</th>
        <th>Jenis Barang</th>
        <th class="text-right">Tarif (Rp.)</th>
        <th class="center">Qty</th>
        <th class="text-right">Total</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td style="width: 100px">{{ $item->transport->num_pol }}</td>
          <td style="width: 100px">{{ $item->num_prefix }}</td>
          <td>{{ $item->no_sj }}</td>
          <td>{{ $item->no_shipment }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->routefrom->name }}</td>
          <td>{{ $item->routeto->name }}</td>
          <td>{{ $item->cargo->name }}</td>
          <td class="text-right">{{ number_format($item->basic_price, 2, ',', '.') }}</td>
          <td class="text-center">{{ number_format($item->payload, 2, ',', '.') }}</td>
          <td class="text-center">{{ number_format($item->total_basic_price, 2, ',', '.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <td colspan="10" class="text-right">Total Rp.</td>
      <td class="text-right">{{ number_format($data->sum('basic_price'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('payload'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_basic_price'), 2, ',', '.') }}</td>
      </tfoot>
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
