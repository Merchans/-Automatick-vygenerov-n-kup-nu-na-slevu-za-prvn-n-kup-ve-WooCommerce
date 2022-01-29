<?php
class WC_First_Order_Coupon_Email extends WC_Email
{

    /**
     * Unique identifier
     *    
     */
    protected $language_slug = 'musilda';


    /**
     * Set email defaults
     *
     */
    public function __construct()
    {

        $this->id          = 'wc_first_order_coupon_email';
        $this->customer_email = true;
        $this->title       = __('Kupón na první objednávku', $this->language_slug);
        $this->description = __('Kupón na první objednávku', $this->language_slug);
        $this->heading     = __('Kupón na první objednávku', $this->language_slug);
        $this->subject     = __('Kupón na první objednávku z {site_title}', $this->language_slug);

        $this->template_html  = 'first-order-coupon-email.php';
        $this->template_plain = 'first-order-coupon-email-plain.php';
        $this->template_base  =  CUSTOM_WC_EMAIL_PATH . 'email/';

       

        parent::__construct();
    }

    /**
     * Determine if the email should actually be sent and setup email merge variables
     *
     */
    public function trigger($order_id, $order = false)
    {
        
        $this->setup_locale();

        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }
        
        if (is_a($order, 'WC_Order')) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        
        }

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'            => $this
        ), '', $this->template_base);
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html($this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'            => $this
        ), '', $this->template_base);
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'         => __('Enable/Disable', $this->language_slug),
                'type'          => 'checkbox',
                'label'         => __('Enable this email notification', $this->language_slug),
                'default'       => 'yes',
            ),
            'subject' => array(
                'title'         => __('Subject', 'woocommerce'),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => sprintf(__('Available placeholders: %s', $this->language_slug), '<code>{site_title}, {order_date}, {order_number}</code>'),
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __('Email heading', $this->language_slug),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => sprintf(__('Available placeholders: %s', $this->language_slug), '<code>{site_title}, {order_date}, {order_number}</code>'),
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'email_type' => array(
                'title'         => __('Email type', $this->language_slug),
                'type'          => 'select',
                'description'   => __('Choose which format of email to send.', $this->language_slug),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'       => $this->get_email_type_options(),
                'desc_tip'      => true,
            ),
        );
    }
} // end class

