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
    <!--begin: Datatable-->
    <table class="table table-bordered ">
      <thead>
        <tr>
          <th class="text-center" scope="col" width="5%"><button type="button"
              class="add btn btn-sm btn-primary">+</button>
          </th>
          <th class="text-left" scope="col" width="45%">Produk</th>
          <th class="text-right" scope="col" width="10%">Jumlah</th>
          <th class="text-right" scope="col" wdith="20%">Harga</th>
          <th class="text-right" scope="col" width="20%">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr class="items" id="items_1">
          <td></td>
          <td><input type="text" name="name[]" class="form-control" /></td>
          <td><input type="text" name="qty[]" class="form-control" /></td>
          <td><input type="text" name="price[]" class="currency form-control" /></td>
          <td><input type="text" name="total[]" class="currency form-control" disabled /></td>
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
          <td class="text-right">Grand Total</td>
          <td><input type="text" class="currency form-control" disabled /></td>
        </tr>
      </tbody>
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
<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function() {
    initCurrency();
    initCalculation();

    function initCurrency(){
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits:0,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
      });
    }
    function initCalculation(){
      $('input[name^="qty"],input[name^="price"],input[name^="gp"],input[name ^= "discount"], input[name ^= "qty"], input[name ^= "total"],input[name ^= "totaltax"], input[name ^= "totalamtincltax"],input[name ^= "grandtotal"]').on('keyup',function()  {
          //alert("The text has been changed.");
          var total      = 0;
          var grandtotal = 0;
          var $row       = $(this).closest("tr"); //<table> class
          var qty        = parseInt($row.find('input[name="qty[]"]').val());
          var price      = parseFloat($row.find('input[name="price[]"]').val());
          total          = (price * qty) || 0;
          $row.find('input[name="total[]"]').val(total);
          $('input[name^="total"]').each(function() {
            grandtotal += parseInt($(this).val());
          });
          console.log(grandtotal);
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
        initCurrency();
        initCalculation();
      }
    });

    function raw_items(nextindex){
      return "<td><button id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems'>-</button></td>"+'<td><input type="text" name="name[]" class="form-control" /></td>'+
      '<td><input type="text" name="qty[]" class="form-control" /></td>'+
      '<td><input type="text" name="price[]" class="currency calculate form-control" /></td>'+
      '<td><input type="text" name="total[]" class="currency calculate form-control" disabled /></td>';
    }

  });
</script>
@endsection
