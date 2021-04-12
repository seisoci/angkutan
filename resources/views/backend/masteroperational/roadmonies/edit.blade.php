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
              <option value="fix">Fix</option>
              <option value="calculate">Kalkulasi (Ongkosan * Kapasistas (KG/KUBIK/DLL) )</option>
            </select>
          </div>
          <div class="form-group">
            <label>Harga Ongkosan</label>
            <input type="text" name="expense" class="currency form-control" placeholder="Input Harga Ongkosan" />
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

    $("#select2TypeCapacities").select2({
      placeholder: "Search Kapasitas",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.typecapacities.select2') }}",
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
    }).on('change', function (e){
      getData();
    });

    $('#select2Type').on('change', function() {
      getData();
    });

    function getData() {
      var formData = {
        type_capacity_id: $('#select2TypeCapacities').find(':selected').val(),
        type: $('#select2Type').find(':selected').val(),
        road_money_id: "{{ Request::segment(3) }}"
      }
      $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type:'POST',
          url: "{{ route('backend.roadmonies.typecapacities') }}",
          data: formData,
          success:function(response) {
            if(response.data){
              let data = response.data.pivot;
              $('input[name=road_engkel]').val(data.road_engkel);
              $('input[name=road_tronton]').val(data.road_tronton);
              $('input[name=expense]').val(data.expense);
              $('select[name=type]').val(data.type);
            }else{
              $('input[name=road_engkel]').val('');
              $('input[name=road_tronton]').val('');
              $('input[name=expense]').val('');
            }
          }
      });
    }

    $("#formUpdate").submit(function(e){
      e.preventDefault();
      var form 	= $(this);
      var btnSubmit = form.find("[type='submit']");
      var btnSubmitHtml = btnSubmit.html();
      var spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
      var url 	= form.attr("action");
      var data 	= new FormData(this);
      $.ajax({
        beforeSend:function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled","disabled");
        },
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        cache: false,
        processData: false,
        contentType: false,
        type: "POST",
        url : url,
        data : data,
        success: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          if ( response.status == "success" ){
            toastr.success(response.message,'Success !');
            setTimeout(function() {
              if(response.redirect == "" || response.redirect == "reload"){
								location.reload();
							} else {
								location.href = response.redirect;
							}
            }, 1000);
          }else{
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each( response.error, function( key, value ) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error("Please complete your form",'Failed !');
          }
        },error: function(response){
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });

    $("#formUpdateTypeCapacity").submit(function(e){
      e.preventDefault();
      var form 	= $(this);
      var btnSubmit = form.find("[type='submit']");
      var btnSubmitHtml = btnSubmit.html();
      var spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
      var url 	= form.attr("action");
      var data 	= new FormData(this);
      $.ajax({
        beforeSend:function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled","disabled");
        },
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        cache: false,
        processData: false,
        contentType: false,
        type: "POST",
        url : url,
        data : data,
        success: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          if ( response.status == "success" ){
            toastr.success(response.message,'Success !');
          }else{
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each( response.error, function( key, value ) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error("Please complete your form",'Failed !');
          }
        },error: function(response){
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });
  });
</script>
@endsection
