<?php
class initPaymentPerson {
  public function __construct() {
    add_action('admin_menu', array($this,'payment_person_table_admin_menu'));
  }

  function payment_person_table_admin_menu() {
    add_menu_page('Arcux Payment', 'Arcux Payment', 'activate_plugins', 'arcuxpayment', array($this,'payment_menu_init_page_handler'), plugins_url('../icon/', __FILE__) . 'arcux.png',
    2);
    add_submenu_page('arcuxpayment','List Person','List Person', 'activate_plugins', 'arcuxpayment', array($this,'payment_menu_init_page_handler'));
    add_submenu_page('arcuxpayment', 'New Person', '+ New Person', 'activate_plugins', 'person_payment', array($this,'payment_admin_form_handler'));
  }

  function payment_menu_init_page_handler() {
    global $wpdb;

    $table = new Payment_Person_List_Table();
    $table->person_payment_prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
      $message = '<div class="notice notice-error" id="message"><p>Person deleted but not his operation in <strong>Culqi</strong>.</p></div>';
    }
    ?>
    <div class="wrap">
      <h2>People who paid</h2>
      <?php echo $message; ?>
      <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php
          $table->display();
        ?>
      </form>
    </div>
  <?php
  }

  function payment_admin_form_handler() {
    global $wpdb;
    $resources =  new Resources_Payment();
    $message   =  '';
    $notice    =  '';
    $default   =  array(
      'id'          => 0,
      'first_name'  => '',
      'last_name'   => '',
      'mobile'      => '',
      'email'       => '',
    );

    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
      $item = shortcode_atts($default, $_REQUEST);
      $item_valid = $this->custom_table_validate_person($item);
      if ($item_valid === true) {
        if ($item['id'] == 0) {
          $result = $wpdb->insert("{$wpdb->prefix}payment_person",
            array(
              'first_name'      =>  $item['first_name'],
              'last_name'       =>  $item['last_name'],
              'mobile'          =>  $item['mobile'],
              'country'         =>  $item['country'],
              'email'           =>  $item['email'],
              'course'          =>  $item['course'],
              'token_checkout'  =>  $item['token_checkout'],
              'token_payment'   =>  $item['token_payment'],
              'amount_init'     =>  $item['amount_init'],
              'amount_payment'  =>  $item['amount_payment'],
              'date_create'     =>  time(),
            )
          );

          if ($result) {
            $message = 'Item was successfully create saved';
          } else {
            $notice = 'There was an error while saving item';
          }
        } else {
          $result = $wpdb->update(
            "{$wpdb->prefix}payment_person",
            array(
              'id'              =>  $item['id'],
              'first_name'      =>  $item['first_name'],
              'last_name'       =>  $item['last_name'],
              'mobile'          =>  $item['mobile'],
              'country'         =>  $item['country'],
              'email'           =>  $item['email'],
              'course'          =>  $item['course'],
              'token_checkout'  =>  $item['token_checkout'],
              'token_payment'   =>  $item['token_payment'],
              'amount_init'     =>  $item['amount_init'],
              'amount_payment'  =>  $item['amount_payment'],
            ), array('id'       =>  $item['id'])
          );

          if ($result) {
            $message = 'Item was successfully update updated';
          } else {
            $notice = 'There was an error while updating item';
          }
        }
      } else {
        $notice = $item_valid;
      }
    } else {
      $item = $default;
      if (isset($_REQUEST['id'])) {
        $item = $wpdb->get_row(
          $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}payment_person WHERE id = %d",
            $_REQUEST['id']
          ), ARRAY_A
        );
        if (!$item) {
          $item = $default;
          $notice = 'Item not found';
        }
      }
    }

    add_meta_box('person_payment_meta_box', 'Person data', array($this,'form_payment_handler'), 'person_payment', 'normal', 'default'); ?>
    <div class="wrap">
      <div class="icon32 icon32-posts-post" id="icon-edit">
        <br>
      </div>
      <h2>
        Payment
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=arcuxpayment');?>">
          back to list
        </a>
      </h2>
      <?php if (!empty($notice)): ?>
        <div id="notice" class="error">
          <p><?php echo $notice ?></p>
        </div>
      <?php endif;?>
      <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo $message ?></p></div>
      <?php endif;?>
      <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>" />
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
        <div class="metabox-holder" id="poststuff">
          <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container">
              <div id="side-sortables" class="meta-box-sortables ui-sortable">
                <div id="submitdiv" class="postbox ">
                  <h2 class="hndle ui-sortable-handle"><span>Publicar</span></h2>
                  <div class="inside">
                    <div class="submitbox" id="submitpost">
                      <div id="minor-publishing">
                        <div class="misc-pub-section">
                          <input type="submit" value="Save Payment" id="submit" class="button button-primary button-large" name="submit">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="postbox-container-2" class="postbox-container">
              <div id="post-body">
                <div id="post-body-content">
                  <?php do_meta_boxes('person_payment', 'normal', $item); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <style>
    <?php
      include_once(plugin_dir_path( __FILE__ ) . '../css/table-result.css');
    ?>
    </style>
    <?php
  }

  function form_payment_handler($item) {
    global $wpdb; ?>
    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
      <tbody>
      <tr class="form-field">
        <th valign="top" scope="row">
          <label for="first_name">First Name</label>
        </th>
          <td>
            <input id="first_name" name="first_name" type="text" style="width: 95%" value="<?php echo esc_attr($item['first_name'])?>" size="50" class="code" required>
          </td>
          <th valign="top" scope="row">
            <label for="last_name">Last Name</label>
          </th>
          <td>
            <input id="last_name" name="last_name" type="text" style="width: 95%" value="<?php echo esc_attr($item['last_name'])?>" size="50" class="code" required>
          </td>
        </tr>
        <tr class="form-field">
          <th valign="top" scope="row">
            <label for="mobile">mobile</label>
          </th>
          <td>
            <input id="mobile" name="mobile" type="text" style="width: 95%" value="<?php echo esc_attr($item['mobile'])?>" size="50" class="code" required>
          </td>
          <th valign="top" scope="row">
            <label for="country">email</label>
          </th>
          <td>
          <input id="email" name="email" type="text" style="width: 95%" value="<?php echo esc_attr($item['email'])?>" size="50" class="code" required>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
    if ($item['course']) { ?>
      <div class="table_leads">
        <table>
          <caption>
            <?php
            $resources = new Resources_Payment();
            echo $resources->nameCourse($item['course']);
            ?>
          </caption>
          <thead>
            <tr>
              <th scope="col">Token Checkout</th>
              <th scope="col">Token Payment</th>
              <th scope="col">Amount Init</th>
              <th scope="col">Amount Payment</th>
            </tr>
          </thead>
          <tbody>
          <tr>
            <td scope="col">
              <?php echo esc_attr($item['token_checkout'])?>
            </td>
            <td scope="col">
              <?php echo esc_attr($item['token_payment'])?>
            </td>
            <td scope="col">
              $<?php echo esc_attr($item['amount_init'])?>  USD
            </td>
            <td scope="col">
              $<?php echo esc_attr($item['amount_payment'])?> USD
            </td>
          </tr>
          </tbody>
        </table>
        <table>
          <thead>
            <tr>
              <th scope="col">Country</th>
              <th scope="col">DNI</th>
            </tr>
          </thead>
          <tbody>
          <tr>
            <td scope="col">
              <?php echo esc_attr($item['country'])?>
            </td>
            <td scope="col">
              <?php echo esc_attr($item['dni'])?>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <?php
    }
  }

  function custom_table_validate_person($item) {
    $messages = array();

    if (empty($item['first_name'])) $messages[] = 'first name is required';
    if (empty($item['last_name'])) $messages[]  = 'last name is required';
    if (empty($item['mobile'])) $messages[]     = 'mobile is required';
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = 'E-Mail is in wrong format';

    if (empty($messages)) return true;
    return implode('<br />', $messages);
  }
}