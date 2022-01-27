<?php

$coupon_code = get_post_meta( $order->get_id(), 'generated_coupon', true );
?>

<?php do_action('woocommerce_email_header', $email_heading ); ?>

<h2><?php _e( 'Děkujeme za první nákup!', 'musilda' ); ?></h2>
<h3><?php _e( 'Obdrželi jste slevový kupón na další nákup', 'musilda' ); ?></h3>
<p><?php _e( 'Pro získání slevy, při dalším nákupu zadete v pokladně nálsedující kód:', 'musilda' ); ?> <?php echo $coupon_code; ?></p>

<?php do_action( 'woocommerce_email_footer' ); 