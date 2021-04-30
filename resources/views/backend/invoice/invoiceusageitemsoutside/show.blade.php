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
        <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Pembelian Barang Diluar</u></h2>
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
              <td scope="col" colspan="2" class="text-left" style="width:15%">Nama Supir</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:23%"> {{ $data->driver->name }}</td>
            </tr>
            <tr>
              <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" colspan="2" class="text-left" style="width:15%">No. Polisi</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:23%"> {{ $data->transport->num_pol }}</td>
            </tr>
          </tbody>
        </table>
        <div class="separator separator-solid separator-border-1"></div>
        <table class="table" style="font-size: 11px !important">
          <thead>
            <tr>
              <th scope="col" style="width:5%">#</th>
              <th scope="col" style="width:45%">Produk</th>
              <th scope="col" style="width:5%">Jumlah</th>
              <th scope="col" style="width:25%" class="text-right">Harga</th>
              <th scope="col" style="width:25%" class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data->usageitem as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->name }}</td>
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
}
@endsection
