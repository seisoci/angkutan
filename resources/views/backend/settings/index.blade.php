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
    <form id="formStore" action="{{ route('backend.settings.store') }}">
      @csrf
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
              <label class="mx-0 text-bold d-block">Logo</label>
              <img id="avatar"
                   src="{{ $logo->value != NULL ? asset("/images/original/".$logo->value) : asset('media/bg/no-content.svg') }}"
                   style="object-fit: fit; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="100px"
                   width="300px">
              <input type="file" class="image d-block" name="logo" accept=".jpg, .jpeg, .png">
              <p class="text-muted ml-75 mt-50"><small>Allowed JPG, JPEG or PNG. Max
                  size of
                  2000kB</small></p>
            </div>
            <div class="form-group">
              <label class="mx-0 text-bold d-block">Favicon</label>
              <img id="avatar"
                   src="{{ $favicon->value != NULL ? asset("/images/original/".$favicon->value) : asset('media/bg/no-content.svg') }}"
                   style="object-fit: fit; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="50px"
                   width="50px">
              <input type="file" class="image d-block" name="favicon" accept=".jpg, .jpeg, .png">
              <p class="text-muted ml-75 mt-50"><small>Allowed JPG, JPEG or PNG. Max
                  size of
                  2000kB</small></p>
            </div>
            @foreach ($data as $item)
              <div class="form-group">
                <label>{{ ucwords($item->name) }} <span class="text-danger">*</span></label>
                <input type="text" name="id[]" value="{{ $item->id }}" hidden>
                @if($item->name == 'potongan sparepart' || $item->name == 'gaji supir')
                  <div class="input-group">
                    <input type="number" class="form-control" name="value[]" value="{{ $item->value }}" min="0">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                  </div>
                @else
                  <input type="text" class="form-control" name="value[]" value="{{ $item->value }}">
                @endif
              </div>
            @endforeach
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
      $("#formStore").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var btnSubmit = form.find("[type='submit']");
        var btnSubmitHtml = btnSubmit.html();
        var url = form.attr("action");
        var data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled", "disabled");
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
              setTimeout(function () {
                if (response.redirect == "" || response.redirect == "reload") {
                  location.reload();
                } else {
                  location.href = response.redirect;
                }
              }, 1000);
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error("Please complete your form", 'Failed !');
            }
          },
          error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });

      $(".phone").inputmask("mask", {
        mask: "(9999) 9999-99999",
        placeholder: ""
      });

      $(".image").change(function () {
        let thumb = $(this).parent().find('img');
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            thumb.attr('src', e.target.result);
          }
          reader.readAsDataURL(this.files[0]);
        }
      });
    });
  </script>
@endsection
