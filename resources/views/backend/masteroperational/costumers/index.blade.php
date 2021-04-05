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
          <th>Nama</th>
          <th>Telp</th>
          <th>Alamat</th>
          <th>Created At</th>
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
      <form id="formStore" action="{{ route('backend.costumers.store') }}">
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
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Costumer</label>
                <input type="text" name="name" class="form-control form-control-solid"
                  placeholder="Input Nama Costumer" />
              </div>
              <div class="form-group">
                <label>Nama Emergency</label>
                <input type="text" name="emergency_name" class="form-control form-control-solid"
                  placeholder="Input Nama Emergency" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. Telp/HP Costumer</label>
                <input type="text" name="phone" class="phone form-control form-control-solid"
                  placeholder="Input No. Telp/HP Costumer" />
              </div>
              <div class="form-group">
                <label>No. Telp/HP Emergency</label>
                <input type="text" name="emergency_phone" class="phone form-control form-control-solid"
                  placeholder="Input No. Telp/HP Emergency" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Costumer</label>
            <textarea name="address" rows="3" class="form-control form-control-solid"
              placeholder="Input Alamat"></textarea>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="description" rows="5" class="form-control form-control-solid"
              placeholder="Input Keterangan"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" type="button" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create {{ $config['page_title'] }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i aria-hidden="true" class="ki ki-close"></i>
        </button>
      </div>
      <form id="formUpdate" action="#">
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
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Costumer</label>
                <input type="text" name="name" class="form-control form-control-solid"
                  placeholder="Input Nama Costumer" />
              </div>
              <div class="form-group">
                <label>Nama Emergency</label>
                <input type="text" name="emergency_name" class="form-control form-control-solid"
                  placeholder="Input Nama Emergency" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. Telp/HP Costumer</label>
                <input type="text" name="phone" class="phone form-control form-control-solid"
                  placeholder="Input No. Telp/HP Costumer" />
              </div>
              <div class="form-group">
                <label>No. Telp/HP Emergency</label>
                <input type="text" name="emergency_phone" class="phone form-control form-control-solid"
                  placeholder="Input No. Telp/HP Emergency" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Costumer</label>
            <textarea name="address" rows="3" class="form-control form-control-solid"
              placeholder="Input Alamat"></textarea>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="description" rows="5" class="form-control form-control-solid"
              placeholder="Input Keterangan"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" type="button" class="btn btn-primary">Submit</button>
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
        Are you sure you want to delete this item? </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button id="formDelete" type="button" class="btn btn-danger">Submit</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Costumer</h5>
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
        <div class="form-group row">
          <label class="col-md-3">Nama Costumer</label>
          <input type="text" name="name" class="form-control form-control-solid col-md-9"
            placeholder="Input Nama Costumer" disabled />
        </div>
        <div class="form-group row">
          <label class="col-md-3">Nama Emergency</label>
          <input type="text" name="emergency_name" class="form-control form-control-solid col-md-9"
            placeholder="Input Nama Emergency" disabled />
        </div>
        <div class="form-group row">
          <label class="col-md-3">No. Telp/HP Costumer</label>
          <input type="text" name="phone" class="phone form-control form-control-solid col-md-9"
            placeholder="Input No. Telp/HP Costumer" disabled />
        </div>
        <div class="form-group row">
          <label class="col-md-3">No. Telp/HP Emergency</label>
          <input type="text" name="emergency_phone" class="phone form-control form-control-solid col-md-9"
            placeholder="Input No. Telp/HP Emergency" disabled />
        </div>
        <div class="form-group">
          <label>Alamat Costumer</label>
          <textarea name="address" rows="3" class="form-control form-control-solid" placeholder="Input Alamat"
            disabled></textarea>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <textarea name="description" rows="5" class="form-control form-control-solid" placeholder="Input Keterangan"
            disabled></textarea>
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
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
{{-- page scripts --}}
<script type="text/javascript">
  $(document).ready(function(){
    var dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[3, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.costumers.index') }}",
        columns: [
            {data: 'name', name: 'name'},
            {data: 'phone', name: 'phone'},
            {data: 'address', name: 'address'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
    });
    $(".phone").inputmask("mask", {
      mask: "(9999) 9999-99999",
      placeholder: ""
    });

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.costumers.index") }}/'+ id);
    });
    $('#modalDelete').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
    });
    $('#modalCreate').on('show.bs.modal', function (event) {
    });
    $('#modalCreate').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_phone"]').val('');
      $(this).find('.modal-body').find('input[name="phone"]').val('');
      $(this).find('.modal-body').find('textarea[name="address"]').val('');
      $(this).find('.modal-body').find('textarea[name="description"]').val('');
    });
    $('#modalEdit').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      var name = $(event.relatedTarget).data('name');
      var emergency_name = $(event.relatedTarget).data('emergency_name');
      var emergency_phone = $(event.relatedTarget).data('emergency_phone');
      var phone = $(event.relatedTarget).data('phone');
      var address = $(event.relatedTarget).data('address');
      var description = $(event.relatedTarget).data('description');
      $(this).find('#formUpdate').attr('action', '{{ route("backend.costumers.index") }}/'+id)
      $(this).find('.modal-body').find('input[name="name"]').val(name);
      $(this).find('.modal-body').find('input[name="emergency_name"]').val(emergency_name);
      $(this).find('.modal-body').find('input[name="emergency_phone"]').val(emergency_phone);
      $(this).find('.modal-body').find('input[name="phone"]').val(phone);
      $(this).find('.modal-body').find('textarea[name="address"]').val(address);
      $(this).find('.modal-body').find('textarea[name="description"]').val(description);
    });
    $('#modalEdit').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_phone"]').val('');
      $(this).find('.modal-body').find('input[name="phone"]').val('');
      $(this).find('.modal-body').find('textarea[name="address"]').val('');
      $(this).find('.modal-body').find('textarea[name="description"]').val('');
      $(this).find('#formUpdate').attr('action', '#')
    });
    $('#modalShow').on('show.bs.modal', function (event) {
      var name = $(event.relatedTarget).data('name');
      var emergency_name = $(event.relatedTarget).data('emergency_name');
      var emergency_phone = $(event.relatedTarget).data('emergency_phone');
      var phone = $(event.relatedTarget).data('phone');
      var address = $(event.relatedTarget).data('address');
      var description = $(event.relatedTarget).data('description');
      $(this).find('.modal-body').find('input[name="name"]').val(name);
      $(this).find('.modal-body').find('input[name="emergency_name"]').val(emergency_name);
      $(this).find('.modal-body').find('input[name="emergency_phone"]').val(emergency_phone);
      $(this).find('.modal-body').find('input[name="phone"]').val(phone);
      $(this).find('.modal-body').find('textarea[name="address"]').val(address);
      $(this).find('.modal-body').find('textarea[name="description"]').val(description);
    });
    $('#modalShow').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_name"]').val('');
      $(this).find('.modal-body').find('input[name="emergency_phone"]').val('');
      $(this).find('.modal-body').find('input[name="phone"]').val('');
      $(this).find('.modal-body').find('textarea[name="address"]').val('');
      $(this).find('.modal-body').find('textarea[name="description"]').val('');
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
            $('#modalCreate').modal('hide');
            dataTable.draw();
            $("[role='alert']").parent().css("display", "none");
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
          $('#modalCreate').modal('hide');
          $('#modalCreate').find('a[name="id"]').attr('href', '');
        }
      });
    });

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
          if (response.status == "success" ){
            toastr.success(response.message,'Success !');
            $('#modalEdit').modal('hide');
            dataTable.draw();
            $("[role='alert']").parent().css("display", "none");
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
            $('#modalEdit').modal('hide');
            $('#modalEdit').find('a[name="id"]').attr('href', '');
        }
      });
    });

    $("#formDelete").click(function(e){
      e.preventDefault();
      var form 	    = $(this);
      var url 	    = $('#modalDelete').find('a[name="id"]').attr('href');
      var btnSubmit = form.find("[type='submit']");
      var btnSubmitHtml = btnSubmit.html();
      $.ajax({
        beforeSend:function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled","disabled");
        },
        type: 'DELETE',
        url: url,
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
          if(response.status == "success"){
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.success(response.message,'Success !');
            $('#modalDelete').modal('hide');
            dataTable.draw();
          }else{
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.message,'Failed !');
            $('#modalDelete').modal('hide');
          }
        },
        error: function (response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message ,'Failed !');
          $('#modalDelete').modal('hide');
          $('#modalDelete').find('a[name="id"]').attr('href', '');
        }
      });
    });
  });
</script>
@endsection
