<?php
include_once('ajax.php');
include_once('resources.php');

global $t;
$t = get_option('token_public');

class shortcodePayment {
  public function __construct() {
    add_shortcode('payment-culqi', array($this, 'shortcode_payment'));
  }

  function shortcode_payment($atts) {
    ob_start();
    wp_enqueue_style('payment/intlTelInput.css', '//cdnjs.cloudflare.com/ajax/libs/intl-tel-input/15.0.2/css/intlTelInput.css', false, null);
    wp_enqueue_style('payment/bootstrap.css', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', false, null);
    wp_enqueue_script('payment/main.js', plugins_url('../js/form.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_script('payment/waitMe.min.js', plugins_url('../js/waitMe.min.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_script('payment/intlTelInput.min.js', '//cdnjs.cloudflare.com/ajax/libs/intl-tel-input/15.0.2/js/intlTelInput.min.js', ['jquery'], null, true);
    wp_enqueue_script('payment/utils.js', '//cdnjs.cloudflare.com/ajax/libs/intl-tel-input/15.0.2/js/utils.js?1549804213570', ['jquery'], null, true);
    wp_enqueue_style('payment/style.css', plugins_url('../css/style.css', __FILE__), false, null);
    wp_enqueue_style('payment/waitMe.min.css', plugins_url('../css/waitMe.min.css', __FILE__), false, null);

    $atts = shortcode_atts(
      array(
        'course'   => 'c0',
        'submit'   => 'enviar',
        'plan'     => 'full', // year, full, free
        'image'    => 'https://arcux.net/wp-content/uploads/2019/08/Planos-con-photoshop.jpg',
        'redirect' => 'https://arcux.net'
      ), $atts, 'payment-culqi'
    );
    $this->form_public($atts);
    return ob_get_clean();
  }

  function form_public($atts) {
    global $wpdb;
    $course_details = $wpdb->get_row(
      "SELECT * FROM {$wpdb->prefix}payment_course WHERE code = '$atts[course]'",
      ARRAY_A
    );
    if ($course_details) { ?>
      <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.js"></script>
      <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
      <div class="only_form mt-5">
        <div class="row">
          <div class="col-12">
            <div class="info_course">
              <div class="row no-gutters mb-4">
                <div class="col-3 text-center">
                  <img src="<?php echo $atts['image']; ?>">
                </div>
                <div class="col-5">
                  <p class="name_course"><?php echo $course_details['name']; ?></p>
                  <p class="plan">
                    <?php
                    if($atts['plan'] == 'year') {
                      echo 'Plan Anual';
                    } else if($atts['plan'] == 'full') {
                      echo 'Plan de por vida';
                    }
                    ?>
                  </p>
                </div>
                <div class="col-4">
                  <p class="cost">$ <?php echo $course_details['amount']; ?> USD</p>
                </div>
              </div>
            </div>
            <form id="form_payment" class="needs-validation" data-toggle="validator" action="#" method="post" novalidate>
              <input type="hidden" name="course" value="<?php echo $course_details['code']; ?>">
              <input type="hidden" id="country" name="country">
              <div class="row no-gutters">
                <div class="col-6 pr-2">
                  <div class="form-group">
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Nombres *" required>
                  </div>
                </div>
                <div class="col-6 pl-2">
                  <div class="form-group">
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Apellidos *" required>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <input type="tel" class="form-control" style="max-width: 100% !important;" id="mobile" name="mobile" required>
              </div>
              <div class="row no-gutters">
                <div class="col-6 pr-2">
                  <div class="form-group">
                    <input type="email" class="form-control" size="50" data-culqi="card[email]" id="card[email]" name="email" placeholder="Correo Electrónico *">
                  </div>
                </div>
                <div class="col-6 pl-2">
                  <div class="form-group">
                    <input type="number" class="form-control" id="dni" name="dni" placeholder="Documento de identidad" style="max-width: 100%">
                  </div>
                </div>
              </div>
              <div class="row no-gutters">
                <div class="col-6 pr-2">
                  <div class="form-group">
                    <input type="text" class="form-control" name="address" placeholder="Dirección">
                  </div>
                </div>
                <div class="col-6 pl-2">
                  <div class="form-group">
                    <input type="text" class="form-control" name="address_city" placeholder="Ciudad">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <input type="text" class="form-control px-4" size="20" name="number" data-culqi="card[number]" id="card[number]" placeholder="Número de tarjeta *" required>
              </div>
              <div class="row no-gutters">
                <div class="col-8">
                  <div class="form-group input-group pr-2">
                    <input type="text" class="form-control" minlength="2" maxlength="2" size="2" name="exp_month" data-culqi="card[exp_month]" id="card[exp_month]" placeholder="MM *" required>
                    <input type="text" class="form-control" minlength="4" maxlength="4" size="4" name="exp_year" data-culqi="card[exp_year]" id="card[exp_year]" placeholder="AAAA *" required>
                  </div>
                </div>
                <div class="col-4 pl-2">
                  <input type="text" class="form-control" minlength="3" maxlength="3" size="4" name="cvv" data-culqi="card[cvv]" id="card[cvv]" placeholder="CVC *" required>
                </div>
              </div>
              <p class="fields_required">Todos los campos marcados con * son obligatorios.</p>
              <div class="text-center text-md-left">
                <button type="submit" class="d-inline-block btn btn-success btn-action ax_btn-next-form"><?php echo $atts['submit'] ?></button>
              </div>
            </form>
            <div id="message_payment_result" class="text-center" style="font-size: 18px;"></div>
          </div>
        </div>
      </div>
      <script src="https://checkout.culqi.com/v2"></script>
      <script>        
        window.token = '<?php echo get_option('token_public'); ?>';
        function culqi() {
          if (Culqi.token) {
            var token = Culqi.token;
            if (token.object != 'error') {
              if (token.active) {
                var fd = new FormData();
                fd.append('action',     "formPaymentGo");
                fd.append('cMq3Df',     "<?php echo wp_create_nonce(date('d')); ?>");
                fd.append('course',     "<?php echo $course_details['id']; ?>");
                fd.append('plan',       "<?php echo $atts['plan']; ?>");
                fd.append('country',    jQuery("#form_payment input[name=country]").val());
                fd.append('email',      jQuery("#form_payment input[name=email]").val());
                fd.append('mobile',     jQuery("#form_payment input[name=mobile]").val());
                fd.append('first_name', jQuery("#form_payment input[name=first_name]").val());
                fd.append('last_name',  jQuery("#form_payment input[name=last_name]").val());
                fd.append('dni',        jQuery("#form_payment input[name=dni]").val());
                fd.append('address',    jQuery("#form_payment input[name=address]").val());
                fd.append('city',       jQuery("#form_payment input[name=address_city]").val());
                fd.append('token',      JSON.stringify(token));

                jQuery.ajax({
                  type: 'POST',
                  url: '<?php echo admin_url('admin-ajax.php'); ?>',
                  data: fd,
                  contentType: false,
                  processData: false,
                  success:function(r) {
                    if (r.success == true) {
                      jQuery('#form_payment').waitMe({
                        effect: 'timer',
                        text: r.message,
                        bg: 'rgba(250, 250, 250, 0.75)',
                        color:'#88b53d',
                      });
                      jQuery('#message_payment_result').html('');
                      setTimeout(function() {
                        window.location = '<?php echo $atts['redirect']; ?>';
                      }, 2000);
                    } else {
                      jQuery('#form_payment').waitMe('hide');
                      jQuery('#message_payment_result').html(r.message).addClass('alert-error');
                    }
                  },
                  error: function() {}
                });
              }
            } else {
              jQuery('#form_payment').waitMe('hide');
              jQuery('#message_payment_result').html('Error al procesar la información.').addClass('alert-error');
            }
          } else {
            jQuery('#message_payment_result').html(Culqi.error.user_message).addClass('alert-error');
          }
        };
      </script>
      <?php
    } else {
      echo '<div style="color: red; text-align: center">Error: el curso no existe.</div>';
    }
  }
}