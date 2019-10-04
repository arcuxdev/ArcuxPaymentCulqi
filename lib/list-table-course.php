<?php
global $table_payment_course;
$table_payment_course = '0.1';

function payment_table_install() {
  global $wpdb;
  global $table_payment_course;

  $sql_course = "CREATE TABLE " . $wpdb->prefix . "payment_course (
    id int(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(200) NOT NULL,
    amount VARCHAR(10) NOT NULL,
    date_create VARCHAR(10) NOT NULL,
    date_update VARCHAR(10) NOT NULL,
    PRIMARY KEY (id)
  ) COLLATE {$wpdb->collate};";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql_course);

  add_option('table_payment_course', $table_payment_course);

  $installed_ver = get_option('table_payment_course');
  if ($installed_ver != $table_payment_course) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_course);

    update_option('table_payment_course', $table_payment_course);
  }
}
register_activation_hook(__FILE__, 'payment_table_install');

function payment_table_update_db_check() {
  global $table_payment_course;

  if (get_site_option('table_payment_course') != $table_payment_course) {
    payment_table_install();
  }
}
add_action('plugins_loaded', 'payment_table_update_db_check');

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Payment_List_Table extends WP_List_Table {
  function __construct() {
    global $status, $page;

    parent::__construct(
      array(
        'singular' => 'arcuxcourse',
        'plural'   => 'arcuxcourses',
      )
    );
  }

  function column_default($item, $column_name) {
    return $item[$column_name];
  }

  function column_code($item) {
    return '<strong>[arcux-payment course="' . $item['code'] . '" submit="Pagar" plan="full" image="" redirect=""]</strong>';
  }

  function column_amount($item) {
    return '<strong>' . $item['amount'] . '</strong>';
  }

  function column_date_create($item) {
    $resources = new Resources_Payment();
    return $resources->nicetime($item['date_create']);
  }

  function column_date_update($item) {
    $resources = new Resources_Payment();
    return $resources->nicetime($item['date_update']);
  }

  function column_name($item) {
    $actions = array(
      'edit' => sprintf('<a href="?page=courses_form&id=%s">%s</a>', $item['id'], __('Edit', 'custom_table_example')),
      'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'custom_table_example')),
    );

    return sprintf('%s %s',
      $item['name'],
      $this->row_actions($actions)
    );
  }

  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="id[]" value="%s" />',
      $item['id']
    );
  }

  function get_columns() {
    $columns = array(
      'cb'          => '<input type="checkbox" />',
      'name'        => 'Name',
      'code'        => 'Code',
      'amount'      => 'Amount',
      'date_create' => 'Date Create',
      'date_update' => 'Date Update',
    );

    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'name'        => array('name', true),
      'date_create' => array('date_create', false),
      'date_update' => array('date_update', false),
    );

    return $sortable_columns;
  }

  function get_bulk_actions() {
    $actions = array(
      'delete' => 'Delete'
    );
    return $actions;
  }

  function course_payment_process_bulk_action() {
    global $wpdb;

    if ('delete' === $this->current_action()) {
      $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
      if (is_array($ids)) $ids = implode(',', $ids);

      if (!empty($ids)) {
        $wpdb->query("DELETE FROM {$wpdb->prefix}payment_course WHERE id IN($ids)");
      }
    }
  }

  function prepare_items() {
    global $wpdb;
    $per_page = 10;
    $columns  = $this->get_columns();
    $hidden   = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->course_payment_process_bulk_action();

    $paged   = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
    $order   = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

    $this->items = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}payment_course ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged
      ), ARRAY_A
    );

    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}payment_course");

    $this->set_pagination_args(
      array(
        'total_items' => $total_items,
        'per_page'    => $per_page,
        'total_pages' => ceil($total_items / $per_page)
      )
    );
  }
}