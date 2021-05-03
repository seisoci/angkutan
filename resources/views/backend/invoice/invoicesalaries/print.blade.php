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
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
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

      h3 {
        color: #000 !important;
      }

      @page {
        size: A4 landscape;
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
            <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
            </td>
            <td scope="col" class="text-left" style="width:10%"></td>
            <td scope="col" class="text-left pl-20" style="width:20%">Tanggal</td>
            <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
            <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at }}</td>
          </tr>
          <tr>
            <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
            <td scope="col" class="text-left" style="width:10%"></td>
            <td scope="col" class="text-left pl-20" style="width:20%">No. Referensi</td>
            <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
            <td scope="col" class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
          </tr>
          <tr>
            <td scope="col">{{ $profile['telp'] ?? ''}}</td>
            <td scope="col" class="text-left" style="width:10%"></td>
            <td scope="col" class="text-left pl-20" style="width:20%">Nama Supir</td>
            <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
            <td scope="col" class="text-left" style="width:18%"> {{ $data->driver->name }}</td>
          </tr>
          <tr>
            <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
            <td scope="col" class="text-left" style="width:10%"></td>
            <td scope="col" class="text-left pl-20" style="width:20%">No. Polisi</td>
            <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
            <td scope="col" class="text-left" style="width:18%"> {{ $data->transport->num_pol }}</td>
          </tr>
        </tbody>
      </table>
      <div class="separator separator-solid separator-border-1"></div>
      <h3 class="my-4"><u>Gaji</u></h3>
      <div class="separator separator-solid separator-border-1"></div>
      <table class="table">
        <thead>
          <tr>
            <th scope="col" style="width:5%">#</th>
            <th scope="col" style="width:15%">No. Job Order</th>
            <th scope="col" style="width:5%">KETERANGAN</th>
            <th scope="col" style="width:30%">PELANGGAN</th>
            <th scope="col" style="width:30%">RUTE</th>
            <th scope="col" class="text-right" style="width:15%">JUMLAH</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data->joborders as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->num_prefix }}</td>
            <td>Gaji</td>
            <td>{{ $item->costumer->name }}</td>
            <td>{{ $item->routefrom->name }} -> {{ $item->routeto->name }}</td>
            <td class="text-right">{{ number_format($item->total_salary ?? 0, 2, ',', '.') }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5" class="text-right font-weight-bolder text-uppercase">TOTAL GAJI:</td>
            <td class="text-right font-weight-bolder">
              {{ number_format($data->joborders->sum('total_salary') ?? 0, 2, '.', '.') }}
            </td>
          </tr>
        </tbody>
      </table>
      <h3 class="my-4"><u>Kasbon</u></h3>
      <div class="separator separator-solid separator-border-1"></div>
      <table class="table">
        <thead>
          <tr>
            <th scope="col" style="width:5%">#</th>
            <th scope="col" style="width:15%">Tanggal</th>
            <th scope="col" style="width:5%"></th>
            <th scope="col" style="width:30%"></th>
            <th scope="col" style="width:30%"></th>
            <th scope="col" class="text-right" style="width:15%">JUMLAH</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data->kasbon as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td colspan="4">{{ $item->created_at }}</td>
            <td class="text-right">{{ number_format($item->amount ?? 0, 2, ',', '.') }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5" class="text-right font-weight-bolder text-uppercase">TOTAL KASBON:</td>
            <td class="text-right font-weight-bolder">
              {{ number_format($data->kasbon->sum('amount') ?? 0, 2, '.', '.') }}</td>
          </tr>
          <tr>
            <td colspan="4" class="text-left font-weight-bolder">
              {{ ucwords(Terbilang::terbilang($data->grandtotal)) }}
            </td>
            <td class="text-right font-weight-bolder text-uppercase">TOTAL DITERIMA:</td>
            <td class="text-right font-weight-bolder">
              {{ number_format($data->grandtotal?? 0, 2, '.', '.') }}</td>
          </tr>
        </tbody>
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
