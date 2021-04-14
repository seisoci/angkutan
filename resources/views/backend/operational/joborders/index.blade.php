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
      <a href="{{ route('backend.joborders.create') }}" class="btn btn-primary font-weight-bolder">
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
    <div class="mb-10">
      <div class="row align-items-center">
        <div class="col-12">
          <div class="row align-items-center">
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>LDO:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Supir:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>No. Pol:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Costumer:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
          </div>
          <div class="row align-items-center">
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Rute Dari:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Rute Ke:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Muatan:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Tanggal Mulai:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
          </div>
          <div class="row align-items-center">
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Rute Selesai:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Status Job Order:</label>
                <select class="form-control" id="select2Driver">
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--begin: Datatable-->
    <table class="table table-bordered table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Nama</th>
          <th>No. Job Order</th>
          <th>LDO</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Muatan</th>
          <th>Tanggal MULAI</th>
          <th>Tanggal Selesai</th>
          <th>Status JO</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
{{-- Modal --}}
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
    $(".currency").inputmask('decimal', {
      groupSeparator: '.',
      digits:0,
      rightAlign: true,
      removeMaskOnSubmit: true
    });

    var dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[9, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.joborders.index') }}",
        columns: [
            {data: 'prefix', name: 'prefix'},
            {data: 'num_bill', name: 'num_bill'},
            {data: 'anotherexpedition.name', name: 'anotherexpedition.name', defaultContent: ''},
            {data: 'driver.name', name: 'driver.name'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'costumer.name', name: 'costumer.name'},
            {data: 'routefrom.name', name: 'routefrom.name'},
            {data: 'routeto.name', name: 'routeto.name'},
            {data: 'cargo.name', name: 'cargo.name'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'date_end', name: 'date_end', defaultContent: ''},
            {data: 'status_cargo', name: 'status_cargo'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
        {
          className: 'dt-center',
          targets: 11,
          width: '75px',
          render: function(data, type, full, meta) {
            var status = {
              'mulai': {'title': 'Mulai', 'class': ' label-light-info'},
              'muat': {'title': 'Muat', 'class': ' label-light-dark'},
              'bongkar': {'title': 'Bongkar', 'class': ' label-light-primary'},
              'selesai': {'title': 'Selesai', 'class': ' label-light-success'},
              'batal': {'title': 'Batal', 'class': ' label-light-danger'},
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

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.brands.index") }}/'+ id);
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
      var id = $(event.relatedTarget).data('id');
      var name = $(event.relatedTarget).data('name');
      $(this).find('#formUpdate').attr('action', '{{ route("backend.brands.index") }}/'+id)
      $(this).find('.modal-body').find('input[name="name"]').val(name);
    });
    $('#modalEdit').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="name"]').val('');
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
      var btnHtml   = form.html();
      var spinner   = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
      $.ajax({
        beforeSend:function() {
          form.prop('disabled', true).html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...");
        },
        type: 'DELETE',
        url: url,
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
          if(response.status == "success"){
            form.prop('disabled', false).html(btnHtml);
            toastr.success(response.message,'Success !');
            $('#modalDelete').modal('hide');
            dataTable.draw();
          }else{
            form.prop('disabled', false).html(btnHtml);
            toastr.error(response.message,'Failed !');
            $('#modalDelete').modal('hide');
          }
        },
        error: function (response) {
          form.prop('disabled', false).text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
          toastr.error(response.responseJSON.message ,'Failed !');
          $('#modalDelete').modal('hide');
          $('#modalDelete').find('a[name="id"]').attr('href', '');
        }
      });
    });
  });
</script>
@endsection
