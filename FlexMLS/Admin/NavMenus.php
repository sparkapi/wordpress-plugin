<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class NavMenus {

	// TO DO: Allow users to select Saved Searches for navigation menus in the
	// Appearance->Menus interface

	public static function add_saved_searches_meta_boxes(){
		$SparkAPI = new \SparkAPI\Core();
		if( $SparkAPI->generate_auth_token() ){
			//add_meta_box( 'flexmls-saved-searches', 'Flexmls&reg; Saved Searches', array( 'FlexMLS\Admin\NavMenus', 'meta_box_saved_searches' ), 'nav-menus', 'side', null );
		}
	}

	public static function nav_menu_meta_box_object( $object ){
		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );
		if( $all_idx_links ){
			add_meta_box( 'flexmls-saved-searches', 'Flexmls&reg; Saved Searches', array( 'FlexMLS\Admin\NavMenus', 'meta_box_saved_searches' ), 'nav-menus', 'side', null );
		}
		return $object;
	}

	public static function meta_box_saved_searches( $object, $args ){
		global $nav_menu_selected_id;
		$walker = new \Walker_Nav_Menu_Checklist();

		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );
		if( !$all_idx_links ){
			return;
		}

		$idx_page_links = array();

		foreach( $all_idx_links as $all_idx_link ){
			$link = array();
			$link[ 'classes' ] = array();
			$link[ 'db_id' ] = 0;
			$link[ 'menu_item_parent' ] = 0;
			$link[ 'type' ] = 'flexmls-saved-search';
			$link[ 'object_id' ] = $all_idx_link[ 'LinkId' ];
			$link[ 'title' ] = $all_idx_link[ 'Name' ];
			$link[ 'object' ] = 'flexmls-saved-search';
			$link[ 'url' ] = home_url( $all_idx_link[ 'Id' ] );
			$link[ 'attr_title' ] = $all_idx_link[ 'Name' ];
			$link[ 'target' ] = '';
			$link[ 'xfn' ] = '';
			$idx_page_links[] = (object) $link;
		}

		$removed_args = array( 'action', 'customlink-tab', 'edit-menu-item', 'menu-item', 'page-tab', '_wpnonce' );
		?>
		<div id="flexmls-saved-searches" class="categorydiv">
			<ul class="flexmlsss-tabs add-menu-item-tabs">
				<li class="tabs"><a href="javascript:void();" onclick="return false;">Saved Searches</a></li>
			</ul>
			<div class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul class="categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $idx_page_links ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>
			</div>
			<p class="button-controls wp-clearfix">
				<span class="list-controls">
					<a href="<?php echo esc_url( add_query_arg( array( 'flexmlsss-tabs' => 'all', 'selectall' => 1, ), remove_query_arg( $removed_args ) )); ?>#flexmls-saved-searches" class="select-all">Select All</a>
				</span>
				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-flexmlsss-menu-item" id="submit-flexmls-saved-searches" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

}