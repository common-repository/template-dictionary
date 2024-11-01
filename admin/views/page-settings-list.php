<?php if( ! defined('ABSPATH') ) exit; ?>
<div class="wrap template-dictionary-wrap">
	<h2>Template dictionary settings</h2>
	<?php
		$this->print_all_notices();
		$list_table->prepare_items();
		$list_table->display();
	?>
</div>