<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class MarketStats extends BaseWidget {

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

	public static function tinymce_get_stat_options() {
		exit( json_encode( self::$stat_options ) );
	}

	public function __construct(){
		parent::__construct( 'flexmls_market_stats', 'Flexmls&reg;: Market Statistics', array(
			'classname' => 'flexmls_market_stats',
			'description' => 'Monthly summary listing data about the market in beautifully displayed graphs',
		) );
	}

	public function form( $instance ){

		if($instance == NULL) {
			$instance = array();
		}

		$defaults = array(
			'title' => 'Market Statistics',
			'stat_type' => 'absorption',
			'chart_data' => array( 'AbsorptionRate' ),
			'chart_type' => 'line',
			'property_type' => 'A',
			'time_period' => 12,
			'location_field_name_to_display' => '',
			'location_field_name_to_search' => '',
			'location_field_value_to_search' => '',
			'location_field' => '',
		);

		$data = array_merge($defaults, $instance);

		// The shortcode generator stores chart_data as a string, but we need an array
		if ( ! is_array($data['chart_data'])) {
			$data['chart_data'] = explode(',', $data['chart_data']);
		}

		$data['stat_options'] = self::$stat_options;

		$flexmls_settings = get_option( 'flexmls_settings' );
		$data['property_types'] = $flexmls_settings[ 'general' ][ 'property_types' ];

		echo $this->render('market_stats/form.php', $data);

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
		$instance[ 'time_period' ] = min( 12, max( 1, absint( $new_instance[ 'time_period' ] ) ) );
		$instance[ 'location_field' ] = !isset( $new_instance[ 'location_field' ] ) ? '' : sanitize_text_field( $new_instance[ 'location_field' ] );

		return $instance;
	}

	public function widget( $args, $instance ){
		wp_enqueue_script( 'chartjs' );

		$stat_type = $instance[ 'stat_type' ];
		$chart_data = $instance[ 'chart_data' ];
		$chart_type = $instance[ 'chart_type' ];
		$property_type = $instance[ 'property_type' ];
		$time_period = $instance[ 'time_period' ];
		$location_field = isset( $instance[ 'location_field' ] ) ? $instance[ 'location_field' ] : null;
		$location_field_name_to_search = '';
		$location_field_value_to_search = '';
		if( $location_field ){
			$location_field_pieces = explode( '***', $location_field );
			$location_field_name_to_search = $location_field_pieces[ 1 ];
			$location_field_value_to_search = $location_field_pieces[ 0 ];
		}

		$MarketStats = new \SparkAPI\MarketStats();
		$data = $MarketStats->get_market_data( $stat_type, $chart_data, $property_type, $location_field_name_to_search, $location_field_value_to_search );

		if( !empty( $data ) && 1 < count( $data ) ){

			echo $args[ 'before_widget' ];

			if( !empty( $instance[ 'title' ] ) ){
				echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
			}

			$raw_id = array_key_exists('widget_id', $args) ? $args[ 'widget_id' ] : 'market_stats';
			$id = sprintf( '%u', crc32( $raw_id ) );
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
