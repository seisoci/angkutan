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
        <form id="formUpdate" action="{{ route('backend.employeessalary.update', Request::segment(3)) }}">
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
              <label for="select2TipeGaji">Tipe Gaji</label>
              <select name="employee_master_id" class="form-control" id="select2TipeGaji"></select>
            </div>
            <div class="form-group">
              <label>Nominal Gaji <span class="text-danger">*</span></label>
              <input type="text" name="amount" class="form-control currency" placeholder="Enter Nominal Gaji"/>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary mr-2">Submit</button>
              <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
            </div>
          </div>
        </form>
        <!--end::Form-->
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">
            List Gaji
          </h3>
        </div>
        <!--begin::Form-->
        <div class="card-body">
          <table class="table">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Jenis Gaji</th>
              <th scope="col" class="text-right">Nominal</th>
              <th scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $item)
              <tr>
                <td>{{  $loop->iteration }}</td>
                <td>{{  $item->name }}</td>
                <td class="font-weight-boldest currency">{{ $item->pivot->amount }}</td>
                <td><a href="/backend/employeessalary/{{ Request::segment(3) }}/{{  $item->id }}/destroy"
                       class="btn btn-sm btn-danger destroy">X</a></td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
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
    $(document).ready(function () {
      initCurrency();

      function initCurrency() {
        $(".currency").inputmask('decimal', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
        });
      }

      $("#select2TipeGaji").select2({
        placeholder: "Search Tipe Gaji",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.employeesmaster.select2') }}",
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

      $("#formUpdate").submit(function (e) {
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
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
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              fetchData();
              $('input[name="amount"]').val('');
              $("#select2TipeGaji").val("");
              $("#select2TipeGaji").trigger("change");
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

      $(".table").on('click' , '.destroy', function (e) {
        e.preventDefault();
        let btnSubmit = $(this);
        let btnSubmitHtml = btnSubmit.html();
        let url = this.href;
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i>").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          type: "GET",
          url: url,
          success: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              fetchData();
            } else {
              toastr.error("Please complete your form", 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });

      function fetchData(id) {
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          url: "{{ route('backend.employeessalary.index') }}/{{ Request::segment(3) }}/fetchdata",
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              let table = $('.table tbody');
              let data = response.data;
              table.empty();
              data.forEach(function (item, i) {
                table.append('<tr>'+
                  '<td>'+ (i+1) +'</td>' +
                  '<td>'+ item['name'] +'</td>' +
                  '<td class="font-weight-boldest currency">'+ item['pivot']['amount'] +'</td>' +
                  '<td><a href="/backend/employeessalary/{{ Request::segment(3) }}/{{  $item->id }}/destroy" class="btn btn-sm btn-danger destroy">X</a></td>' +
                  '</tr>');
                initCurrency();
              });
            } else {
              toastr.error("Failed Fetch Data", 'Failed !');
            }
          }, error: function (response) {
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      }
    });
  </script>
@endsection
