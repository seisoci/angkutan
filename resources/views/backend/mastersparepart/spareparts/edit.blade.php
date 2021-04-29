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
      <form id="formUpdate" action="{{ route('backend.spareparts.update', Request::segment(3)) }}">
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
            <label class="mx-0 text-bold d-block">Image Kendaraan</label>
            <img id="avatar"
              src="{{ $data->photo != NULL ? asset("/images/original/".$data->photo) : asset('media/bg/no-content.svg') }}"
              style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="100px"
              width="100px">
            <input type="file" class="image d-block" name="photo" accept=".jpg, .jpeg, .png">
            <p class="text-muted ml-75 mt-50 d-block"><small>Allowed JPG, JPEG or PNG. Max
                size of
                2000kB</small></p>
          </div>
          <div class="form-group">
            <label>Nama Spare Part</label>
            <input type="text" name="name" class="form-control" placeholder="Input Nama Spare Spart"
              value="{{ $data->name ?? '' }}" />
          </div>
          <div class="form-group">
            <label>Nama Brand<span class="text-danger">*</span></label>
            <select class="form-control" id="select2Brands" name="brand_id">
              <option value="{{ $data->brand->id }}" selected>{{ $data->brand->name }}</option>s
            </select>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select class="form-control" id="select2Categories" multiple="multiple" name="categories[]">
              @foreach ($data->categories as $item)
              <option value="{{ $item->id }}" selected>{{ $item->name }}</option>
              @endforeach
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

    $("#select2Brands").select2({
      placeholder: "Search Brands",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.brands.select2') }}",
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

    $("#select2Suppliers").select2({
      placeholder: "Search Suppliers",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.supplierspareparts.select2') }}",
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

    $("#select2Categories").select2({
      placeholder: "Search Categories",
      allowClear: true,
      tags: true,
      ajax: {
          url: "{{ route('backend.categories.select2') }}",
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
      createTag: function(params) {
      return undefined;
      }
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
