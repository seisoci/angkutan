{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!-- begin::Card-->
<div class="card card-custom overflow-hidden">
  <div class="card-body p-0">
    <!-- begin: Invoice-->
    <!-- begin: Invoice header-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
      <div class="col-md-9">
        <div class="d-flex justify-content-between pb-10 pb-md-20 flex-column flex-md-row">
          <div class="d-flex justify-content-between pt-6">
            <div class="d-flex flex-column flex-root">
              <h1 class="display-4 font-weight-boldest mb-10">JOB ORDER</h1>
              <span class="font-weight-bolder mb-2">{{ $profile['name'] ?? '' }}</span>
              <span class="opacity-70">{{ $profile['address'] ?? '' }}
                <br />{{ $profile['telp'] ?? '' }} <br />{{ $profile['fax'] ?? '' }}</span>
            </div>
          </div>
          <div class="d-flex flex-column align-items-md-end px-0">
            <!--begin::Logo-->
            <a href="#" class="mb-5">
              <img
                src="{{ $profile['logo_url'] != NULL ? asset("/images/thumbnail/".$profile['logo_url']) : asset('media/bg/no-content.svg') }}"
                width="75px" height="75px" />
            </a>
            <!--end::Logo-->
          </div>
        </div>
        <div class="border-bottom w-100"></div>
        <div class="d-flex justify-content-between pt-6">
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">SUPIR</span>
            <span class="opacity-70">{{ $data->driver->name ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">NO. POL</span>
            <span class="opacity-70">{{ $data->transport->num_pol ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">NO. JOB ORDER</span>
            <span class="opacity-70">{{ $data->prefix. '-'.$data->num_bill }}</span>
          </div>
        </div>
        <div class="d-flex justify-content-between pt-6">
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">TANGGAL MULAI</span>
            <span class="opacity-70">{{ $data->date_begin ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">TANGGAL SELESAI</span>
            <span class="opacity-70">{{ $data->date_end ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">{{ $data->type == 'ldo' ? 'Pemilik' : NULL }}</span>
            <span class="opacity-70">{{ $data->anotherexpedition->name }}</span>
          </div>
        </div>
      </div>
    </div>
    <!-- end: Invoice header-->
    <!-- begin: Invoice body-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
      <div class="col-md-9">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">Pelanggan</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Rute Dari</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Rute Ke</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Jenis Barang</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Tipe Barang</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Unit (Qty)</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Uang Jalan</th>
              </tr>
            </thead>
            <tbody>
              <tr class="font-weight-boldest">
                <td class="pl-0 pt-7">{{ $data->costumer->name ?? '' }}</td>
                <td class="text-right pt-7">{{ $data->routefrom->name ?? ''}}</td>
                <td class="text-right pt-7">{{ $data->routeto->name ?? '' }}</td>
                <td class="pr-0 pt-7 text-right">{{ $data->cargo->name ?? '' }}
                <td class="pr-0 pt-7 text-right">{{ $data->type_capacity ?? '' }}</td>
                <td class="pr-0 pt-7 text-right">{{ $data->payload ?? '' }}</td>
                <td class="pr-0 pt-7 text-right">{{ number_format($data->road_money ?? 0,0, '.', '.') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- end: Invoice body-->
    <!-- begin: Invoice action-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0 d-print-none">
      <div class="col-md-9">
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-primary font-weight-bold" onclick="window.print();">Print
            Job Order</button>
        </div>
      </div>
    </div>
    <!-- end: Invoice action-->
    <!-- end: Invoice-->
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script>
  $(document).ready(function(){
    $('body').addClass('print-content-only');
  });
</script>
{{-- page scripts --}}
@endsection
