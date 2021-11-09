{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

  <!--begin::Card-->
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <!--begin::Button-->
        <a href="#" data-toggle="modal" data-target="#modalCreate" class="btn btn-primary font-weight-bolder">
        <span class="svg-icon svg-icon-md">
          <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
               viewBox="0 0 24 24" version="1.1">
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <rect x="0" y="0" width="24" height="24"></rect>
              <circle fill="#000000" cx="9" cy="15" r="6"></circle>
              <path
                d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z"
                fill="#000000" opacity="0.3"></path>
            </g>
          </svg>
          <!--end::Svg Icon-->
        </span>New Record</a>
        <!--end::Button-->
      </div>
    </div>

    <div class="card-body">
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th>Image</th>
          <th>Nama PT</th>
          <th>Nickname PT</th>
          <th>Pemilik</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Fax</th>
          <th>Alamat</th>
          <th>Default</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  {{-- Modal --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create {{ $config['page_title'] }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form id="formStore" action="{{ route('backend.cooperation.store') }}">
          @csrf
          <div class="modal-body">
            <div class="form-group" style="display:none;">
              <div class="alert alert-custom alert-light-danger" role="alert">
                <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
                <div class="alert-text">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="mx-0 text-bold d-block">Logo Perusahaan</label>
                  <img id="avatar" src="{{ asset('media/bg/no-content.svg') }}"
                       style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="150px"
                       width="150px">
                  <input type="file" class="image d-block" name="image" accept=".jpg, .jpeg, .png">
                  <p class="text-muted ml-75 mt-50 d-block"><small>Allowed JPG, JPEG or PNG. Max
                      size of
                      2000kB</small></p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Perusahaan</label>
                  <input type="text" name="name" class="form-control form-control-solid" placeholder="Input Nama Bank"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Panggilan Perusahaan</label>
                  <input type="text" name="nickname" class="form-control form-control-solid"
                         placeholder="Input Nama Cabang"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Pemilik</label>
                  <input type="text" name="owner" class="form-control form-control-solid"
                         placeholder="Input Nama Pemilik"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" name="email" class="form-control form-control-solid" placeholder="Input Email"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" name="phone" class="form-control form-control-solid"
                         placeholder="Input No. Phone"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Fax</label>
                  <input type="text" name="fax" class="form-control form-control-solid" placeholder="Input Fax"/>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea rows="4" name="address" class="form-control form-control-solid"
                        placeholder="Input Alamat"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit {{ $config['page_title'] }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form class="formUpdate" action="#">
          @method('PUT')
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="modal-body">
            <div class="form-group" style="display:none;">
              <div class="alert alert-custom alert-light-danger" role="alert">
                <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
                <div class="alert-text">
                </div>
              </div>
            </div>
            <input type="hidden" name="default">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="mx-0 text-bold d-block">Logo Perusahaan</label>
                  <img id="avatar" src="{{ asset('media/bg/no-content.svg') }}"
                       style="object-fit: cover; border: 1px solid #d9d9d9" class="mb-2 border-2 mx-auto" height="150px"
                       width="150px">
                  <input type="file" class="image d-block" name="image" accept=".jpg, .jpeg, .png">
                  <p class="text-muted ml-75 mt-50 d-block"><small>Allowed JPG, JPEG or PNG. Max
                      size of
                      2000kB</small></p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Perusahaan</label>
                  <input type="text" name="name" class="form-control form-control-solid" placeholder="Input Nama Bank"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Panggilan Perusahaan</label>
                  <input type="text" name="nickname" class="form-control form-control-solid"
                         placeholder="Input Nama Cabang"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Pemilik</label>
                  <input type="text" name="owner" class="form-control form-control-solid"
                         placeholder="Input Nama Pemilik"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" name="email" class="form-control form-control-solid" placeholder="Input Email"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" name="phone" class="form-control form-control-solid"
                         placeholder="Input No. Phone"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Fax</label>
                  <input type="text" name="fax" class="form-control form-control-solid" placeholder="Input Fax"/>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea rows="4" name="address" class="form-control form-control-solid"
                        placeholder="Input Alamat"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalEditDefault" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Sebagai Default</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form class="formUpdate" action="#">
          @method('PUT')
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="modal-body">
            <div class="form-group" style="display:none;">
              <div class="alert alert-custom alert-light-danger" role="alert">
                <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
                <div class="alert-text">
                </div>
              </div>
            </div>
            Ubah sebagai default untuk seluruh dokumen ?
            <input type="hidden" name="default" value="1">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete</h5>
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
          <button id="formDelete" type="button" class="btn btn-danger">Submit</button>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection


{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits: 0,
        rightAlign: true,
        removeMaskOnSubmit: true
      });
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.cooperation.index') }}",
        columns: [
          {data: 'image', name: 'image', orderable: false, searchable: false},
          {data: 'name', name: 'name'},
          {data: 'nickname', name: 'nickname'},
          {data: 'owner', name: 'owner'},
          {data: 'email', name: 'email'},
          {data: 'phone', name: 'phone'},
          {data: 'fax', name: 'fax'},
          {data: 'address', name: 'address'},
          {data: 'default', name: 'default'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            orderable: false,
            targets: 0,
            render: function (data, type, full, meta) {
              let output = `
              <img width="100px" height="100px" src="` + data + `" alt="photo">
              `
              return output;
            }
          },
          {
            className: 'dt-center',
            targets: 8,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                0: {'title': 'Tidak', 'class': ' label-light-danger'},
                1: {'title': 'Ya', 'class': ' label-light-success'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
                '</span>';
            },
          },
        ]
      });

      $(".image").change(function () {
        let thumb = $(this).parent().find('img');
        if (this.files && this.files[0]) {
          let reader = new FileReader();
          reader.onload = function (e) {
            thumb.attr('src', e.target.result);
          }
          reader.readAsDataURL(this.files[0]);
        }
      });

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.cooperation.index") }}/' + id);
      });
      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
      });
      $('#modalCreate').on('show.bs.modal', function (event) {
      });
      $('#modalCreate').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="name"]').val('');
      });
      $('#modalEdit').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        let image = $(event.relatedTarget).data('image');
        let name = $(event.relatedTarget).data('name');
        let nickname = $(event.relatedTarget).data('nickname');
        let owner = $(event.relatedTarget).data('owner');
        let email = $(event.relatedTarget).data('email');
        let phone = $(event.relatedTarget).data('phone');
        let fax = $(event.relatedTarget).data('fax');
        let address = $(event.relatedTarget).data('address');
        let defaultData = $(event.relatedTarget).data('default');
        $(this).find('.formUpdate').attr('action', '{{ route("backend.cooperation.index") }}/' + id)
        if (image) {
          $(this).find('.modal-body').find('img').attr('src', '/storage/images/thumbnail/' + image);
        } else {
          $(this).find('.modal-body').find('img').attr('src', '/media/bg/no-content.svg');
        }
        $(this).find('.modal-body').find('input[name="name"]').val(name);
        $(this).find('.modal-body').find('input[name="nickname"]').val(nickname);
        $(this).find('.modal-body').find('input[name="owner"]').val(owner);
        $(this).find('.modal-body').find('input[name="email"]').val(email);
        $(this).find('.modal-body').find('input[name="phone"]').val(phone);
        $(this).find('.modal-body').find('input[name="fax"]').val(fax);
        $(this).find('.modal-body').find('input[name="default"]').val(defaultData);
        $(this).find('.modal-body').find('textarea[name="address"]').val(address);
      });
      $('#modalEdit').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="image"]').val('');
        $(this).find('.modal-body').find('input[name="name"]').val('');
        $(this).find('.modal-body').find('input[name="nickname"]').val('');
        $(this).find('.modal-body').find('input[name="owner"]').val('');
        $(this).find('.modal-body').find('input[name="email"]').val('');
        $(this).find('.modal-body').find('input[name="phone"]').val('');
        $(this).find('.modal-body').find('input[name="fax"]').val('');
        $(this).find('.modal-body').find('input[name="default"]').val('');
        $(this).find('.modal-body').find('textarea[name="address"]').val('');
      });
      $('#modalEditDefault').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.formUpdate').attr('action', '{{ route("backend.cooperation.index") }}/' + id)
      });
      $('#modalEditDefault').on('hidden.bs.modal', function (event) {
      });
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
            if (response.status == "success") {
              toastr.success(response.message, 'Success !');
              $('#modalCreate').modal('hide');
              dataTable.draw();
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

      $(".formUpdate").submit(function (e) {
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
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              $('#modalEdit').modal('hide');
              $('#modalEditDefault').modal('hide');
              dataTable.draw();
              $("[role='alert']").parent().css("display", "none");
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
            $('#modalEdit').modal('hide');
            $('#modalEdit').find('a[name="id"]').attr('href', '');
            $('#modalEditDefault').modal('hide');
            $('#modalEditDefault').find('a[name="id"]').attr('href', '');
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
            form.prop('disabled', true).html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...");
          },
          type: 'DELETE',
          url: url,
          dataType: 'json',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          success: function (response) {
            if (response.status == "success") {
              form.prop('disabled', false).html(btnHtml);
              toastr.success(response.message, 'Success !');
              $('#modalDelete').modal('hide');
              dataTable.draw();
            } else {
              form.prop('disabled', false).html(btnHtml);
              toastr.error(response.message, 'Failed !');
              $('#modalDelete').modal('hide');
            }
          },
          error: function (response) {
            form.prop('disabled', false).text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalDelete').modal('hide');
            $('#modalDelete').find('a[name="id"]').attr('href', '');
          }
        });
      });
    });
  </script>
@endsection
