<?php

   if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    // only excute if Biz Text Id is verfied 
    if (get_option('wpbiztextwc_hidden_field') == "Y"){

        // hook for new order - send notification
        add_action( 'woocommerce_new_order', 'wpbiztextwc_action_woocommerce_new_order', 10, 1 ); 
        function wpbiztextwc_action_woocommerce_new_order( $order_get_id ) { 

            $order = wc_get_order( $order_get_id );
            wpbiztextwc_check_to_send_text("pending", $order, "new");   

        }; 
        
        // hook for status change of order
        add_action( 'woocommerce_order_status_changed', 'wpbiztextwc_order_status_changed', 10, 4 ); 
        function wpbiztextwc_order_status_changed( $this_get_id, $this_status_transition_from, $this_status_transition_to, $instance ) { 
          
            $order = wc_get_order( $this_get_id );

            // all status changes

            if (isset($_POST['wpbiztextwc_metabox_chbx_hidden'])){
                $hidden_field = sanitize_text_field($_POST['wpbiztextwc_metabox_chbx_hidden']);
                
                if ($hidden_field == 'Y'){

                        //sms overriding 
                        if (sanitize_text_field($_POST['wpbiztextwc_override_order_status_chbx']) == 'on'){
                
                            $change_message_admin = sanitize_text_field($_POST['wpbiztextwc_custom_sms_txtarea']);
                            wpbiztextwc_send_text($change_message_admin, "to-client",  $order, $this_status_transition_to);

                        } else {
    
                            wpbiztextwc_check_to_send_text($this_status_transition_to, $order, $this_status_transition_from);
                            
                        }
                
                } 
            } else {
    
                wpbiztextwc_check_to_send_text($this_status_transition_to, $order, $this_status_transition_from);
            }
        }
       
    
    } // end status change function
        
    /* check what status, if enabled based off settings in Text Message SMS tab */
   function wpbiztextwc_check_to_send_text($status, $order, $this_status_transition_from) {

        
        $biztext_valid_status = array("pending","processing","completed","cancelled","failed", "on-hold", "refunded");
        
        $biztext_status_change_client_chbx = get_option('wpbiztextwc_enable_status_changes_chbx');
        
        // check to see if status is a valid Biz Text Status (other status may be added) 
        if (in_array($status, $biztext_valid_status)) {

            // to business 

            $status_message = "";
            switch ($status) {
                case "pending":
                    $status_message = "- Waiting for payment.";
                    break;
                case "processing":
                    $status_message = "- Payment accepted, fulfilling order.";
                    break;
                case "completed":
                    $status_message = "- Order completed.";
                    break;
                case "cancelled":
                    $status_message = "- Order cancelled.";
                    break;
                case "failed":
                    $status_message = "- Payment failed.";
                    break;
                case "on-hold":
                    $status_message = "- Confirming payment.";
                    break;
                case "refunded":
                    $status_message = "- Order refund.";
                    break;
            }
            
            if (get_option('wpbiztextwc_enable_status_changes_admin_chbx') == "yes" && $this_status_transition_from != "new" ){

                $change_message_admin = get_option('wpbiztextwc_set_change_order_admin_txtarea') . "(status changed from " . $this_status_transition_from . " to " . $status . ")" ;
                if($status == "processing"){
                    $customer_message = get_option('wpbiztextwc_set_' . $status . '_order_txtarea');
                    if ($biztext_status_change_client_chbx  == "yes"){
                        $customer_message = get_option('wpbiztextwc_set_change_order_txtarea') . $status_message;
                    }
                    $change_message_admin= array($change_message_admin, $customer_message);

                    wpbiztextwc_send_text($change_message_admin, "to-business-admin",  $order, $status);
                }
                wpbiztextwc_send_text($change_message_admin, "to-business",  $order, $status);
    
            } else if (get_option('wpbiztextwc_enable_' . $status . '_order_admin_chbx') == "yes"){

                 $message = get_option('wpbiztextwc_set_' . $status . '_order_admin_txtarea');
                 if($status == "processing"){
                     $customer_message = get_option('wpbiztextwc_set_' . $status . '_order_txtarea');
                     $message = array($message, $customer_message);
                     wpbiztextwc_send_text($change_message_admin, "to-business-admin",  $order, $status);
                 }
                 wpbiztextwc_send_text($message, "to-business", $order, $status);

            }
    
            if ($status != "processing"){

                // to customer
                if ($biztext_status_change_client_chbx  == "yes" && $this_status_transition_from != "new" ){

                    $change_message = get_option('wpbiztextwc_set_change_order_txtarea') . $status_message;
                    wpbiztextwc_send_text($change_message, "to-client",  $order, $status);
        
                } else if (get_option('wpbiztextwc_enable_' . $status . '_order_chbx') == "yes"){

                     $customer_message = get_option('wpbiztextwc_set_' . $status . '_order_txtarea');
                     wpbiztextwc_send_text($customer_message, "to-client" , $order, $status);

                }      
    
            }  
        
        }

    }
    
    // send text 
    function wpbiztextwc_send_text($text_message, $texttype, $order, $order_status){
    
        $order_data = $order->get_data();
        $order_items = $order->get_items();
        $order_date = $order->get_date_created()->format ('F j, Y');
        $order_num = $order_data['id'];
        
        // decide where to get number from extra field or from existing phone field

        $order_phone_number = (get_option('wpbiztextwc_add_mobile_field') == "yes") ? get_post_meta($order->get_id(), '_billing_mobile_wpbiztextwc_phone', true ) :$order_data['billing']['phone'];
        if (isset($_POST['wpbiztextwc_text_field'])){
            $order_phone_number = sanitize_text_field($_POST['wpbiztextwc_text_field']);
        }
        
        $order_first_name = $order_data['billing']['first_name'];
        $order_last_name = $order_data['billing']['last_name'];
        $order_total = $order_data['total'];
        $order_date_created = $order_date;

        $notifyNumber = get_option('wpbiztextwc_notif_number');
        $notifyNickname =  trim(get_option('wpbiztextwc_notif_number_nickname'));

        $text_message = wpbiztextwc_replace_placeholders($text_message, $order);
        $biztext_id = get_option('wpbiztextwc_biztext_id');

        if ($texttype == "to-client"){
            //for client
            $bizTextData = array(
                'websiteId' => $biztext_id,
                'txt' => $text_message,
                'to' =>  preg_replace('/[^0-9]/', '', $order_phone_number),
                "nickname" => $order_first_name . " " . $order_last_name
            );

            $url = 'https://www.biztextsolutions.com/api/send/to-client';

        } else if ($texttype == "to-business") {
            //for processing - client
            if($order_status == "processing"){
    
                $bizTextData = array(
                    'websiteId' => $biztext_id,
                    'txt' => $text_message[0],
                    'from' => preg_replace('/[^0-9]/', '', $order_phone_number),
                    'nickname' => $order_first_name . " " . $order_last_name,
                    'response' => $text_message[1] // optional, auto response

                );
        
                 $url = 'https://www.biztextsolutions.com/api/send/to-business';
        
    
            } else {
                 //for admin
                 $bizTextData = array(
                    'websiteId' => $biztext_id,
                    'txt' => $text_message,
                    'to' => preg_replace('/[^0-9]/', '',  $notifyNumber)

                );
        
                if ($notifyNickname != ''){
                    $bizTextData = array_merge($bizTextData, array('nickname' =>  $notifyNickname));
                }   
        
                $url = 'https://www.biztextsolutions.com/api/send/to-client';
                
            }

        } else if ($texttype == "to-business-admin") {
   
                //for admin
                $bizTextData = array(
                    'websiteId' => $biztext_id,
                    'txt' => $text_message[0],
                    'to' => preg_replace('/[^0-9]/', '',  $notifyNumber)

                );
        
                if ($notifyNickname != ''){
                    $bizTextData = array_merge($bizTextData, array('nickname' =>  $notifyNickname));
                }   
        
                $url = 'https://www.biztextsolutions.com/api/send/to-client';
                
        }

        $bizTextData = wp_json_encode($bizTextData);

        $biztext_response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => $bizTextData,
                'data_format' => 'body'
        ));
        
        if ($biztext_response['response']['code'] == 400) {
        
                echo 0;
                return 0;
            
            } else {
                echo 1;
                return 1;
              
        } 
        
        // die();


    }

    
    // replace placeholders with data
    function wpbiztextwc_replace_placeholders($text_message, $order) {
    
        $order_link = $order->get_view_order_url();

        $order_data = $order->get_data();
        $order_items = $order->get_items();
        $order_date = $order->get_date_created()->format ('F j, Y');

        /* each item */

        $order_items_list = array();

        foreach ($order_items as $item_id => $item_data) {

            $product_name = $item_data['name'];
            $product_quantity = $item_data['quantity'];

            array_push($order_items_list,$product_name . "(" . $product_quantity . ")");

        }

        $order_num = $order_data['id'];
        $order_phone_number = $order_data['billing']['phone'];
        $order_first_name = $order_data['billing']['first_name'];
        $order_last_name = $order_data['billing']['last_name'];
        $order_total = $order_data['total'];
        $order_date_created = $order_date;
        $order_note = $order_data['customer_note'];
        $site_name = get_bloginfo( "name" ,'raw' );

        $variables = array(

            "order_number"=> $order_num,
            "first_name"=> $order_first_name,
            "last_name"=> $order_last_name,
            "phone_number" => $order_phone_number,
            "order_total" => $order_total,
            "order_date" => $order_date_created,
            "site_url" => home_url(),
            "order_note" =>  $order_note,
            "order_items" => implode(', ', $order_items_list),
            "view_order_link" => $order_link,
            "site_name" => $site_name

        );

        $replacement = array_combine(
            array_map(function($k) { return '{'.strtoupper($k).'}'; }, array_keys($variables)),
            array_values($variables)
        );
    
        if(is_array($text_message)){

            $text_message_from = strtr($text_message[0], $replacement);
            $text_message_response = strtr($text_message[1], $replacement);

            return $text_message = array( $text_message_from , $text_message_response );

        } else {

            return $text_message = strtr($text_message, $replacement);

        }

    }
    
    // admin error message
    function wpbiztextwc_notice() {
        ?>
        <div class="notice is-dismissible notice-info">
            <p><?php _e( '<strong>You are almost ready to send SMS notifications for WooCommerce</strong>. To activate <a href="admin.php?page=wc-settings&tab=wpbiztextwc">complete the set up</a>.', 'my_plugin_textdomain' ); ?></p>
        </div>
        <?php
    }
    
     // message if Biz Text Id is not verified
    if ($options = get_option('wpbiztextwc_hidden_field') != "Y") {
    
        add_action( 'admin_notices', 'wpbiztextwc_notice' );
    
    }

    
  

?>