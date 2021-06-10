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
    <form id="formStore" action="{{ route('backend.mastercoa.store') }}">
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
          <div class="col-md-8 offset-md-1">
            <div class="form-group row">
              <label class="col-form-label text-right col-lg-3 col-sm-12">Nama Akun<span
                  class="text-danger"> *</span></label>
              <div class="col-lg-9 col-md-9 col-sm-12">
                <input type="text" class="form-control" name="name"/>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-right col-lg-3 col-sm-12">Akun Parent<span class="text-danger"> *</span></label>
              <div class="col-lg-9 col-md-9 col-sm-12">
                <select id="select2Coa" name="parent_id" class="form-control">
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-right col-lg-3 col-sm-12">Saldo Normal<span
                  class="text-danger"> *</span></label>
              <div class="col-lg-9 col-md-9 col-sm-12">
                <select name="normal_balance" class="form-control">
                  <option value="Dr">Debit</option>
                  <option value="Kr">Kredit</option>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-right col-lg-3 col-sm-12">Kelompok<span
                  class="text-danger"> *</span></label>
              <div class="col-lg-9 col-md-9 col-sm-12">
                <select name="type" class="form-control">
                  <option value="harta">Harta</option>
                  <option value="kewajiban">Kewajiban</option>
                  <option value="modal">Modal</option>
                  <option value="pendapatan">Pendapatan</option>
                  <option value="beban">Beban</option>
                </select>
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
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let url = form.attr("action");
        let data = new FormData(this);
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
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              setTimeout(function () {
                if (response.redirect === "" || response.redirect === "reload") {
                  location.reload();
                } else {
                  location.href = response.redirect;
                }
              }, 1000);
              $("[role='alert']").parent().css("display", "none");
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
            $('#modalCreate').modal('hide');
            $('#modalCreate').find('a[name="id"]').attr('href', '');
          }
        });
      });

      $("#select2Coa").select2({
        placeholder: "Choose Parent",
        ajax: {
          url: "{{ route('backend.mastercoa.select2') }}",
          dataType: "json",
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });
    });
  </script>
@endsection
