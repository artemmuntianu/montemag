<?php

if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class InScale_Product_List_Table extends WP_List_Table
{

  public function __construct()
  {
    parent::__construct(array(
      'singular' => __('product', 'is_list_table'),
      'plural' => __('products', 'is_list_table'),
      'ajax' => false
    ));
  }

  public function no_items()
  {
    _e('No products found.');
  }

  public function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'name':
        return $item[$column_name];
      default:
        return print_r($item, true);
    }
  }

  public function get_sortable_columns()
  {
    $sortable_columns = array(
      'name' => array('name', false)
    );
    return $sortable_columns;
  }

  public function get_columns()
  {
    $columns = array(
      'name' => __('Name', 'is_list_table')
    );
    return $columns;
  }

  public function usort_reorder($a, $b)
  {
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'name';
    $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
    $result = strcmp($a[$orderby], $b[$orderby]);
    return ($order === 'asc') ? $result : -$result;
  }

  public function column_name($item)
  {
    $actions = array(
      'edit' => sprintf('<a href="?page=%s&action=%s&product=%s">Edit</a>', $_REQUEST['page'], 'image_combining', $item['id'])
    );
    return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
  }

  public function prepare_items()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'posts';
    $per_page = 5;
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_type = 'product'");
    $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
    $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
    $dbQuery = $wpdb->prepare("SELECT ID as id, post_title as name FROM $table_name WHERE post_type = 'product' ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged);
    $this->items = $wpdb->get_results($dbQuery, ARRAY_A);
    $this->set_pagination_args(array(
      'total_items' => $total_items,
      'per_page' => $per_page,
      'total_pages' => ceil($total_items / $per_page)
    ));
  }
}