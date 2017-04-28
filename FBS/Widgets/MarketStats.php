<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class MarketStats extends \WP_Widget {

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

	public function __construct() {
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

	public function update( $new_instance, $old_instance ) {
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
		$location_field_name_to_search = isset( $instance[ 'location_field_name_to_search' ] ) ? $instance[ 'location_field_name_to_search' ] : null;
		$location_field_value_to_search = isset( $instance[ 'location_field_value_to_search' ] ) ? $instance[ 'location_field_value_to_search' ] : null;

		$MarketStats = new \SparkAPI\MarketStats();
		write_log( $MarketStats->get_market_data( $stat_type, $chart_data, $property_type, $location_field_name_to_search, $location_field_value_to_search ) );

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
        labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
        datasets: [{
            label: '# of Votes',
            data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
		});
			})(jQuery);
		</script>
		<?php
		echo $args[ 'after_widget' ];
	}
}