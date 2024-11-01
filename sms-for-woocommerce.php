<?php

 /**
 * Plugin Name: Text Message SMS Extension for WooCommerce
 * Description: Integrate SMS with WooCommerce to send order Text notifications that allow for replies. 
 * Version: 1.1.0
 * Author: Biz Text
 * Author URI: https://www.biztextsolutions.com/
 * Developer: Biz Text
 * Developer URI: https://www.biztextsolutions.com/
 * Text Domain: text-message-sms-extension-for-woocommerce
 * Domain Path: /languages
 *
 * WC requires at least: 3.9.1 
 * WC tested up to: 4.0.1 
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
 

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    //define constants below
    if ( ! defined( 'WPBIZTEXTWC_ADMIN_READ_WRITE_CAPABILITY' ) ) {
        define( 'WPBIZTEXTWC_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
    }

    /**
     * Check if WooCommerce is active
     **/
 
     if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
        // add to WooCommerce Admin Menu
        function wpbiztextwc_submenu_page() {
    
            add_submenu_page( 
                    'woocommerce', 
                    'SMS Biz Text', 
                    'SMS Biz Text', 
                    'edit_pages', 
                    'admin.php?page=wc-settings&tab=wpbiztextwc', 
                    '' 
            );

        } 
        add_action('admin_menu', 'wpbiztextwc_submenu_page');
        
         // set defaults
        if (!get_option('wpbiztextwc_hidden_field')){
    
            $default_options = array(
        
                'biztext_id' => '',
                'hidden_field' => 'N',
                'placeholders' => array("{ORDER_NUMBER}","{FIRST_NAME}","{LAST_NAME}","{PHONE_NUMBER}","{ORDER_TOTAL}","{ORDER_DATE}","{SITE_URL}","{ORDER_NOTE}","{ORDER_ITEMS}","{VIEW_ORDER_LINK}","{SITE_NAME}"),
                'notif_number' => '',
                'notif_number_nickname' => '',
                'mobile_field_info' => __( 'Receive SMS Order Notifications', 'my-text-domain' ),
                'mobile_field_requried' => 'yes',
                'add_mobile_field' => 'yes',
                'mobile_field_use_phone' => 'no',
                'enable_pending_order_chbx' => 'yes', // Customer SMS Notifications
                'set_pending_order_txtarea' => __( '{FIRST_NAME}, thank you for choosing {SITE_NAME}. Your order #{ORDER_NUMBER} is pending payment. You can view here {VIEW_ORDER_LINK}.', 'my-textdomain' ),
                'enable_processing_order_chbx' => 'yes',
                'set_processing_order_txtarea' => __('{FIRST_NAME}, payment for your order #{ORDER_NUMBER} has been accepted.', 'my-textdomain'),
                'enable_completed_order_chbx' => 'yes',
                'set_completed_order_txtarea' => __('{FIRST_NAME}, your order #{ORDER_NUMBER}. Thank you for shopping on {SITE_NAME}.', 'my-textdomain'),
                'enable_cancelled_order_chbx' => 'yes',
                'set_cancelled_order_txtarea' => __('{FIRST_NAME}, your order #{ORDER_NUMBER} has been cancelled.', 'my-textdomain' ),
                'enable_failed_order_chbx' => 'yes',
                'set_failed_order_txtarea' => __('{FIRST_NAME}, your order #{ORDER_NUMBER} payment has failed.', 'my-textdomain' ),
                'enable_on-hold_order_chbx' => 'yes',
                'set_on-hold_order_txtarea' => __( '{FIRST_NAME}, your order #{ORDER_NUMBER} is verifying payment.', 'my-textdomain' ),
                'enable_refunded_order_chbx' => 'yes',
                'set_refunded_order_txtarea' => __('{FIRST_NAME}, your order #{ORDER_NUMBER} has been refunded.', 'my-textdomain' ),
                'enable_status_changes_chbx' => 'yes',
                'set_change_order_txtarea' => __( '{FIRST_NAME}, your order #{ORDER_NUMBER} {VIEW_ORDER_LINK} has changed.', 'my-textdomain'),
                'enable_pending_order_admin_chbx' => 'yes', // Admin SMS Notifications
                'set_pending_order_admin_txtarea' => __('A new order #{ORDER_NUMBER} for {ORDER_TOTAL} is pending.', 'my-textdomain' ),
                'enable_processing_order_admin_chbx' => 'yes',
                'set_processing_order_admin_txtarea' => __( 'Order #{ORDER_NUMBER} payment received.', 'my-text-domain' ),                
                'enable_completed_order_admin_chbx' => 'yes',
                'set_completed_order_admin_txtarea' => __( 'Order #{ORDER_NUMBER} is complete.', 'my-text-domain' ),
                'enable_cancelled_order_admin_chbx' => 'yes',
                'set_cancelled_order_admin_txtarea' => __('Order #{ORDER_NUMBER} has been cancelled.', 'my-text-domain' ),
                'enable_failed_order_admin_chbx' => 'yes',
                'set_failed_order_admin_txtarea' => __('Order #{ORDER_NUMBER} payment has failed.', 'my-text-domain' ),
                'enable_on-hold_order_admin_chbx' => 'yes',
                'set_on-hold_order_admin_txtarea' => __('Order #{ORDER_NUMBER} in on hold.', 'my-text-domain' ),
                'enable_refunded_order_admin_chbx' => 'yes',
                'set_refunded_order_admin_txtarea' => __('Order #{ORDER_NUMBER} has been refunded.', 'my-text-domain' ),
                'enable_status_changes_admin_chbx' => 'yes',
                'set_change_order_admin_txtarea' => __('Order #{ORDER_NUMBER}: {ORDER_ITEMS} . Status has changed.', 'my-textdomain' )

            );
        
            foreach ($default_options as $key => $value) {
                update_option('wpbiztextwc_' . $key, $value);
            }

        }
       
        require_once('wpbiztextwc-text-templates.php');
        
    
        /* add Meta Box (Biz Text Custom SMS) to order */
        if(is_admin()){
            add_action('add_meta_boxes', 'wpbiztextwc_custom_sms_metabox');
        }

        function wpbiztextwc_custom_sms_metabox(){
            add_meta_box(
                'wpbiztextwc_order_sms_metabox', //id of metabox
                esc_html('Biz Text Custom SMS'), //title
                'wpbiztextwc_create_custom_sms_metabox', //callback to display metabox
                'shop_order', //page to show this
                'side', //side
                'high' //priority -- layout of metabox
            );
        }
    
        function wpbiztextwc_create_custom_sms_metabox($data){
        
            $status = array("Pending","Processing","Completed","Cancelled","Failed", "On-hold", "Refunded");
            $placeholders = get_option('wpbiztextwc_placeholders');
            sort($placeholders);
            
            ?>
        
            <p>
                <select name='wpbiztextwc_custom_sms_templates' id='wpbiztextwc_custom_sms_templates' style="width:100%" onchange="return wpbiztextwc_select_template(this);">
                    <option value=""><?= _e('Choose a template...', 'my-text-domain'); ?></option>
                        <?php for ($x = 0; $x <  count($status); $x++) { 
                            $option_id = 'wpbiztextwc_status_' . strtolower($status[$x]);    
                        ?>
                            <option id='<?= $option_id ?>' value="<?php  echo $status[$x] ?>" data-sms-template="<?php  echo get_option('wpbiztextwc_set_' . strtolower($status[$x]) . '_order_txtarea') ?>"><?php echo $status[$x] ?></option>
                        <?php } ?>
                </select>
            </p>
            <p>
                <select name='wpbiztextwc_custom_sms_placeholders' id='wpbiztextwc_custom_sms_placeholders' style="width:100%" onchange="return wpbiztextwc_selectplaceholder(this)">
                        <option value="insert"><?= _e('Placeholder to insert...', 'my-text-domain'); ?></option>

                        <?php for ($x =0; $x < count($placeholders); $x++) { ?>
                            <option value='<?= $placeholders[$x] ?>'><?= $placeholders[$x] ?></option>
                        <?php } ?>
                </select> 
            </p>
            <p>
                <textarea id='wpbiztextwc_custom_sms_txtarea' name='wpbiztextwc_custom_sms_txtarea' rows="4" style='width:100%;'></textarea>
                <?php if(get_option('wpbiztextwc_hidden_field') == "Y" ) { ?>
                <p>
                    <div id='wpbiztextwc_custom_sms_error_msg'></div>
                    <button type='button' class="button button-secondary" id="wpbiztextwc_send_sms_metabxbtn" name="wpbiztextwc_send_sms_metabxbtn" data-link='<?php echo admin_url('admin-ajax.php'); ?>' data-id='<?= sanitize_text_field($_GET['post']); ?>' onclick="wpbiztextwc_send_to_client()"><?= _e('Send SMS', 'my-text-domain'); ?></button>
                    <p id='wpbiztextwc_status_change_checkbox_section' style='display:none'>
                        
                        <label for="wpbiztextwc_override_order_status_chbx"><input type="checkbox" name="wpbiztextwc_override_order_status_chbx" id="wpbiztextwc_override_order_status_chbx" >Override SMS template, send for <span id="wpbiztextwc_override_order_status_chbx_text">Status</span> status. Use this to send custom SMS for an order status change. It will override your settings for that template.</label>
                    </p>
                </p>
                <input type="hidden" id="wpbiztextwc_metabox_chbx_hidden" name="wpbiztextwc_metabox_chbx_hidden" value="Y"> 
                <?php } else { ?>
                    <div class="error inline">
                     <strong><a href="admin.php?page=wc-settings&tab=wpbiztextwc">Verify your Biz Text Id</a> to send SMS notifications. Need an account, <a href="https://www.biztextsolutions.com/pricing/">sign up here</a></strong>
                    </div>
                <?php } ?>
            </p>
        <?php        
    
        } /* end add Meta Box (Biz Text Custom SMS) to order */
    
    
        // adding css and JS
        function wpbiztextwc_load_scripts_js() {
            $js_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'admin/js/wpbiztextwc-script.js'));
            wp_enqueue_script('wpbiztextwc-script.js', plugins_url('admin/js/wpbiztextwc-script.js', __FILE__), array(), $js_ver);

        }

        function wpbiztextwc_load_styles_css() {
            $cs_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'admin/css/wpbiztextwc-styles.css'));
            wp_enqueue_style('wpbiztextwc-styles.css', plugins_url('admin/css/wpbiztextwc-styles.css', __FILE__), array(), $cs_ver);
        }
    
        function wpbiztextwc_load_styles_css_varify() {
    
            $cs_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'admin/css/wpbiztextwc-styles_table.css'));
            wp_enqueue_style('wpbiztextwc-styles_table.css', plugins_url('admin/css/wpbiztextwc-styles_table.css', __FILE__), array(), $cs_ver);
    
        }
    
        function wpbiztextwc_load_styles_css_table() {
    
            $getFile;
    
            if ($_GET['section'] == "customer"){
        
            
                $getFile = (get_option('wpbiztextwc_enable_status_changes_chbx') === "yes") ? "table" : "table2" ;
        
        
            }
        
            if ($_GET['section'] == "admin"){
        
                $getFile = (get_option('wpbiztextwc_enable_status_changes_admin_chbx') === "yes") ? "table" : "table2" ;
        
            }
        
            $cs_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'admin/css/wpbiztextwc-styles_' . $getFile .'.css'));
            wp_enqueue_style('wpbiztextwc-styles_' . $getFile . '.css', plugins_url('admin/css/wpbiztextwc-styles_' . $getFile .'.css', __FILE__), array(), $cs_ver);
        
    
        }
    
        if (isset($_GET['page']) && isset($_GET['tab'])) {

            if ($_GET['page'] == "wc-settings" && $_GET['tab'] == "wpbiztextwc") { 

                 add_action('admin_print_scripts', 'wpbiztextwc_load_scripts_js', 5);
                 add_action('admin_head', 'wpbiztextwc_load_styles_css');
             
                 if(isset($_GET['section'])){
             
                    if($_GET['section'] == "customer" || $_GET['section'] == "admin" ){
                
                        add_action('admin_head', 'wpbiztextwc_load_styles_css_table');
                
                    }
             
                 }
             
             
            } 
        }
    
        function wpbiztextwc_load_order_scripts_js(){
        
            $js_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'admin/js/wpbiztextwc-order-script.js'));
            wp_enqueue_script('wpbiztextwc-order-script.js', plugins_url('admin/js/wpbiztextwc-order-script.js', __FILE__), array('jquery'), $js_ver);
        }
        add_action('admin_print_scripts', 'wpbiztextwc_load_order_scripts_js', 5);
    
        /* add mobile phone field */
        // create mobile phone field
        
        // check to see if option to add field is checked
        if (get_option('wpbiztextwc_hidden_field') == "Y"){ 
            if (get_option('wpbiztextwc_add_mobile_field') == "yes"){
        
                // display the mobile phone field on the checkout page
                // create mobile phone field for checkout billling details
                add_filter( 'woocommerce_checkout_fields', 'wpbiztextwc_custom_checkout_fields' );
                function wpbiztextwc_custom_checkout_fields($fields){
            
                    $biztext_mobile_description = get_option('wpbiztextwc_mobile_field_info');
                    $biztext_mobile_requried = (get_option('wpbiztextwc_mobile_field_requried') == "yes")? true: false;
                
                    $fields['wpbiztextwc_extra_fields'] = array(
                            'wpbiztextwc_text_field' => array(
                                'type' => 'tel',
                                'required'      => $biztext_mobile_requried,
                                'label' => __( 'Mobile Phone', 'my-text-domain' ),
                                'description'  =>  __( $biztext_mobile_description, 'my-text-domain') 
                                )
                    
                            );
                    return $fields;
                }
                
                // display field on billing details
                add_action( 'woocommerce_after_checkout_billing_form' ,'wpbiztextwc_extra_checkout_fields' );
                function wpbiztextwc_extra_checkout_fields(){
                    $checkout = WC()->checkout(); ?>
                    <div class="extra-fields">
                        <?php
                        foreach ( $checkout->checkout_fields['wpbiztextwc_extra_fields'] as $key => $field ) : ?>
                                <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                            <?php endforeach; ?>
                    </div><?php 
                }
                
                // save 
                add_action( 'woocommerce_checkout_update_order_meta', 'wpbiztextwc_save_extra_checkout_fields', 10, 2 );
                function wpbiztextwc_save_extra_checkout_fields( $order_id, $posted ){
            
                    if( isset( $posted['wpbiztextwc_text_field'] ) ) {
                        update_post_meta( $order_id, '_billing_mobile_wpbiztextwc_phone', wpbiztextwc_format_mobile_number(sanitize_text_field( $posted['wpbiztextwc_text_field'] )) );
                    }
            
                }
                
                /* add fields to order details */
                add_action( 'woocommerce_admin_order_data_after_billing_address', 'wpbiztextwc_display_order_data_in_admin' );
                function wpbiztextwc_display_order_data_in_admin( $order ){  ?>
    
                    <div class="address">
                        <?php

                            $mobile_number_btwc = esc_html(get_post_meta($order->get_id(), '_billing_mobile_wpbiztextwc_phone', true ));
                            
                            // use phone number for mobile phone field
                            if (!metadata_exists('post',$order->get_id(),'_billing_mobile_wpbiztextwc_phone')) {
                                if ( get_option('wpbiztextwc_add_mobile_field') == "yes" && 
                                        get_option('wpbiztextwc_mobile_field_use_phone') == "yes"){  

                                        $order_data = $order->get_data();
                                        $order_phone_number = $order_data['billing']['phone'];
                                    
                                        update_post_meta($order_data['id'],'_billing_mobile_wpbiztextwc_phone',  wpbiztextwc_format_mobile_number($order_phone_number));                                
                                }
                            }
                            
                            $mobile_number_btwc = get_post_meta($order->get_id(), '_billing_mobile_wpbiztextwc_phone', true );
                            echo '<p><strong>' . __( 'Mobile Phone' ) . ':</strong>' . '<a href="tel:' . preg_replace('/[^0-9]/', '', $mobile_number_btwc) . '">'. $mobile_number_btwc . '</a></p>';?>
                        </div>
                        <div class="edit_address">
                        <?php woocommerce_wp_text_input( 
                        
                                array( 'id' => '_billing_mobile_wpbiztextwc_phone',
                                       'label' =>  __( 'Mobile Phone' ),
                                       'wrapper_class' => '_billing_company_field',
                                       'custom_attributes' => array('onblur' => 'wpbiztextwcMobileNotifValidation(this)')
                        
                                ) 
                            ); ?>
                    </div><?php
        
                }  
                
                // save in order details
                add_action( 'woocommerce_process_shop_order_meta', 'wpbiztextwc_save_extra_details', 45, 2 );
                function wpbiztextwc_save_extra_details( $post_id, $post ){
            
                    update_post_meta( $post_id, '_billing_mobile_wpbiztextwc_phone',  wpbiztextwc_format_mobile_number(wc_clean( $_POST[ '_billing_mobile_wpbiztextwc_phone' ] )));
        
                }
                
                /* -- add column and bulk actions to order list */
 
                // Adding to admin order list bulk dropdown
                add_filter( 'bulk_actions-edit-shop_order', 'wpbiztextwc_bulk_actions_use_phone', 20, 1 );
                function wpbiztextwc_bulk_actions_use_phone( $actions ) {
                    $actions['use_phone_sms_biztext'] = __( 'SMS enable - using Existing Phone Number', 'my-text-domain' );
                    $actions['remove_phone_sms_biztext'] = __( 'SMS disable - remove Mobile Number', 'my-text-domain');
                    return $actions;
                }
        
        
                // Make the action from selected orders
                add_filter( 'handle_bulk_actions-edit-shop_order', 'wpbiztextwc_bulk_actions_use_phone_action', 10, 3 );
                function wpbiztextwc_bulk_actions_use_phone_action( $redirect_to, $action, $post_ids ) {
            
                    if ( $action !== 'use_phone_sms_biztext' && $action !== 'remove_phone_sms_biztext' )
                    return $redirect_to; // Exit

                    $processed_ids = array();

                    foreach ( $post_ids as $post_id ) {
                        $order = wc_get_order( $post_id );
                        $order_data = $order->get_data();
                        $order_phone_number = $order_data['billing']['phone'];

    
                        // add number to mobile
                        if ( $action == 'use_phone_sms_biztext'){
                
                             // executed on each selected order
                            if (!metadata_exists('post',$order->get_id(),'_billing_mobile_wpbiztextwc_phone')) {
                                    
                                    update_post_meta($order_data['id'],'_billing_mobile_wpbiztextwc_phone', wpbiztextwc_format_mobile_number($order_phone_number));    
                                    $processed_ids[] = $post_id;
                    
                            } 
                
                        }
                
                         // remove number
                        if ( $action == 'remove_phone_sms_biztext'){
                
                             // executed on each selected order
                            if (metadata_exists('post',$order->get_id(),'_billing_mobile_wpbiztextwc_phone')) {
                                    
                                    delete_post_meta($order_data['id'],'_billing_mobile_wpbiztextwc_phone');
                                    $processed_ids[] = $post_id;
                    
                            } 
                            
                
                        }
                
                    }
                    
                    return $redirect_to = add_query_arg( array(
                         $action => '1',
                        'processed_count' => count( $processed_ids ),
                        'processed_ids' => implode( ',', $processed_ids ),
                        'message_type' => $action
                    ), $redirect_to );
            
                }
        
                // The results notice from bulk action on orders - 
                add_action( 'admin_notices', 'wpbiztextwc_bulk_actions_use_phone_action_admin_notice' );
                function wpbiztextwc_bulk_actions_use_phone_action_admin_notice() {
                
                    if(isset($_REQUEST['use_phone_sms_biztext']) &&  isset($_REQUEST['remove_phone_sms_biztext'])){
                    
                         if ( empty( sanitize_text_field($_REQUEST['use_phone_sms_biztext']) )  && empty( sanitize_text_field($_REQUEST['remove_phone_sms_biztext']) )) return; // Exit
                    
                        $count = intval( sanitize_text_field($_REQUEST['processed_count']) );
                        $actionDone = ( sanitize_text_field($_REQUEST['message_type']) == "use_phone_sms_biztext")? "Added" : "Removed";

                        printf( '<div id="biztext-bulk-add-message" class="updated fade"><p>' .
                            _n( $actionDone . ' %s Order Mobile Number.'  ,
                            $actionDone . ' %s Orders Mobile Number.' ,
                            $count,
                            $actionDone
                        ) . '</p></div>', $count );
                    
                    }
                    
                }
        
                // Add Column to order list
                add_filter( 'manage_edit-shop_order_columns', 'wpbiztextwc_add_mobile_number_field', 20);
                function wpbiztextwc_add_mobile_number_field( $columns ) {
        
                    $reordered_columns = array();

                    // Inserting columns to a specific location
                    foreach( $columns as $key => $column){
                        $reordered_columns[$key] = $column;
                        if( $key ==  'order_status' ){
                            // Inserting after "Status" column
                            $reordered_columns['sms_mobile_number'] = __( 'Mobile Number (SMS)','theme_domain');
                        }
                    }
                    return $reordered_columns; 
        
                }
 
                add_action( 'manage_shop_order_posts_custom_column', 'wpbiztextwc_add_mobile_number_field_content' );
                function wpbiztextwc_add_mobile_number_field_content( $column ) {
   
                    global $post;
 
                    if ( 'sms_mobile_number' === $column ) {
 
                        $order = wc_get_order( $post->ID );
                        $mobile_number = esc_html(get_post_meta($order->get_id(), '_billing_mobile_wpbiztextwc_phone', true ));
                        if ($mobile_number != ""){
                
                             echo $mobile_number;
                
                        } else {
                
                            echo "_";
                
                        }
      
                    }
                }             
                
                function wpbiztextwc_format_mobile_number($phone_number){
                
                    // remove all characters
                    $pattern_remove = '/\D/'; 
                    $replacement_remove = ''; 
                
                    //format (xxx) xxx-xxxx
                    $mobile_phone = preg_replace($pattern_remove, $replacement_remove, $phone_number); 
                    $pattern_format = '/(\d{3})(\d{3})(\d{4})/'; 
                    $replacement_format = '($1) $2-$3'; 
        
                    $formated_number = preg_replace($pattern_format, $replacement_format, $mobile_phone); 
                
                    return $formated_number;
                
                }  
                        
            } else {
        
                // Hook into billing details checkout change phone field to mobile phone and add description
                add_filter( 'woocommerce_checkout_fields' , 'wpbiztextwc_custom_override_checkout_fields' );
                function wpbiztextwc_custom_override_checkout_fields( $fields ) {

                    $biztext_mobile_description = get_option('wpbiztextwc_mobile_field_info');

                    $fields['billing']['billing_phone']['description'] = $biztext_mobile_description;
                    $fields['billing']['billing_phone']['label'] = 'Mobile Phone';
                    return $fields;
                
                }  
                
                // change phone to mobile phone field
                add_filter('woocommerce_billing_fields', 'wpbiztextwc_modify_billing_fields', 10);   
                function wpbiztextwc_modify_billing_fields($fields){
                
                    $fields['billing_phone']['label'] = 'Mobile Phone';
                    return $fields;
                
                }   
                
            }
            
            add_action('wp_ajax_wpbiztextwc_send_custom_text', 'wpbiztextwc_send_custom_text');
            add_action('wp_ajax_nopriv_wpbiztextwc_send_custom_text', 'wpbiztextwc_send_custom_text');

            function wpbiztextwc_send_custom_text(){
                $id = sanitize_text_field(trim($_POST['id']));
                $txt = sanitize_text_field(trim($_POST['txt']));
                $addtoNote = '(Sent by SMS) : ';
                $order = wc_get_order($id);
                // if this doesn't send, then indicate that in the note - TODO
                if (wpbiztextwc_send_text($txt, "to-client", $order, "completed") == 1) {
                    $note = wpbiztextwc_replace_placeholders($txt, $order);
                    $order->add_order_note( $addtoNote . $note  , 1, true );
                }
                die();
            }

        }
        require_once 'admin/wpbiztextwc-settings.php';
    } // end check if WooCommerce is active
    

?>