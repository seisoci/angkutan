{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<!--begin::Card-->
<div class="card card-custom gutter-b mb-8">
  <div class="card-header">
    <div class="card-title">
      <h3 class="card-label text-center">
        Laporan Rekapitulasi
      </h3>
    </div>
  </div>
  <form action="{{ route('backend.recapitulation.index') }}">
    <div class="card-body">
      @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">No. Polisi</label>
        <div class=" col-lg-4 col-md-9 col-sm-12">
          <select id="select2Transport" class="form-control" name="transport_id">
            @if ($transport && $transport != 'Semua Mobil')
            <option value="{{ $transport->id }}">{{ $transport->num_pol }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">Supir</label>
        <div class=" col-lg-4 col-md-9 col-sm-12">
          <select id="select2Driver" class="form-control" name="driver_id">
            @if ($driver && $driver != 'Semua Supir')
            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">Tanggal Mulai (Dari)</label>
        <div class="col-lg-4 col-md-9 col-sm-12">
          <div class="input-group date">
            <input type="text" class="form-control datepicker" readonly name="date_begin"
              value="{{ $date_begin ?? '' }}" />
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="la la-calendar"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">Tanggal Mulai (Sampai)</label>
        <div class="col-lg-4 col-md-9 col-sm-12">
          <div class="input-group date">
            <input type="text" class="form-control datepicker" readonly name="date_end" value="{{ $date_end ?? '' }}" />
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="la la-calendar"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
      <button type="submit" class="btn btn-primary font-weight-bold">Cari Data</a>
    </div>
  </form>
</div>
<div class="card card-custom">
  <div class="card-body">
    {{-- Laporan Pendapatan Mobil --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Pendapatan Mobil</u></h4>
        <p class="text-dark-75 font-weight-normal my-0">No. Polisi: {{ $transport->num_pol ?? $transport }}</p>
        <p class="text-dark-75 font-weight-normal my-0">Priode: {{ $date_begin ?? ''}} sd {{ $date_end ?? '' }}</p>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    @if(!empty($data))
    <table class="table table-bordered w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th class="text-center">Tanggal</th>
          <th class="text-center">S. Jalan</th>
          <th class="text-center">Pelanggan</th>
          <th class="text-center">Dari</th>
          <th class="text-center">Tujuan</th>
          <th class="text-center">Jenis Barang</th>
          <th class="text-center">Tarif(Rp.)</th>
          <th class="text-center">Qty(Unit)</th>
          <th class="text-center">Total(Rp.)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->routefrom->name }}</td>
          <td>{{ $item->routeto->name }}</td>
          <td>{{ $item->cargo->name }}</td>
          <td class="text-right">{{ number_format($item->basic_price, 2,'.', ',') }}</td>
          <td class="text-right">{{ $item->payload }}</td>
          <td class="text-right">{{ number_format($item->total_basic_price, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="9" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_basic_price'), 2, '.', ',') }}</td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Biaya Operasional --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Biaya Operasional</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    @foreach ($data as $item)
    @php $noOperational = 1; @endphp
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>Master Biaya</th>
          <th>Keterangan</th>
          <th class="text-right">Jumlah</th>
          <th>S. Jalan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center">{{ $noOperational++ }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>UANG JALAN</td>
          <td></td>
          <td class="text-right">{{ number_format($item->road_money, 2, '.', ',') }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
        </tr>
        @foreach ($item->operationalexpense as $itemExpense)
        <tr>
          <td class="text-center">{{ $noOperational++ }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $itemExpense->expense->name }}</td>
          <td>{{ $itemExpense->description }}</td>
          <td class="text-right">{{ number_format($itemExpense->amount, 2, '.', ',') }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-right">Sub Total Rp. </td>
          <td class="text-right">
            {{ number_format($item->total_operational, 2, '.', ',') }}</td>
          <td></td>
        </tr>
        @if($loop->last)
        <tr>
          <td colspan="4" class="text-right">Total Operational Rp. </td>
          <td class="text-right">
            {{ number_format($data->sum('total_operational'), 2, '.', ',') }}</td>
          <td></td>
        </tr>
        @endif
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    @endforeach
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Sparepart --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Sparepart</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>S. Jalan</th>
          <th>Nama Supir</th>
          <th>No. Polisi</th>
          <th class="text-right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td class="text-right">{{ number_format($item->total_sparepart, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_sparepart'), 2, '.', ',') }}
          </td>
        </tr>
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Gaji Supir --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Gaji Supir</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>S. Jalan</th>
          <th>Nama Supir</th>
          <th>No. Polisi</th>
          <th class="text-right">Gaji</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td class="text-right">{{ number_format($item->total_salary, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_salary'), 2, '.', ',') }}</td>
        </tr>
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    <table class="table w-full small">
      <tbody>
        <tr>
          <td>Total Pendapatan</td>
          <td class="text-right">{{ number_format($data->sum('total_basic_price'), 2, '.', ',') }}</td>
        </tr>
        <tr>
          <td>Total Biaya</td>
          <td class="text-right">
            {{ number_format(($data->sum('total_operational') + $data->sum('total_sparepart') + $data->sum('total_salary')), 2, '.', ',') }}
          </td>
        </tr>
        <tr>
          <td>Total Bersih</td>
          <td class="text-right">
            {{ number_format($data->sum('total_basic_price') - ($data->sum('total_operational') + $data->sum('total_sparepart') + $data->sum('total_salary')), 2, '.', ',') }}
          </td>
        </tr>
      </tbody>
      <tfoot>
      </tfoot>
    </table>
    @endif
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
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit {{ $config['page_title'] }}</h5>
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
          <div class="form-group">
            <label>Status Job Order:</label>
            <select class="form-control" name="status_cargo">
              <option value="mulai">Mulai</option>
              <option value="selesai">Selesai</option>
              <option value="batal">Batal</option>
            </select>
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
        order: [[9, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.salaries.index') }}",
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
            {data: 'driver.name', name: 'driver.name'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'costumer.name', name: 'costumer.name'},
            {data: 'cargo.name', name: 'cargo.name'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'date_end', name: 'date_end', defaultContent: ''},
            {data: 'status_salary', name: 'status_salary', defaultContent: ''},
            {data: 'total_salary', name: 'total_salary', defaultContent: '', render: $.fn.dataTable.render.number( '.', '.', 0)},
            {data: 'created_at', name: 'created_at'},
        ],
        columnDefs: [
        {
          className: 'dt-center',
          targets: 8,
          width: '75px',
          render: function(data, type, full, meta) {
            var status = {
              0: {'title': 'Belum dibayar', 'class': ' label-light-danger'},
              1: {'title': 'Sudah dibayar', 'class': ' label-light-success'},
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
      placeholder: "Search Driver",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.drivers.select2') }}",
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
          url: "{{ route('backend.transports.select2') }}",
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
          url: "{{ route('backend.transports.select2') }}",
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
