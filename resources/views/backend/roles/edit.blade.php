{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">
            {{ $config['page_title'] }}
          </h3>
        </div>
        <!--begin::Form-->
        <form id="formUpdate" action="{{ route('backend.roles.update', Request::segment(3)) }}">
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
              <label>Role Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Enter name" value="{{ $data->name }}" disabled/>
            </div>
            <div class="card card-custom">
              <div class="card-header flex-wrap py-3">
                <div class="card-title">
                  <h3 class="card-label">List Permission</h3>
                </div>
              </div>
              <div class="card-body">
                <!--begin: Datatable-->
                <table class="table table-bordered table-hover" id="Datatable">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th class="text-center" colspan="5">Nama</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($listPermission as $key => $item)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $key }}</td>
                      @foreach($item as $keyChild => $item_child)
                        <td class="text-center">
                          <label class="col-3 col-form-label row"
                                 for="{{ $item_child['id'] }}">{{ ucwords($item_child['name']) }}</label>
                          <div class="col-3 row">
                           <span class="switch switch-sm">
                            <label>
                               <input id="{{ $item_child['id'] }}" type="checkbox" name="permission[]"
                                      value="{{ $item_child['id'] }}" {{ in_array($item_child['id'], $rolePermissions) ? 'checked' : NULL }} />
                             <span>
                             </span>
                            </label>
                           </span>
                          </div>
                        </td>
                      @endforeach
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>

            {{--            <div class="form-group">--}}
            {{--              <div class="checkbox-list">--}}
            {{--                @foreach($listPermission as $value)--}}
            {{--                  <label class="checkbox">--}}
            {{--                    <input type="checkbox" name="permission[]" value="{{ $value->id }}"--}}
            {{--                      {{ in_array($value->id, $rolePermissions) ? 'checked' : NULL }} />--}}
            {{--                    {{ ucwords($value->name) }}--}}
            {{--                  </label>--}}
            {{--                @endforeach--}}
            {{--              </div>--}}
            {{--            </div>--}}
            <div class="card-footer">
              <button type="submit" class="btn btn-primary mr-2">Submit</button>
              <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css"/>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}

  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
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
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });
    });
  </script>
@endsection
