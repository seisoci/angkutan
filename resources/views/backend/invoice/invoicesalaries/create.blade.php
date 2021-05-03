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
  </div>
  <form id="formStore" action="{{ route('backend.invoicesalaries.store') }}">
    @csrf
    <div id="JobOrderId">
    </div>
    <div id="KasbonId">
    </div>
    <div class="card-body">
      <div class="mb-10">
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Prefix:</label>
                  <div class="col-lg-6">
                    <select name="prefix" class="form-control" id="select2Prefix">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">No. Slip Gaji:</label>
                  <div class="col-lg-6">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Supir:</label>
                  <div class="col-lg-6">
                    <select name="driver_id" class="form-control" id="select2Driver">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Kendaraan:</label>
                  <div class="col-lg-6">
                    <select name="transport_id" class="form-control" id="select2Transport">
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <h4 class="mt-10"><u>Gaji</u></h4>
            <div class="separator separator-solid separator-border-1"></div>
            <table id="table_invoice" class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" class="text-center" style="width: 5%">#</th>
                  <th scope="col" style="width: 20%">Tanggal</th>
                  <th scope="col" style="width: 20%">S. Jalan</th>
                  <th scope="col" style="width: 20%">No. Polisi</th>
                  <th scope="col" class="text-right" style="width: 15%">Jumlah</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
              </tfoot>
            </table>
            <h4 class="mt-10"><u>Kasbon</u></h4>
            <div class="separator separator-solid separator-border-1"></div>
            <table id="table_Kasbon" class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" class="text-center" style="width: 5%">#</th>
                  <th scope="col" style="width: 20%">Tanggal</th>
                  <th scope="col" style="width: 20%">Keterangan</th>
                  <th style="width: 20%"></th>
                  <th scope="col" class="text-right" style="width: 15%">Jumlah</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
              </tfoot>
            </table>
            <div class="float-right">
              <label for="" class="font-weight-bolder">Grand Total Net: </label>
              <input type="text" class="form-control form-control-solid grandtotalnetto money" disabled>
              <input type="hidden" name="grandtotalnetto" class="form-control form-control-solid grandtotalnetto money">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
      <button type="submit" class="btn btn-primary mr-2">Buat Invoice</button>
    </div>
  </form>
</div>

{{-- DataTables Gaji --}}
<div class="card card-custom mt-10">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">Gaji Supir
        <span class="d-block text-muted pt-2 font-size-sm">List Gaji Supir</span></h3>
    </div>
    <div class="card-toolbar">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <button id="submitAppend" class="btn btn-primary">Masukan Ke Form Gaji</button>
    </div>
  </div>
  <div class="card-body">
    <!--begin: Datatable-->
    <table class="table table-bordered table-hover" id="Datatable">
      <thead>
        <tr>
          <th></th>
          <th>Tanggal Mulai</th>
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Gaji</th>
          <th>Created At</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

{{-- DataTables Kasbon --}}
<div class="card card-custom mt-10">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">Kasbon Supir
        <span class="d-block text-muted pt-2 font-size-sm">Kasbon Supir</span></h3>
    </div>
    <div class="card-toolbar">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <button id="submitKasbonAppend" class="btn btn-primary">Masukan Ke Form Gaji</button>
    </div>
  </div>
  <div class="card-body">
    <!--begin: Datatable-->
    <table class="table table-bordered table-hover" id="DatatableKasbon">
      <thead>
        <tr>
          <th></th>
          <th>Tanggal Kasbon</th>
          <th>Nama Supir</th>
          <th>Jumlah</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/backend/datatables/dataTables.checkboxes.css') }}" rel="stylesheet" type="text/css" />
<style>
  .select2-container--default .select2-selection--single {
    border-radius: 0 !important;
  }
</style>
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/backend/datatables/dataTables.checkboxes.js') }}" type="text/javascript"></script>
{{-- page scripts --}}
<script type="text/javascript">
  $(document).ready(function(){
    var dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[7, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicesalaries.create') }}",
          data: function(d){
            d.driver_id = $('#select2Driver').find(':selected').val();
            d.transport_id = $('#select2Transport').find(':selected').val();
          }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'prefix', name: 'prefix'},
            {data: 'num_bill', name: 'num_bill'},
            {data: 'driver.name', name: 'driver.name'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'total_salary', name: 'total_salary', render: $.fn.dataTable.render.number( '.', '.', 2), className: 'dt-right', orderable:false, searchable: false},
            {data: 'created_at', name: 'created_at'},
        ],
        columnDefs: [
          {
            targets: 0,
            checkboxes: {
              selectRow: true
            }
          },
        ],
        select: {
          style: 'multi'
        },
    });

    var dataTableKasbon = $('#DatatableKasbon').DataTable({
      responsive: false,
      scrollX: true,
      processing: true,
      serverSide: true,
      order: [[1, 'desc']],
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
      pageLength: 10,
      ajax: {
        url: "{{ route('backend.invoicesalaries.datatablekasbon') }}",
        data: function(d){
          d.driver_id = $('#select2Driver').find(':selected').val();
        }
      },
      columns: [
          {data: 'id', name: 'id'},
          {data: 'created_at', name: 'created_at'},
          {data: 'driver.name', name: 'driver.name'},
          {data: 'amount', name: 'amount', render: $.fn.dataTable.render.number( '.', '.', 2), className: 'dt-right'},
      ],
      columnDefs: [
        {
          targets: 0,
          checkboxes: {
            selectRow: true
          }
        },
      ],
      select: {
        style: 'multi'
      },
    });

    function calculateGrandTotal(){
      let total_gaji = parseInt($('input[name=grand_total_gaji]').val()) || 0;
      let total_kasbon = parseInt($('input[name=grand_total_kasbon]').val()) || 0;
      let grantotal_netto = total_gaji - total_kasbon;
      return grantotal_netto;
    }

    $('#submitAppend').on('click', function(e){
        e.preventDefault();
        let selected = dataTable.column(0).checkboxes.selected();
        var dataSelected = [];
        $.each(selected, function(index, data){
          dataSelected.push(data);
        });

      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:'POST',
        url: "{{ route('backend.invoicesalaries.findbypk') }}",
        data: {data: JSON.stringify(dataSelected)},
        success:function(response) {
          if(response.data){
            $('#table_invoice tbody').empty();
            $('#table_invoice tfoot').empty();
            $('#JobOrderId').empty();
            var total = 0;
            $.each(response.data, function(index, data){
              total += data.total_salary;
              $('#JobOrderId').append('<input type="hidden" name="job_order_id[]" value="'+data.id+'">');
              $('#table_invoice tbody').append('<tr>'+
              ' <td class="text-center">'+(index+1)+'</td>'+
              ' <td>'+data.date_begin+'</td>'+
              ' <td>'+data.prefix+'-'+data.num_bill+'</td>'+
              ' <td>'+data.transport.num_pol+'</td>'+
              ' <td class="text-right money">'+data.total_salary+'</td>'+
              '</tr>');
            });
            $('#JobOrderId').append('<input type="hidden" name="grand_total_gaji" value="'+total+'">');

            $('#table_invoice tfoot').append('<tr>'+
              '<td colspan="4" class="text-right">Total</td>'+
              '<td class="text-right money">'+total+'</td>'+
              '</tr>');

            $(".money").inputmask({
              'alias': 'decimal',
              'groupSeparator': ',',
              'autoGroup': true,
              'digits': 2,
              'digitsOptional': false,
            })
            $('input[name=grand_total_netto]').val(calculateGrandTotal());
            $('.grandtotalnetto').val(calculateGrandTotal());
          }
        }
      });
    });


    $('#submitKasbonAppend').on('click', function(e){
      e.preventDefault();
      let selected = dataTableKasbon.column(0).checkboxes.selected();
      var dataSelected = [];
      $.each(selected, function(index, data){
        dataSelected.push(data);
      });
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:'POST',
        url: "{{ route('backend.invoicesalaries.findbypkkasbon') }}",
        data: {data: JSON.stringify(dataSelected)},
        success:function(response) {
          if(response.data){
            $('#table_Kasbon tbody').empty();
            $('#table_Kasbon tfoot').empty();
            $('#KasbonId').empty();
            var total_kasbon = 0;
            $.each(response.data, function(index, data){
              console.log(total_kasbon);
              total_kasbon += parseInt(data.amount);
              $('#KasbonId').append('<input type="hidden" name="kasbon_id[]" value="'+data.id+'">');
              $('#table_Kasbon tbody').append('<tr>'+
              ' <td class="text-center">'+(index+1)+'</td>'+
              ' <td>'+data.created_at+'</td>'+
              ' <td colspan="2">Kasbon</td>'+
              ' <td class="text-right money text-danger">'+data.amount+'</td>'+
              '</tr>');
            });
            $('#KasbonId').append('<input type="hidden" name="grand_total_kasbon" value="'+total_kasbon+'">');

            $('#table_Kasbon tfoot').append('<tr>'+
              '<td colspan="4" class="text-right">Total</td>'+
              '<td class="text-right money">'+total_kasbon+'</td>'+
              '</tr>');

            $(".money").inputmask({
              'alias': 'decimal',
              'groupSeparator': ',',
              'autoGroup': true,
              'digits': 2,
              'digitsOptional': false,
            });
            $('input[name=grand_total_netto]').val(calculateGrandTotal());
            $('.grandtotalnetto').val(calculateGrandTotal());
          }
        }
      });
    });

    $('#statusCargoModal').on('change', function(){
      if(this.value == 'selesai'){
        $("#dateEndModal").parent().css("display", "block");
        $("#dateEndModal").parent().find('label').css("display", "block");
      }else{
        $("#dateEndModal").parent().css("display", "none");
        $("#dateEndModal").parent().find('label').css("display", "none");
      }
    });

    $("#select2Prefix").select2({
      placeholder: "Choose Prefix",
      allowClear: true,
      ajax: {
        url: "{{ route('backend.prefixes.select2') }}",
        dataType: "json",
        delay: 250,
        cache: true,
        data: function(e) {
          return {
            type: 'operational',
            q: e.term || '',
            page: e.page || 1
          }
        },
      },
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
      dataTableKasbon.draw();
      $('#table_invoice tbody').empty();
      $('#table_invoice tfoot').empty();
      $('#JobOrderId').empty();
      $('#table_Kasbon tbody').empty();
      $('#table_Kasbon tfoot').empty();
      $('#KasbonId').empty();
      $('.grandtotalnetto').val('');
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
      dataTableKasbon.draw();
      $('#table_invoice tbody').empty();
      $('#table_invoice tfoot').empty();
      $('#JobOrderId').empty();
      $('#table_Kasbon tbody').empty();
      $('#table_Kasbon tfoot').empty();
      $('#KasbonId').empty();
      $('.grandtotalnetto').val('');
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
            setTimeout(function() {
              if(response.redirect == "" || response.redirect == "reload"){
								location.reload();
							} else {
								location.href = response.redirect;
							}
            }, 1000);
          } else {
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each(response.error, function(key, value) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error(response.message || "Please complete your form", 'Failed !');
          }
        },
        error: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });
  });
</script>
@endsection
