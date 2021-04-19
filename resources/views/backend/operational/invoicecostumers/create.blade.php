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
  <form id="formStore" action="{{ route('backend.invoicecostumers.store') }}">
    @csrf
    <div id="TampungId">
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
                  <label class="col-lg-3 col-form-label">No. Invoice Costumer:</label>
                  <div class="col-lg-6">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Pelanggan:</label>
                  <div class="col-lg-9">
                    <select name="costumer_id" class="form-control" id="select2Costumer">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Memo:</label>
                  <div class="col-lg-9">
                    <textarea name="memo" class="form-control rounded-0"></textarea>
                  </div>
                </div>
              </div>
            </div>
            <table id="table_invoice" class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" class="text-center">#</th>
                  <th scope="col">Tanggal</th>
                  <th scope="col">S. Jalan</th>
                  <th scope="col">Pelanggan</th>
                  <th scope="col">Rute Dari</th>
                  <th scope="col">Rute Ke</th>
                  <th scope="col">Jenis Barang</th>
                  <th scope="col" class="text-right">Tarif (Rp.)</th>
                  <th scope="col">Qty (Unit)</th>
                  <th scope="col" class="text-right">Total (Rp.)</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
      <button type="submit" class="btn btn-primary mr-2">Buat Invoice</button>
    </div>
  </form>
</div>

{{-- DataTables --}}
<div class="card card-custom mt-10">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title'] }}
        <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
    </div>
    <div class="card-toolbar">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <button id="submitAppend" class="btn btn-primary">Masukan Ke Form Invoice</button>
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
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Jenis Barang</th>
          <th>Ongkosan Dasar</th>
          <th>Qty (Unit)</th>
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
        order: [[11, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicecostumers.create') }}",
          data: function(d){
            d.costumer_id = $('#select2Costumer').find(':selected').val();
          }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'prefix', name: 'prefix'},
            {data: 'num_bill', name: 'num_bill'},
            {data: 'costumer.name', name: 'costumer.name'},
            {data: 'routefrom.name', name: 'routefrom.name'},
            {data: 'routeto.name', name: 'routeto.name'},
            {data: 'cargo.name', name: 'cargo.name'},
            {data: 'basic_price', name: 'basic_price', render: $.fn.dataTable.render.number( '.', '.', 2)},
            {data: 'payload', name: 'payload'},
            {data: 'invoice_bill', name: 'invoice_bill', render: $.fn.dataTable.render.number( '.', '.', 2)},
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
        url: "{{ route('backend.invoicecostumers.findbypk') }}",
        data: {data: JSON.stringify(dataSelected)},
        success:function(response) {
          if(response.data){
            $('#table_invoice tbody').empty();
            $('#table_invoice tfoot').empty();
            $('#TampungId').empty();
            var total = 0;
            $.each(response.data, function(index, data){
              total += parseFloat(data.invoice_bill);
              $('#TampungId').append('<input type="hidden" name="job_order_id[]" value="'+data.id+'">');
              $('#table_invoice tbody').append('<tr>'+
              ' <td class="text-center">'+(index+1)+'</td>'+
              ' <td>'+data.date_begin+'</td>'+
              ' <td>'+data.prefix+'-'+data.num_bill+'</td>'+
              ' <td>'+data.costumer.name+'</td>'+
              ' <td>'+data.routefrom.name+'</td>'+
              ' <td>'+data.routeto.name+'</td>'+
              ' <td>'+data.cargo.name+'</td>'+
              ' <td class="text-right money">'+data.basic_price+'</td>'+
              ' <td>'+data.payload+'</td>'+
              ' <td class="text-right money">'+data.invoice_bill+'</td>'+
              '</tr>');
            });
            $('#TampungId').append('<input type="hidden" name="grand_total" value="'+total+'">');

            $('#table_invoice tfoot').append('<tr>'+
              '<td colspan="9" class="text-right">Total</td>'+
              '<td class="text-right money">'+total+'</td>'+
              '</tr>');

            $(".money").inputmask({
              'alias': 'decimal',
              'groupSeparator': ',',
              'autoGroup': true,
              'digits': 2,
              'digitsOptional': false,
            });
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
      $('#table_invoice tbody').empty();
      $('#table_invoice tfoot').empty();
      $('#TampungId').empty();
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
            toastr.error("Please complete your form", 'Failed !');
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
