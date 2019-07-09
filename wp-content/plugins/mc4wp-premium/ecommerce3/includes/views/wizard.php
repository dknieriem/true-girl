<?php defined( 'ABSPATH' ) or exit;

$current = (int) $_GET['wizard'];

$steps = array(
    '1' => __( 'Setup your store', 'mc4wp-ecommerce' ),
    '2' => __( 'Add your products', 'mc4wp-ecommerce' ),
    '3' => __( 'Add your orders', 'mc4wp-ecommerce' ),
);
?>

<div class="wrap ecommerce" id="mc4wp-admin">
    <div class="wizard">
        
        <h1 class="page-title">E-Commerce Configuration</h1>

        <div class="steps-nav">
            <?php foreach( $steps as $number => $label  ) {
                printf( '<div class="step %s">', $number == $current ? 'current' : '' );
                if( $current > $number ) {
                    printf( '<a href="%s">%d. %s</a>', add_query_arg( array( 'wizard' => $number ) ), $number, $label );
                } else {
                    printf( '<span>%d. %s</span>', $number, $label );
                }
                printf("</div>");
            } ?>
        </div>

        <div class="well">

            <div class="wizard-step clearfix" style="display: <?php echo $current == 1 ? 'block' : 'none'; ?>">
                <?php require __DIR__ . '/parts/config-store.php'; ?>
                <br style="clear: none;" />
            </div>

            <div class="wizard-step clearfix" style="display: <?php echo $current == 2 ? 'block' : 'none'; ?>">
                <?php require __DIR__ . '/parts/config-products.php'; ?>
                <br style="clear: none;" />

                <p class="submit">
                    <a class="button button-primary next" href="<?php echo add_query_arg( array( 'wizard' => 3 ) ); ?>" disabled="disabled"><?php _e( 'Next', 'mc4wp-ecommerce' ); ?></a>
                </p>
            </div>

            <div class="wizard-step clearfix" style="display: <?php echo $current == 3 ? 'block' : 'none'; ?>">
                <?php require __DIR__ . '/parts/config-orders.php'; ?>
                <br style="clear: none;" />

                <p class="submit">
                    <a class="button button-primary next" href="<?php echo remove_query_arg( 'wizard' ); ?>" disabled="disabled"><?php _e( 'Finish', 'mc4wp-ecommerce' ); ?></a>
                </p>
            </div>


        </div><!-- / .well -->
    </div><!-- / .wizard -->
</div><!-- / .wrap -->
