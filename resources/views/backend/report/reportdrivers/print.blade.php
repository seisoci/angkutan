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
        size: A4 portrait;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>LAPORAN Data Supir</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td class="font-weight-normal" style="width:50%">Printed: {{ $config['current_time'] }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:20%">{{ $cooperationDefault['nickname'] ?? '' }}</td>
      </tr>
      <tr>
        <td style="width:50%"></td>
        <td class="text-left" style="width:10%"></td>
        <td class="text-left" style="width:18%">{{ $cooperationDefault['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td></td>
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
        <th>#</th>
        <th>Nama Pelanggan</th>
        <th>Alamat</th>
        <th>No. Telp</th>
        <th>Expired SIM</th>
        <th>Status</th>
        <th>Foto</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->name }}</td>
          <td>{{ $item->address }}</td>
          <td>{{ $item->phone }}</td>
          <td>{{ $item->expired_sim }}</td>
          <td>{{ ucwords($item->status) }}</td>
          <td>
            <img
              height="100px"
              width="80px"
              src="{{ !empty($item->photo) ? asset("storage/images/thumbnail/$item->photo") : asset('media/users/blank.png') }}" alt="Profile">
          </td>
        </tr>
      @endforeach
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
