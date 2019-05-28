<?php
if ( $isTestRunning ):
	$test_url = admin_url( 'admin.php?page=tho_admin_dashboard' ) . '#test/' . $runningTest->id;
	?>
	<h4 class="tho-center-align">
		<?php echo __( "Post reports", THO_TRANSLATE_DOMAIN ); ?>
	</h4>

	<table class="tho_statistics_table">
		<tr>
			<td><?php echo __( "Views:", THO_TRANSLATE_DOMAIN ); ?></td>
			<td colspan="2">
				<span class="tho-large-text">
					<?php echo $test_statistics['total_views']; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __( "Engagements:", THO_TRANSLATE_DOMAIN ); ?>
			</td>
			<td>
				<span class="tho-large-text">
					<?php echo $test_statistics['total_engagements']; ?>
				</span>
			</td>
			<td>
				<a href="javascript:void(0);" onclick="show_hide_tho_engagemets_details();" class="tho-blue-text tho-show-hide-table">
					<?php echo __( "Details", THO_TRANSLATE_DOMAIN ); ?><span id="tho_engagements_details_status" class="dashicons">&#xf347;</span></a>
			</td>
		</tr>
		<tr class="tho_engagements_details" style="display: none;">
			<td colspan="3">
				<table class="tho_statistics_table">
					<tr>
						<td>
							<span class="tho-icon-pointer tho-icon-statistics"></span>
							<span class="tho-blue-text"><?php echo __( "Clicks", THO_TRANSLATE_DOMAIN ); ?></span>
						</td>
						<td><?php echo $test_statistics['click_engagements']; ?>/<?php echo $test_statistics['click_views']; ?></td>
						<td class="tho-right-align"><strong><?php echo $test_statistics['click_engagement_p']; ?></strong></td>
					</tr>
					<tr>
						<td>
							<span class="tho-icon-arrows tho-icon-statistics"></span>
							<span class="tho-blue-text"><?php echo __( "Scrolls", THO_TRANSLATE_DOMAIN ); ?></span>
						</td>
						<td><?php echo $test_statistics['scroll_engagements']; ?>/<?php echo $test_statistics['scroll_views']; ?></td>
						<td class="tho-right-align"><strong><?php echo $test_statistics['scroll_engagement_p']; ?></strong></td>
					</tr>
					<tr>
						<td>
							<span class="tho-icon-clock tho-icon-statistics"></span>
							<span class="tho-blue-text"><?php echo __( "Time on site", THO_TRANSLATE_DOMAIN ); ?></span>
						</td>
						<td><?php echo $test_statistics['time_engagements']; ?>/<?php echo $test_statistics['time_views']; ?></td>
						<td class="tho-right-align"><strong><?php echo $test_statistics['time_engagement_p']; ?></strong></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td><?php echo __( "Engagement rate", THO_TRANSLATE_DOMAIN ); ?>:</td>
			<td colspan="2">
				<span class="tho-large-text">
					<?php echo $test_statistics['engagement_rate']; ?>
				</span>
			</td>
		</tr>
	</table>
	<br>
	<hr class="tho-line">
	<table class="tho_statistics_table tho-table-fixed tho-table-links">
		<tr>
			<td>
				<a class="tho-center-align tho-no-underline" href="<?php echo admin_url( 'admin.php?page=tho_admin_dashboard' ); ?>#reporting/<?php echo THO_ENGAGEMENT_REPORT; ?>/<?php echo $post_id; ?>">
					<div class="tho-icon-chart tho-red-text tho-large-icon"></div>
					<span class="tho-report-icon">
						<?php echo __( "Reports", THO_TRANSLATE_DOMAIN ); ?>
					</span>
				</a>
			</td>
			<td>
				<a class="tho-center-align tho-no-underline" href="<?php echo $test_url; ?>">
					<div class="tho-icon-flask tho-green-text tho-large-icon"></div>
					<span class="tho-report-icon">
						<?php echo __( "View Test", THO_TRANSLATE_DOMAIN ); ?>
					</span>
				</a>
			</td>
			<td>
				<a class="tho-center-align tho-no-underline" href="<?php echo admin_url( 'admin.php?page=tho_admin_dashboard' ); ?>#dashboard">
					<div class="tho-icon-home tho-blue-text tho-large-icon"></div>
					<span class="tho-report-icon">
						<?php echo __( "Headline Optimizer Home", THO_TRANSLATE_DOMAIN ); ?>
					</span>
				</a>
			</td>
		</tr>
	</table>
	<script>
		function show_hide_tho_engagemets_details() {
			jQuery( ".tho_engagements_details" ).toggle();
			if ( jQuery( ".tho_engagements_details" ).is( ":visible" ) ) {
				jQuery( "#tho_engagements_details_status" ).html( "&#xf343;" );
			} else {
				jQuery( "#tho_engagements_details_status" ).html( "&#xf347;" );
			}
		}
	</script>
	<?php
else:
	?>
	<p><?php echo __( "Currently there are no tests running for this piece of content.", THO_TRANSLATE_DOMAIN ); ?></p>
	<p><?php echo __( "Add one or more headline variations and save the post to start a new test.", THO_TRANSLATE_DOMAIN ); ?></p>
	<p><a target="_blank" href="https://thrivethemes.com/tkb_item/how-to-create-a-headline-test-directly-from-your-postpage/"><?php echo __( "How does this work?", THO_TRANSLATE_DOMAIN ); ?></a></p>
	<?php
endif;
