<?php 
// Add a custom setting tab to Woocommerce > Settings section https://gist.github.com/bekarice/34aaeda2d4729ef87ad7

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    function wpbiztextwc_add_settings() {

        class WC_Settings_WpBiztextWc extends WC_Settings_Page {

            public function __construct() {
        
                $this->id    = 'wpbiztextwc';
                $this->label = __( 'Text Messages SMS', 'my-text-domain' );
            
                add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
                add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
                add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
                add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );

            }
        
            // set sections
            public function get_sections() {
        
                $sections = array(
                    ''         => 'Initialize',
                    'customer' => 'Customer SMS Notification',
                    'admin' => 'Admin SMS Notification'
                );
            
                return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
            }
    
            // get setting array	 
            public function get_settings( $current_section = '' ) {
        
                // display placeholders
                $chosen_placeholders = get_option('wpbiztextwc_placeholders');
                sort($chosen_placeholders, SORT_NATURAL | SORT_FLAG_CASE);

                $fix_placholders = (get_option('wpbiztextwc_placeholders_fixed') == "yes") ? 'wpbiztextwc_placeholders_fix' :'';
                $btplaceholders = '<span id="wpbiztextwc-placeholders" class="wpbiztextwc-placeholders">';
            
                for ($x = 0; $x <  count($chosen_placeholders); $x++) {
                   $btplaceholders .= '<span class="wpbiztextwc-placeholders-button" data-clipboard-text="' . $chosen_placeholders[$x] . '">' .  $chosen_placeholders[$x]  .'</span>';
                }
            
                $btplaceholders .= '</span>';

                // message for order status used
                $checkbox_options = ('customer' == $current_section) ? get_option('wpbiztextwc_enable_status_changes_chbx') : get_option('wpbiztextwc_enable_status_changes_admin_chbx') ;
                $status_change_desc = ($checkbox_options == 'yes') ? __( 'Same SMS for Status Change is Enabled', 'my-text-domain' ): '';
                $status_change_class =  'wpbiztextwc-status-active';
                $status_different_sms = 'wpbiztextwc-status-active';
            
                $status_change_only_desc = ($checkbox_options == 'no') ? __( 'Same SMS for Status Change is Not Enabled', 'my-text-domain')  : '';
            
                // customer SMS Notificaitons Section
                if ( 'customer' == $current_section ) {
                 
                    $settings = apply_filters( $this->id . '_enable_settings', array(
                
                        // pending (new order)
                        array(
                            'name' => __( 'Customer SMS Notification Templates', 'my-text-domain' ),
                            'type' => 'title',
                            'desc' => __( 'Select when to send your customer a text Messasge', 'my-text-domain' ) . '</br><span id="' . $fix_placholders . '">' . __( 'Placeholders: ', 'my-text-domain' ) .$btplaceholders . '</span>' ,
                            'id'   => $this->id . '_customer_sms_notifs'
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_pending_order_chbx',
                            'name'     => __( 'Order Pending (New Order)', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),

                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_pending_order_txtarea',
                            'desc_tip' => __( 'Send a SMS when a customer places an order and is making payment.', 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')	
                        ),
                    
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id . '_customer_sms_notifs'
                        ),
                    
                        // Different SMS for each Status Change options
                        array(
                            'type' => 'title',
                            'desc' =>   '<hr><h3>' . __( 'Different SMS for each Status Change', 'my-text-domain' ) . '</h3><br><span id="btwc_customer_sms_statuschange" class="' . $status_change_class  .'">' . $status_change_desc . '</span>',
                            'id'   => $this->id . '_customer_sms_statuschange'
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_processing_order_chbx',
                            'name'     => __( 'Order Processing', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-disable'
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_processing_order_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's payment is accepted.", 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),

                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_completed_order_chbx',
                            'name'     => __( 'Order Completed', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_completed_order_txtarea',
                            'desc_tip' =>  __( 'Send a SMS when an order is complete.', 'my-text-domain'),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')    
                        ),
                
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_cancelled_order_chbx',
                            'name'     => __( 'Order Cancelled', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_cancelled_order_txtarea',
                            'desc_tip' => __( 'Send a SMS when an order is cancelled.', 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),	
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_failed_order_chbx',
                            'name'     => __( 'Order Failed', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_failed_order_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's payment has failed", 'my-text-domain' ) ,
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_on-hold_order_chbx',
                            'name'     => __( 'Order On Hold', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_on-hold_order_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order is on hold.", 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_refunded_order_chbx',
                            'name'     => __( 'Order Refunded', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_refunded_order_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order is refunded." , 'my-text-domain' ) ,
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                    
                            'type'     => 'sectionend',
                            'id'       => $this->id . '_customer_sms_statuschange'
                        ),
                        // End Different SMS for each Status Change options
                        // Same SMS for a Status Change
    
                        array(
                    
                            'type' => 'title',
                            'desc' => '<hr><h3>' . __( 'Same SMS for a Status Change', 'my-text-domain' ) . '</h3><span id="btwc_customer_sms_status" class="' . $status_different_sms  .'">' . $status_change_only_desc . '</span>', 
                            'id'   => $this->id . '_customer_sms_statusonly'
                        ),
                
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_status_changes_chbx',
                            'name'     => __( 'Every Order Status Change', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' ),
                            'class'    => 'wpbiztextwc-status-change-chbx'
                        ),
                
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_change_order_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order status changes. The status from and to are automatically added.", 'my-text-domain' ) ,
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                    
                            'type'     => 'sectionend',
                            'id'       => $this->id . '_customer_sms_statusonly'
                        ),
    
                    
                    ) ); 
                
                // admin SMS Notificaitons Section
                } else if ('admin' == $current_section)  {
                
                    $settings = apply_filters( $this->id . '_support_settings', array(
                
                        // pending (new order)
                        array(
                            'name' => __( 'Your SMS Notification Templates', 'my-text-domain' ),
                            'type' => 'title',
                            'desc' => __( 'Select when to send the admin a text Messasge.' , 'my-text-domain' ) . '<br><span id="' . $fix_placholders . '"> Placeholders: ' . $btplaceholders . '</span>',
                            'id'   => $this->id . '_admin_sms_notifs'
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_pending_order_admin_chbx',
                            'name'     => __( 'Order Pending (New Order)', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_pending_order_admin_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's payment is accepted." , 'my-text-domain' ) ,
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id . '_admin_sms_notifs'
                        ),
                    
                        // Different SMS for each admin Status Change options
                    
                        array(
                        
                            'type' => 'title',
                            'desc' => '<hr><h3>' . __( 'Different SMS for each Status Change', 'my-text-domain' ) . '</h3><br><span id="btwc_customer_sms_statuschange" class="' . $status_change_class  .'">' . $status_change_desc . '</span>',
                            'id'   => $this->id . '_admin_sms_statuschange'
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_processing_order_admin_chbx',
                            'name'     => __( 'Order Processing', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_processing_order_admin_txtarea',
                            'desc_tip' =>  __( "Send a SMS when a customer's payment is accepted.", 'my-text-domain' ), 
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),

                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_completed_order_admin_chbx',
                            'name'     => __( 'Order Completed', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_completed_order_admin_txtarea',
                            'desc_tip' => __('Send a SMS when an order is complete.', 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_cancelled_order_admin_chbx',
                            'name'     => __( 'Order Cancelled', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_cancelled_order_admin_txtarea',
                            'desc_tip' => __( 'Send a SMS when an order is canceled.', 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),	
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_failed_order_admin_chbx',
                            'name'     => __( 'Order Failed', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_failed_order_admin_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's payment fails.", 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_on-hold_order_admin_chbx',
                            'name'     => __( 'Order On Hold', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_on-hold_order_admin_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order is on hold.", 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_refunded_order_admin_chbx',
                            'name'     => __( 'Order Refunded', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' )
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_refunded_order_admin_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order is refunded.", 'my-text-domain' ),
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                        array(
                    
                            'type'     => 'sectionend',
                            'id'       => $this->id . '_admin_sms_statuschange',
                        ),
                    
                        // end status change options
                            
                        array(
                    
                            'type' => 'title',
                            'desc' => '<hr><h3>' . __( 'Same SMS for a Status Change', 'my-text-domain')  . '</h3><span id="btwc_customer_sms_status" class="' . $status_different_sms  .'">' . $status_change_only_desc . '</span>', 
                            'id'   => $this->id . '_admin_sms_statusonly',
        
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_enable_status_changes_admin_chbx',
                            'name'     => __( 'Every Order Status Change', 'my-text-domain' ),
                            'desc'     => __( 'Enable', 'my-text-domain' ),
                            'class'    => 'wpbiztextwc-status-change-chbx'
                        ),
                    
                        array(
                            'type'     => 'textarea',
                            'id'       => $this->id . '_set_change_order_admin_txtarea',
                            'desc_tip' => __( "Send a SMS when a customer's order status changes. The status from and to are automatically added.", 'my-text-domain' ) ,
                            'class'    => 'Biz-Text-WC-text-area',
                            'custom_attributes' => array('onblur' => 'wpBizTextWcTextTemplateValidation(this)')
                        ),
                    
                
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id . '_admin_sms_statusonly'
                        ),
                    
                    ) ); 
                
                } else {
                
                    // intalize section 
                    $options = get_option('wpbiztextwc_hidden_field');
                    $text = __( 'Biz Text Id not verified', 'my-text-domain' );
                    $color = 'color:red;';
                    if ($options == 'Y'){
                        $text = __( 'Biz Text Id verified', 'my-text-domain' );
                        $color = 'color:#46b450;';
                    } 
                
                    $settings = apply_filters( $this->id . '_biztext_global_settings', array(
                
                        array(
                            'name' => __( 'Activate SMS Notifications by Biz Text', 'my-text-domain' ),
                            'type' => 'title',
                            'desc' => 'To start sending SMS notifications and for help follow the <a href="https://www.biztextsolutions.com/integrations/wordpress/text-message-sms-extension-for-woocommerce-tutorial/" target="_blank"> Text Message SMS Extension For WooCommerce Tutorial</a>. <br><p>Copy your Biz Text Id, paste, and verify to use the contact form.<br>
                                To find your Biz Text Id, go to your Texting Dashboard, click My Account, and look under Biz Numbers.</p>',
                            'id'   => $this->id . '_global_settings',
                        ),
                    
                        array(
                            'type'     => 'text',
                            'id'       =>  $this->id . '_biztext_id',
                            'name'     => __( 'Biz Text Id', 'my-text-domain' ),
                            'desc_tip' => __( 'Copy your Biz Text Id, paste, and verify to activate SMS notifications.', 'my-text-domain' ),
                            'desc'     =>   '<span id="wpbiztextwc-verification-text" style="' . $color. '"><strong>' . $text . '</strong></span><span id="wpbiztextwc-spinner" class="spinner"></span>	'

                        ),
                    
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id .  '_global_settings'
                        ),
                    
                        array(
                            'name' => __( 'Admin SMS Notification', 'my-text-domain' ),
                            'type' => 'title',
                            'desc' => __( 'Enter a number to receive order SMS notifications.', 'my-text-domain' ),
                            'id'   => $this->id . '_notification_settings'
                        ),
        
                        array(
                            'type'     => 'text',
                            'id'       => $this->id . '_notif_number',
                            'name'     => __( 'Order Notification Mobile Number', 'my-text-domain' ),
                            'desc_tip' => __( 'Enter a mobile number to receive order notification.', 'my-text-domain' ),
                            'desc' => '(xxx) xxx-xxxx',
                            'custom_attributes' => array('onblur' => 'wpbiztextwcMobileNotifValidation(this)')
                        
                        ),
                    
                        array(
                            'type'     => 'text',
                            'id'       => $this->id . '_notif_number_nickname',
                            'name'     => __( 'Order Notification Nickname', 'my-text-domain' ),
                            'desc_tip' => __( 'Name that will appear on the Texting Dashboard.', 'my-text-domain' )
                        
                        ),
                
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id .  '_notification_settings'
                        ),
                    
                        array(
                            'name' => __( 'Billing Details', 'my-text-domain' ),
                            'type' => 'title',
                            'desc' =>__( "By default, a Mobile Phone Field is added to billing details to send SMS notifications.<br> If disabled, the label for the Phone filed becomes Mobile Phone, and the description displayed.", 'my-text-domain' ),
                            'id'   => $this->id . '_mobile_field_settings'
                        ),
                    
                        array(
                            'type'     => 'text',
                            'id'       => $this->id . '_mobile_field_info',
                            'name'     => __( 'Description', 'my-text-domain' ),
                            'desc_tip' => __( 'Information to show the customer under the field used to collect their mobile number.', 'my-text-domain' )
                        
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_add_mobile_field',
                            'name'     => __( 'Mobile Phone Field', 'my-text-domain' ),
                            'desc'     => __( 'Add to Billing Details to collect customers number.', 'my-text-domain' )
                        
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_mobile_field_requried',
                            'desc'     => __( 'Make the Mobile Phone field required.', 'my-text-domain' )
                        
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_mobile_field_use_phone',
                            'desc'     => __( 'Use the number in Phone field for Mobile Phone if empty (existing orders)', 'my-text-domain' )
                        
                        ),
                    
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id .  '_mobile_field_settings'
                        ),
                    
                        array(
                            'name' => __( 'Template Placeholders', 'my-text-domain' ),
                            'type' => 'title',
                            'id'   => $this->id . '_other_settings'
                        ),
                    
                        array(
                            'type'     => 'checkbox',
                            'id'       => $this->id . '_placeholders_fixed',
                            'name'     => __( 'Fix Placeholders to Bottom of Window', 'my-text-domain' )
                        ),
                    
                        array(
                            'type' => 'sectionend',
                            'id'   => $this->id .  '_other_settings'
                        )
                    
                    
                    ) );

                }
            
                return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
            
            }
        
            // output sections
            public function output() {
        
                global $current_section;
            
                $checkbox_options = ('customer' == $current_section) ? get_option('wpbiztextwc_enable_status_changes_chbx') : get_option('wpbiztextwc_enable_status_changes_admin_chbx') ;
                $message_to_use = ($checkbox_options == "yes") ? __( "Notifications sent for all order status changes. To have a different SMS for each status change, disable Every Order Status Change.", 'my-text-domain' ) : __("Notifications sent to the enabled order statuses. To have an SMS for all order status changes, enable and customize Every Order Status Change.", 'my-text-domain' );
            
                // message if Biz Text Id is not verified
                if (get_option('wpbiztextwc_hidden_field') != "Y") { ?>
            
                    <div id="wpbiztextwc-messages" class="error inline">
                        <p><?php _e('<a href="admin.php?page=wc-settings&tab=wpbiztextwc">Verify your Biz Text Id</a> to send SMS notifications. Need an account, <a href="https://www.biztextsolutions.com/pricing/">sign up here</a>.', 'my_plugin_textdomain' ); ?></p>
                    </div>
            
                <?php } else { 
        
                    if (isset($_GET['section'])) {
                
                        if ($current_section == "customer") { 
                        
                            bizText_error_messages($message_to_use);
                    
                         }   
        
                    } 
                
                    // check if mobile number for admin notifications
                    if (isset($_GET['section'])){
                
                        if ($current_section == "admin" ){
                
                            if ( trim(get_option($this->id . '_notif_number')) == "" ) { ?>
                        
                                <div id="wpbiztextwc-messages" class="error inline">
                                    <p><?php _e( '<a href="admin.php?page=wc-settings&tab=wpbiztextwc">Enter a Order Notification Mobile Number</a> to send Admin SMS notifications.', 'my_plugin_textdomain' ); ?></p>
                                </div>
        
                            <?php } else { 
                    
                               bizText_error_messages($message_to_use);

                            }
                
                        }
                
                    }
                
                }
            
            
                $settings = $this->get_settings( $current_section );
                WC_Admin_Settings::output_fields( $settings );
            
                echo "<h3 id='wpbiztextwc-error-templates'></h3>";
            
            
                if (isset($_GET['tab'])){
    
                    if ($_GET['tab'] == 'wpbiztextwc' && (!isset($_GET['section']) || $_GET['section'] != 'customer' && $_GET['section'] !== 'admin')){
                        $options = get_option('wpbiztextwc_hidden_field');
                    
                        echo "<input type='hidden' name='wpbiztextwc_hidden_field' id='" . $this->id . "_hidden_field' value='$options' >";
                        echo "<input type='hidden' name='wpbiztextwc_hidden_field_id' id='wpbiztextwc_hidden_field_id' value='Y'>";
                    }
                
                
                }
            
            }
        
            // save the settings
            public function save() {
        
                global $current_section;
            
                $settings = $this->get_settings( $current_section );
        
                WC_Admin_Settings::save_fields( $settings );

            }

        }
    
        return new WC_Settings_WpBiztextWc();

    }

    add_filter( 'woocommerce_get_settings_pages', 'wpbiztextwc_add_settings', 15 );

            if (isset($_POST['wpbiztextwc_hidden_field'])) { 
                $hidden_field = sanitize_text_field($_POST['wpbiztextwc_hidden_field_id']);

                if ($hidden_field == 'Y'){
                    $wpbiztextwc_hidden_data = sanitize_text_field($_POST['wpbiztextwc_hidden_field']);
                    update_option('wpbiztextwc_hidden_field',$wpbiztextwc_hidden_data);
                }
            }

    function bizText_error_messages($message_to_use){

            echo '<div id="wpbiztextwc-messages" class="notice inline"><p>' . $message_to_use  . '</p></div>' ;

    }


?>