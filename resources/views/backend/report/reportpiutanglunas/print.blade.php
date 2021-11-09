<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
    <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
          type="text/css" />
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
        size: A4 portrait;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>LAPORAN PIUTANG (Lunas/Partial)</u></h2>
    <table class="table table-borderless table-title">
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
        <td class="text-left" style="width:18%">{{ $cooperationDefault['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td class="font-weight-normal" style="width:50%">Pelanggan: {{ $costumer->name ?? '' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:18%">Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
      </tr>
      <tr>
        <td></td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:18%">Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th style="width:5%">#</th>
        <th>Tanggal</th>
        <th>Pelanggan</th>
        <th>No. Invoice</th>
        <th style="text-align: right">Total Tagihan</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->invoice_date }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->num_invoice }}</td>
          <td style="text-align: right">{{ number_format($item->total_bill ?? 0,2, ',', '.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <th colspan="4" style="text-align: right">Total</th>
      <th style="font-weight: bold; text-align: right">{{ number_format($data->sum('total_bill') ??  0,2, ',', '.') }}</th>
      </tfoot>
    </table>
  </div>
</div>
</body>
@foreach(config('layout.resources.js') as $script)
  <script src="{{ asset($script) }}" type="text/javascript"></script>
  <script>
    window.onload = function(e){
      window.print();
    }
    window.setTimeout(function(){
      window.close();
    }, 2000);
  </script>
@endforeach

</html>
