{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
{{-- Dashboard 1 --}}
<!--begin::Card-->
<div class="card card-custom mt-6">
  <div class="card-header flex-wrap py-3">
    <div class="card-title">
      <h3 class="card-label">{{ $config['page_title'] }}
      </h3>
    </div>
    <div class="card-toolbar">
    </div>
  </div>
  <div class="card-body">
    <form id="formStore" action="{{ route('backend.invoiceusageitemsoutside.store') }}">
      @csrf
      <input type="hidden" name="type" value="outside">
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
                <label class="col-lg-3 col-form-label">No. Invoice Pemakaian:</label>
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
                <div class="col-lg-9">
                  <select name="driver_id" class="form-control" id="select2Driver">
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label">No. Pol:</label>
                <div class="col-lg-9">
                  <select name="transport_id" class="form-control" id="select2Transport">
                  </select>
                </div>
              </div>
            </div>
          </div>
          <table class="table table-bordered ">
            <thead>
              <tr>
                <th class="text-center" scope="col" width="5%"><button type="button"
                    class="add btn btn-sm btn-primary rounded-0">+</button>
                </th>
                <th class="text-left" scope="col" width="45%">Produk</th>
                <th class="text-center" scope="col" width="10%">Unit</th>
                <th class="text-right" scope="col" wdith="20%">Harga</th>
                <th class="text-right" scope="col" wdith="20%">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr class="items" id="items_1">
                <td></td>
                <td>
                  <input type="hidden" name="items[stock_id][]">
                  <input type="text" class="form-control rounded-0" name="items[name][]">
                </td>
                <td><input type="text" name="items[qty][]" class="form-control rounded-0 unit" />
                </td>
                <td><input type="text" name="items[price][]" class="rounded-0 form-control currency" />
                </td>
                <td><input type="text" name="items[total][]" class="currency rounded-0 form-control text-right"
                    disabled></td>
              </tr>
            </tbody>
          </table>
          <table class="table table-borderless ">
            <thead>
              <tr>
                <th class="text-center" scope="col" width="5%"></th>
                <th class="text-left" scope="col" width="45%"></th>
                <th class="text-right" scope="col" width="10%"></th>
                <th class="text-right" scope="col" wdith="20%"></th>
                <th class="text-right" scope="col" width="20%"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="pt-6">Total Tagihan</td>
                <td width="22%"><input id="totalTagihan" type="text" class="currency form-control rounded-0" disabled />
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right"><button type="submit" class="btn btn-primary rounded-0">Submit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<style>
  .select2-container--default .select2-selection--single {
    border-radius: 0 !important;
  }
</style>
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

{{-- page scripts --}}
<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function() {
    initCalculation();
    initCurrency();

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
            type: 'sparepart',
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
    });


    function initCurrency(){
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits:0,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
        allowMinus: false,
      });

      $(".unit").inputmask('numeric', {
        groupSeparator: '.',
        digits:0,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
        allowMinus: false,
        min: 1
      });
    }

    function initCalculation(){
      $('input[name^="items[qty]"], input[name^="items[price]"]').on('keyup',function()  {
        var $row       = $(this).closest("tr");
        var qty        = parseInt($row.find('input[name="items[qty][]"]').val());
        var price      = parseInt($row.find('input[name="items[price][]"]').val());
        var totalTagihan = 0;
        total = qty * price;
        $row.find('input[name="items[total][]"]').val(total);
        $('input[name^="items[total]"]').each(function() {
          totalTagihan += parseInt($(this).val());
        });
        $('#totalTagihan').val(totalTagihan);
      });
    }

    $('tbody').on('click', '.rmItems',function(){
      var id = this.id;
      var split_id = id.split("_");
      var deleteindex = split_id[1];
      $("#items_" + deleteindex).remove();
    });

    $(".add").on('click', function(){
      var total_items = $(".items").length;
      var lastid = $(".items:last").attr("id");
      var split_id = lastid.split("_");
      var nextindex = Number(split_id[1]) + 1;
      var max = 100;
      if(total_items < max ){
        $(".items:last").after("<tr class='items' id='items_"+ nextindex +"'></tr>");
        $("#items_" + nextindex).append(raw_items(nextindex));
        initCalculation();
        initCurrency();
      }
    });

    function raw_items(nextindex){
      return "<td><button id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>"+'<td><input type="hidden" name="items[stock_id][]"><input type="text" class="form-control rounded-0" name="items[name][]"></td>'+
      '<td><input type="text" name="items[qty][]" class="form-control unit rounded-0"/></td>'+
      '<td><input type="text" name="items[price][]" class="form-control rounded-0 currency" /></td>'+
      '<td><input type="text" name="items[total][]" class="currency rounded-0 form-control text-right" disabled></td>'
      ;
    }

    $("#formStore").submit(function(e) {
      $('.unit').inputmask('remove');
      $('.currency').inputmask('remove');
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
            initCurrency();
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each(response.error, function(key, value) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error("Please complete your form", 'Failed !');
          }
        },
        error: function(response) {
          initCurrency();
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });

  });
</script>
@endsection
