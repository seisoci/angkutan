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
      <form id="formUpdate" action="{{ route('backend.transports.update', Request::segment(3)) }}">
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
          <input type="hidden" name="another_expedition_id" value="{{ $data->another_expedition_id ?? NULL }}" />
          <div class="form-group">
            <label class="mx-0 text-bold">Image Kendaraan</label>
            <img id="avatar"
              src="{{ $data->photo != NULL ? asset("/images/original/".$data->photo) : asset('media/bg/no-content.svg') }}"
              style="object-fit: fit; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="250px"
              width="100%">
            <input type="file" class="image" name="photo" accept=".jpg, .jpeg, .png">
            <p class="text-muted ml-75 mt-50"><small>Allowed JPG, JPEG or PNG. Max
                size of
                2000kB</small></p>
          </div>
          <div class="form-group">
            <label>No. Polisi <span class="text-danger">*</span></label>
            <input type="text" name="num_pol" class="form-control" placeholder="Input No. Polisi"
              value="{{ $data->num_pol ?? '' }}" />
          </div>
          <div class="form-group">
            <label>Merk</label>
            <input type="text" name="merk" class="form-control" placeholder="Input Merk"
              value="{{ $data->merk ?? '' }}" />
          </div>
          <div class="form-group">
            <label>Tipe</label>
            <input type="text" name="type" class="form-control" placeholder="Input Tipe"
              value="{{ $data->type ?? '' }}" />
          </div>
          <div class="form-group">
            <label class="d-block">Jenis Mobil</label>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-sm btn-info active">
                <input type="radio" name="type_car" value="ya" {{ $data->type_car == 'engkel' ? 'checked' : NULL }}>
                Engkel
                (Kecil)
              </label>
              <label class="btn btn-sm btn-info">
                <input type="radio" name="type_car" value="tidak" {{ $data->type_car == 'tronton' ? 'checked' : NULL }}>
                Tronton
                (Besar)
              </label>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="d-block">Dump</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-sm btn-info active">
                    <input type="radio" name="dump" value="ya" {{ $data->dump == 'ya' ? 'checked' : NULL }}> Ya
                  </label>
                  <label class="btn btn-sm btn-info">
                    <input type="radio" name="dump" value="tidak" {{ $data->dump == 'tidak' ? 'checked' : NULL }}> Tidak
                  </label>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Max Muatan</label>
                <div class="input-group">
                  <input type="number" name="max_weight" class="form-control" placeholder="Input Max Muatan"
                    value="{{ $data->max_weight ?? '' }}">
                  <div class="input-group-append">
                    <span class="input-group-text">TON</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tahun</label>
                <div class="input-group">
                  <input type="text" name="year" class="form-control yearDate" readonly="readonly" placeholder="Tahun"
                    value="{{ $data->year ?? '' }}" />
                  <div class="input-group-append">
                    <span class="input-group-text">
                      <i class="la la-calendar"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tgl Berlaku STNK</label>
                <div class="input-group date">
                  <input type="text" name="expired_stnk" class="form-control" readonly="readonly"
                    placeholder="Tgl Berlaku STNK" value="{{ $data->expired_stnk ?? '' }}" />
                  <div class="input-group-append">
                    <span class="input-group-text">
                      <i class="la la-calendar-check-o"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="description" rows="5" class="form-control form-control"
              placeholder="Input Keterangan">{{ $data->description ?? '' }}</textarea>
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

    $('.date').datepicker({
      format: 'yyyy-mm-dd',
    });

    $('.yearDate').datepicker({
      format: "yyyy",
      viewMode: "years",
      minViewMode: "years"
    });

    $(".image").change(function() {
      let thumb = $(this).parent().find('img');
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          thumb.attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
      }
    });
  });
</script>
@endsection
