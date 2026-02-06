<?php
/**
 * Filename: tabs-list-and-switches.php
 * Description: tabs list and switches backend.
 *
 * @package WP_Easy_Pay
 */

if ( isset( $_COOKIE['wpep-setting-tab'] ) ) {
	$tab_opened = sanitize_text_field( wp_unslash( $_COOKIE['wpep-setting-tab'] ) );
} else {
	$tab_opened = 'panel-1-ctrl';
}

?>
<!-- TAB CONTROLLERS -->
<input id="panel-1-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-1-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="panel-2-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-2-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="panel-3-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-3-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="panel-4-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-4-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="panel-5-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-5-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="panel-6-ctrl" class="panel-radios" type="radio"
		name="tab-radios" 
		<?php
		if ( 'panel-6-ctrl' === $tab_opened ) :
			echo 'checked';
endif;
		?>
		/>
<input id="nav-ctrl" class="panel-radios" type="checkbox" name="nav-checkbox"/>
<!-- TABS LIST -->
<ul id="tabs-list">
	<!-- MENU TOGGLE -->
	<label id="open-nav-label" for="nav-ctrl"></label>
	<?php
	if ( wepp_fs()->is__premium_only() ) {
		?>
	<li id="li-for-panel-1" data-id="panel-1-ctrl">
		<label class="panel-label" for="panel-1-ctrl">Square account settings</label>
	</li>
	<!--INLINE-BLOCK FIX -->
		<?php
	}
	?>

	<li id="li-for-panel-2" data-id="panel-2-ctrl">
		<label class="panel-label" for="panel-2-ctrl">Form settings</label>
	</li>
	<!--INLINE-BLOCK FIX -->
	<li id="li-for-panel-3" data-id="panel-3-ctrl">
		<label class="panel-label" for="panel-3-ctrl">Extra fields</label>
	</li>
	<!--INLINE-BLOCK FIX -->
	<li id="li-for-panel-4" data-id="panel-4-ctrl">
		<label class="panel-label" for="panel-4-ctrl">Notifications</label>
	</li>
	<!--INLINE-BLOCK FIX -->
	<li id="li-for-panel-5" data-id="panel-5-ctrl">
		<label class="panel-label" for="panel-5-ctrl">Transaction notes</label>
	</li>
	<?php
	if ( wepp_fs()->is__premium_only() ) {
		?>
		<li id="li-for-panel-6" data-id="panel-6-ctrl" class="last">
			<label class="panel-label" for="panel-6-ctrl">Additional charges</label>
		</li>
		<?php
	}
	?>

	<label id="close-nav-label" for="nav-ctrl">Close</label>

	<!-- <li class="last-child swtichHold">
		<a href="#" class="settingsIcon"><i class="fa fa-gear"></i></a>
	</li> -->
</ul>
