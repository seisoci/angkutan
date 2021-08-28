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
        <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Detail Opname</u></h2>
        <table class="table table-borderless table-title">
          <tbody>
            <tr>
              <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
              </td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-right" style="width:20%">Tanggal</td>
              <td class="text-left" style="width:2%">: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
            </tr>
            <tr>
              <td>Telp: {{ $cooperationDefault['phone'] ?? ''}}</td>
            </tr>
            <tr>
              <td>Fax: {{ $cooperationDefault['fax'] ?? ''}}</td>
            </tr>
            <tr>
              <td colspan="5">Deskripsi : {{ $data->description ?? ''}}</td>
            </tr>
          </tbody>
        </table>
        <div class="separator separator-solid separator-border-1"></div>
        <table class="table" style="font-size: 11px !important">
          <thead>
            <tr>
              <th style="width:5%">#</th>
              <th style="width:65%">Produk</th>
              <th class="text-center" style="width:10%">Stok Sistem</th>
              <th class="text-center" style="width:10%">Stok Fisik</th>
              <th class="text-center" style="width:10%">Selisih</th>
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
