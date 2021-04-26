{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!-- begin::Card-->
<div class="card card-custom overflow-hidden">
  <div class="card-body p-0">
    <!-- begin: Invoice-->
    <!-- begin: Invoice header-->
    <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
      <div class="col-md-9">
        <div class="d-flex justify-content-between pb-10 pb-md-20 flex-column flex-md-row">
          @if ($data->type == 'self')
          <h1 class="display-4 font-weight-boldest mb-10">PENGAMBILAN<br> BARANG</h1>
          @elseif($data->type == 'outside')
          <h1 class="display-4 font-weight-boldest mb-10">PEMBELIAN<br> BARANG DILUAR</h1>
          @endif
          <div class="d-flex flex-column align-items-md-end px-0">
            <!--begin::Logo-->
            <a href="#" class="mb-5">
              <img
                src="{{ $profile['logo_url'] != NULL ? asset("/images/thumbnail/".$profile['logo_url']) : asset('media/bg/no-content.svg') }}"
                width="75px" height="75px" />
            </a>
            <!--end::Logo-->
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <span class="d-flex flex-column align-items-md-start opacity-70">
            <span class="font-weight-bold">NAMA: {{ $data->driver->name ?? '' }}</span>
            <span class="font-weight-bold">NO. POL: {{ $data->transport->num_pol ?? ''}}</span>
          </span>
          <span class=" d-flex flex-column align-items-md-end opacity-70">
            <span>{{ $profile['name'] ?? '' }}</span>
            <span>{{ $profile['telp'] ?? ''}}</span>
            <span>{{ $profile['email'] ?? '' }}</span>
            <span>{{ $profile['address'] ?? '' }}</span>
          </span>
        </div>
      </div>
    </div>
    <!-- end: Invoice header-->
    <!-- begin: Invoice body-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
      <div class="col-md-9">
        <div class="table-responsive">
          @if ($data->type == 'self')
          <table class="table">
            <thead>
              <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">Produk</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->usageitem as $item)
              <tr class="font-weight-normal">
                <td class="pl-0 pt-7">{{ $item->sparepart->name ?? '' }}</td>
                <td class="text-right pt-7">{{ $item->qty ?? ''}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @elseif($data->type == 'outside')
          <table class="table">
            <thead>
              <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">Produk</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Jumlah</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Harga</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->usageitem as $item)
              <tr class="font-weight-normal">
                <td class="pl-0 pt-7">{{ $item->name ?? '' }}</td>
                <td class="text-right pt-7">{{ $item->qty ?? ''}}</td>
                <td class="text-right pt-7">{{ number_format($item->price ?? 0,2, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="font-weight-normal">
                <td class="pl-0 pt-7"></td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total Tagihan</td>
                <td class="text-right font-weight-bold text-uppercase">
                  {{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
          @endif
        </div>
      </div>
    </div>
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0 d-print-none">
      <div class="col-md-9">
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-primary font-weight-bold" onclick="window.print();">Print</button>
        </div>
      </div>
    </div>
    <!-- end: Invoice action-->
    <!-- end: Invoice-->
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

{{-- Styles Section --}}
@section('styles')
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script>
  $(document).ready(function(){
    $('body').addClass('print-content-only');
    $(".currency").inputmask('decimal', {
      groupSeparator: '.',
      digits: 0,
      rightAlign: true,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });
    $(".select2Expense").select2({
      placeholder: "Search Biaya",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.expenses.select2') }}",
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
    });
    $("#formStore").submit(function(e) {
      e.preventDefault();
      var form = $(this);
      var btnSubmit = $('#submit');
      var btnSubmitHtml = btnSubmit.html();
      var url = form.attr("action");
      var data = new FormData(this);
      $.ajax({
        beforeSend: function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i>").prop("disabled","disabled");
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

    $('#modalDelete').on('show.bs.modal', function (event) {
      var id = $(event.relatedTarget).data('id');
      $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.operationalexpenses.index") }}/'+ id);
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
          if (response.status == "success") {
            toastr.success(response.message, 'Success !');
            $('#modalDelete').modal('hide');
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
{{-- page scripts --}}
@endsection
