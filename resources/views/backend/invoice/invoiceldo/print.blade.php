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
    <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Invoice Payment LDO</u></h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
        </td>
        <td class="text-left" style="width:10%"></td>
        <td colspan="2" class="text-left" style="width:15%">Tanggal</td>
        <td class="text-left" style="width:2%">: &ensp;</td>
        <td class="text-left" style="width:23%"> {{ $data->created_at }}</td>
      </tr>
      <tr>
        <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
        <td class="text-left" style="width:10%"></td>
        <td colspan="2" class="text-left" style="width:15%">No. Referensi</td>
        <td class="text-left" style="width:2%">: &ensp;</td>
        <td class="text-left" style="width:23%"> {{ $data->num_invoice }}</td>
      </tr>
      <tr>
        <td>Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
        <td class="text-left" style="width:10%"></td>
        <td colspan="2" class="text-left" style="width:15%">LDO</td>
        <td class="text-left" style="width:2%">: &ensp;</td>
        <td class="text-left" style="width:23%"> {{ $data->anotherexpedition->name }}</td>
      </tr>
      <tr>
        <td>Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    <table class="table" style="font-size: 10px !important">
      <thead>
      <tr>
        <th class="text-center">#</th>
        <th>Tanggal</th>
        <th>S. Jalan</th>
        <th>No. Pol</th>
        <th>LDO</th>
        <th>Pelanggan</th>
        <th>Rute Dari</th>
        <th>Tujuan</th>
        <th>Jenis Barang</th>
        <th>Tarif LDO (Rp.)</th>
        <th>Qty (Unit)</th>
        <th>Total Harga Dasar</th>
        <th>Total Operasional</th>
        <th>Tagihan Bersih</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($data->joborders as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->no_sj }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td>{{ $item->anotherexpedition->name }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->routefrom->name }}</td>
          <td>{{ $item->routeto->name }}</td>
          <td>{{ $item->cargo->name }}</td>
          <td>{{ number_format($item->basic_price_ldo ?? 0, 2, '.', ',') }}</td>
          <td>{{ $item->payload }}</td>
          <td>{{ number_format($item->total_basic_price_ldo ?? 0, 2, '.', ',') }}</td>
          <td>{{ number_format($item->roadmoneydetail_sum_amount ?? 0, 2, '.', ',') }}</td>
          <td>{{ number_format($item->total_netto_ldo ?? 0, 2, '.', ',')}}</td>
        </tr>
        @foreach($item->piutangklaimldo as $piutangklaim)
          <tr>
            <td></td>
            <td colspan="12">{{ $piutangklaim->description }}</td>
            <td style="text-align: right">
              @if($piutangklaim->type == "tambah")
                {{ number_format($piutangklaim->amount , 2, ',','.') }}
              @else
                -{{ number_format($piutangklaim->amount , 2, ',','.') }}
              @endif
            </td>
          </tr>
        @endforeach
      @endforeach
      <tr>
          <td colspan="10" class="text-left font-weight-bolder">
            {{ ucwords(Terbilang::terbilang($data->total_bill)) }}
          </td>
          <td class="text-right font-weight-bolder text-uppercase" colspan="3">TOTAL DITERIMA:</td>
          <td class="text-right font-weight-bolder">{{ number_format($data->total_bill ?? 0, 2, '.', ',') }}</td>
        </tr>
      </tbody>
    </table>
    <h4 class="text-dark"><u>Pembayaran</u></h4>
    <table class="table w-100">
      <thead>
      <tr>
        <th style="width:20%;">Tanggal Pembayaran</th>
        <th style="width:30%;">Keterangan</th>
        <th style="width:20%;" class="text-right">Nominal</th>
        <th style="width:30%;" class="text-right">Total Dibayar</th>
      </tr>
      </thead>
      <tbody>
      @foreach($data->paymentldos as $item)
        <tr>
          <td>{{ $item->date_payment }}</td>
          <td>{{ $item->description }}</td>
          <td class="text-right">{{ number_format($item->payment ?? 0,2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($item->payment ?? 0,2, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="3" class="text-right font-weight-bolder">Total Pembayaran</td>
        <td class="text-right font-weight-bolder">{{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bolder">Total Piutang</td>
        <td class="text-right font-weight-bolder">{{ number_format($data->total_piutang ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bolder">Total Pemotongan</td>
        <td class="text-right font-weight-bolder">{{ number_format($data->total_cut ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bolder">Total Tagihan</td>
        <td class="text-right font-weight-bolder">{{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-weight-bolder">Sisa Pembayaran</td>
        <td class="text-right font-weight-bolder">{{ number_format($data->rest_payment ?? 0,2, ',', '.') }}</td>
      </tr>
      </tbody>
    </table>
    <div class="d-flex justify-content-around mt-20">
      <div class="mr-20">
        <h4 class="font-weight-bolder text-dark pb-30 text-center">Mengetahui</h4>
        <h5 class="font-weight-bolder text-dark text-center text-uppercase"><u>{{  auth()->user()->name }}</u></h5>
      </div>
      <div class="ml-20">
        <h4 class="font-weight-bolder text-dark pb-30 text-center">Mengetahui</h4>
        <h5 class="font-weight-bolder text-dark text-center text-uppercase"><u>{{  $data->anotherexpedition->name }}</u>
        </h5>
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
