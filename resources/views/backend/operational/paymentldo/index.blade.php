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
                <select class="form-control" id="select2AnotherExpedition">
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
                <select class="form-control" id="select2Transport">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Costumer:</label>
                <select class="form-control" id="select2Costumer">
                </select>
              </div>
            </div>
          </div>
          <div class="row align-items-center">
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Muatan:</label>
                <select class="form-control" id="select2Cargo">
                </select>
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Tanggal Mulai:</label>
                <input id="dateBegin" readonly type="text" class="form-control datepicker" placeholder="Cari Tanggal"
                  style="width:100% !important">
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Tanggal Selesai:</label>
                <input id="dateEnd" name="date_end" readonly type="text" class="form-control datepicker"
                  placeholder="Cari Tanggal" style="width:100% !important">
              </div>
            </div>
            <div class="col-md-3 my-md-0">
              <div class="form-group">
                <label>Status Pembayaran:</label>
                <select class="form-control" id="selectStatusSalary">
                  <option value="">Pilih Status</option>
                  <option value="0">Belum dibayar</option>
                  <option value="1">Sudah Dibayar</option>
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
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>LDO</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Pelanggan</th>
          <th>Muatan</th>
          <th>Tanggal Mulai</th>
          <th>Tanggal Selesai</th>
          <th>Status Pembayaran</th>
          <th>Tagihan</th>
          <th>Created At</th>
        </tr>
      </thead>
    </table>
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
      order: [[11, 'desc']],
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
      pageLength: 10,
      ajax: {
        url: "{{ route('backend.paymentldo.index') }}",
        data: function(d){
          d.another_expedition_id = $('#select2AnotherExpedition').find(':selected').val();
          d.driver_id = $('#select2Driver').find(':selected').val();
          d.transport_id = $('#select2Transport').find(':selected').val();
          d.costumer_id = $('#select2Costumer').find(':selected').val();
          d.cargo_id = $('#select2Cargo').find(':selected').val();
          d.route_from = $('#select2RouteFrom').find(':selected').val();
          d.route_to = $('#select2RouteTo').find(':selected').val();
          d.date_begin = $('#dateBegin').val();
          d.date_end = $('#dateEnd').val();
          d.status_cargo = $('#selectStatus').find(':selected').val();
          d.status_salary = $('#selectStatusSalary').find(':selected').val();
        }
      },
      columns: [
          {data: 'prefix', name: 'prefix'},
          {data: 'num_bill', name: 'num_bill'},
          {data: 'anotherexpedition.name', name: 'anotherexpedition.name'},
          {data: 'driver.name', name: 'driver.name'},
          {data: 'transport.num_pol', name: 'transport.num_pol'},
          {data: 'costumer.name', name: 'costumer.name'},
          {data: 'cargo.name', name: 'cargo.name'},
          {data: 'date_begin', name: 'date_begin'},
          {data: 'date_end', name: 'date_end', defaultContent: ''},
          {data: 'status_payment_ldo', name: 'status_payment_ldo',},
          {data: 'total_netto_ldo', name: 'total_netto_ldo', orderable: false, searchable: false, defaultContent: '', render: $.fn.dataTable.render.number( ',', '.', 2)},
          {data: 'created_at', name: 'created_at'},
      ],
      columnDefs: [
        {
          className: 'dt-center',
          targets: 9,
          width: '75px',
          render: function(data, type, full, meta) {
            var status = {
              0: {'title': 'Unapid', 'class': ' label-light-danger'},
              1: {'title': 'Paid', 'class': ' label-light-success'},
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

    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      todayHighlight: !0,
      todayBtn: "linked",
      clearBtn: !0,
    }).on('change', function(){
      dataTable.draw();
    });

    $("#select2AnotherExpedition").select2({
      placeholder: "Search LDO",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.anotherexpedition.select2') }}",
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
    $("#select2Driver").select2({
      placeholder: "Search Supir",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.drivers.select2self') }}",
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
    $("#select2Transport").select2({
      placeholder: "Search Kendaraan",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.transports.select2self') }}",
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
    $("#select2Costumer").select2({
      placeholder: "Search Pelanggan",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.costumers.select2') }}",
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
    $("#select2Cargo").select2({
      placeholder: "Search Muatan",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.cargos.select2') }}",
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
    $("#select2RouteFrom").select2({
      placeholder: "Search Rute Dari",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.routes.select2') }}",
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
    $("#select2RouteTo").select2({
      placeholder: "Search Rute Ke",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.routes.select2') }}",
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
    $('#selectStatus').on('change', function(){
      dataTable.draw();
    })

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.brands.index") }}/'+ id);
    });
    $('#modalDelete').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
    });
    $('#modalEdit').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      var status_cargo = $(event.relatedTarget).data('status_cargo');
      var date_end = $(event.relatedTarget).data('date_end');
      $(this).find('#formUpdate').attr('action', '{{ route("backend.joborders.index") }}/'+id)
      $(this).find('.modal-body').find('select[name="status_cargo"]').val(status_cargo);
      $(this).find('.modal-body').find('input[name="date_end"]').val(date_end);
    });
    $('#modalEdit').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('select[name="status_cargo"]').val('');
      $(this).find('.modal-body').find('input[name="date_end"]').val('');
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
