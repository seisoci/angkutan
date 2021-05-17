{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!-- begin::Card-->
<div class="card card-custom overflow-hidden">
  {{-- Header --}}
  <div class="card-header d-flex justify-content-end align-items-center">
    <div class="">
      <div class="btn-group btn-group-md" role="group" aria-label="Large button group">
        <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
            class="fa fa-arrow-left"></i> Back</button>
        <a href="{{ $config['print_url'] }}" target="_blank" class="btn btn-outline-secondary"><i
            class="fa fa-print"></i> Print</a>
      </div>
    </div>
  </div>
  {{-- Body --}}
  <div class="card-body p-0">
    <!-- begin: Invoice header-->
    <div class="row justify-content-center py-8 px-8 px-md-0">
      <div class="col-md-11">
        <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Invoice Payment LDO</u></h2>
        <table class="table table-borderless table-title">
          <tbody>
            <tr>
              <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
              </td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" colspan="2" class="text-left" style="width:15%">Tanggal</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:23%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" colspan="2" class="text-left" style="width:15%">No. Referensi</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:23%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td scope="col">{{ $profile['telp'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" colspan="2" class="text-left" style="width:15%">LDO</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:23%"> {{ $data->anotherexpedition->name }}</td>
            </tr>
            <tr>
              <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
            </tr>
          </tbody>
        </table>
        <div class="separator separator-solid separator-border-1"></div>
        <table class="table" style="font-size: 11px !important">
          <thead>
            <tr>
              <th scope="col" class="text-center">#</th>
              <th scope="col">Tanggal</th>
              <th scope="col">S. Jalan</th>
              <th scope="col">LDO</th>
              <th scope="col">Pelanggan</th>
              <th scope="col">Rute Dari</th>
              <th scope="col">Rute Ke</th>
              <th scope="col">Jenis Barang</th>
              <th scope="col">Tarif LDO (Rp.)</th>
              <th scope="col">Qty (Unit)</th>
              <th scope="col">Total Harga Dasar</th>
              <th scope="col">Total Operasional</th>
              <th scope="col">Tagihan Bersih</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data->joborders as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->date_begin }}</td>
              <td>{{ $item->num_prefix }}</td>
              <td>{{ $item->anotherexpedition->name }}</td>
              <td>{{ $item->costumer->name }}</td>
              <td>{{ $item->routefrom->name }}</td>
              <td>{{ $item->routeto->name }}</td>
              <td>{{ $item->cargo->name }}</td>
              <td>{{ number_format($item->basic_price_ldo ?? 0, 2, '.', '.') }}</td>
              <td>{{ $item->payload }}</td>
              <td>{{ number_format($item->total_basic_price_ldo ?? 0, 2, '.', '.') }}</td>
              <td>{{ number_format($item->total_operational ?? 0, 2, '.', '.') }}</td>
              <td>{{ number_format($item->total_netto_ldo ?? 0, 2, '.', '.')}}</td>
            </tr>
            @endforeach
            <tr>
              <td colspan="11" class="text-left font-weight-bolder">
                {{ ucwords(Terbilang::terbilang($data->total_bill)) }}
              </td>
              <td class="text-right font-weight-bolder text-uppercase">TOTAL DITERIMA:</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->total_bill ?? 0, 2, '.', '.') }}</td>
            </tr>
          </tbody>
        </table>
        <h4><u>Pembayaran</u></h4>
        <table class="table">
          <thead>
          <tr>
            <th scope="col" width="20%">Tanggal Pembayaran</th>
            <th scope="col" width="30%">Keterangan</th>
            <th scope="col" width="25%" class="text-right">Nominal</th>
            <th scope="col" width="25%" class="text-right">Total Dibayar</th>
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
      </div>
    </div>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<style>
  .table-title td,
  th {
    padding: 0;
  }
</style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}

{{-- page scripts --}}
@endsection
