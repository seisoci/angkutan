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

      @page {
        size: A4 potrait;
      }
    }
  </style>
</head>

<body>
  <div class="row justify-content-center py-8 px-8 px-md-0">
    <div class="col-md-11">
      <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Detail Opname</u></h2>
      <table class="table table-borderless table-title">
        <tbody>
          <tr>
            <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
            </td>
            <td scope="col" class="text-left" style="width:10%"></td>
            <td scope="col" class="text-right" style="width:20%">Tanggal</td>
            <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
            <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at }}</td>
          </tr>
          <tr>
            <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
          </tr>
          <tr>
            <td scope="col">{{ $profile['telp'] ?? ''}}</td>
          </tr>
          <tr>
            <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
          </tr>
          <tr>
            <td scope="col" colspan="5">Deskripsi : {{ $data->description ?? ''}}</td>
          </tr>
        </tbody>
      </table>
      <div class="separator separator-solid separator-border-1"></div>
      <table class="table" style="font-size: 11px !important">
        <thead>
          <tr>
            <th scope="col" style="width:5%">#</th>
            <th scope="col" style="width:65%">Produk</th>
            <th scope="col" class="text-center" style="width:10%">Stok Sistem</th>
            <th scope="col" class="text-center" style="width:10%">Stok Fisik</th>
            <th scope="col" class="text-center" style="width:10%">Selisih</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data->opnamedetail as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->sparepart->name }}</td>
            <td class="text-center">{{ $item->qty_system }}</td>
            <td class="text-center">{{ $item->qty }}</td>
            <td class="text-center">{{ $item->qty_difference }}</td>
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
  window.onload = function(e){
    window.print();
  }
  window.setTimeout(function(){
    window.close();
  }, 2000);
</script>
@endforeach

</html>
