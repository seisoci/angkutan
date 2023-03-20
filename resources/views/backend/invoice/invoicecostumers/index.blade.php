{{-- Extends layout --}}
@extends('layout.default')

@section('content')
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <a href="{{ route('backend.invoicecostumers.create') }}" class="btn btn-primary font-weight-bolder">
        <span class="svg-icon svg-icon-md">
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
        </span>New Record</a>
      </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="Datatable">
            <thead>
            <tr>
              <th></th>
              <th>Invoice Number</th>
              <th>Tgl Invoice</th>
              <th>Tgl Jth. Tempo Invoice</th>
              <th>Nama Pelanggan</th>
              <th>Total Tagihan</th>
              <th>Total Pembayaran</th>
              <th>Pajak (Rp.)</th>
              <th>Potongan Klaim</th>
              <th>Sisa Tagihan</th>
              <th>Created At</th>
              <th>Action</th>
            </tr>
            </thead>
          </table>
        </div>
    </div>
    <div class="modal fade" id="modalEditTax" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Bayar Seluruh Pajak ?</h5>
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
              <div class="form-group">
                <input type="hidden" name="type" value="tax">
                <label>Master Akun</label>
                <select name="coa_id" class="form-control" style="width: 100%">
                  @foreach($selectCoa->coa as $item)
                    <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                  @endforeach
                </select>
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
    <div class="modal fade" id="modalEditFee" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Bayar Seluruh Fee ?</h5>
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
              <div class="form-group">
                <input type="hidden" name="type" value="fee">
                <label>Master Akun</label>
                <select name="coa_id" class="form-control" style="width: 100%">
                  @foreach($selectCoa->coa as $item)
                    <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                  @endforeach
                </select>
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
@endsection

@section('styles')
  <link href="{{ asset('css/backend/datatables/dataTables.control.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script id="details-template" type="text/x-handlebars-template">
    @verbatim
    <table class="table table-bordered " id="posts-{{id}}">
      <thead>
      <tr>
        <th>No. Surat Jalan</th>
        <th>Total Tagihan</th>
      </tr>
      </thead>
    </table>
    @endverbatim
  </script>
  <script type="text/javascript">
    $(function () {
      let template = Handlebars.compile($("#details-template").html());
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[10, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        stateSave: true,
        dom: 'Bfrtip',
        buttons: [
          'colvis'
        ],
        ajax: "{{ route('backend.invoicecostumers.index') }}",
        columns: [
          {
            "className": 'details-control',
            "orderable": false,
            "searchable": false,
            "data": null,
            "defaultContent": ''
          },
          {data: 'num_invoice', name: 'num_bill', orderable: false},
          {data: 'invoice_date', name: 'invoice_date'},
          {data: 'due_date', name: 'due_date'},
          {data: 'costumer.name', name: 'costumer.name'},
          {
            data: 'total_bill',
            name: 'total_bill',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_payment',
            name: 'total_payment',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_tax',
            name: 'total_tax',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_cut',
            name: 'total_cut',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'rest_payment',
            name: 'rest_payment',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
      });

      $('#Datatable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dataTable.row(tr);
        let tableId = 'posts-' + row.data().id;

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
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
          ajax: data.details_url,
          columns: [
            {data: 'num_bill', name: 'num_bill', orderable: false},
            {
              data: 'total_basic_price',
              name: 'total_basic_price',
              render: $.fn.dataTable.render.number(',', '.', 2),
              orderable: false,
              searchable: false,
              className: 'dt-right'
            }
          ]
        })
      }

      $('#modalEditTax').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        let name = $(event.relatedTarget).data('name');
        $(this).find('.formUpdate').attr('action', 'invoicecostumerstaxfee/'+id)
        $(this).find('.modal-body').find('input[name="name"]').val(name);
      });
      $('#modalEditTax').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="name"]').val('');
      });

      $('#modalEditFee').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        let name = $(event.relatedTarget).data('name');
        $(this).find('.formUpdate').attr('action', 'invoicecostumerstaxfee/'+id)
        $(this).find('.modal-body').find('input[name="name"]').val(name);
      });
      $('#modalEditFee').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="name"]').val('');
      });

      $(".formUpdate").submit(function(e){
        e.preventDefault();
        let form 	= $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url 	= form.attr("action");
        let data 	= new FormData(this);
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
            if (response.status === "success" ){
              toastr.success(response.message,'Success !');
              $('#modalEditTax').modal('hide');
              $('#modalEditFee').modal('hide');
              dataTable.draw();
              $("[role='alert']").parent().css("display", "none");
            }else{
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each( response.error, function( key, value ) {
                $(".alert-text").append('<span style="display: block">'+value+'</span>');
              });
              toastr.error(( response.message|| "Please complete your form"),'Failed !');
            }
          },error: function(response){
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalEditTax').modal('hide');
            $('#modalEditTax').find('a[name="id"]').attr('href', '');
            $('#modalEditFee').modal('hide');
            $('#modalEditFee').find('a[name="id"]').attr('href', '');
          }
        });
      });

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.invoicecostumers.index") }}/'+ id);
      });
      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
      });

      $("#formDelete").click(function(e){
        e.preventDefault();
        let form 	    = $(this);
        let url 	    = $('#modalDelete').find('a[name="id"]').attr('href');
        let btnHtml   = form.html();
        let spinner   = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
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
