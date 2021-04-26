{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
{{-- Dashboard 1 --}}
<!--begin::Card-->
<div class="card card-custom">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title_driver'] }}
        <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description_driver'] }}</span></h3>
    </div>
    <div class="card-toolbar">
      <!--begin::Button-->
      <a href="{{ route('backend.anotherexpedition.create_driver', $id) }}" class="btn btn-primary font-weight-bolder">
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
    <table class="table table-bordered table-hover" id="DatatableDriver">
      <thead>
        <tr>
          <th>Image</th>
          <th>Nama</th>
          <th>Telp</th>
          <th>No SIM</th>
          <th>No KTP</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<div class="card card-custom mt-6">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title_transport'] }}
        <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description_transport'] }}</span></h3>
    </div>
    <div class="card-toolbar">
      <!--begin::Button-->
      <a href="{{ route('backend.anotherexpedition.create_transport', $id) }}"
        class="btn btn-primary font-weight-bolder">
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
    <table class="table table-bordered table-hover" id="DatatableTransport">
      <thead>
        <tr>
          <th>Image</th>
          <th>No. Pol</th>
          <th>Merk</th>
          <th>Tipe</th>
          <th>Tahun</th>
          <th>Tanggal Berlaku STNK</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<div class="modal fade text-left" id="modalDeleteDriver" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel"
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
        <button id="formDeleteDriver" type="button" class="btn btn-danger">Accept</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade text-left" id="modalDeleteTransport" tabindex="-1" role="dialog"
  aria-labelledby="modalDeleteLabel" aria-hidden="true">
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
        <button id="formDeleteTransport" type="button" class="btn btn-danger">Accept</button>
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
<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(function () {
    var dataTableDriver = $('#DatatableDriver').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[6, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.anotherexpedition.datatable_driver', $id) }}",
        columns: [
            {data: 'image', name: 'image', searchable: false},
            {data: 'name', name: 'name'},
            {data: 'phone', name: 'phone'},
            {data: 'sim', name: 'sim'},
            {data: 'ktp', name: 'ktp'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
        {
          className: 'dt-center',
          orderable: false,
          targets: 0,
          render: function(data, type, full, meta) {
            let output = `
              <div class="symbol symbol-80">
                <img src="` + data + `" alt="photo">
              </div>`
            return output;
          }
        },
        {
          className: 'dt-center',
          targets: 5,
          width: '75px',
          render: function(data, type, full, meta) {
            var status = {
              'active': {'title': 'Active', 'class': ' label-light-success'},
              'inactive': {'title': 'Inactive', 'class': ' label-light-danger'},
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

    var dataTableTransport = $('#DatatableTransport').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[7, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 9,
        ajax: "{{ route('backend.anotherexpedition.datatable_transport', $id) }}",
        columns: [
            {data: 'image', name: 'image', searchable: false},
            {data: 'num_pol', name: 'num_pol'},
            {data: 'merk', name: 'merk'},
            {data: 'type', name: 'type'},
            {data: 'year', name: 'year'},
            {data: 'expired_stnk', name: 'expired_stnk'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
        {
          className: 'dt-center',
          orderable: false,
          targets: 0,
          render: function(data, type, full, meta) {
            let output = `
              <img width="120px" height="75px" src="` + data + `" alt="photo">
              `
            return output;
          }
        },
        {
          className: 'dt-center',
          targets: 6,
          width: '75px',
          render: function(data, type, full, meta) {
            var status = {
              'ya': {'title': 'Ya', 'class': ' label-light-success'},
              'tidak': {'title': 'Tidak', 'class': ' label-light-danger'},
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

    $('#modalDeleteDriver').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.drivers.index") }}/'+ id);
    });
    $('#modalDeleteDriver').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
    });
    $('#modalDeleteTransport').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.transports.index") }}/'+ id);
    });
    $('#modalDeleteTransport').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
    });

    $("#formDeleteDriver").click(function(e){
      e.preventDefault();
      var form 	    = $(this);
      var url 	    = $('#modalDeleteDriver').find('a[name="id"]').attr('href');
      var btnHtml   = form.html();
      var spinner   = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
      $.ajax({
        beforeSend:function() {
          form.text(' Loading. . .').prepend(spinner);
        },
        type: 'DELETE',
        url: url,
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
          if(response.status == "success"){
            toastr.success(response.message,'Success !');
            form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            $('#modalDeleteDriver').modal('hide');
            dataTableDriver.draw();
          }else{
            toastr.error(response.message,'Failed !');
            form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            $('#modalDeleteDriver').modal('hide');
          }

        },
        error: function (response) {
          toastr.error(response.responseJSON.message ,'Failed !');
          form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
          $('#modalDeleteDriver').modal('hide');
          $('#modalDeleteDriver').find('a[name="id"]').attr('href', '');
        }
      });
    });
    $("#formDeleteTransport").click(function(e){
      e.preventDefault();
      var form 	    = $(this);
      var url 	    = $('#modalDeleteTransport').find('a[name="id"]').attr('href');
      var btnHtml   = form.html();
      var spinner   = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
      $.ajax({
          beforeSend:function() {
            form.text(' Loading. . .').prepend(spinner);
          },
          type: 'DELETE',
          url: url,
          dataType: 'json',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function (response) {
            if(response.status == "success"){
              toastr.success(response.message,'Success !');
              form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
              $('#modalDeleteTransport').modal('hide');
              dataTableTransport.draw();
            }else{
              toastr.error(response.message,'Failed !');
              form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
              $('#modalDeleteTransport').modal('hide');
            }
          },
          error: function (response) {
            toastr.error(response.responseJSON.message ,'Failed !');
            form.text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            $('#modalDeleteTransport').modal('hide');
            $('#modalDeleteTransport').find('a[name="id"]').attr('href', '');
          }
      });
    });
  });
</script>
@endsection
