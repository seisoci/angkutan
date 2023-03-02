<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
    <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
          type="text/css"/>
  @endforeach

  <style type="text/css">
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
        size: A4 portrait;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>{{ $config['page_title'] }}</u>
    </h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td class="font-weight-normal" style="width:10%">Printed: {{ $config['current_time'] }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:10%">{{ $cooperationDefault['nickname'] ?? '' }}</td>
      </tr>
      <tr>
        <td class="font-weight-normal">Priode: {{ $date ?? 'All' }}
        </td>
        <td class="text-left"></td>
        <td class="text-left">{{ $cooperationDefault['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td class="font-weight-normal">Nama Supir: {{ $driver }}
        <td class="text-left"></td>
        <td class="text-left">Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
      </tr>
      <tr>
        <td class="font-weight-normal">No. Polisi: {{ $transport }}
        <td class="text-left"></td>
        <td class="text-left">Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
      </tr>
      <tr>
        <td class="font-weight-normal">Nama Sparepart: {{ $sparepart }}
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th scope="col">#</th>
        <th>No. Pemakaian</th>
        <th>Tgl Pemakaian</th>
        <th>Nama Sparepart</th>
        <th>Nama Supir</th>
        <th>No. Polisi</th>
        <th>Keterangan</th>
        <th class="text-center">Jumlah</th>
        <th class="text-right">Harga</th>
        <th class="text-right">Total Harga</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->num_invoice }}</td>
          <td>{{ $item->invoice_date }}</td>
          <td>{{ $item->sparepart_name }}</td>
          <td>{{ $item->driver_name }}</td>
          <td>{{ $item->num_pol }}</td>
          <td>{{ $item->description }}</td>
          <td class="text-center">{{ $item->qty }}</td>
          <td class="text-right">{{ number_format($item->price, 2, '.', ',') }}</td>
          <td class="text-right">{{ number_format($item->total_price, 2, '.', ',') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <td colspan="7" class="text-right">Total</td>
      <td class="text-center">{{ $data->sum('qty') }}</td>
      <td class="text-right">{{ number_format($data->sum('price'), 2, '.', ',') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_price'), 2, '.', ',') }}</td>
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
