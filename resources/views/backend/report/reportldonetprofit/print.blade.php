<!DOCTYPE html>
<html>

<head>
  <style>
    table {
      width: 100%;
      page-break-after: auto;
    }

    @media print {
      .table-title td,
      th {
        padding: 0;
      }

      table {
        width: 100%;
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
        padding: 1em;
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

      .table-outline {
        border: 1px solid black;
      }

      .table-outline thead th {
        border-top: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
      }

      .table-outline td {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border-top: none !important;
      }
    }
  </style>
</head>
<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <div class="col-md-11">
      <h2 style="text-align: center"><u>{{ $config['page_title'] }}</u>
      </h2>
      <table style="margin-bottom: 10px">
        <tbody>
        <tr>
          <td class="font-weight-normal" style="width:50%">Printed: {{ $config['current_time'] }}
          </td>
          <td class="text-left" style="width:10%"></td>
          <td class="text-left" style="width:20%">{{ $cooperationDefault['nickname'] ?? '' }}</td>
        </tr>
        <tr>
          <td class="font-weight-normal" style="width:50%">Priode: {{ $date ?? 'All' }}
          </td>
          <td class="text-left" style="width:10%"></td>
          <td class="text-left" style="width:25%">{{ $cooperationDefault['address'] ?? '' }}</td>
        </tr>
        <tr>
          <td class="font-weight-normal" style="width:50%">LDO: {{ $anotherExpedition ?? 'All' }}
          </td>
          <td class="text-left" style="width:10%"></td>
          <td class="text-left" style="width:18%">Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
        </tr>
        </tbody>
      </table>
      <table style="padding-top: 0 !important; padding-bottom: 0 !important; margin-bottom: 0 !important; border-left: 1px solid #000 !important;
             border-right: 1px solid #000 !important;border-top: 1px solid #000 !important;border-bottom: 1px solid #000 !important;font-family: monospace; font-size:10px;">
        <thead>
        <tr>
          <th style="width:5%">#</th>
          <th>No. Job Order</th>
          <th>Tgl Mulai</th>
          <th>Tgl Selesai</th>
          <th>LDO</th>
          <th>Supir LDO</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Muatan</th>
          <th style="text-align: right">Total Harga Dasar</th>
          <th style="text-align: right">Total Harga Dasar(Inc. Tax & FEE)</th>
          <th style="text-align: right">Total Harga Dasar LDO</th>
          <th style="text-align: right">Pendapatan Kotor</th>
          <th style="text-align: right">Pendapatan Bersih(Inc. Tax & FEE)</th>
          <th>Created At</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->num_prefix }}</td>
            <td>{{ $item->date_begin }}</td>
            <td>{{ $item->date_end }}</td>
            <td>{{ $item->anotherexpedition->name }}</td>
            <td>{{ $item->driver->name }}</td>
            <td>{{ $item->costumer->name }}</td>
            <td>{{ $item->routefrom->name }}</td>
            <td>{{ $item->routeto->name }}</td>
            <td>{{ $item->cargo->name }}</td>
            <td style="text-align: right">{{ number_format($item->total_basic_price, 2, '.',',') }}</td>
            <td style="text-align: right">{{ number_format($item->total_basic_price_after_thanks, 2, '.',',') }}</td>
            <td style="text-align: right">{{ number_format($item->total_basic_price_ldo, 2, '.',',') }}</td>
            <td
              style="text-align: right">{{ number_format(($item->total_basic_price - $item->total_basic_price_ldo), 2, '.',',') }}</td>
            <td
              style="text-align: right">{{ number_format(($item->total_basic_price_after_thanks - $item->total_basic_price_ldo), 2, '.',',') }}</td>
            <td>{{ $item->created_at }}</td>
          </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
          <td colspan="10" style="text-align: right; font-weight: bold">TOTAL</td>
          <td
            style="text-align: right; font-weight: bold">{{ number_format($data->sum('total_basic_price'), 2, ',', '.') }}</td>
          <td
            style="text-align: right; font-weight: bold">{{ number_format($data->sum('total_basic_price_after_thanks'), 2, ',', '.') }}</td>
          <td
            style="text-align: right; font-weight: bold">{{ number_format($data->sum('total_basic_price_ldo'), 2, ',', '.') }}</td>
          <td
            style="text-align: right; font-weight: bold">{{ number_format(($data->sum('total_basic_price') - $data->sum('total_basic_price_ldo')), 2, ',', '.') }}</td>
          <td
            style="text-align: right; font-weight: bold">{{ number_format(($data->sum('total_basic_price_after_thanks') - $data->sum('total_basic_price_ldo')), 2, ',', '.') }}</td>
        </tr>
        </tfoot>
      </table>
    </div>
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
