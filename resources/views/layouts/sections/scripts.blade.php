<!-- BEGIN: Vendor JS-->
<script src="{{ secure_asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ secure_asset(mix('assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ secure_asset(mix('assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ secure_asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ secure_asset(mix('assets/vendor/js/menu.js')) }}"></script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script src="{{ secure_asset(mix('assets/js/main.js')) }}"></script>
<script src="{{secure_asset('assets/sweet-alert2/sweetalert2.all.min.js')}}"></script>
<script src="{{secure_asset('assets/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{secure_asset('assets/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


<script type="text/javascript">
  $(document).ready( function () {
      $('#expiry_user').DataTable();
  });
  $(document).ready( function () {
      $('#inactive_user').DataTable();
  });
  $(document).ready( function () {
      $('#users_list').DataTable();
  });

  /*function googleTranslateElementInit() {
        setCookie('googtrans', '/en/es/', 1);
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,es',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
    function setCookie(key, value, expiry) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }*/

  	function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,es',  // Specify included languages (English and Spanish)
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }

    document.getElementById('languageSelector').addEventListener('change', function () {
        var selectedLanguage = this.value;
        // Change the Google Translate Element language
        google.translate.translatePage(selectedLanguage);
    });
</script>
<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
