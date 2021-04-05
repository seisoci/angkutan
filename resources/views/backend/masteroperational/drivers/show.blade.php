{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="card card-custom">
  <div class="card-header">
    <h3 class="card-title">
      {{ $config['page_title'] }}
    </h3>
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
            <div class="image-input-wrapper"
              style="background-image: url({{ $data->photo != NULL ? asset("/images/original/".$data->photo) : asset('media/users/blank.png') }})">
            </div>
          </div>
          <span class="form-text text-muted">Maximum file 2 MB and format png, jpg, jpeg</span>
        </div>
        <div class="form-group">
          <label>Nama <span class="text-danger">*</span></label>
          <h5>{{ $data->name }}</h5>
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
    <div class="card-footer d-flex justify-content-end">
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

  {{-- page scripts --}}
  @endsection
