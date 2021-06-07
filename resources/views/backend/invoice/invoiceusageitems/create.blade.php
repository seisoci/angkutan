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
    <form id="formStore" action="{{ route('backend.invoiceusageitems.store') }}">
      @csrf
      <input type="hidden" name="type" value="self">
      <div class="row align-items-center border border-dark py-10 px-4">
        <div class="col-12">
          <div class="row align-items-center">
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">Tanggal Invoice:</label>
                <div class="col-md-6">
                  <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                         placeholder="Tanggal Invoice" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">Prefix:</label>
                <div class="col-lg-6">
                  <select name="prefix" class="form-control" id="select2Prefix">
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">No. Invoice Pemakaian:</label>
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
                <th class="text-left" scope="col" width="55%">Produk</th>
                <th class="text-center" scope="col" width="20%">Stok Tersedia</th>
                <th class="text-right" scope="col" wdith="20%">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              <tr class="items" id="items_1">
                <td></td>
                <td>
                  <input type="hidden" name="items[stock_id][]">
                  <select class="form-control select2Stocks" name="items[sparepart_id][]"></select></td>
                <td><input type="text" name="items[qty_system][]" class="form-control rounded-0 unit" disabled />
                </td>
                <td><input type="number" min="1" name="items[qty][]" class="unit rounded-0 form-control" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="d-flex justify-content-end mt-5">
        <button type="submit" class="btn btn-primary rounded-0">Submit</button>
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
    initSelect2();
    initCurrency();
    initDate();

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

    function initSelect2(){
      $(".select2Stocks").select2({
        placeholder: "Search SparePart",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.stocks.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            let arrayUsed = [];
            $('select[name^="items[sparepart_id]"]').each(function() {
              if($(this).val()){
                arrayUsed.push($(this).val());
              }
            });
            return {
              used: arrayUsed,
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function(evt){
        let $row = $(this).closest("tr");
        $row.find('input[name="items[qty_system][]"]').val('');
        $row.find('input[name="items[qty][]"]').val('').attr('max', 0);
        $row.find('input[name="items[stock_id][]"]').val('');
      })
      .on('select2:select', function(evt){
        let $row = $(this).closest("tr");
        $row.find('input[name="items[qty_system][]"]').val(evt.params.data.qty)
        $row.find('input[name="items[qty][]"]').attr('max', evt.params.data.qty);
        $row.find('input[name="items[stock_id][]"]').val(evt.params.data.stock_id);
      });
    }

    function initCurrency(){
      $(".unit").inputmask('numeric', {
        groupSeparator: '.',
        digits:0,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
        allowMinus: false,
      });
    }

    function initDate() {
      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        todayBtn: "linked",
        clearBtn: true,
        todayHighlight: true
      });
    }

    function initCalculation(){
      $('input[name^="items[qty_now]"]').on('keyup',function()  {
        let $row       = $(this).closest("tr");
        let qty_system = parseInt($row.find('input[name="items[qty_system][]"]').val());
        let qty_now    = parseInt($row.find('input[name="items[qty_now][]"]').val());
        qty_difference = qty_now -  qty_system;
        $row.find('input[name="items[qty_difference][]"]').val(qty_difference);
      });
    }

    $('tbody').on('click', '.rmItems',function(){
      let id = this.id;
      let split_id = id.split("_");
      let deleteindex = split_id[1];
      $("#items_" + deleteindex).remove();
    });

    $(".add").on('click', function(){
      let total_items = $(".items").length;
      let lastid = $(".items:last").attr("id");
      let split_id = lastid.split("_");
      let nextindex = Number(split_id[1]) + 1;
      let max = 100;
      if(total_items < max ){
        $(".items:last").after("<tr class='items' id='items_"+ nextindex +"'></tr>");
        $("#items_" + nextindex).append(raw_items(nextindex));
        initCalculation();
        initSelect2();
        initCurrency();
      }
    });

    function raw_items(nextindex){
      return "<td><button id='itemsx_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>"+'<td><input type="hidden" name="items[stock_id][]"><select class="form-control select2Stocks" name="items[sparepart_id][]"></select></td>'+
      '<td><input type="text" name="items[qty_system][]" class="form-control unit rounded-0" disabled/></td>'+
      '<td><input type="number" max="1" name="items[qty][]" class="unit form-control rounded-0" /></td>';
    }

    $("#formStore").submit(function(e) {
      $('.unit').inputmask('remove');
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
