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
        padding: 1em;
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
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Slip Gaji</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Nama</td>
        <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td scope="col" class="text-left" style="width:18%"> {{ $data->employee->name }}</td>
      </tr>
      <tr>
        <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Bulan</td>
        <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td scope="col" class="text-left" style="width:18%"> {{ $data->monthlysalary->name }}</td>
      </tr>
      <tr>
        <td scope="col">{{ $profile['telp'] ?? ''}}</td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
        <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
        <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
        <td scope="col" class="text-left" style="width:10%"></td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table">
      <thead>
      <tr>
        <th scope="col" class="text-center" width="2%">#</th>
        <th scope="col">Keterangan</th>
        <th scope="col" class="text-right">Total</th>
      </tr>
      </thead>
      <tbody>
      @foreach($data->monthlysalarydetailemployees as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{  $item->employeemaster->name }}</td>
          <td
            class="text-right currency">{{ number_format($item->amount ?? 0,2, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="2" class="text-left font-weight-bolder">
          {{ ucwords(Terbilang::terbilang($data->monthlysalarydetailemployees->sum('amount'))) }}
        </td>
        <td
          class="text-right font-weight-bolder">Total
          Nominal: {{ number_format($data->monthlysalarydetailemployees->sum('amount') ?? 0,2, ',', '.') }}</td>
      </tr>
      </tbody>
    </table>
    <div class="d-flex justify-content-around mt-20">
      <div class="mr-20">
        <h4 class="font-weight-bolder text-dark pb-30 text-center">Mengetahui</h4>
        <h5 class="font-weight-bolder text-dark text-center text-uppercase"><u>{{  auth()->user()->name }}</u></h5>
      </div>
      <div class="ml-20">
        <h4  class="font-weight-bolder text-dark pb-30 text-center">Mengetahui</h4>
        <h5 class="font-weight-bolder text-dark text-center text-uppercase"><u>{{  $data->employee->name }}</u></h5>
      </div>
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
