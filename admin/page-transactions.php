<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="get">
		<?php $transactions_list_table->display(); ?>
	</form>
</div>
