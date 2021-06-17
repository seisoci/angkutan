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
    <!--begin: Datatable-->
    <table class="table table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Nama Spare part</th>
          <th>Unit</th>
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
    let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[0, 'asc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.stocks.index') }}",
        columns: [
            {data: 'name', name: 'spareparts.name'},
            {data: 'qty', name: 'qty'},
        ],
    });

  });
</script>
@endsection
