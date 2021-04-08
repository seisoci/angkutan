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
      <!--begin::Button-->
      <a href="{{ route('backend.spareparts.create') }}" class="btn btn-primary font-weight-bolder">
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
        <div class="col-md-3 my-2 my-md-0">
          <div class="form-group">
            <label>Supplier:</label>
            <select class="form-control" id="select2Suppliers">
            </select>
          </div>
        </div>
        <div class="col-md-3 my-2 my-md-0">
          <div class="form-group">
            <label>Brand:</label>
            <select class="form-control" id="select2Brands">
            </select>
          </div>
        </div>
        <div class="col-md-3 my-2 my-md-0">
          <div class="form-group">
            <label>Kategori:</label>
            <select class="form-control" id="select2Categories">
            </select>
          </div>
        </div>
      </div>
    </div>
    <!--begin: Datatable-->
    <table class="table table-bordered table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Nama</th>
          <th>Supplier</th>
          <th>Harga</th>
          <th>Jumlah</th>
          <th>Total</th>
          <th>Brand</th>
          <th>Kategori</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
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
      order: [[8, 'desc']],
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
      pageLength: 10,
      ajax: {
        url: "{{ route('backend.spareparts.index') }}",
        data: function(d){
          d.supplier_sparepart_id = $('#select2Suppliers').find(':selected').val();
          d.brand_id = $('#select2Brands').find(':selected').val();
          d.category_id = $('#select2Categories').find(':selected').val();
        }
      },
      columns: [
          {data: 'image', name: 'image', searchable: false},
          {data: 'name', name: 'name'},
          {data: 'supplier.name', name: 'supplier.name'},
          {data: 'price', name: 'price', render: $.fn.dataTable.render.number( '.', '.', 0)},
          {data: 'qty', name: 'qty'},
          {data: 'amount', name: 'amount', render: $.fn.dataTable.render.number( '.', '.', 0)},
          {data: 'brand.name', name: 'brand.name'},
          {data: 'categories[,].name', name: 'created_at', orderable: false},
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
              <img width="100px" height="100px" src="` + data + `" alt="photo">
              `
            return output;
          }
        },
        {
          targets: 7,
          render: function(data, type, full, meta) {
            let array = data.split(',');
            let output = '';
            $.each(array, function(key, value) {
                if(value){
                  output += `
                  <span class="label label-lg font-weight-bold label-light-success label-inline my-1">`+value+`</span>
                  `;
                }
            });
            return output;
          }
        },
      ],
    });

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.spareparts.index") }}/'+ id);
    });
    $('#modalDelete').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
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

    $("#select2Brands").select2({
      placeholder: "Search Brands",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.brands.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
      dataTable.draw();
    });

    $("#select2Suppliers").select2({
      placeholder: "Search Suppliers",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.supplierspareparts.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
      dataTable.draw();
    });

    $("#select2Categories").select2({
      placeholder: "Search Categories",
      allowClear: true,
      tags: true,
      ajax: {
          url: "{{ route('backend.categories.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
      createTag: function(params) {
      return undefined;
      }
    }).on('change', function (e){
      dataTable.draw();
    });
  });
</script>
@endsection
