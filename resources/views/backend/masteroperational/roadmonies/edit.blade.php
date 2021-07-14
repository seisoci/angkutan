{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  <div class="row">
    <div class="col-12 mb-10">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">
            History Uang Jalan
          </h3>
        </div>
        <!--begin::Form-->
        <div class="card-body">
          <div class="row">
            @foreach($data->typecapacities as $item)
            <div class="col-md-6">
              <div class="d-flex align-items-center mb-9 bg-light-success rounded p-5">
                <!--begin::Icon-->
                <span class="svg-icon svg-icon-success mr-5">
                <span class="svg-icon svg-icon-lg">
                  <!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Home/Library.svg-->
                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                       width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                      <rect x="0" y="0" width="24" height="24"></rect>
                      <path
                        d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z"
                        fill="#000000"></path>
                      <rect fill="#000000" opacity="0.3"
                            transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)"
                            x="16.3255682" y="2.94551858" width="3" height="18" rx="1"></rect>
                    </g>
                  </svg>
                  <!--end::Svg Icon-->
                </span>
              </span>
                <!--end::Icon-->
                <!--begin::Title-->
                <div class="d-flex flex-column flex-grow-1 mr-2">
                  <span class="font-weight-bold text-dark-75 font-size-lg mb-1">{{ $item->name }} ({{ $item->pivot->type == 'fix' ? 'Fix Borongan' : 'Kalkulasi' }})</span>
                  <span class="text-dark-75 font-weight-bold">Harga Ongkosan: {{ number_format($item->pivot->expense, 0,'.',',') }}</span>
                  <span class="text-dark-75 font-weight-bold">Uang Jalan Engkel: {{ number_format($item->pivot->road_engkel, 0,'.',',') }}</span>
                  <span class="text-dark-75 font-weight-bold">Uang Jalan Tronton: {{ number_format($item->pivot->road_tronton, 0,'.',',') }}</span>
                </div>
                <!--end::Title-->
              </div>
            </div>
            @endforeach
          </div>

        </div>
        <!--end::Form-->
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">
            {{ $config['page_title'] }}
          </h3>
        </div>
        <!--begin::Form-->
        <form id="formUpdate" action="{{ route('backend.roadmonies.update', Request::segment(3)) }}">
          <meta name="csrf-token" content="{{ csrf_token() }}">
          @method('PUT')
          <div class="card-body">
            <div class="form-group" style="display:none;">
              <div class="alert alert-custom alert-light-danger" role="alert">
                <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
                <div class="alert-text">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Costumer <span class="text-danger">*</span></label>
              <select class="form-control" id="select2" name="costumer_id">
                <option value="{{ $data->costumer_id }}" selected>{{ $data->costumers->name }}</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Rute Dari<span class="text-danger">*</span></label>
                  <select class="form-control select2Routes" name="route_from">
                    <option value="{{ $data->route_from }}">{{ $data->routefrom->name }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Rute Ke<span class="text-danger">*</span></label>
                  <select class="form-control select2Routes" name="route_to">
                    <option value="{{ $data->route_to }}">{{ $data->routeto->name }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Muatan<span class="text-danger">*</span></label>
              <select class="form-control" id="select2Cargos" name="cargo_id">
                <option value="{{ $data->cargo_id }}">{{ $data->cargo->name }}</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Fee Pemberian<span class="text-danger">*</span></label>
                  <input type="text" class="form-control currency" name="fee_thanks"
                         value="{{ $data->fee_thanks ?? 0 }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tax PPH<span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control currency" name="tax_pph" value="{{ $data->tax_pph ?? 0 }}"/>
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
              <button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">Cancel</button>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
        <!--end::Form-->
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">
            Setting Uang Jalan
          </h3>
        </div>
        <!--begin::Form-->
        <form id="formUpdateTypeCapacity"
              action="{{ route('backend.roadmonies.updatetypecapacities', Request::segment(3)) }}">
          <meta name="csrf-token" content="{{ csrf_token() }}">
          @method('PUT')
          <div class="card-body">
            <div class="form-group">
              <label>Kapasitas<span class="text-danger">*</span></label>
              <select class="form-control" id="select2TypeCapacities" name="type_capacity_id">
              </select>
            </div>
            <div class="form-group">
              <label>Tipe Ongkosan<span class="text-danger">*</span></label>
              <select class="form-control" id="select2Type" name="type">
                <option value="fix">Fix (Borongan)</option>
                <option value="calculate">Kalkulasi (Ongkosan * Kapasistas (KG/KUBIK/TON/DLL) )</option>
              </select>
            </div>
            <div class="form-group">
              <label>Harga Ongkosan</label>
              <input type="text" name="expense" class="currency form-control" placeholder="Input Harga Ongkosan"/>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Uang Jalan Engkel</label>
                  <input type="text" name="road_engkel" class="currency form-control"
                         placeholder="Input Uang Jalan Engkel"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Uang Jalan Tronton</label>
                  <input type="text" name="road_tronton" class="currency form-control"
                         placeholder="Input Uang Jalan Tronton"/>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Update Uang Jalan</button>
            </div>
          </div>
        </form>
        <!--end::Form-->
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}

  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      new KTImageInput('kt_image_2');
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits: 0,
        rightAlign: true,
        removeMaskOnSubmit: true
      });

      $("#select2").select2({
        placeholder: "Search Costumer",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.costumers.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });

      $(".select2Routes").select2({
        placeholder: "Search Rute",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.routes.select2') }}",
          dataType: "json",
          delay: 250,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
          cache: true
        },
      });

      $("#select2Cargos").select2({
        placeholder: "Search Muatan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.cargos.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });

      $("#select2TypeCapacities").select2({
        placeholder: "Search Kapasitas",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.typecapacities.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function (e) {
        getData();
      });

      $('#select2Type').on('change', function () {
        getData();
      });

      function getData() {
        let formData = {
          type_capacity_id: $('#select2TypeCapacities').find(':selected').val(),
          type: $('#select2Type').find(':selected').val(),
          road_money_id: "{{ Request::segment(3) }}"
        }
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type: 'POST',
          url: "{{ route('backend.roadmonies.typecapacities') }}",
          data: formData,
          success: function (response) {
            if (response.data) {
              let data = response.data.pivot;
              $('input[name=road_engkel]').val(data.road_engkel);
              $('input[name=road_tronton]').val(data.road_tronton);
              $('input[name=expense]').val(data.expense);
              $('select[name=type]').val(data.type);
            } else {
              $('input[name=road_engkel]').val('');
              $('input[name=road_tronton]').val('');
              $('input[name=expense]').val('');
            }
          }
        });
      }

      $("#formUpdate").submit(function (e) {
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          type: "POST",
          url: url,
          data: data,
          success: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            if (response.status == "success") {
              toastr.success(response.message, 'Success !');
              // setTimeout(function () {
              //   if (response.redirect == "" || response.redirect == "reload") {
              //     location.reload();
              //   } else {
              //     location.href = response.redirect;
              //   }
              // }, 1000);
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error("Please complete your form", 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });

      $("#formUpdateTypeCapacity").submit(function (e) {
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          type: "POST",
          url: url,
          data: data,
          success: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            if (response.status == "success") {
              toastr.success(response.message, 'Success !');
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error("Please complete your form", 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });
    });
  </script>
@endsection
