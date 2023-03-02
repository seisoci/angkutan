@extends('layout.default')

@section('content')
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
                <label class="col-lg-4 col-form-label">Tanggal Invoice:</label>
                <div class="col-md-8">
                  <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                         placeholder="Tanggal Invoice" readonly>
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
                <label class="col-lg-4 col-form-label">No. Invoice Pemakaian:</label>
                <div class="col-lg-8">
                  <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                  <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">No. Pol:</label>
                <div class="col-lg-8">
                  <select name="transport_id" class="form-control" id="select2Transport">
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">Memo:</label>
                <div class="col-lg-8">
                  <textarea name="memo" class="form-control rounded-0"></textarea>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-4 col-form-label">Master Akun:</label>
                <div class="col-lg-8">
                  <select name="coa_id" class="form-control rounded-0" style="min-width: 250px">
                    @foreach($selectCoa->coa as $item)
                      <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered ">
              <thead>
              <tr>
                <th class="text-center" scope="col" style="min-width: 50px"><button type="button"
                                                                       class="add btn btn-sm btn-primary rounded-0">+</button>
                </th>
                <th class="text-left" scope="col" style="min-width: 300px">Produk</th>
                <th class="text-center" scope="col" style="width: 75px">Unit</th>
                <th class="text-right" scope="col" style="min-width: 150px">Harga</th>
                <th class="text-right" scope="col" style="min-width: 150px">Total</th>
                <th class="text-right" scope="col">Deskripsi</th>
              </tr>
              </thead>
              <tbody>
              <tr class="items" id="items_1">
                <td></td>
                <td>
                  <input type="hidden" name="items[stock_id][]">
                  <input type="text" class="form-control rounded-0" name="items[name][]" style="min-width: 300px">
                </td>
                <td><input type="text" name="items[qty][]" class="form-control rounded-0 unit" style="min-width: 75px"/>
                </td>
                <td><input type="text" name="items[price][]" class="rounded-0 form-control currency" style="min-width: 150px"/>
                </td>
                <td><input type="text" name="items[total][]" class="currency rounded-0 form-control text-right" style="min-width: 150px"
                           disabled></td>
                <td><input type="text" name="items[description][]" class="rounded-0 form-control"
                           style="min-width: 250px"/></td>
              </tr>
              </tbody>
            </table>
          </div>
          <table class="table table-borderless ">
            <thead>
              <tr>
                <th class="text-center" scope="col"></th>
                <th class="text-left" scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="pt-6 text-right float-right">Total Tagihan</td>
                <td style="width: 150px"><input id="totalTagihan" type="text" class="currency form-control rounded-0 float-right" disabled  style="width: 150px"/>
                </td>
              </tr>
              <tr>
                <td class="text-right" colspan="2"><button type="submit" class="btn btn-primary rounded-0">Submit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('styles')
<style>
  .select2-container--default .select2-selection--single {
    border-radius: 0 !important;
  }
</style>
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('scripts')
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function() {
    initCalculation();
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
      width: '100%',
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

    function initDate() {
      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        todayBtn: "linked",
        clearBtn: true,
        todayHighlight: true
      });
    }

    function initCalculation(){
      $('input[name^="items[qty]"], input[name^="items[price]"]').on('keyup',function()  {
        let $row       = $(this).closest("tr");
        let qty        = parseInt($row.find('input[name="items[qty][]"]').val()) || 0;
        let price      = parseInt($row.find('input[name="items[price][]"]').val()) || 0;
        let totalTagihan = 0;
        total = qty * price;
        $row.find('input[name="items[total][]"]').val(total);
        $('input[name^="items[total]"]').each(function() {
          totalTagihan += parseInt($(this).val());
        });
        $('#totalTagihan').val(totalTagihan);
      });
    }

    $('tbody').on('click', '.rmItems',function(){
      let id = this.id;
      let split_id = id.split("_");
      let deleteindex = split_id[1];
      $("#items_" + deleteindex).remove();

      let $row       = $(this).closest("tr");
      let qty        = parseInt($row.find('input[name="items[qty][]"]').val());
      let price      = parseInt($row.find('input[name="items[price][]"]').val());
      let totalTagihan = 0;
      total = qty * price;
      $row.find('input[name="items[total][]"]').val(total);
      $('input[name^="items[total]"]').each(function() {
        totalTagihan += parseInt($(this).val());
      });
      $('#totalTagihan').val(totalTagihan);
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
        initCurrency();
      }
    });

    function raw_items(nextindex){
      return "<td><button type='button' id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>"+'<td><input type="hidden" name="items[stock_id][]"><input type="text" class="form-control rounded-0" name="items[name][]"></td>'+
      '<td><input type="text" name="items[qty][]" class="form-control unit rounded-0"/></td>'+
      '<td><input type="text" name="items[price][]" class="form-control rounded-0 currency" /></td>'+
      '<td><input type="text" name="items[total][]" class="currency rounded-0 form-control text-right" disabled></td>'+
      '<td><input type="text" name="items[description][]" class="rounded-0 form-control" style="min-width: 250px"/></td>'
      ;
    }

    $("#formStore").submit(function(e) {
      $('.unit').inputmask('remove');
      $('.currency').inputmask('remove');
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
            setTimeout(function() {
              if(response.redirect === "" || response.redirect === "reload"){
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
