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
    <div id="TampungId">
    </div>
    <div class="card-body">
      <div class="mb-10">
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Tanggal Invoice:</label>
                  <div class="col-md-8">
                    <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date" placeholder="Tanggal Invoice" readonly>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Supir:</label>
                  <div class="col-lg-8">
                    <select name="driver_id" class="form-control" id="select2Driver">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Master Akun:</label>
                  <div class="col-lg-8">
                    <select name="coa_id" class="form-control rounded-0">
                      @foreach($selectCoa->coa as $item)
                        <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">No. Slip Gaji:</label>
                  <div class="col-lg-8">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <table id="table_invoice" class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" class="text-center">No</th>
                  <th scope="col">Tanggal Muat</th>
                  <th scope="col">S. Jalan</th>
                  <th scope="col">No. Polisi</th>
                  <th scope="col">Muatan</th>
                  <th scope="col">Rute</th>
                  <th scope="col" class="text-right">Jumlah</th>
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

<div class="card card-custom mt-10">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title'] }}
        <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
    </div>
    <div class="card-toolbar">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <button id="submitAppend" class="btn btn-primary">Masukan Ke Form Gaji</button>
    </div>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-hover" id="Datatable">
      <thead>
        <tr>
          <th></th>
          <th>Tanggal Muat</th>
          <th>No. Job Order</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Rute Awal</th>
          <th>Rute Tujuan</th>
          <th>Gaji</th>
          <th>Created At</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/backend/datatables/dataTables.checkboxes.css') }}" rel="stylesheet" type="text/css" />
<style>
  .select2-container--default .select2-selection--single {
    border-radius: 0 !important;
  }
</style>
@endsection

@section('scripts')
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/backend/datatables/dataTables.checkboxes.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function(){
    let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[8, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ url()->current() }}",
          data: function(d){
            d.driver_id = $('#select2Driver').find(':selected').val();
          }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'num_bill', name: 'num_bill'},
            {data: 'driver.name', name: 'driver.name'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'routefrom.name', name: 'routefrom.name'},
            {data: 'routeto.name', name: 'routeto.name'},
            {
              data: 'total_salary',
              name: 'total_salary',
              render: $.fn.dataTable.render.number( '.', '.', 2),
              orderable:false,
              searchable: false},
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
        let dataSelected = [];
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
            $('#TampungId').empty();
            let total = 0;
            $.each(response.data, function(index, data){
              total += parseFloat(data.total_salary);
              $('#TampungId').append('<input type="hidden" name="job_order_id[]" value="'+data.id+'">');
              $('#table_invoice tbody').append('<tr>'+
              ' <td class="text-center">'+(index+1)+'</td>'+
              ' <td>'+data.date_begin+'</td>'+
              ' <td>'+data.num_bill+'</td>'+
              ' <td>'+data.transport.num_pol+'</td>'+
              ' <td>'+data.cargo.name+'</td>'+
              ' <td>'+data.routefrom.name+' - '+data.routeto.name+'</td>'+
              ' <td class="text-right money">'+data.total_salary+'</td>'+
              '</tr>');
            });
            $('#TampungId').append('<input type="hidden" name="grand_total" value="'+total+'">');

            $('#table_invoice tfoot').append('<tr>'+
              '<td colspan="6" class="text-right">Total</td>'+
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
      $('#table_invoice tbody').empty();
      $('#table_invoice tfoot').empty();
      $('#TampungId').empty();
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
      $('#table_invoice tbody').empty();
      $('#table_invoice tfoot').empty();
      $('#TampungId').empty();
    });

    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      todayBtn: "linked",
      clearBtn: true,
      todayHighlight: true
    });

    $("#formStore").submit(function(e) {
      e.preventDefault();
      let form = $(this);
      let btnSubmit = form.find("[type='submit']");
      let btnSubmitHtml = btnSubmit.html();
      let url = form.attr("action");
      let data = new FormData(this);
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
            toastr.error((response.message ? response.message : "Please complete your form"), 'Failed !');
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
