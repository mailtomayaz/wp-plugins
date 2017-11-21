<?php
/*
 * Plugin Name: Simple WP plugin with database table 
 * Description: this plugin creates database table 
 * Plugin Uri: ''
 * Author URI: ''
 * Author: Muhammad Ayaz
 * License: Public Domain
 * Version: 1.0
 * email: mailtomayaz@gmail.com
 */

/*
 * Defining custom table
 * 
 * 
 *  */

global $custom_table_db_version;

//version of the table
$custom_table_db_version = '1.0';

/*

 * register_activiation_hook implementation
 * will be called when user activites plugin first time
 * 
 * PART 1 Definig Custom Table
 *  */

function custom_table_db_install() {

    global $wpdb;
    global $custom_table_db_version;
    //table name
    $table_name = $wpdb->prefix . 'custom_db_tbl';
    //sql to create table
    $sql = "CREATE TABLE " . $table_name . "("
            . "id int(11) NOT NULL AUTO_INCREMENT,"
            . "name tinytext NOT NULL,"
            . "email VARCHAR(100) NOT NULL,"
            . "age int(11) NULL,"
            . "PRIMARY KEY (id)"
            . ")";

    //Do not execute query directly, we are calling dbDelta which can migrate database
    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for upgrade
    add_option('custom_table_db_version', $custom_table_db_version);

    /*

     * Example of updating to 1.1 version
     * must be repeated for each new version
     * we are using dbDalta to migrate table changes
     * 
     *      */

    $installed_ver = get_option('custom_table_db_version');
    if ($installed_ver != $custom_table_db_version) {
        $sql = "CREATE TABLE " . $table_name . "("
                . "id int(11) NOT NULL AUTO_INCREMENT,"
                . "name tinytext NOT NULL,"
                . "email VARCHAR(200) NOT NULL ,"
                . "age int(11) NULL"
                . "PRIMARY KEY (id)"
                . ")";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        //update options
        update_option('custom_table_db_version', $custom_table_vesion);
    }
}

/*

 * register hook
 * create table when plugin activated
 * 
 *  */
register_activation_hook(__FILE__, 'custom_table_db_install');

/*

 * Intall custom data
 * 
 *  */

function custom_table_install_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_db_tbl';
    $wpdb->insert($table_name, array(
        'name' => 'Ayaz',
        'email' => 'mailtomayaz@gmail.com',
        'age' => '36'
    ));
    $wpdb->insert($table_name, array(
        'name' => 'Fahad',
        'email' => 'fahad@gmail.com',
        'age' => '25'
    ));
}

/*

 * register activation hook to insert data into table
 * 
 *  */

register_activation_hook(__FILE__, 'custom_table_install_data');


/*

 * Updating plugin database
 * 
 * 
 *  */

function custom_table_db_check() {
    global $custom_table_db_version;
    if (get_site_option('custom_table_db_version') != $custom_table_db_version) {
        custom_table_db_install();
    }
}

//add action
//add_action('plugins_loaded','custom_table_db_check');

/*
 * Part 2 
 * Custom table list of our table
 * 
 * 
 *  */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/*

 * 
 * custom class which will show table data in a good way
 * 
 *  */

class Custom_Table_list extends WP_List_Table {

    //constructure
    function __construct() {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'Person',
            'plural' => 'Persons'
                )
        );
    }

    //default column render
    /*

     * @param $item - row (key, value array)
     * @param $column_name -string (key)
     * @return HTML
     *      */

    function column_default($item, $column_name) {
        //parent::column_default($item, $column_name);
        return $item[$column_name];
    }

    /*

     * How to render specific column
     * method name would be "column_[column_name]"
     *      */

    function column_age($item) {
        return '<em>' . $item['age'] . '</em>';
    }

    /*

     * nama field
     *      */

    function column_name($item) {

        $actions = array(
            'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 'custom_table_example')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'custom_table_example')),
        );
        return sprintf('%s %s', $item['name'], $this->row_actions($actions)
        );
    }

    /*

     * Required
     * checkbox column renders
     *      */

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }

    /*

     * Required
     * This method returns columns to display in table
     * 
     *      */

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'custom_table_example'),
            'email' => __('Email', 'custom_table_example'),
            'age' => __('Age', 'custom_table_example'),
        );
        return $columns;
    }

    /*

     * Optional 
     * This method return column that may be used to sort table
     * 
     * @return array
     *  */

    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array('name', true),
            'email' => array('email', false),
            'age' => array('age', false)
        );
        return $sortable_columns;
    }

    /*
     * optional 
     * returns array of bulk actions if has any
     * @returns array
     * 
     *    */

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /*

     * Processing of bulk items
     *    */

    function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_db_tbl'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids))
                $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /*
     * required
     * it will get rows from database and shows in tabble
     * 
     * 
     *    */

    function prepare_items() {
        global $wpdb;

        //name of table to get records
        $table_name = $wpdb->prefix . 'custom_db_tbl';
        //number of recoreds per page
        $per_page = 5;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        //configure the table header
        $this->_column_headers = array($columns, $hidden, $sortable);

        //process bulk action if any      
        $this->process_bulk_action();

        //paganiation settings

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        //prepar query 
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        //Required
        //define items array

        $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page' => $per_page,
                    'total_pages' => ceil($total_items / $per_page)// calculate page count
        ));
    }

}

/*

 * Admin Page
 * Custom page for the backend table
 * admin_menu hook implementation 
 * add menu to backend
 *  */

function custom_plugin_admin_menu() {


    add_menu_page(__('Persons', 'custom_table_example'), __('Persons', 'custom_table_example'), 'activate_plugins', 'persons', 'custom_table_example_persons_page_handler');
    add_submenu_page('persons', __('Persons', 'custom_table_example'), __('Persons', 'custom_table_example'), 'activate_plugins', 'persons', 'custom_table_example_persons_page_handler');
    // add new will be described in next part
    add_submenu_page('persons', __('Add new', 'custom_table_example'), __('Add new', 'custom_table_example'), 'activate_plugins', 'persons_form', 'custom_table_example_persons_form_page_handler');
}

//add into hook
add_action('admin_menu', 'custom_plugin_admin_menu');

/*

 * List page handler
 * this function renders custom table
 * 
 *  */

function custom_table_example_persons_page_handler() {
    global $wpdb;

    $table = new Custom_Table_list();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'custom_table_example'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Persons', 'custom_table_example') ?> <a class="add-new-h2"
                                                             href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons_form'); ?>"><?php _e('Add new', 'custom_table_example') ?></a>
        </h2>
    <?php echo $message; ?>

        <form id="persons-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
    <?php $table->display() ?>
        </form>

    </div>
    <?php
}

/*

 * Add and edit rows
 * 
 *  */

function custom_table_example_persons_form_page_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_db_tbl'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'age' => null,
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = custom_table_example_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'custom_table_example');
                } else {
                    $notice = __('There was an error while saving item', 'custom_table_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'custom_table_example');
                } else {
                    $notice = __('There was an error while updating item', 'custom_table_example');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    } else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'custom_table_example');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Person data', 'custom_table_example_persons_form_meta_box_handler', 'person', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Person', 'custom_table_example') ?> <a class="add-new-h2"
                                                            href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons'); ?>"><?php _e('back to list', 'custom_table_example') ?></a>
        </h2>

    <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
    <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
    <?php /* And here we call our custom meta box */ ?>
    <?php do_meta_boxes('person', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Save', 'custom_table_example') ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
        <?php
    }

    /**
     * custom meta box
     */
    function custom_table_example_persons_form_meta_box_handler($item) {
        ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php _e('Name', 'custom_table_example') ?></label>
                </th>
                <td>
                    <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name']) ?>"
                           size="50" class="code" placeholder="<?php _e('Your name', 'custom_table_example') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('E-Mail', 'custom_table_example') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 95%" value="<?php echo esc_attr($item['email']) ?>"
                           size="50" class="code" placeholder="<?php _e('Your E-Mail', 'custom_table_example') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="age"><?php _e('Age', 'custom_table_example') ?></label>
                </th>
                <td>
                    <input id="age" name="age" type="number" style="width: 95%" value="<?php echo esc_attr($item['age']) ?>"
                           size="50" class="code" placeholder="<?php _e('Your age', 'custom_table_example') ?>" required>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

/**
 * error handling
 */
function custom_table_example_validate_person($item) {
    $messages = array();

    if (empty($item['name']))
        $messages[] = __('Name is required', 'custom_table_example');
    if (!empty($item['email']) && !is_email($item['email']))
        $messages[] = __('E-Mail is in wrong format', 'custom_table_example');
    if (!ctype_digit($item['age']))
        $messages[] = __('Age in wrong format', 'custom_table_example');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages))
        return true;
    return implode('<br />', $messages);
}

/**
 * language settings
 */
function custom_table_example_languages() {
    load_plugin_textdomain('custom_table_example', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'custom_table_example_languages');
