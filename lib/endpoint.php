<?php
class initEndPoint {
  public function __construct() {
    add_action('init', array($this, 'add_cors_http_header'));

    add_action( 'rest_api_init',
      function () {
        register_rest_route(
          'notification', '/slack/',
          array(
            'methods'  => 'POST',
            'callback' => array($this, 'slack_notification'),
          )
        );
      }
    );
  }

  function countryValue($id, $return) {
    $resources = new Resources_Payment();
    $value = $resources->countryArray[$id];
    return ucwords(strtolower($value[$return]));
  }

  function add_cors_http_header() {
    header("Access-Control-Allow-Origin: *");
  }

  function slack_notification( WP_REST_Request $request ) {
    $status    = $request['status'];
    $msg_user   = $request['msg_user'];
    $first_name = $request['first_name'];
    $last_name  = $request['last_name'];
    $course     = $request['course'];
    $amount     = $request['amount'];
    $mobile     = $request['mobile'];
    $country    = $request['country'];
    $plan       = $request['plan'];
    $email      = $request['email'];
    $slackMessagee = $this->slackMessage(
      $status,
      $msg_user,
      $first_name . ' ' . $last_name,
      $course,
      $amount,
      $country,
      $mobile,
      $email
    );

    if(get_option('notification_email') == '1') {
      $sendMaill = $this->sendMail(
        $course,
        $first_name,
        $last_name,
        $amount,
        $mobile,
        $country,
        $plan,
        $email
      );
      if ($sendMaill) {
        $data = array(
          'status'  => true
        );
      } else {
        $data = array(
          'status'  => false
        );
      }
    } else {
      $data = array(
        'status'  => true
      );
    }

    wp_send_json($data);
  }

  function slackMessage($status='', $msg_user = '', $user = '', $course = '', $amount = '', $country = '', $mobile = '', $email = '') {
    if ($status == 'ok') {
      $msg = "*Pago realizado:*\n *Monto:*     :heavy_dollar_sign: *" . $amount . "* \n *Curso:*       :books: " . $course . " \n *Persona:*    :man-raising-hand: " . $user;
      $attachments = array(
        array(
          'title'      => 'Escribir por WhatsApp a ' . $user,
          'title_link' => 'https://api.whatsapp.com/send?phone=' . $this->countryValue(strtoupper($country), 'code') . $mobile . '&text=Hola *' . $user . '*, tu inscripción al curso *' . $course . '* se ha procesado. Accede a tu curso en el siguiente enlace:',
          'text'       => 'Pais: ' . $this->countryValue(strtoupper($country), 'name') . ' - Numero: ' . $mobile . ' - Correo: ' . $email,
          'color'      => '#7CD197'
        )
      );
    } else {
      $msg = "*Se produjo un problema:* :skull: \n *Monto:*     :heavy_dollar_sign: *" . $amount . "* \n *Curso:*       :books: " . $course . " \n *Persona:*    :disappointed: " . $user . " \n *Mensaje:*   :sos: " . $msg_user;
      $attachments = array(
        array(
          'title'      => 'Escribir por WhatsApp a ' . $user,
          'title_link' => 'https://api.whatsapp.com/send?phone=' . $this->countryValue(strtoupper($country), 'code') . $mobile . '&text=Hola *' . $user . '*, si tienes algun problema para acceder al curso *' . $course . '* estoy aquí para poderte ayudar.',
          'text'       => 'Pais: ' . $this->countryValue(strtoupper($country), 'name') . ' - Numero: ' . $mobile . ' - Correo: ' . $email,
          'color'      => '#db4437'
        )
      );
    }

    try {
      $url = get_option('notification_slack');
      $response = wp_remote_request($url,
        array(
          'method' => 'POST',
          'body'   => array(
            'payload' => json_encode(
              array(
                "text"        => $msg,
                'attachments' => $attachments,
              )
            )
          )
        )
      );
      wp_remote_retrieve_body($response);

      $data_slack = array(
        "success"  =>  true
      );
    } catch(Exception $e) {
      $data_slack = array(
        "success"  =>  false
      );
    }

    return $data_slack;
  }

  function sendMail($curso = '', $first_name = '', $last_name = '', $amount = '', $mobile = '', $country = '', $plan = '', $email = '') {
    try {
      $subject = 'Pago: ' . $curso . ' - ' . $first_name;
      $body = '
      <h2>Arcux: Pagos con Culqi</h2>
      <p><strong>Curso:</strong> ' . $curso . '</p>
      <p><strong>Monto pagado:</strong> ' . substr($amount, 0, -2) . '</p>
      <hr>
      <p><strong>Nombre y Apellidos:</strong> ' . $first_name . ' ' . $last_name . '</p>
      <p><strong>Telefono:</strong> <a href="https://api.whatsapp.com/send?phone=' . $this->countryValue(strtoupper($country), 'code') . $mobile . '&text=Hola ' . $first_name . ' ' . $last_name . ',">' . $this->countryValue(strtoupper($country), 'code') . $mobile . '</a></p>
      <p><strong>Pais:</strong> ' . strtoupper($country) . '</p>
      <p><strong>Plan:</strong> ' . $plan . '</p>
      <p><strong>Correo:</strong> ' . $email . '</p>
      <p><br><br>Gracias.<br></p>
      ';
      $to = get_option('admin_email');
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail( $to, $subject, $body, $headers );

      $data_mail = array(
        "success"  =>  true
      );
    } catch(Exception $e) {
      $data_mail = array(
        "success"  =>  false
      );
    }

    return $data_mail;
  }
}