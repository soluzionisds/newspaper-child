<?php
/*** Template Name: Full Width Page without Title */
get_header();
?>
<div class="td-main-content-wrap td-container-wrap">
	<div class="td-container">
		<div class="td-pb-row">
			<div class="td-pb-span12 td-main-content">
				<div class="td-ss-main-content">
					<?php
						if (have_posts()) {
							while ( have_posts() ) : the_post();
								?>
								<div class="td-page-content tagdiv-type">
									<?php the_content(); ?>
								</div>
					<?php endwhile;//end loop
							comments_template('', true);
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
