jQuery(document).ready(function($) {
  Culqi.publicKey = window.token;
  Culqi.init();

  var input = document.querySelector("#mobile");
  var iti = window.intlTelInput(input, {
    separateDialCode: true,
    autoPlaceholder: "polite",
    formatOnDisplay: true,
    hiddeInput: "full_number",
    preferredCountries: ["pe", "mx", "co", "ec", "bo"],
    initialCountry: "auto",
    geoIpLookup: function(callback) {
      $.get('//ipinfo.io', function() {}, "jsonp").always(function(resp) {
        var countryCode = (resp && resp.country) ? resp.country : "";
        if (resp.country == 'bo' || resp.country == 'cl' || resp.country == 'cr' || resp.country == 'ec' || resp.country == 'ni' || resp.country == 'uy' || resp.country == 've') {
          $("#form_payment input[name='dni']").attr("placeholder", "CI");
        } else if (resp.country == 'co') {
          $("#form_payment input[name='dni']").attr("placeholder", "CC");
        } else if (resp.country == 'sv') {
          $("#form_payment input[name='dni']").attr("placeholder", "DUI");
        } else if (resp.country == 'gt') {
          $("#form_payment input[name='dni']").attr("placeholder", "DPI");
        } else if (resp.country == 'hn') {
          $("#form_payment input[name='dni']").attr("placeholder", "TDI");
        } else if (resp.country == 'mx') {
          $("#form_payment input[name='dni']").attr("placeholder", "CURP");
        } else if (resp.country == 'pa') {
          $("#form_payment input[name='dni']").attr("placeholder", "CIP");
        } else if (resp.country == 'py') {
          $("#form_payment input[name='dni']").attr("placeholder", "CI");
        } else if (resp.country == 'do') {
          $("#form_payment input[name='dni']").attr("placeholder", "CIE");
        } else {
          $("#form_payment input[name='dni']").attr("placeholder", "DNI");
        }
        $("#form_payment input[name='country']").val(resp.country);
        callback(countryCode);
      });
    },
  });

  $("#country").val(iti.getSelectedCountryData().iso2);

  input.addEventListener('countrychange', function(e) {
    $countryCode = iti.getSelectedCountryData().iso2;
    $("#country").val($countryCode);

    if ($countryCode == 'bo' || $countryCode == 'cl' || $countryCode == 'cr' || $countryCode == 'ec' || $countryCode == 'ni' || $countryCode == 'uy' || $countryCode == 've') {
      $("#form_payment input[name='dni']").attr("placeholder", "CI");
    } else if ($countryCode == 'co') {
      $("#form_payment input[name='dni']").attr("placeholder", "CC");
    } else if ($countryCode == 'sv') {
      $("#form_payment input[name='dni']").attr("placeholder", "DUI");
    } else if ($countryCode == 'gt') {
      $("#form_payment input[name='dni']").attr("placeholder", "DPI");
    } else if ($countryCode == 'hn') {
      $("#form_payment input[name='dni']").attr("placeholder", "TDI");
    } else if ($countryCode == 'mx') {
      $("#form_payment input[name='dni']").attr("placeholder", "CURP");
    } else if ($countryCode == 'pa') {
      $("#form_payment input[name='dni']").attr("placeholder", "CIP");
    } else if ($countryCode == 'py') {
      $("#form_payment input[name='dni']").attr("placeholder", "CI");
    } else if ($countryCode == 'do') {
      $("#form_payment input[name='dni']").attr("placeholder", "CIE");
    } else {
      $("#form_payment input[name='dni']").attr("placeholder", "DNI");
    }
  }, false);

  $.validator.addMethod(
    "mobil_country",
    function(value, element) {
      return this.optional(element) || iti.isValidNumber();
    }, "El formato del telefono es incorrecto."
  );

  $.validator.addMethod(
    "laxEmail",
    function(value, element) {
      return this.optional(element) || /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(value);
    }, 'Ingrese un correo valido.'
  );

  $('#form_payment').validate({
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorElement: 'em',
    debug: false,
    rules: {
      first_name: {
        required: true
      },
      last_name: {
        required: true
      },
      mobile: {
        required: true,
        minlength: 5,
        mobil_country: true
      },
      email: {
        required: {
          depends:function() {
            $(this).val($.trim($(this).val()));
            return true;
          }
        },
        laxEmail: true
      },
      dni: {
        required: true,
        minlength: 2
      },
      address: {
        required: true,
        minlength: 2,
        maxlength: 90
      },
      address_city: {
        required: true,
        minlength: 2,
        maxlength: 90
      },
      number: {
        required: true,
        creditcard: true,
      },
      exp_month: {
        required: true,
        minlength: 2,
        maxlength: 2,
        digits: true
      },
      exp_year: {
        required: true,
        minlength: 4,
        maxlength: 4,
        digits: true
      },
      cvv: {
        required: true,
        minlength: 3,
        maxlength: 3,
        digits: true
      },
    },
    messages: {
      first_name: {
        required: ''
      },
      last_name: {
        required: ''
      },
      email: {
        required: '',
        laxEmail: 'Por favor ingrese un correo valido.',
        email: 'Por favor ingrese un correo valido.',
      },
      dni: {
        required: '',
        minlength: '',
      },
      address: {
        required: '',
        minlength: '',
        maxlength: 'No supere los 90 carácter por favor.',
      },
      address_city: {
        required: '',
        minlength: '',
        maxlength: 'No supere los 90 carácter por favor.',
      },
      mobile: {
        required: '',
        NICNumber: 'Por favor ingresa un documento valido.'
      },
      number: {
        required: '',
        creditcard: 'Por favor, introduzca un número de tarjeta de crédito válida.'
      },
      exp_month: {
        required: '',
        minlength: '',
        digits: ''
      },
      exp_year: {
        required: '',
        minlength: '',
        digits: ''
      },
      cvv: {
        required: '',
        minlength: '',
        digits: ''
      },
    },
    submitHandler: function() {
      Culqi.createToken();
      $('#form_payment').waitMe({
        effect: 'rotation',
        text: 'Procesando pago...',
        bg: 'rgba(250, 250, 250, 0.75)',
        color:'#88b53d',
      });
    },
  });
});