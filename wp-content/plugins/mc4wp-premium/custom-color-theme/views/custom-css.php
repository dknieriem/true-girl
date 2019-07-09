.mc4wp-form-<?php echo $form_id; ?> input[type="submit"],
.mc4wp-form-<?php echo $form_id; ?> button {
	color: <?php echo $font_color; ?>;
	background-color: <?php echo $color; ?>;
	border-color: <?php echo $darker_color; ?>;
}

.mc4wp-form-<?php echo $form_id; ?> input[type="submit"]:hover,
.mc4wp-form-<?php echo $form_id; ?> button:hover,
.mc4wp-form-<?php echo $form_id; ?> input[type="submit"]:active,
.mc4wp-form-<?php echo $form_id; ?> button:active,
.mc4wp-form-<?php echo $form_id; ?> input[type="submit"]:focus,
.mc4wp-form-<?php echo $form_id; ?> button:focus {
	color: <?php echo $font_color; ?>;
	background-color: <?php echo $darker_color; ?>;
	border-color: <?php echo $darkest_color; ?>;
}

.mc4wp-form-<?php echo $form_id; ?> input[type="text"]:focus,
.mc4wp-form-<?php echo $form_id; ?> input[type="email"]:focus,
.mc4wp-form-<?php echo $form_id; ?> input[type="tel"]:focus,
.mc4wp-form-<?php echo $form_id; ?> input[type="date"]:focus,
.mc4wp-form-<?php echo $form_id; ?> input[type="url"]:focus,
.mc4wp-form-<?php echo $form_id; ?> textarea:focus,
.mc4wp-form-<?php echo $form_id; ?> select:focus {
	border-color: <?php echo $color; ?>;
}