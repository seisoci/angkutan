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
      <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Pembelian Barang Diluar</u></h2>
      <table class="table table-borderless table-title">
        <tbody>
          <tr>
            <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
            </td>
            <td class="text-left" style="width:10%"></td>
            <td colspan="2" class="text-left" style="width:15%">Tanggal</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:23%"> {{ $data->created_at }}</td>
          </tr>
          <tr>
            <td style="width:50%">{{ $profile['address'] ?? '' }}</td>
            <td class="text-left" style="width:10%"></td>
            <td colspan="2" class="text-left" style="width:15%">No. Referensi</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:23%"> {{ $data->num_invoice }}</td>
          </tr>
          <tr>
            <td>{{ $profile['phone'] ?? ''}}</td>
            <td class="text-left" style="width:10%"></td>
            <td colspan="2" class="text-left" style="width:15%">Nama Supir</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:23%"> {{ $data->driver->name }}</td>
          </tr>
          <tr>
            <td>Fax: {{ $profile['fax'] ?? ''}}</td>
            <td class="text-left" style="width:10%"></td>
            <td colspan="2" class="text-left" style="width:15%">No. Polisi</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:23%"> {{ $data->transport->num_pol }}</td>
          </tr>
          <tr>
            <td></td>
            <td class="text-left" style="width:10%"></td>
            <td colspan="2" class="text-left" style="width:15%">Memo</td>
            <td class="text-left" style="width:2%">: &ensp;</td>
            <td class="text-left" style="width:23%"> {{ $data->memo }}</td>
          </tr>
        </tbody>
      </table>
      <div class="separator separator-solid separator-border-1"></div>
      <table class="table" style="font-size: 11px !important">
        <thead>
          <tr>
            <th style="width:5%">#</th>
            <th style="width:45%">Produk</th>
            <th style="width:25%">Keterangan</th>
            <th style="width:5%">Jumlah</th>
            <th style="width:25%" class="text-right">Harga</th>
            <th style="width:25%" class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data->usageitem as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->qty }}</td>
            <td class="text-right">{{ number_format($item->price ?? 0, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->total_price ?? 0, 2, ',', '.') }}</td>
          </tr>
          @endforeach
          <tr class="font-weight-normal">
            <td colspan="3" class="text-left font-weight-bolder">
              {{ ucwords(Terbilang::terbilang($data->total_payment)) }}
            </td>
            <td class="text-right font-weight-bolder text-uppercase">Total Tagihan</td>
            <td class="text-right font-weight-bolder">
              {{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
            <td></td>
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
