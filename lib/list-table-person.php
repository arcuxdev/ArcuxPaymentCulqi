<?php
global $table_payment_person;
$table_payment_person = '0.1';

function payment_person_table_install() {
  global $wpdb;
  global $table_payment_person;

  $sql_person = "CREATE TABLE " . $wpdb->prefix . "payment_person (
    id int(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(200) NOT NULL,
    last_name VARCHAR(200) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    mobile VARCHAR(50) NOT NULL,
    country VARCHAR(6) NOT NULL,
    address VARCHAR(200) NOT NULL,
    city VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    course int(11) NOT NULL,
    plan VARCHAR(4) NOT NULL,
    token_checkout VARCHAR(50) NOT NULL,
    token_payment VARCHAR(50) NOT NULL,
    amount_init VARCHAR(50) NOT NULL,
    amount_payment VARCHAR(50) NOT NULL,
    date_create VARCHAR(10) NOT NULL,
    date_update VARCHAR(10) NOT NULL,
    PRIMARY KEY (id)
  ) COLLATE {$wpdb->collate};";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql_person);

  add_option('table_payment_person', $table_payment_person);

  $installed_ver = get_option('table_payment_person');
  if ($installed_ver != $table_payment_person) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_person);

    update_option('table_payment_person', $table_payment_person);
  }
}
register_activation_hook(__FILE__, 'payment_person_table_install');

function payment_course_table_update_db_check() {
  global $table_payment_person;

  if (get_site_option('table_payment_person') != $table_payment_person) {
    payment_person_table_install();
  }
}

add_action('plugins_loaded', 'payment_course_table_update_db_check');

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Payment_Person_List_Table extends WP_List_Table {
  function __construct() {
    global $status, $page;

    parent::__construct(
      array(
        'singular' => 'arcuxpayment',
        'plural'   => 'arcuxpayments',
      )
    );
  }

  function countryValue($id, $return) {
    $resources = new Resources_Payment();
    $value = $resources->countryArray[$id];
    return ucwords(strtolower($value[$return]));
  }

  function column_default($item, $column_name) {
    return $item[$column_name];
  }

  function column_country($item) {
    return '<strong>' . $this->countryValue(strtoupper($item['country']), 'name') . '</strong>';
  }

  function column_amount_payment($item) {
    return '<strong>$' . $item['amount_payment'] . '</strong> USD';
  }

  function column_course($item) {
    $resources = new Resources_Payment();
    return $resources->nameCourse($item['course']);
  }

  function column_date_create($item) {
    $resources = new Resources_Payment();
    return $resources->nicetime($item['date_create']);
  }

  function column_first_name($item) {
    $actions = array(
      'edit'    =>  sprintf('<a href="?page=person_payment&id=%s">%s</a>', $item['id'], 'Edit'),
      'delete'  =>  sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], 'Delete'),
    );

    return sprintf('%s %s',
      $item['first_name'],
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
      'cb'              =>  '<input type="checkbox" />',
      'first_name'      =>  'First name',
      'last_name'       =>  'Last name',
      'country'         =>  'Country',
      'amount_payment'  =>  'Amount',
      'course'          =>  'Curso',
      'date_create'     =>  'Date Create',
    );

    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'date_create' => array('date_create', false),
    );

    return $sortable_columns;
  }

  function get_bulk_actions() {
    $actions = array(
      'delete' => 'Delete'
    );
    return $actions;
  }

  function person_payment_process_bulk_action() {
    global $wpdb;

    if ('delete' === $this->current_action()) {
      $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
      if (is_array($ids)) $ids = implode(',', $ids);

      if (!empty($ids)) {
        // delete person of wp
        $wpdb->query("DELETE FROM {$wpdb->prefix}payment_person WHERE id IN($ids)");
      }
    }
  }

  function person_payment_prepare_items() {
    global $wpdb;
    $per_page = 10;
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->person_payment_process_bulk_action();

    $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'date_create';
    $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

    $this->items = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}payment_person ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged
      ), ARRAY_A
    );

    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}payment_person");

    $this->set_pagination_args(
      array(
        'total_items' => $total_items,
        'per_page' => $per_page,
        'total_pages' => ceil($total_items / $per_page)
      )
    );
  }
}