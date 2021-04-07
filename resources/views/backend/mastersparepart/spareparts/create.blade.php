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
      <form id="formStore" action="{{ route('backend.spareparts.store') }}">
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
            <label class="mx-0 text-bold d-block">Image Kendaraan</label>
            <img id="avatar" src="{{ asset('media/bg/no-content.svg') }}"
              style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="100px"
              width="100px">
            <input type="file" class="image d-block" name="photo" accept=".jpg, .jpeg, .png">
            <p class="text-muted ml-75 mt-50 d-block"><small>Allowed JPG, JPEG or PNG. Max
                size of
                2000kB</small></p>
          </div>
          <div class="form-group">
            <label>Nama Supplier<span class="text-danger">*</span></label>
            <select class="form-control" id="select2Suppliers" name="supplier_sparepart_id">
            </select>
          </div>
          <div class="form-group">
            <label>Nama Spare Part</label>
            <input type="text" name="name" class="form-control" placeholder="Input Nama Spare Spart" />
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Harga</label>
                <input type="text" name="price" class="currency form-control" placeholder="Input Harga" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Jumlah</label>
                <input type="text" name="qty" class="form-control" placeholder="Input Jumlah" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Brand<span class="text-danger">*</span></label>
            <select class="form-control" id="select2Brands" name="brand_id">
            </select>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select class="form-control" id="select2Categories" multiple="multiple" name="categories[]">
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
    $(".currency").inputmask('decimal', {
      groupSeparator: '.',
      digits:0,
      rightAlign: true,
      removeMaskOnSubmit: true
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

    $("#select2Brands").select2({
      placeholder: "Search Brands",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.spareparts.select2Brands') }}",
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

    $("#select2Suppliers").select2({
      placeholder: "Search Suppliers",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.spareparts.select2Suppliers') }}",
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

    $("#select2Categories").select2({
      placeholder: "Search Categories",
      allowClear: true,
      tags: true,
      ajax: {
          url: "{{ route('backend.spareparts.select2Categories') }}",
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
