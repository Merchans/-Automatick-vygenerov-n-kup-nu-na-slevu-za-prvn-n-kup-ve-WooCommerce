
<?php
$coupon_code = get_post_meta( $order->get_id(), 'generated_coupon', true );

echo "= " . $email_heading . " =\n\n";

echo __( 'Děkujeme za první nákup!', 'musilda' ) . '\n\n';
echo __( 'Obdrželi jste slevový kupón na další nákup', 'musilda' ) . 'n\n';

echo __( 'Pro získání slevy, při dalším nákupu zadete v pokladně nálsedující kód:', 'musilda' ) . ' <strong>' , $coupon_code . '</strong>\n\n';


echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );