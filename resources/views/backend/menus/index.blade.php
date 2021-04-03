{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row">
  <div class="col-sm-6">
    <div class="card card-table">
      <div class="card-header">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-body">
        <div class="dd" id="menuList">{!! $data['nestable'] !!}</div>
      </div>
      <div class="card-footer">
        <form id="changeHierarchy" method="POST" action="{{ route('backend.menus.change_hierarchy') }}">
          @csrf
          <input type="hidden" id="output" name="hierarchy" />
          <button type="submit" class="btn btn-success btn-sm" style="display:none">Save Change <i
              class="fa fa-fw fa-save"></i></button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <form id="formStore" action="{{ route('backend.menus.store') }}" autocomplete="off">
      @csrf
      <input type="hidden" name="menu_type" value="top-menu">
      <div class="card card-table">
        <div class="card-header">
          <h3 class="card-title">Menu Add</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label>Title</label>
            <div style="position:relative; display:block;">
              <input id="in_title" type="text" class="form-control input-sm" name="title" />
              <div id="result"></div>
            </div>
          </div>
          <div class="form-group">
            <label>Url</label>
            <input id="in_url" type="text" class="form-control input-sm" name="url" />
          </div>
          <div class="form-group">
            <label style="display: block;">Target</label>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-sm btn-info active">
                <input type="radio" name="target" value="_self" checked> Self
              </label>
              <label class="btn btn-sm btn-info">
                <input type="radio" name="target" value="_blank"> Blank
              </label>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="btn-group">
            <button type="submit" class="btn btn-success btn-sm">Add <i class="fa fa-fw fa-plus"></i></button>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/backend/menus/menus.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/backend/menus/nestable.css') }}" rel="stylesheet" type="text/css" />
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/backend/menus/nestable.js') }}" type="text/javascript"></script>
{{-- page scripts --}}
<script type="text/javascript">
  $(document).ready(function(){
    $("#formStore").submit(function(e) {
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
              window.location.href = response.redirect;
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

    $("#changeHierarchy").submit(function(e) {
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
              window.location.href = response.redirect;
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

    $("#in_title").keyup(function(e){
      e.preventDefault();
      var field = $(this).parent();
      var value = $(this).val();
      $.ajax({
        cache	: false,
        type 	: "POST",
        url 	: "{{ route('backend.menus.autocomplete') }}",
        data 	: {	q : value },
        success:function(response) {
          if(response != ""){
            $("#result").html(response).fadeIn();
            $(".link-item").click(function(e){
              e.preventDefault();
              var item_title = $(this).find("strong").html();
              var item_url = $(this).attr("data-href");
              $("#in_title").val(item_title);
              $("#in_url").val(item_url);
              $("#result").html("").fadeOut("fast");
            });
            $(field).blur(function(){
              $("#result").html("").fadeOut("fast");
            });
          } else {
            $("#result").html("").fadeOut("fast");
          }
        }
      });
    });

    $('#menuList').nestable({maxDepth:2}).on('change', function(){
      var json_values = window.JSON.stringify($(this).nestable('serialize'));
      $("#output").val(json_values);
      $("#changeHierarchy [type='submit']").fadeIn();
    });

    $(".btn-delete").click(function(e){
      e.preventDefault();
      var btnSubmit 	    = $(this);
      var btnSubmitHtml   = btnSubmit.html();
      var url             = btnSubmit.attr("data-action");
      $.ajax({
        beforeSend:function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled","disabled");
        },
        cache: false,
        type: 'DELETE',
        url: url,
        dataType: 'JSON',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          if (response.status == "success") {
            toastr.success(response.message, 'Success !');
            setTimeout(function() {
              window.location.href = response.redirect;
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
  });
</script>
@endsection
