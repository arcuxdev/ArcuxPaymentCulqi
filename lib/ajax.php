<?php
class Ajax_payment {
  public function __construct() {
    add_action('wp_ajax_formPaymentGo', [$this, 'formPaymentGo']);
    add_action('wp_ajax_nopriv_formPaymentGo', [$this, 'formPaymentGo']);
  }

  function countryValue($id, $return) {
    $resources = new Resources_Payment();
    $value = $resources->countryArray[$id];
    return ucwords(strtolower($value[$return]));
  }

  function ownName($cadena) {
    $cadena=mb_convert_case($cadena, MB_CASE_TITLE, "utf8");
    return($cadena);
  }

  function notification($data = []) {
    $url = home_url() . '/wp-json/notification/slack/';
    $response = wp_remote_request($url,
      array(
        'method' => 'POST',
        'body'   => $data
      )
    );
    wp_remote_retrieve_body($response);
  }

  public function formPaymentGo() {
    global $wpdb;
    $nonce       = sanitize_text_field($_POST['cMq3Df']);
    $token       = wp_strip_all_tags($_POST['token']);
    $tokenfilter = str_replace('\"', '"', $token);
    $tokenall    = json_decode($tokenfilter);
    $first_name  = $this->ownName($_POST['first_name']);
    $last_name   = $this->ownName($_POST['last_name']);
    $mobile      = sanitize_text_field($_POST['mobile']);
    $plan        = sanitize_text_field($_POST['plan']);
    $country     = sanitize_text_field($_POST['country']);
    $email       = sanitize_text_field($_POST['email']);
    $course      = sanitize_text_field($_POST['course']);
    $dni         = sanitize_text_field($_POST['dni']);
    $address     = sanitize_text_field($_POST['address']);
    $city        = sanitize_text_field($_POST['city']);

    if (
      wp_verify_nonce($nonce, date('d')) &&
      $tokenall->id
    ) {
      $course_total = $wpdb->get_row(
        "SELECT * FROM {$wpdb->prefix}payment_course WHERE id = '$course'",
        ARRAY_A
      );

      $culqi = new \Culqi\Culqi(
        array('api_key' => get_option('token_private'))
      );

      if ($course_total['id'] == $course) {
        try {
          $data_culqi = $culqi->Charges->create(
            array(
              "amount"            =>  $course_total['amount'] . '00',
              "antifraud_details" =>  array(
                "first_name"      =>  $first_name,
                "last_name"       =>  $last_name,
                "address"         =>  $address,
                "address_city"    =>  $city,
                "country_code"    =>  strtoupper($country),
                "phone_number"    =>  $mobile,
              ),
              "capture"           =>  true,
              "currency_code"     =>  "USD",
              "description"       =>  $course_total['name'],
              "installments"      =>  0,
              "email"             =>  $email,
              "metadata"          =>  array(
                "order_id"        =>  $course_total['code'] . "_".rand(1, 99999),
                "course_id"       =>  $course_total['id'],
                "course_code"     =>  $course_total['code'],
                "dni"             =>  $dni
              ),
              "source_id"         =>  $tokenall->id
            )
          );
        } catch(Exception $e) {
          $data_culqi = array(
            "success"  =>  false,
            "message"  =>  $e->getMessage()
          );
        }
      } else {
        $data = array(
          "success"  =>  false,
          "message"  => "El curso no coincide."
        );
      }

      if ($data_culqi) {
        if ($data_culqi->object === 'charge') {
          if ($data_culqi->outcome->type === 'venta_exitosa') {
            $result = $wpdb->insert(
              "{$wpdb->prefix}payment_person",
              array(
                'first_name'      => $first_name,
                'last_name'       => $last_name,
                'dni'             => $dni,
                'mobile'          => $mobile,
                'country'         => $country,
                'address'         => $address,
                'city'            => $city,
                'email'           => $email,
                'course'          => $course_total['id'],
                'plan'            => $plan,
                'token_checkout'  => $tokenall->id,
                'token_payment'   => $data_culqi->id,
                'amount_init'     => $course_total['amount'],
                'amount_payment'  => substr($data_culqi->amount, 0, -2),
                'date_create'     => time(),
              )
            );

            if($result) {
              $urlSlack = get_option('notification_slack');
              if ($urlSlack) {
                $data = array(
                  'status'      => 'ok',
                  'msg_user'    => '',
                  'first_name'  => $first_name,
                  'last_name'   => $last_name,
                  'course'      => $course_total['name'],
                  'amount'      => substr($data_culqi->amount, 0, -2),
                  'mobile'      => $mobile,
                  'country'     => strtoupper($country),
                  'plan'        => $plan,
                  'email'       => $email
                );
                $this->notification($data);
              }
              $data = array(
                "success"  =>  true,
                "message"  => 'Tu pago se a realizado. '
              );
            } else {
              $data = array(
                "success"  => false,
                "message"  => 'incorrecto.'
              );
            }
          }
        } else if ($data_culqi->object === 'error') {
          $data = array(
            'status'      => 'off',
            'msg_user'    => $data_culqi->user_message,
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'course'      => $course_total['name'],
            'amount'      => $course_total['amount'],
            'mobile'      => $mobile,
            'country'     => strtoupper($country),
            'plan'        => $plan,
            'email'       => $email
          );
          $this->notification($data);
          $data = array(
            "success" =>  false,
            "message" =>  $data_culqi->user_message
          );
        }
      } else {
        $data = array(
          "status"  =>  false,
          "message" =>  "Incorrect"
        );
      }
    } else {
      $data = array(
        "status"  =>  false,
        "message" =>  "Por favor vuelva a actualizar la pagina."
      );
    }
    wp_send_json($data);
  }
}

new Ajax_payment();
