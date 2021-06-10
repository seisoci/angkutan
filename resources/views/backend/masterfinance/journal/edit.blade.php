{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  <div class="card card-custom">
    <div class="card-header">
      <h3 class="card-title">
        {{ $config['page_title'] }}
      </h3>
    </div>
    <!--begin::Form-->
    <form id="formUpdate" action="{{ route('backend.journals.update', Request::segment(3)) }}">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      @method('PUT')
      <div class="card-body">
        <div class="form-group" style="display:none;">
          <div class="alert alert-custom alert-light-danger" role="alert">
            <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
            <div class="alert-text">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group row">
              <label class="col-form-label text-right col-lg-2 col-md-2 col-sm-12">Tanggal Transaksi<span
                  class="text-danger"> *</span></label>
              <div class="col-lg-6 col-md-6 col-sm-12">
                <input type="text" class="form-control rounded-0 datepicker" name="date_journal" value="{{ $data[0]->date_journal }}" readonly/>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <table class="table table-borderless table-responsive">
              <thead>
              <tr>
                <th class="text-center" scope="col">
                  <button type="button"
                          class="add btn btn-sm btn-primary rounded-0">+
                  </button>
                </th>
                <th class="text-left" scope="col">Kode Akun</th>
                <th class="text-left" scope="col">Deskripsi</th>
                <th class="text-right" scope="col">Debit</th>
                <th class="text-right" scope="col">Kredit</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data as $item)
                <tr class="items" id="items_{{ $loop->iteration }}">
                  @if($loop->first)
                    <td></td>
                  @else
                    <td><button type="button" id='items_{{ $loop->iteration }}' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>
                  @endif
                  <td><select class="form-control select2Coa" name="items[coa_id][]">
                      <option value="{{ $item->coa_id }}">{{ $item->coa->code ." - ". $item->coa->name }}</option></select></td>
                  <td><input type="text" name="items[description][]" class="form-control rounded-0" value="{{ $item->description }}"/>
                  </td>
                  <td><input type="text" name="items[debit][]" class="currency rounded-0 form-control" value="{{ $item->debit }}"/></td>
                  <td><input type="text" name="items[kredit][]" class="currency rounded-0 form-control" value="{{ $item->kredit }}"/></td>
                </tr>
              @endforeach
              </tbody>
              <tfoot>
              <tr class="text-right font-weight-bolder">
                <td colspan="3">Total</td>
                <td><input type="text" id="totalDebit" class="currency rounded-0 form-control" style="width: 140px" disabled></td>
                <td><input type="text" id="totalKredit" class="currency rounded-0 form-control" style="width: 140px" disabled></td>
              </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <button type="button" class="btn btn-secondary mr-2 rounded-0" onclick="window.history.back();">Cancel
          </button>
          <button type="submit" class="btn btn-primary rounded-0">Submit</button>
        </div>
      </div>
    </form>
    <!--end::Form-->
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <style>
    .table tbody th:nth-child(1), .table tbody td:nth-child(1) input {
      width: 50px;
    }

    .table tbody th:nth-child(2), .table tbody td:nth-child(2) .select2 {
      width: 300px !important;
    }

    .table tbody th:nth-child(3), .table tbody td:nth-child(3) input {
      width: 300px;
    }

    .table tbody th:nth-child(4) input, .table tbody td:nth-child(4) input, .table tbody th:nth-child(5) input, .table tbody td:nth-child(5) input {
      width: 140px;
    }

    .select2-container--default .select2-selection--single {
      border-radius: 0 !important;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      initSelect2();
      initCurrency();
      initCalculation();
      let totalDebit = 0;
      let totalKredit = 0;
      $('input[name^="items[debit]"]').each(function () {
        totalDebit += parseInt($(this).val()) || 0;
      });
      $('input[name^="items[kredit]"]').each(function () {
        totalKredit += parseInt($(this).val()) || 0;
      });
      $('#totalDebit').val(totalDebit);
      $('#totalKredit').val(totalKredit);


      $("#formUpdate").submit(function(e){
        $('.currency').inputmask('remove');
        e.preventDefault();
        let form 	= $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url 	= form.attr("action");
        let data 	= new FormData(this);
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
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              setTimeout(function () {
                if (response.redirect === "" || response.redirect === "reload") {
                  location.reload();
                } else {
                  location.href = response.redirect;
                }
              }, 1000);
            } else {
              initCurrency();
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error((response.message || "Please complete your form"), 'Failed !');
            }
          },error: function(response){
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });

      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        todayBtn: "linked",
        clearBtn: true,
        todayHighlight: true,
      });

      $('tbody').on('click', '.rmItems', function () {
        let id = this.id;
        let split_id = id.split("_");
        let deleteindex = split_id[1];
        $("#items_" + deleteindex).remove()
        initCalculation();
        initSelect2();
        initCurrency();
        let totalDebit = 0;
        let totalKredit = 0;
        $('input[name^="items[debit]"]').each(function () {
          totalDebit += parseInt($(this).val()) || 0;
        });
        $('input[name^="items[kredit]"]').each(function () {
          totalKredit += parseInt($(this).val()) || 0;
        });
        $('#totalDebit').val(totalDebit);
        $('#totalKredit').val(totalKredit);
      })

      $(".add").on('click', function () {
        let total_items = $(".items").length;
        let lastid = $(".items:last").attr("id");
        let split_id = lastid.split("_");
        let nextindex = Number(split_id[1]) + 1;
        let max = 100;
        if (total_items < max) {
          $(".items:last").after("<tr class='items' id='items_" + nextindex + "'></tr>");
          $("#items_" + nextindex).append(raw_items(nextindex));
          initCalculation();
          initSelect2();
          initCurrency();
        }
      });


      function initCalculation() {
        $('input[name^="items[debit]"]').on('keyup', function () {
          let $row = $(this).closest("tr"); //<table> class
          $row.find('input[name="items[kredit][]"]').val(0);
        });

        $('input[name^="items[kredit]"]').on('keyup', function () {
          let $row = $(this).closest("tr"); //<table> class
          $row.find('input[name="items[debit][]"]').val(0);
        });

        $('input[name^="items[debit]"],input[name^="items[kredit]"]').on('keyup', function () {
          let totalDebit = 0;
          let totalKredit = 0;
          $('input[name^="items[debit]"]').each(function () {
            totalDebit += parseInt($(this).val()) || 0;
          });
          $('input[name^="items[kredit]"]').each(function () {
            totalKredit += parseInt($(this).val()) || 0;
          });
          $('#totalDebit').val(totalDebit);
          $('#totalKredit').val(totalKredit);
        });
      }

      function raw_items(nextindex) {
        return "<td><button type='button' id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>" +
          '<td><select class="form-control select2Coa" name="items[coa_id][]"></select></td>' +
          '<td><input type="text" name="items[description][]" class="form-control rounded-0 unit"/></td>' +
          '<td><input type="text" name="items[debit][]" class="currency rounded-0 form-control"/></td>' +
          '<td><input type="text" name="items[kredit][]" class="currency rounded-0 form-control"/></td>';
      }


      function initCurrency() {
        $(".currency").inputmask('decimal', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
          allowMinus: false
        });
      }

      function initSelect2() {
        $(".select2Coa").select2({
          placeholder: "Choose Akun",
          ajax: {
            url: "{{ route('backend.mastercoa.select2self') }}",
            dataType: "json",
            cache: true,
            data: function (e) {
              return {
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
        });
      }
    });
  </script>
@endsection
