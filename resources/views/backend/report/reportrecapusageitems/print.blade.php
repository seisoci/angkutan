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
        padding: 2em;
        color: #fff;
        background-color: #000;
        font-size: 9px;
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
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>{{ $config['page_title'] }}</u>
    </h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:10%">Printed: {{ $config['current_time'] }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:10%">{{ $profile['name'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal">Priode: {{ $date ?? 'All' }}
        </td>
        <td scope="col" class="text-left"></td>
        <td scope="col" class="text-left">{{ $profile['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal"">Nama Supir: {{ $driver }}
        <td scope="col" class="text-left"></td>
        <td scope="col" class="text-left"> {{ $profile['telp'] ?? ''}}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal">No. Polisi: {{ $transport }}
        <td scope="col" class="text-left"></td>
        <td scope="col" class="text-left">FAX {{ $profile['fax'] ?? ''}}</td>
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
        <th>Nama Supir</th>
        <th>No. Polisi</th>
        <th class="text-right">Total Pemakaian</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->num_invoice }}</td>
          <td>{{ $item->invoice_date }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td class="text-right">{{ $item->usageitem_sum_qty }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <td colspan="5" class="text-right">Total</td>
      <td class="text-right">{{ $data->sum('usageitem_sum_qty') }}</td>
      <td></td>
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
