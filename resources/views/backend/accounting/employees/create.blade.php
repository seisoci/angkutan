{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-6">
    <div class="card card-custom">
      <div class="card-header">
        <h3 class="card-title">
          {{ $config['page_title'] }}
        </h3>
      </div>
      <!--begin::Form-->
      <form id="formStore" action="{{ route('backend.employees.store') }}">
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
            <div class="image-input" id="kt_image_2">
              <div class="image-input-wrapper" style="background-image: url({{ asset('/media/users/blank.png') }})">
              </div>
              <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
                <i class="fa fa-pen icon-sm text-muted"></i>
                <input type="file" name="profile_avatar" accept=".png, .jpg, .jpeg" />
                <input type="hidden" name="profile_avatar_remove" />
              </label>

              <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                data-action="cancel" data-toggle="tooltip" title="Cancel avatar">
                <i class="ki ki-bold-close icon-xs text-muted"></i>
              </span>
            </div>
            <span class="form-text text-muted">Maximum file 2 MB and format png, jpg, jpeg</span>
          </div>
          <div class="form-group">
            <label class="mx-0 text-bold">Image KTP</label>
            <img id="avatar" src="{{ asset('media/bg/no-content.svg') }}"
              style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="250px"
              width="100%">
            <input type="file" class="image" name="photo_ktp" accept=".jpg, .jpeg, .png">
            <p class="text-muted ml-75 mt-50"><small>Allowed JPG, JPEG or PNG. Max
                size of
                2000kB</small></p>
          </div>
          <div class="form-group">
            <label>Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Enter Nama" />
          </div>
          <div class="form-group">
            <label>Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="position" class="form-control" placeholder="Enter Jabatan" />
          </div>
          <div class="form-group">
            <label>No. ATM <span class="text-danger">*</span></label>
            <input type="text" name="no_card" class="form-control" placeholder="Enter No. ATM" />
          </div>
          <div class="form-group">
            <label for="activeSelect">Active <span class="text-danger">*</span></label>
            <select class="form-control" id="activeSelect" name="status">
              <option value="inactive">Inactive</option>
              <option value="active">Active</option>
            </select>
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
