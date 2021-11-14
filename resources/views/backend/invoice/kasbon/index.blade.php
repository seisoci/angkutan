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
    <div class="mb-10">
      <div class="row align-items-center">
        <div class="col-12 mb-10">
          <div class="alert alert-custom alert-outline-primary fade show mb-5">
            <div class="alert-icon"><i class="flaticon-warning"></i></div>
            <div class="d-flex flex-column">
              <h4>Sisa Saldo</h4>
              @foreach($saldoGroup as $item)
                <div><b>{{ $item['name'] }} : <span class="text-success">{{ number_format($item['balance'], 2,'.',',') }}</span></b></div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--begin: Datatable-->
    <table class="table table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Total Hutang</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
{{-- Modal --}}
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create {{ $config['page_title'] }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i aria-hidden="true" class="ki ki-close"></i>
        </button>
      </div>
      <form id="formStore" action="{{ route('backend.kasbon.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group" style="display:none;">
            <div class="alert alert-custom alert-light-danger" role="alert">
              <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
              <div class="alert-text">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Supir</label>
            <select id="select2Driver" name="driver_id" class="form-control" style="width:100%">
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="text" name="date_payment" class="form-control w-100 datepicker" readonly/>
          </div>
          <div class="form-group">
            <label>Master Akun</label>
            <select name="coa_id" class="form-control">
              @foreach($selectCoa->coa as $item)
                <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
            @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Jenis Kasbon</label>
            <select class="form-control" name="type">
              <option value="">Pilih Jenis Kasbon</option>
              <option value="hutang">Hutang</option>
              <option value="pembayaran">Bayar</option>
            </select>
          </div>
          <div class="form-group">
            <label>Nominal</label>
            <input type="text" name="amount" class="form-control currency" placeholder="Nominal Kasbon" />
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea type="text" name="description" class="form-control" rows="5" placeholder="Keterangan"></textarea>
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
      removeMaskOnSubmit: true,
      autoUnmask: true,
      allowMinus: false,
    });

    let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.kasbon.index') }}",
        columns: [
            {data: 'driver.name', name: 'driver.name'},
            {data: 'amount', name: 'amount', render: $.fn.dataTable.render.number( '.', '.', 2), className: 'dt-right'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'},
        ],
    });

    $('#modalCreate').on('show.bs.modal', function (event) {
    });
    $('#modalCreate').on('hidden.bs.modal', function (event) {
      $("#select2Driver").val('').trigger('change');
      $(this).find('.modal-body').find('input[name="amount"]').val('');
      $(this).find('.modal-body').find('textarea[name="description"]').val('');
    });

    $("#select2Driver").select2({
      placeholder: "Search Supir",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.drivers.select2self') }}",
          dataType: "json",
          cache: true,
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    });

    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      todayBtn: "linked",
      clearBtn: true,
      todayHighlight: true,
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
          if (response.status === "success") {
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
            toastr.error(( response.message|| "Please complete your form"), 'Failed !');
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

  });
</script>
@endsection
