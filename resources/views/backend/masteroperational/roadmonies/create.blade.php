{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-6 col-sm-12">
    <div class="card card-custom">
      <div class="card-header">
        <h3 class="card-title">
          {{ $config['page_title'] }}
        </h3>
      </div>

      <!--begin::Form-->
      <form id="formStore" action="{{ route('backend.roadmonies.store') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @csrf
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
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Rute Dari<span class="text-danger">*</span></label>
                <select class="form-control select2Routes" name="route_from">
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Rute Ke<span class="text-danger">*</span></label>
                <select class="form-control select2Routes" name="route_to">
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Muatan<span class="text-danger">*</span></label>
            <select class="form-control" id="select2Cargos" name="cargo_id">
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Fee Pemberian<span class="text-danger">*</span></label>
                <input type="text" class="form-control currency" name="fee_thanks">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tax PPH<span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" class="form-control currency" name="tax_pph" />
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
  $(document).ready(function(){
    new KTImageInput('kt_image_2');
    $(".currency").inputmask('decimal', {
      groupSeparator: '.',
      digits: 2,
      rightAlign: true,
      removeMaskOnSubmit: true,
      allowMinus: false
    });

    $("#select2").select2({
      placeholder: "Search Costumer",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.costumers.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
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
          data: function(e) {
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    });

    $("#formStore").submit(function(e) {
      e.preventDefault();
      let form = $(this);
      let btnSubmit = form.find("[type='submit']");
      let btnSubmitHtml = btnSubmit.html();
      let url = form.attr("action");
      let data = new FormData(this);
      $.ajax({
        beforeSend: function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled","disabled");
        },
        cache: false,
        processData: false,
        contentType: false,
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          if (response.status == "success") {
            toastr.success(response.message, 'Success !');
            setTimeout(function() {
              if(response.redirect == "" || response.redirect == "reload"){
								location.reload();
							} else {
								location.href = response.redirect;
							}
            }, 1000);
          } else {
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each(response.error, function(key, value) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error(response.message || "Please complete your form", 'Failed !');
          }
        },
        error: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });

  });
</script>
@endsection
