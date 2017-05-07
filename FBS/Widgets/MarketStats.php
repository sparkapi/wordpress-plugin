<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class MarketStats extends \WP_Widget {

	public static $chart_colors = array(
		array(78,183,78),
		array(238,198,67),
		array(183,195,243),
		array(89,127,89),
		array(95,95,95),
		array(1,22,56),
		array(227,74,111),
		array(224,109,6),
		array(243,91,4),
		array(241,135,1)
	);

	public static $stat_options = array(
		'absorption' => array(
			'AbsorptionRate' => 'Absorption Rate',
		),
		'inventory' => array(
			'ActiveListings' => 'Number of Active Listings',
			'NewListings' => 'Number of New Listings',
			'PendedListings' => 'Number of Pended Listings',
			'SoldListings' => 'Number of Sold Listings'
		),
		'price' => array(
			'ActiveAverageListPrice' => 'Active Average List Price',
			'NewAverageListPrice' => 'New Average List Price',
			'PendedAverageListPrice' => 'Pended Average List Price',
			'SoldAverageListPrice' => 'Sold Average List Price',
			'SoldAverageSoldPrice' => 'Sold Average Sold Price',
			'ActiveMedianListPrice' => 'Active Median List Price',
			'NewMedianListPrice' => 'New Median List Price',
			'PendedMedianListPrice' => 'Pended Median List Price',
			'SoldMedianListPrice' => 'Sold Median List Price',
			'SoldMedianSoldPrice' => 'Sold Median Sold Price'
		),
		'ratio' => array(
			'SaleToOriginalListPriceRatio' => 'Sale To Original List Price',
			'SaleToListPriceRatio' => 'Sale To List Price'
		),
		'dom' => array(
			'AverageDom' => 'Average DOM',
			'AverageCdom' => 'Average Cummulative DOM'
		),
		'volume' => array(
			'ActiveListVolume' => 'Active List Volume',
			'NewListVolume' => 'New List Volume',
			'PendedListVolume' => 'Pended List Volume',
			'SoldListVolume' => 'Sold List Volume',
			'SoldSaleVolume' => 'Sold Sale Volume'
		)
	);

	public function __construct(){
		parent::__construct( 'flexmls_market_stats', 'Flexmls&reg;: Market Statistics', array(
			'classname' => 'flexmls_market_stats',
			'description' => 'Monthly summary listing data about the market, displayed graphs.',
		) );
	}

	public function form( $instance ){
		add_thickbox();
		$title = !isset( $instance[ 'title' ] ) ? 'Market Statistics' : $instance[ 'title' ];
		$stat_type = !isset( $instance[ 'stat_type' ] ) ? 'absorption' : $instance[ 'stat_type' ];
		$chart_data = !isset( $instance[ 'chart_data' ] ) ? array( 'AbsorptionRate' ) : $instance[ 'chart_data' ];
		$chart_type = !isset( $instance[ 'chart_type' ] ) ? 'line' : $instance[ 'chart_type' ];
		$property_type = !isset( $instance[ 'property_type' ] ) ? 'A' : $instance[ 'property_type' ];
		$time_period = !isset( $instance[ 'time_period' ] ) ? 12 : $instance[ 'time_period' ];
		$location_field_name_to_display = !isset( $instance[ 'location_field_name_to_display' ] ) ? '' : $instance[ 'location_field_name_to_display' ];
		$location_field_name_to_search = !isset( $instance[ 'location_field_name_to_search' ] ) ? '' : $instance[ 'location_field_name_to_search' ];
		$location_field_value_to_search = !isset( $instance[ 'location_field_value_to_search' ] ) ? '' : $instance[ 'location_field_value_to_search' ];
		$flexmls_settings = get_option( 'flexmls_settings' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>">Type of Statistics</label>
			<select class="widefat flexmls-widget-market-stat-selector" id="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'stat_type' ) ); ?>" data-options='<?php echo json_encode( self::$stat_options ); ?>'>
				<option value="absorption" <?php selected( $stat_type, 'absorption' ); ?>>Absorption Rate</option>
				<option value="inventory" <?php selected( $stat_type, 'inventory' ); ?>>Inventory</option>
				<option value="price" <?php selected( $stat_type, 'price' ); ?>>Price</option>
				<option value="ratio" <?php selected( $stat_type, 'ratio' ); ?>>Sale to List Price Ratios</option>
				<option value="dom" <?php selected( $stat_type, 'dom' ); ?>>Days On Market</option>
				<option value="volume" <?php selected( $stat_type, 'volume' ); ?>>Volume</option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'chart_data' ) ); ?>">What data would you like to display?</label>
			<select multiple class="widefat flexmls-widget-market-stat-options" id="<?php echo esc_attr( $this->get_field_id( 'chart_data' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'chart_data' ) ); ?>[]">
				<?php
					$selectOptions = self::$stat_options[ $stat_type ];
					foreach( $selectOptions as $k => $v ){
						echo '<option value="' . $k . '" ' . selected( in_array( $k, $chart_data ), true, false ) . '>' . $v . '</option>';
					}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>">Chart Type</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'chart_type' ) ); ?>">
				<option value="line" <?php selected( $chart_type, 'line' ); ?>>Line Chart</option>
				<option value="bar" <?php selected( $chart_type, 'bar' ); ?>>Bar Chart</option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>">Property Type</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_type' ) ); ?>">
				<?php foreach( $flexmls_settings[ 'general' ][ 'property_types' ] as $ptype_key => $ptype_values ): ?>
					<option value="<?php echo $ptype_key; ?>" <?php selected( $property_type, $ptype_key ); ?>><?php echo $ptype_values[ 'value' ]; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>">Time Period</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'time_period' ) ); ?>">
				<?php for( $i = 1; $i < 13; $i++ ): ?>
					<option value="<?php echo $i; ?>" <?php selected( $time_period, $i ); ?>><?php printf( _n( '%d Month', '%d Months', $i ), $i ); ?></option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label>Select a Location</label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_display' ) ); ?>" value="<?php echo $location_field_name_to_display; ?>" readonly>
			<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_search' ) ); ?>" value="<?php echo $location_field_name_to_search; ?>">
			<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_value_to_search' ) ); ?>" value="<?php echo $location_field_value_to_search; ?>">
			<button
				type="button"
				class="widefat button-secondary flexmls-location-selector"
				data-limit="1"
				data-name-to-display="<?php echo $this->get_field_name( 'location_field_name_to_display' ); ?>"
				data-name-to-search="<?php echo $this->get_field_name( 'location_field_name_to_search' ); ?>"
				data-value-to-search="<?php echo $this->get_field_name( 'location_field_value_to_search' ); ?>"
				data-target="<?php echo $this->get_field_id( 'location_popup' ); ?>">Select Location</button>
		</p>
		<?php
		\FBS\Admin\Utilities::location_popup( $this->get_field_id( 'location_popup' ) );
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
		$instance[ 'stat_type' ] = sanitize_text_field( $new_instance[ 'stat_type' ] );
		if( !isset( $new_instance[ 'chart_data' ] ) ){
			// A chart type was chosen, but no data points were selected. Populate the "default" data points per the Spark platform
			switch( $instance[ 'stat_type' ] ){
				case 'absorption':
					$instance[ 'chart_data' ] = array( 'AbsorptionRate' );
					break;
				case 'inventory':
					$instance[ 'chart_data' ] = array( 'ActiveListings', 'NewListings' );
					break;
				case 'price':
					$instance[ 'chart_data' ] = array( 'ActiveAverageListPrice', 'ActiveMedianListPrice', 'NewAverageListPrice', 'NewMedianListPrice' );
					break;
				case 'ratio':
					$instance[ 'chart_data' ] = array( 'SaleToOriginalListPriceRatio' );
					break;
				case 'dom':
					$instance[ 'chart_data' ] = array( 'AverageDom' );
					break;
				case 'volume':
					$instance[ 'chart_data' ] = array( 'ActiveListVolume', 'NewListVolume' );
					break;
			}
		} else {
			$instance[ 'chart_data' ] = array_filter( $new_instance[ 'chart_data' ], 'sanitize_text_field' );
		}
		$instance[ 'chart_type' ] = 'bar' == $new_instance[ 'chart_type' ] ? 'bar' : 'line';
		$instance[ 'property_type' ] = sanitize_text_field( $new_instance[ 'property_type' ] );
		$instance[ 'time_period' ] = min( 12, max( 1, intval( $new_instance[ 'time_period' ] ) ) );
		$instance[ 'location_field_name_to_display' ] = !isset( $new_instance[ 'location_field_name_to_display' ] ) ? '' : sanitize_text_field( $new_instance[ 'location_field_name_to_display' ] );
		$instance[ 'location_field_name_to_search' ] = !isset( $new_instance[ 'location_field_name_to_search' ] ) ? '' : sanitize_text_field( $new_instance[ 'location_field_name_to_search' ] );
		$instance[ 'location_field_value_to_search' ] = !isset( $new_instance[ 'location_field_value_to_search' ] ) ? '' : sanitize_text_field( $new_instance[ 'location_field_value_to_search' ] );

		return $instance;
	}

	public function widget( $args, $instance ){
		wp_enqueue_script( 'chartjs' );

		$stat_type = $instance[ 'stat_type' ];
		$chart_data = $instance[ 'chart_data' ];
		$chart_type = $instance[ 'chart_type' ];
		$property_type = $instance[ 'property_type' ];
		$time_period = $instance[ 'time_period' ];
		$location_field_name_to_search = isset( $instance[ 'location_field_name_to_search' ] ) ? $instance[ 'location_field_name_to_search' ] : null;
		$location_field_value_to_search = isset( $instance[ 'location_field_value_to_search' ] ) ? $instance[ 'location_field_value_to_search' ] : null;

		$MarketStats = new \SparkAPI\MarketStats();
		$data = $MarketStats->get_market_data( $stat_type, $chart_data, $property_type, $location_field_name_to_search, $location_field_value_to_search );

		if( !empty( $data ) ){

			echo $args[ 'before_widget' ];

			if( !empty( $instance[ 'title' ] ) ){
				echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
			}

			$id = sprintf( '%u', crc32( $args[ 'widget_id' ] ) );
			?>
			<canvas id="flexmls_market_stats_<?php echo $id; ?>" width="400" height="400"></canvas>
			<script>
				(function($){
					$( document ).ready( function(){
						var chart_<?php echo $id; ?> = new Chart( document.getElementById( 'flexmls_market_stats_<?php echo $id; ?>' ), {
							type: '<?php echo $chart_type; ?>',
							data: {
								labels: [<?php
									$dates = array_reverse( array_slice( $data[ 'Dates' ], 0, $time_period ) );
									$dates_formatted = array();
									for( $i = 0; $i < count( $dates ); $i++ ){
										list($m,$d,$y) = explode( '/', $dates[ $i ] );
										$dates_formatted[] = date( 'M Y', mktime( 1,0,0,$m,$d,$y ) );
									}
									echo '"' . implode( '","', $dates_formatted ) . '"';
								?>],
								datasets: <?php
									$obj = array();
									$chart_colors = MarketStats::$chart_colors;
									$stat_options = MarketStats::$stat_options;
									$color = 0;
									foreach( $data as $key => $vals ){
										if( 'Dates' == $key ){
											continue;
										}
										$vals = array_reverse( array_slice( $vals, 0, $time_period ) );
										$vals_to_display = array();
										for( $i = 0; $i < $time_period; $i++ ){
											$vals_to_display[] = $vals[ $i ];
										}
										$obj[] = array(
											'backgroundColor' => 'rgba(' . implode( ',', $chart_colors[ $color ] ) . ',0.2)',
											'borderWidth' => 1,
											'borderColor' => 'rgba(' . implode( ',', $chart_colors[ $color ] ) . ',0.6)',
											'data' => $vals_to_display,
											'label' => $stat_options[ $stat_type ][ $key ]
										);
										$color++;
									}
									echo json_encode( $obj );
								?>
							},
							options: {
								scales: {
									xAxes: [{
										ticks: {
											autoSkip: false,
											maxRotation: 90,
											minRotation: 90
										}
									}]
								},
								tooltips: {
									enabled: true
								}
							}
						} );
					} );
				} )( jQuery );
			</script>
			<?php
			echo $args[ 'after_widget' ];
		}
	}
}