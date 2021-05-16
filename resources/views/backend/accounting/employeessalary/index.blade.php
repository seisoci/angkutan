{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  {{-- Dashboard 1 --}}
  <!--begin::Card-->
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
      </div>
    </div>

    <div class="card-body">
      <!--begin: Datatable-->
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Image</th>
          <th>Nama</th>
          <th>Jabatan</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  <div class="modal fade" id="modalReset" tabindex="-1" role="dialog" aria-labelledby="modalResetLabel"
       aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalResetLabel">Reset Password</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form id="formReset" method="POST" action="{{ route('backend.users.resetpassword') }}">
          <div class="modal-body">
            @csrf
            <input type="hidden" name="id"></a>
            Are you sure you want to reset password default? <br> (password same with email)
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary font-weight-bold">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade text-left" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel"
       aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDeleteLabel">Delete</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @method('DELETE')
        <div class="modal-body">
          <a href="" type="hidden" name="id" disabled></a>
          Are you sure you want to delete this item?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="formDelete" type="button" class="btn btn-danger">Accept</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalShow" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Karyawaan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group" style="display:none;">
            <div class="alert alert-custom alert-light-danger" role="alert">
              <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
              <div class="alert-text">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Foto Profile</label>
            <img name="photo" width="100%" height="15%">
          </div>
          <div class="form-group">
            <label>Foto KTP</label>
            <img name="photo" width="100%" height="15%">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control form-control-solid" disabled/>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Jabatan</label>
                <input type="text" name="position" class="form-control form-control-solid " disabled/>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <input type="text" name="status" class="form-control form-control-solid" disabled/>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. Rekening</label>
                <input type="text" name="no_card" class="form-control form-control-solid " disabled/>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('css/backend/datatables/dataTables.control.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  <script id="details-template" type="text/x-handlebars-template">
    @verbatim
    <table class="table table-bordered " id="posts-{{id}}">
      <thead>
      <tr>
        <th>Nama Tipe Gaji</th>
        <th>Nominal</th>
      </tr>
      </thead>
    </table>
    @endverbatim
  </script>

  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

  {{-- page scripts --}}
  <script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(function () {
      let template = Handlebars.compile($("#details-template").html());
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[5, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.employeessalary.index') }}",
        columns: [
          {
            "className": 'details-control',
            "orderable": false,
            "searchable": false,
            "data": null,
            "defaultContent": ''
          },
          {data: 'photo', name: 'photo'},
          {data: 'name', name: 'name'},
          {data: 'position', name: 'position', className: 'dt-center'},
          {data: 'status', name: 'status'},
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            orderable: false,
            targets: 1,
            render: function (data, type, full, meta) {
              let output = `
              <div class="symbol symbol-80">
                <img src="` + data + `" alt="photo">
              </div>`
              return output;
            }
          },
          {
            className: 'dt-center',
            targets: 4,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'inactive': {'title': 'Inactive', 'class': ' label-light-danger'},
                'active': {'title': 'Active', 'class': ' label-light-success'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
                '</span>';
            },
          },
        ],
      });

      $('#Datatable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dataTable.row(tr);
        let tableId = 'posts-' + row.data().id;

        if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('shown');
        } else {
          // Open this row
          row.child(template(row.data())).show();
          initTable(tableId, row.data());
          tr.addClass('shown');
          tr.next().find('td').addClass('no-padding bg-gray');
        }
      });

      function initTable(tableId, data) {
        $('#' + tableId).DataTable({
          processing: true,
          serverSide: true,
          order: [1, 'desc'],
          ajax: data.details_url,
          columns: [
            {data: 'name', name: 'name'},
            {
              data: 'amount',
              name: 'amount',
              render: $.fn.dataTable.render.number('.', '.', 2),
              orderable: false,
              searchable: false,
              className: 'dt-right'
            }
          ]
        })
      }

      $('#modalReset').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('input[name="id"]').val(id);
      });
      $('#modalReset').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="id"]').val('');
      });
      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.employees.index") }}/' + id);
      });
      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
      });
      $('#modalShow').on('show.bs.modal', function (event) {
        let name = $(event.relatedTarget).data('name');
        let position = $(event.relatedTarget).data('position');
        let no_card = $(event.relatedTarget).data('no_card');
        let photo = $(event.relatedTarget).data('photo');
        let photo_ktp = $(event.relatedTarget).data('photo_ktp');
        let status = $(event.relatedTarget).data('status');
        $(this).find('.modal-body').find('input[name="name"]').val(name);
        $(this).find('.modal-body').find('input[name="position"]').val(position);
        $(this).find('.modal-body').find('input[name="no_card"]').val(no_card);
        $(this).find('.modal-body').find('input[name="status"]').val(status);
        let photo_img = photo ? '/images/thumbnail/' + photo : '/media/bg/no-content.svg';
        let photo_ktp_img = photo_ktp ? '/images/thumbnail/' + photo_ktp : '/media/bg/no-content.svg';
        $(this).find('.modal-body').find('img[name="photo"]').attr('src', '' + photo_img);
        $(this).find('.modal-body').find('img[name="photo_ktp"]').attr('src', '' + photo_ktp_img);
      });
      $('#modalShow').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="name"]').val('');
        $(this).find('.modal-body').find('input[name="position"]').val('');
        $(this).find('.modal-body').find('input[name="no_card"]').val('');
        $(this).find('.modal-body').find('input[name="status"]').val('');
        $(this).find('.modal-body').find('img[name="photo"]').attr('src', '');
        $(this).find('.modal-body').find('img[name="photo_ktp"]').attr('src', '');
      });

      $("#formReset").submit(function (e) {
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

      $("#formDelete").click(function (e) {
        e.preventDefault();
        let form = $(this);
        let url = $('#modalDelete').find('a[name="id"]').attr('href');
        let btnHtml = form.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        $.ajax({
          beforeSend: function () {
            form.text(' Loading. . .').prepend(spinner);
          },
          type: 'DELETE',
          url: url,
          dataType: 'json',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          success: function (response) {
            toastr.success(response.message, 'Success !');
            form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            $('#modalDelete').modal('hide');
            dataTable.draw();
          },
          error: function (response) {
            toastr.error(response.responseJSON.message, 'Failed !');
            form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            $('#modalDelete').modal('hide');
            $('#modalDelete').find('a[name="id"]').attr('href', '');
          }
        });
      });
    });
  </script>
@endsection
