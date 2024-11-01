<?php if( ! defined('ABSPATH') ) exit; ?>
<div class="wrap template-dictionary-wrap">
	<h2><?php _e( 'Edit value' ); ?></h2>
	<?php $this->print_all_notices(); ?>
	<?php if( $id !== 0 ) : ?>
		<form name="form" method="post" action="">

			<?php wp_nonce_field( 'template_dictionary' ); ?>

			<input type="hidden" name="id" value="<?php echo $id; ?>">

			<table class="form-table">

				<tr valign="top">
					<th scope="row"><?php _e( 'Code', 'template-dictionary' ); ?></th>
					<td><?php echo $code; ?></td>
				</tr>

				<?php foreach ( $this->get_langs() as $lang ) { ?>
				<tr valign="top">
					<th scope="row"><label for="value_<?php echo $lang; ?>"><?php _e( 'Value', 'template-dictionary' ); ?> (<?php echo $lang; ?>)</label></th>
					<td><?php $type->display_field( $lang, isset( $values[$lang] ) ? $values[$lang] : null ); ?></td>
				</tr>
				<?php } ?>
			</table>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save' ); ?>" />
			</p>

		</form>

		<a href="?page=template_dictionary_add_edit_setting&id=<?php echo $id; ?>" class="button"><?php _e( 'Edit settings', 'template-dictionary' ); ?></a>
	<?php endif; ?>
</div>