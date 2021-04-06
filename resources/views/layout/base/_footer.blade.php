{{-- Footer --}}

<div class="footer bg-white py-4 d-flex flex-lg-column {{ Metronic::printClasses('footer', false) }}" id="kt_footer">
  {{-- Container --}}
  <div
    class="{{ Metronic::printClasses('footer-container', false) }} d-flex flex-column flex-md-row align-items-center justify-content-between">
    {{-- Copyright --}}
    <div class="text-dark order-2 order-md-1">
      <span class="text-muted font-weight-bold mr-2">{{ date("Y") }} &copy;</span>
      <a target="_blank" href="https://ginktech.net" class="text-dark-75 text-hover-primary">PT. Gink Tecnology</a>
    </div>

    {{-- Nav --}}
    <div class="nav nav-dark order-1 order-md-2">
    </div>
  </div>
</div>
