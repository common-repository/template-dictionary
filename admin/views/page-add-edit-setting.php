<?php if( ! defined('ABSPATH') ) exit; ?>
<div class="wrap template-dictionary-wrap">
	<h2><?php _e( 'Add/Edit setting', 'template-dictionary' ); ?></h2>
	<?php $this->print_all_notices(); ?>
	<form name="form" method="post" action="">

		<?php wp_nonce_field( 'template_dictionary' ); ?>

		<?php if( $id !== 0 ){ ?>
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<?php } ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="code_input"><?php _e( 'Code', 'template-dictionary' ); ?></label></th>
				<td>
					<input type="text" name="code" id="code_input" value="<?php echo $code; ?>" size="32">
					<p class="description"><?php printf( __( 'Use only these characters: %s.', 'template-dictionary' ), '<code>a-zA-Z0-9_</code>' ); ?> <?php printf( __( 'First character must be from following: %s.', 'template-dictionary' ), '<code>a-z_</code>' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="type-select"><?php _e( 'Type', 'template-dictionary' ); ?></label></th>
				<td>
					<?php if( $type ) : ?>
					<input type="hidden" name="old_type" value="<?php echo $type->type(); ?>" />
					<?php endif; ?>
					<select name="type" id="type-select">
					<?php foreach ( $this->types as $key => $value ) : ?>
						<option value="<?php echo $key; ?>" <?php if( $type ){ selected( $type->type(), $key ); } ?>><?php echo $key; ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>

		<?php
		foreach ( $this->types as $key => $value ) :

			$t = ( $type && $type->type() == $key ) ? $type : new $value();
			$option_fields = $t->get_option_fields();
			if( $option_fields ) :
		?>

			<div class="type-options" id="type-options-<?php echo $key; ?>">
				<h3><?php _e( 'Options', 'template-dictionary' ); ?> (<?php echo $key; ?>)</h3>
				<table class="form-table">

				<?php foreach( $option_fields as $oname => $of ) : ?>

					<tr valign="top">
						<th scope="row"><label for="<?php echo $key . '_' . $oname ?>"><?php echo $of['caption']; ?></label></th>
						<td><?php printf( $of['html'], $t->is_check_option( $oname ) ? checked( true, $t->get_option( $oname ), false ) : $t->get_option( $oname ) ); ?>
							<?php if( isset( $of['desc'] ) ) : ?>
							<p class="description"><?php echo $of['desc']; ?></p>
							<?php endif; ?>
						</td>
					</tr>

				<?php endforeach; ?>

				</table>
			</div>

		<?php
			endif;
		endforeach;
		?>

		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save' ); ?>" />
		</p>

	</form>

	<?php if( $id !== 0 ) : ?>
	<a href="?page=template_dictionary_edit_value&id=<?php echo $id; ?>" class="button"><?php _e( 'Edit values', 'template-dictionary' ); ?></a>
	<?php endif; ?>
</div>