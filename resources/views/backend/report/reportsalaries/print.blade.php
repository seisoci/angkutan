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
        size: A4 potrait;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>LAPORAN REKAP GAJI SUPIR</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Printed: {{ $config['current_time'] }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:20%">{{ $profile['name'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Priode: {{ $date ?? 'All' }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%">{{ $profile['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Supir: {{ $driver->name ?? 'All' }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%"> {{ $profile['telp'] ?? ''}}</td>
      </tr>
      <tr>
        <td></td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%">FAX {{ $profile['fax'] ?? ''}}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th scope="col">#</th>
        <th>Nama Supir</th>
        <th>Nama Pelanggan</th>
        <th>T. Muat</th>
        <th>Sub Total (Inc. Tax, Fee)</th>
        <th>Biaya Operasional</th>
        <th>Spare Part</th>
        <th>Gaji Supir</th>
        <th>Sisa Bersih</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->date_begin }}</td>
          <td class="text-right">{{ number_format($item->total_basic_price_after_thanks, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->total_operational, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->total_sparepart, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->total_salary, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->total_clean_summary, 2, ',', '.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <td colspan="4" class="text-right">Total:</td>
      <td class="text-right">{{ number_format($data->sum('total_basic_price_after_thanks'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_operational'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_sparepart'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_salary'), 2, ',', '.') }}</td>
      <td class="text-right">{{ number_format($data->sum('total_clean_summary'), 2, ',', '.') }}</td>
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