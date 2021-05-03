<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
  <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
    type="text/css" />
  @endforeach
  <style type="text/css">
    .table-title tbody tr td {
      padding-top: 0;
      padding-bottom: 0;
      line-height: 10px;
    }

    h2 {
      font-size: 12px;
    }

    body.receipt .sheet {
      width: 58mm;
      height: 100mm
    }

    @media print {
      body.receipt {
        width: 58mm
      }

      .table-title tbody tr td {
        padding-top: 0;
        padding-bottom: 0;
        line-height: 10px;
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
        width: 58mm;
        height: 100mm;
      }

    }
  </style>
</head>

<body class="receipt">
  <section class="sheet padding-10mm">
    <div class=" row justify-content-center py-8 px-8 px-md-0">
      <div class="col-md-11">
        <h2 class="font-weight-bold text-center mb-10 text-uppercase text-dark" style="margin-bottom: 10px !important">
          <u>Job Order</u></h2>
        <table class="table table-title">
          <tbody style="font-size:10px;">
            <tr>
              <td scope=" col" class="font-weight-bolder" style="width:40%">Pelanggan
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">{{ $data->costumer->name ?? '' }}</td>
            </tr>
            <tr>
              <td scope="col" class="font-weight-bolder" style="width:40%">Rute Dari
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">{{ $data->routefrom->name ?? '' }}</td>
            </tr>
            <tr>
              <td scope="col" class="font-weight-bolder" style="width:40%">Rute Ke
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">{{ $data->routeto->name ?? '' }}</td>
            </tr>
            <tr>
              <td scope="col" class="font-weight-bolder" style="width:40%">Jns Barang
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">{{ $data->type_capacity ?? '' }}</td>
            </tr>
            <tr>
              <td scope="col" class="font-weight-bolder" style="width:40%">Unit (QTY)
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">{{ $data->payload ?? '' }}</td>
            </tr>
            <tr>
              <td scope="col" class="font-weight-bolder" style="width:40%">Uang Jalan
              </td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:63%">
                {{ number_format($data->road_money ?? 0,0, '.', '.') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</body>
@foreach(config('layout.resources.js') as $script)
<script src="{{ asset($script) }}" type="text/javascript"></script>
<script>
  window.onload = function(e){
    window.print();
  }
  // window.setTimeout(function(){
  //   window.close();
  // }, 2000);
</script>
@endforeach

</html>
