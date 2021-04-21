{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="card card-custom">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title'] }}
    </div>
    <div class="card-toolbar">
      <!--begin::Button-->
      <button onclick="window.print();" class="d-print-none btn btn-primary font-weight-bolder">
        <span class="svg-icon svg-icon-md">
          <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
            viewBox="0 0 24 24" version="1.1">
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <rect x="0" y="0" width="24" height="24" />
              <path
                d="M16,17 L16,21 C16,21.5522847 15.5522847,22 15,22 L9,22 C8.44771525,22 8,21.5522847 8,21 L8,17 L5,17 C3.8954305,17 3,16.1045695 3,15 L3,8 C3,6.8954305 3.8954305,6 5,6 L19,6 C20.1045695,6 21,6.8954305 21,8 L21,15 C21,16.1045695 20.1045695,17 19,17 L16,17 Z M17.5,11 C18.3284271,11 19,10.3284271 19,9.5 C19,8.67157288 18.3284271,8 17.5,8 C16.6715729,8 16,8.67157288 16,9.5 C16,10.3284271 16.6715729,11 17.5,11 Z M10,14 L10,20 L14,20 L14,14 L10,14 Z"
                fill="#000000" />
              <rect fill="#000000" opacity="0.3" x="8" y="2" width="8" height="2" rx="1" />
            </g>
          </svg>
          <!--end::Svg Icon-->
        </span>Print</button>
      <!--end::Button-->
    </div>
  </div>
  <!--begin::Form-->
  <div class="card-body">
    <div class="form-group" style="display:none;">
      <div class="alert alert-custom alert-light-danger" role="alert">
        <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
        <div class="alert-text">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <div class="image-input" id="kt_image_2">
            <img class="image-input-wrapper"
              src="{{ $data->photo != NULL ? asset("/images/original/".$data->photo) : asset('media/users/blank.png') }}"
              alt="">
          </div>
        </div>
        <div class="form-group">
          <label>Nama <span class="text-danger">*</span></label>
          <h5>{{ $data->name }}</h5>
        </div>
        <div class="form-group">
          <label>Nama Bank (Untuk Gaji)</label>
          <h5>{{ $data->bank_name }}</h5>
        </div>
        <div class="form-group">
          <label>No. Rekening</label>
          <h5>{{ $data->no_card }}</h5>
        </div>
        <div class="form-group">
          <label>Telp</label>
          <h5>{{ $data->phone }}</h5>
        </div>
        <div class="form-group">
          <label>No. KTP</label>
          <h5>{{ $data->ktp }}</h5>
        </div>
        <div class="form-group">
          <label>No. SIM</label>
          <h5>{{ $data->sim }}</h5>
        </div>
        <div class="form-group">
          <label>Masa Berlaku SIM</label>
          <h5>{{ $data->expired_sim }}</h5>
        </div>
        <div class="form-group">
          <label class="form-text" for="activeSelect">Status</label>
          <span
            class="label label-lg font-weight-bold {{ $data->status == 'active' ? 'label-light-success' : 'label-light-danger' }} label-inline">{{ $data->status }}
          </span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label class="mx-0 text-bold">Image KTP</label>
          <img id="avatar"
            src="{{ $data->photo_ktp != NULL ? asset("/images/original/".$data->photo_ktp) : asset('media/bg/no-content.svg') }}"
            style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="250px"
            width="100%">

        </div>
        <div class="form-group">
          <label class="mx-0 text-bold">Image SIM</label>
          <img id="avatar"
            src="{{ $data->photo_sim != NULL ? asset("/images/original/".$data->photo_sim) : asset('media/bg/no-content.svg') }}"
            style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="250px"
            width="100%">

        </div>
      </div>
    </div>
    <div class="form-group">
      <label>Keterangan</label>
      <h5>{{ $data->description }}</h5>
    </div>
    <div class="card-footer d-flex justify-content-end d-print-none">
      <button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">Cancel</button>
    </div>
    <!--end::Form-->
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
