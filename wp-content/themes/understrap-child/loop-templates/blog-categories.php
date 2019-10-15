<?php 
/**
 * Display category links specified below.
 *
 * @package understrap
 */
?>
		<div class="row">
			<div class="col-md-12 text-center">
				<?php $categories = array(
					"dating & sexuality",
					"health & self image",
					"media",
					"relationships",
					"spiritual life",
					"values"
				);

				foreach($categories as $index => $category_name)
				{
					// Get the ID of a given category
    				$category_id = get_cat_ID( $category_name );

				    // Get the URL of this category
				    $category_link = esc_url( get_category_link( $category_id ) );

				    //output a link to the category archive page
				    echo "<a class=\"button button--small\" href=\"{$category_link}\" title=\"{$category_name}\">{$category_name}</a>";
				    if( $index != count( $categories ) - 1)
				    {
				    	echo "&nbsp;";
				    }
				}
				?>
		    </div>
		</div>