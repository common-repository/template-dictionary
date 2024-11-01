<?php if( ! defined('ABSPATH') ) exit; ?>
<div class="wrap template-dictionary-wrap">
	<h2><?php _e( 'Export/Import' ); ?></h2>
	<?php $this->print_all_notices(); ?>

	<h3><?php _e( 'Export' ); ?></h3>

	<form name="form" method="post" action="">

		<?php wp_nonce_field( 'template_dictionary' ); ?>

		<input type="hidden" name="action" value="export">

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="export_type"><?php _e( 'What to export', 'template-dictionary' ); ?></label></th>
				<td>
					<select name="export_type" id="export_type">
						<option value="sv"><?php _e( 'Settings and values', 'template-dictionary' ); ?></option>
						<option value="s"><?php _e( 'Settings only', 'template-dictionary' ); ?></option>
					</select>
				</td>
			</tr>

			<tr valign="top" id="export_languages_wrapper">
				<th scope="row"><label for="export_languages"><?php _e( 'Languages to export', 'template-dictionary' ); ?></label></th>
				<td>
					<select name="export_languages[]" id="export_languages" multiple="multiple" class="select2">
					<?php foreach ( $this->get_langs() as $lang ) : ?>
						<option value="<?php echo $lang; ?>" selected="selected"><?php echo $lang; ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>

		</table>

		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Export', 'template-dictionary' ); ?>" />
		</p>

	</form>

	<h3><?php _e( 'Import' ); ?></h3>

	<form name="form" method="post" action="" enctype="multipart/form-data">

		<?php wp_nonce_field( 'template_dictionary' ); ?>

		<input type="hidden" name="action" value="import">

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="import_file"><?php _e( 'XML file', 'template-dictionary' ); ?></label></th>
				<td><input type="file" name="import_file" id="import_file" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="import_type"><?php _e( 'Import type', 'template-dictionary' ); ?></label></th>
				<td>
					<select name="import_type" id="import_type">
						<option value="delete"><?php _e( 'Delete settings and import all', 'template-dictionary' ); ?></option>
						<option value="update"><?php _e( 'Update settings and import new', 'template-dictionary' ); ?></option>
						<option value="import_new"><?php _e( 'Import only new settings', 'template-dictionary' ); ?></option>
					</select>
				</td>
			</tr>

		</table>

		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Import', 'template-dictionary' ); ?>" />
		</p>

	</form>
</div>