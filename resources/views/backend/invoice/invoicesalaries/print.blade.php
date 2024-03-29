<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
  <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
    type="text/css" />
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
      <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>SLIP GAJI</u></h2>
      <table class="table table-borderless table-title">
        <tbody>
          <tr>
            <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
            </td>
            <td class="text-left" style="width:10%"></td>
            <td class="text-left pl-20" style="width:20%">Tanggal</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:18%"> {{ $data->invoice_date }}</td>
          </tr>
          <tr>
            <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
            <td class="text-left" style="width:10%"></td>
            <td class="text-left pl-20" style="width:20%">No. Referensi</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
          </tr>
          <tr>
            <td>{{ $cooperationDefault['phone'] ?? ''}}</td>
            <td class="text-left" style="width:10%"></td>
            <td class="text-left pl-20" style="width:20%">Nama Supir</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:18%"> {{ $data->driver->name }}</td>
          </tr>
          <tr>
            <td>FAX {{ $cooperationDefault['fax'] ?? ''}}</td>
            <td class="text-left" style="width:10%"></td>
          </tr>
        </tbody>
      </table>
      <div class="separator separator-solid separator-border-1"></div>
      <table class="table">
        <thead>
          <tr>
            <th style="width:5%">No</th>
            <th style="width:100px">Tgl. Muat</th>
            <th style="width:150px">No. JO</th>
            <th style="width:100px">No. Pol</th>
            <th style="width:200px">Pelanggan</th>
            <th style="width:200px">Rute</th>
            <th class="text-right" style="width:100px">Nominal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data->joborders as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->date_begin }}</td>
            <td>{{ $item->num_bill }}</td>
            <td>{{ $item->transport->num_pol }}</td>
            <td>{{ $item->costumer->name }}</td>
            <td>{{ $item->routefrom->name }} -> {{ $item->routeto->name }}</td>
            <td class="text-right">{{ number_format($item->total_salary ?? 0, 2, ',', '.') }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5" class="text-left font-weight-bolder">
              {{ ucwords(Terbilang::terbilang($data->grandtotal)) }}
            </td>
            <td class="text-right font-weight-bolder text-uppercase">TOTAL DITERIMA:</td>
            <td class="text-right font-weight-bolder">{{ number_format($data->grandtotal ?? 0, 2, '.', ',') }}</td>
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
          <h5 class="font-weight-bolder text-dark text-center text-uppercase"><u>{{  $data->driver->name }}</u></h5>
        </div>
      </div>
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
