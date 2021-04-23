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
    <form id="formStore" action="{{ route('backend.opnames.store') }}">
      @csrf
      <div class="row align-items-center border border-dark py-10 px-4">
        <table class="table table-bordered ">
          <thead>
            <tr>
              <th class="text-center" scope="col" width="5%"><button type="button"
                  class="add btn btn-sm btn-primary rounded-0">+</button>
              </th>
              <th class="text-left" scope="col" width="55%">Produk</th>
              <th class="text-center" scope="col" width="15%">Stok</th>
              <th class="text-center" scope="col" wdith="15%">Stok Saat ini</th>
              <th class="text-center" scope="col" width="15%">Selisih</th>
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
              <td><input type="text" name="items[qty_now][]" class="unit rounded-0 form-control" /></td>
              <td><input type="text" name="items[qty_difference][]" class="unit rounded-0 form-control" disabled /></td>
            </tr>
          </tbody>
        </table>
        <div class="row w-100">
          <div class="col-md-6 offset-md-6">
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="description" rows="5" class="form-control rounded-0"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-end mt-5">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
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

{{-- page scripts --}}
<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function() {
    initCalculation();
    initSelect2();
    initCurrency();

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
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function(evt){
        var $row = $(this).closest("tr");
        $row.find('input[name="items[qty_system][]"]').val('');
        $row.find('input[name="items[qty_difference][]"]').val('');
        $row.find('input[name="items[stock_id][]"]').val('');
      })
      .on('select2:select', function(evt){
        var $row = $(this).closest("tr");
        $row.find('input[name="items[qty_system][]"]').val(evt.params.data.qty);
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
      });
    }

    function initCalculation(){
      $('input[name^="items[qty_now]"]').on('keyup',function()  {
          var $row       = $(this).closest("tr"); //<table> class
          var qty_system = parseInt($row.find('input[name="items[qty_system][]"]').val());
          var qty_now    = parseInt($row.find('input[name="items[qty_now][]"]').val());
          qty_difference = qty_now -  qty_system;
          $row.find('input[name="items[qty_difference][]"]').val(qty_difference);
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
        initSelect2();
        initCurrency();
      }
    });

    function raw_items(nextindex){
      return "<td><button id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>"+'<td><input type="hidden" name="items[stock_id][]"><select class="form-control select2Stocks" name="items[sparepart_id][]"></select></td>'+
      '<td><input type="text" name="items[qty_system][]" class="form-control unit rounded-0" disabled/></td>'+
      '<td><input type="text" data-inputmask=""alias": "decimal"" name="items[qty_now][]" class="unit form-control rounded-0" /></td>'+
      '<td><input type="text" name="items[qty_difference][]" class="unit form-control rounded-0" disabled /></td>';
    }

    $("#formStore").submit(function(e) {
      $('.unit').inputmask('remove');
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
