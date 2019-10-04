<?php
class iniPaymentCourse {
  public function __construct() {
    add_action('admin_menu', array($this,'table_course_admin_menu'));
  }

  function table_course_admin_menu() {
    add_submenu_page('arcuxpayment','List courses','List courses', 'activate_plugins', 'arcuxcourses', array($this,'payment_course_menu_init_page_handler'));
    add_submenu_page('arcuxpayment', 'New courses', '+ New courses', 'activate_plugins', 'courses_form', array($this,'course_admin_form_handler'));
  }

  function payment_course_menu_init_page_handler() {
    global $wpdb;
    $table = new Payment_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
      $message = '<div class="updated below-h2" id="message"><p>item borrado.</p></div>';
    } ?>
    <div class="wrap">
      <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
      <h2>
        Course
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=courses_form');?>">
          New Course
        </a>
      </h2>
      <?php echo $message; ?>
      <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
        <?php
          $table->display();
        ?>
      </form>
    </div>
  <?php
  }

  function course_admin_form_handler() {
    global $wpdb;
    $message = '';
    $notice = '';
    $default = array(
      'id' => 0,
      'name' => '',
      'code' => '',
      'amount' => ''
    );

    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
      $item = shortcode_atts($default, $_REQUEST);

      $item_valid = $this->custom_table_validate_courses($item);
      if ($item_valid === true) {
        if ($item['id'] == 0) {
          $result = $wpdb->insert("{$wpdb->prefix}payment_course",
            array(
              'name'        =>  $item['name'],
              'code'        =>  preg_replace('/\s/', '', $item['code']),
              'amount'      =>  $item['amount'],
              'date_create' =>  time(),
            )
          );
          if ($result) {
            $message = 'Item was successfully create saved';
          } else {
            $notice = 'There was an error while saving item';
          }
        } else {
          $result = $wpdb->update("{$wpdb->prefix}payment_course",
            array(
              'id'          =>  $item['id'],
              'name'        =>  $item['name'],
              'code'        =>  preg_replace('/\s/', '', $item['code']),
              'amount'      =>  $item['amount'],
              'date_update' =>  time(),
            ), array('id'   =>  $item['id'])
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
            "SELECT * FROM {$wpdb->prefix}payment_course WHERE id = %d",
            $_REQUEST['id']
          ), ARRAY_A
        );
        if (!$item) {
          $item = $default;
          $notice = 'Item not found';
        }
      }
    }

    add_meta_box('courses_form_meta_box', 'Campaign data', array($this,'form_course_handler'), 'courses_form', 'normal', 'default'); ?>
    <div class="wrap">
      <div class="icon32 icon32-posts-post" id="icon-edit">
        <br>
      </div>
      <h2>
        Courses
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=arcuxcourses');?>">
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
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
        <div class="metabox-holder" id="poststuff">
          <div id="post-body">
            <div id="post-body-content">
              <?php do_meta_boxes('courses_form', 'normal', $item); ?>
              <input type="submit" value="Save Course" id="submit" class="button-primary" name="submit">
            </div>
          </div>
        </div>
      </form>
    </div>
    <?php
  }

  function form_course_handler($item) { ?>
    <table cellspacing="2" cellpadding="5" style="width: 50%;" class="form-table">
      <tbody>
        <tr class="form-field">
          <th valign="top" scope="row">
            <label for="name">Name</label>
          </th>
          <td>
            <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name'])?>" size="50" class="code" required>
          </td>
        </tr>
        <tr class="form-field">
          <th valign="top" scope="row">
            <label for="code">Code</label>
          </th>
          <td>
            <input id="code" name="code" type="text" style="width: 95%" value="<?php echo preg_replace('/\s/', '', $item['code']); ?>" size="50" class="code" required <?php echo $item['code'] ? 'readonly' : '' ?>>
            <p>Required values without spaces</p>
          </td>
        </tr>
        <tr class="form-field">
          <th valign="top" scope="row">
            <label for="amount">Amount</label>
          </th>
          <td>
            <input id="amount" name="amount" type="number" style="width: 20%" value="<?php echo esc_attr($item['amount'])?>" size="50" class="code" required>
            <p>Amount in USD</p>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
      if ($item['code']) { ?>
        <p>
          <strong>[arcux-payment course="<?php echo $item['code']; ?>" submit="Pagar" plan="full" image="" redirect=""]</strong>
        </p>
        <p><strong>Plan Options:</strong> year, full, free</p>
        <?php
      }
      ?>
    <script>
      (function($) {
        $(document).ready(function() {
          $("#name").on("input", function() {
            $("#code").val(this.value);
            $("#code").val($("#code").val().replace(/\s/g, ""));
          });
          $("#code").change(function() {
            $(this).val($(this).val().replace(/\s/g, ""));
          });
        });
      })(jQuery);
    </script>
    <?php
  }

  function custom_table_validate_courses($item) {
    $messages = array();

    if (empty($item['name'])) $messages[]   = 'name is required';
    if (empty($item['code'])) $messages[]   = 'code is required';
    if (empty($item['amount'])) $messages[] = 'amount are required';

    if (empty($messages)) return true;
    return implode('<br />', $messages);
  } 
}