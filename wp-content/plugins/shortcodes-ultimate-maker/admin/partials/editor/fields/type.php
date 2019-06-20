<?php
defined( 'ABSPATH' ) or exit;
$current = get_post_meta( get_the_ID(), 'sumk_code_type', true );
$deprecated = $current === 'php_return';
$types = $this->get_code_types( $deprecated );
?>

<select name="sumk_code_type" id="sum-type">
	<?php foreach ( $types as $type ) : ?>
		<option value="<?php echo esc_attr( $type['id'] ); ?>" <?php selected( $type['id'] , $current ); ?>><?php echo $type['title']; ?></option>
	<?php endforeach; ?>
</select>

<p class="description"><?php printf( __( 'This setting determines what type of code will be used below in the code editor. %sLearn more about code types%s.', 'shortcodes-ultimate-maker' ), '<a href="http://docs.getshortcodes.com/article/61-custom-shortcode#Code_types" target="_blank"><nobr>', '</nobr></a>' ); ?></p>
