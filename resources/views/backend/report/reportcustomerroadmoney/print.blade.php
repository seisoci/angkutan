<!DOCTYPE html>
<html>

<head>
  <style type="text/css">
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
    <h2 style="text-align: center"><u>{{ $config['page_title'] }}</u>
    </h2>
    <table style="margin-bottom: 10px">
      <tbody>
      <tr>
        <td class="font-weight-normal">Printed: {{ $config['current_time'] }}
        </td>
      </tr>
      </tbody>
    </table>
    @foreach ($data as $item)
      <table style="padding-top: 0 !important; padding-bottom: 0 !important; margin-bottom: 0 !important; border-left: 1px solid #000 !important;
             border-right: 1px solid #000 !important;border-top: 1px solid #000 !important;font-family: monospace; font-size:12px;">
        <tbody>
        <tr>
          <td style="width:15%; font-weight: bold;">Nama Pelanggan
          </td>
          <td style="width: 1%">:</td>
          <td style="width:35%">{{ $item['name'] }}</td>
          <td style="width:6%"></td>
          <td style="width:20%; font-weight: bold;">Kerjasama</td>
          <td style="width:1%">:</td>
          <td
            style="width:35%; text-align: right !important;">{{ $item['cooperation'] }}</td>
        </tr>
        <tr>
          <td class="font-weight-normal" style="width:15%; font-weight: bold;">No. Telp</td>
          <td style="width: 1%">:</td>
          <td style="width:35%">{{ $item['phone'] }}</td>
          <td style="width:6%"></td>
          <td style="width:20%; font-weight: bold;">Nama Darurat</td>
          <td style="width:1%">:</td>
          <td
            style="width:35%; text-align: right !important;">{{ $item['emergency_name'] ?? '' }}</td>
        </tr>
        <tr>
          <td class="font-weight-normal" style="width:15%; font-weight: bold;">Alamat</td>
          <td style="width: 1%">:</td>
          <td style="width:35%">{{ $item['address'] ?? '' }}</td>
          <td style="width:6%"></td>
          <td style="width:20%; font-weight: bold;">No. Telp Darurat</td>
          <td style="width:1%">:</td>
          <td
            style="width:35%; text-align: right !important;">{{ $item['emergency_phone'] ?? '' }}</td>
        </tr>
        </tbody>
      </table>
      <table class="table"
             style="padding-top: 0 !important; padding-bottom: 0 !important; border-left: 1px solid #000 !important;
             border-right: 1px solid #000 !important;border-bottom: 1px solid #000 !important; margin-bottom: 10px;font-family: monospace; font-size:12px;">
        <thead>
        <tr>
          <th style="text-align: center">#</th>
          <th style="text-align: left">Rute Dari</th>
          <th style="text-align: left">Rute Ke</th>
          <th style="text-align: left">Muatan</th>
          <th style="text-align: right">Tax PPH</th>
          <th style="text-align: right">Fee Thanks</th>
        </tr>
        </thead>
        <tbody>
        @foreach($item['roadmoney'] as $child)
          <tr>
            <td style="text-align: center">{{ $loop->iteration }}</td>
            <td>{{ $child['routefrom' ?? '' ] }}</td>
            <td>{{ $child['routeto'] ?? '' }}</td>
            <td>{{ $child['cargo'] ?? '' }}</td>
            <td style="text-align: right">{{ ($child['tax_pph'] ?? 0) }}%</td>
            <td style="text-align: right">{{ number_format(($child['fee_thanks'] ?? 0), 2, '.', ',') }}</td>
          </tr>
        @foreach($child['typecapacities'] as $typeCapacities)
          @if($loop->first)
            <thead>
            <tr>
              <th style="text-align: center; width: 10px; padding-left: 20px">•</th>
              <th style="text-align: left">Uang Jalan Engkel</th>
              <th style="text-align: left">Uang Jalan Tronton</th>
              <th style="text-align: left">Ongkosan</th>
              <th style="text-align: left">Jenis Muatan</th>
              <th style="text-align: left">Tipe</th>
            </tr>
            </thead>
          @endif
          <tbody>
          <tr>
            <td style="text-align: center; width: 10px; padding-left: 20px">•</td>
            <td style="text-align: left">{{ number_format(($typeCapacities->road_engkel ?? 0), 2, '.', ',') }}</td>
            <td
              style="text-align: left">{{ number_format(($typeCapacities->road_tronton ?? 0), 2, '.', ',') }}</td>
            <td style="text-align: left">{{ number_format(($typeCapacities->expense ?? 0), 2, '.', ',') }}</td>
            <td>{{ $typeCapacities->name ?? '' }}</td>
            <td>{{ $typeCapacities->type == 'fix' ? 'Fix' : 'Kalkulasi' }}</td>
          </tr>
          </tbody>
          @endforeach
          @endforeach
          </tbody>
      </table>
    @endforeach
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
