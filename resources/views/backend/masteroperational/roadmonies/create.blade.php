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
                <label>Rute Dari</label>
                <input type="text" name="route_from" class="form-control" placeholder="Input Rute Dari" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Rute Ke</label>
                <input type="text" name="route_to" class="form-control" placeholder="Input Rute Ke" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Muatan</label>
            <input type="text" name="cargo" class="form-control" placeholder="Input Muatan" />
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Uang Jalan Engkel</label>
                <input type="text" name="road_engkel" class="currency form-control"
                  placeholder="Input Uang Jalan Engkel" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Uang Jalan Tronton</label>
                <input type="text" name="road_tronton" class="currency form-control"
                  placeholder="Input Uang Jalan Tronton" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Gaji Engkel</label>
                <input type="text" name="salary_engkel" class="currency form-control" placeholder="Input Gaji Engkel" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Gaji Tronton</label>
                <input type="text" name="salary_tronton" class="currency form-control"
                  placeholder="Input Gaji Tronton" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Gaji Rumusan</label>
                <input type="text" name="amount" class="currency form-control" placeholder="Input Gaji Rumusan" />
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit</button>
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
      digits:0,
      rightAlign: true,
      removeMaskOnSubmit: true
    });

    $("#select2").select2({
      placeholder: "Search Costumer",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.roadmonies.select2') }}",
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

    $("#formStore").submit(function(e) {
      e.preventDefault();
      var form = $(this);
      var btnSubmit = form.find("[type='submit']");
      var btnSubmitHtml = btnSubmit.html();
      var url = form.attr("action");
      var data = new FormData(this);
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
            toastr.error("Please complete your form", 'Failed !');
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
