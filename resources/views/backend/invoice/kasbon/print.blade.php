<!DOCTYPE html>
<html>

<head>
  <style type="text/css">
    @media print {
      table {
        border-collapse: collapse;
      }

      .tableData, .tableData th, .tableData td {
        border: 1px solid black;
        padding: 2px;
      }


    }
  </style>
</head>
<body>
<table>
  <tbody>
  <tr>
    <td colspan="3" style="width:50%; text-align: center; font-weight: bold">{{ $cooperationDefault['nickname'] ?? '' }}
    </td>
  </tr>
  <tr>
    <td style="width:33%;">{{ $cooperationDefault['address'] ?? '' }}</td>
    <td style="width:33%"></td>
    <td style="width:33%">Supir : {{ $data['driver']['name'] ?? '' }}</td>
  </tr>
  <tr>
    <td style="width:33%;">Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
    <td style="width:33%"></td>
    <td style="width:33%">Tanggal: {{ \Carbon\Carbon::now()->format('d M Y') }}</td>
  </tr>
  </tbody>
</table>
<table class="tableData" style="margin-top: 30px; width: 100%; border: 1px solid black">
  <thead>
  <tr>
    <th colspan="4" style="text-align: center">Kasbon</th>
  </tr>
  <tr>
    <th style="text-align: center">Keterangan</th>
    <th style="text-align: center">Tgl</th>
    <th style="text-align: center">Jenis</th>
    <th style="text-align: center">Nominal</th>
  </tr>
  </thead>
  <tbody>
  @foreach($data ?? array() as $item)
    <tr>
      <td style="text-align: left;">{{ $item['description'] ?? '' }}</td>
      <td
        style="text-align: left;">{{ $item['date_payment'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $item['date_payment'])->format('d M Y') : '' }}</td>
      <td style="text-align: left;">{{ ucwords($item['type']) ?? '' }}</td>
      <td style="text-align: right; width: 125px">{{ number_format($item['payment'], 0, '.', ',') }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
<table style="width: 100%; margin-top: 50px">
  <thead>
  <tr>
    <th style="text-align: center">Mengetahui</th>
    <th style="width: 100px"></th>
    <th style="text-align: center">Mengetahui</th>
  </tr>
  </thead>
  <tbody>
  <tr style="height: 120px">
    <td style="text-align: center">{{ auth()->user()->name }}</td>
    <td></td>
    <td style="text-align: center">{{ $driverName ?? '' }}</td>
  </tr>
  </tbody>
</table>
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
