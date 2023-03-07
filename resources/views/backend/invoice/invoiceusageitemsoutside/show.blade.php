@extends('layout.default')

@section('content')
  <div class="card card-custom overflow-hidden">
    <div class="card-header d-flex justify-content-end align-items-center">
      <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
        <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
            class="fa fa-arrow-left"></i> Back
        </button>
        <div class="btn-group" role="group">
          <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary font-weight-bold dropdown-toggle"
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Cetak
          </button>
          <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
            <a id="btnPrint" href="#" class="dropdown-item">Print DotMatrix</a>
            <a target="_blank" href="{{ $config['print_url'] }}" class="dropdown-item">Print Biasa</a>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Pembelian Barang Diluar</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td class="font-weight-bolder text-uppercase"
                  style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
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
              <td colspan="2" class="text-left" style="width:15%">Nama Supir</td>
              <td class="text-left" style="width:2%">: &ensp;</td>
              <td class="text-left" style="width:23%"> {{ $data->driver->name }}</td>
            </tr>
            <tr>
              <td>Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
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
        </div>
      </div>
    </div>
  </div>
@endsection

@section('styles')
  <style>
    .table-title td,
    th {
      padding: 0;
    }
  </style>
@endsection

@section('scripts')
  <script>
    $(document).ready(function () {
      $('#btnPrint').on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: "{{ $config['print_dotmatrix_url'] }}",
          success: function (text) {
            console.log(text);
            $.post('http://localhost/dotmatrix/', JSON.stringify({
              printer: 'DotMatrix',
              data: text,
              autocut: true
            }), function (response) {
              console.log(response);
            });
          }
        });
      });
    });
  </script>
@endsection
