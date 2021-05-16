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
      <a href="{{ route('backend.transports.create') }}" class="btn btn-primary font-weight-bolder">
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
    <table class="table table-bordered table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Image</th>
          <th>No. Pol</th>
          <th>Merk</th>
          <th>Tipe</th>
          <th>Jenis Mobil</th>
          <th>Tahun</th>
          <th>Expired STNK</th>
          <th>Expired KIR</th>
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
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Kendaraan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i aria-hidden="true" class="ki ki-close"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group" style="display:none;">
          <div class="alert alert-custom alert-light-danger" role="alert">
            <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
            <div class="alert-text">
            </div>
          </div>
        </div>
        <div class="form-group">
          <img name="photo" width="100%" height="20%">
        </div>
        <div class="form-group row">
          <label class="col-md-3">No Polisi</label>
          <input type="text" name="num_pol" class="form-control form-control-solid col-md-9" disabled />
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Merk</label>
              <input type="text" name="merk" class="form-control form-control-solid" disabled />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Tipe</label>
              <input type="text" name="type" class="form-control form-control-solid " disabled />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Jenis Mobil</label>
              <input type="text" name="type_car" class="form-control form-control-solid" disabled />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Max Muatan</label>
              <input type="text" name="max_weight" class="form-control form-control-solid " disabled />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Tahun</label>
              <input type="text" name="year" class="form-control form-control-solid " disabled />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Tgl Berlaku STNK</label>
              <input type="text" name="expired_stnk" class="form-control form-control-solid " disabled />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Expired KIR</label>
              <input type="text" name="expired_kir" class="form-control form-control-solid " disabled />
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <textarea name="description" rows="5" class="form-control form-control-solid" disabled></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
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
    var dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[8, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.transports.index') }}",
        columns: [
            {data: 'image', name: 'image', searchable: false},
            {data: 'num_pol', name: 'num_pol'},
            {data: 'merk', name: 'merk'},
            {data: 'type', name: 'type'},
            {data: 'type_car', name: 'type_car'},
            {data: 'year', name: 'year'},
            {data: 'expired_stnk', name: 'expired_stnk'},
            {data: 'expired_kir', name: 'expired_kir'},
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
          targets: 4,
          width: '150px',
          render: function(data, type, full, meta) {
            var status = {
              'engkel': {'title': 'Engkel (Kecil)', 'class': ' label-light-info'},
              'tronton': {'title': 'Tronton (Besar)', 'class': ' label-light-primary'},
            };
            if (typeof status[data] === 'undefined') {
              return data;
            }
            return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
              '</span>';
          },
        },
        {
          className: 'dt-center',
          targets: 7,
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

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.transports.index") }}/'+ id);
    });
    $('#modalDelete').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
    });
    $('#modalShow').on('show.bs.modal', function (event) {
      var num_pol = $(event.relatedTarget).data('num_pol');
      var merk = $(event.relatedTarget).data('merk');
      var type = $(event.relatedTarget).data('type');
      var type_car = $(event.relatedTarget).data('type_car');
      var expired_kir = $(event.relatedTarget).data('expired_kir');
      var year = $(event.relatedTarget).data('year');
      var max_weight = $(event.relatedTarget).data('max_weight');
      var expired_stnk = $(event.relatedTarget).data('expired_stnk');
      var description = $(event.relatedTarget).data('description');
      var photo = $(event.relatedTarget).data('photo');
      $(this).find('.modal-body').find('input[name="num_pol"]').val(num_pol);
      $(this).find('.modal-body').find('input[name="merk"]').val(merk);
      $(this).find('.modal-body').find('input[name="type"]').val(type);
      $(this).find('.modal-body').find('input[name="type_car"]').val(type_car);
      $(this).find('.modal-body').find('input[name="expired_kir"]').val(expired_kir);
      $(this).find('.modal-body').find('input[name="year"]').val(year);
      $(this).find('.modal-body').find('input[name="max_weight"]').val(max_weight + ' Ton');
      $(this).find('.modal-body').find('input[name="expired_stnk"]').val(expired_stnk);
      $(this).find('.modal-body').find('textarea[name="description"]').val(description);
      let photos = photo ? '/images/thumbnail/' + photo : '/media/bg/no-content.svg';
      $(this).find('.modal-body').find('img[name="photo"]').attr('src', '' + photos);
    });
    $('#modalShow').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="num_pol"]').val('');
      $(this).find('.modal-body').find('input[name="merk"]').val('');
      $(this).find('.modal-body').find('input[name="type"]').val('');
      $(this).find('.modal-body').find('input[name="type_car"]').val('');
      $(this).find('.modal-body').find('input[name="expired_kir"]').val('');
      $(this).find('.modal-body').find('input[name="year"]').val('');
      $(this).find('.modal-body').find('input[name="max_weight"]').val('');
      $(this).find('.modal-body').find('input[name="expired_stnk"]').val('');
      $(this).find('.modal-body').find('textarea[name="description"]').val('');
      $(this).find('.modal-body').find('img[name="photo"]').attr('src', '');
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

    function capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    }
  });
</script>
@endsection
