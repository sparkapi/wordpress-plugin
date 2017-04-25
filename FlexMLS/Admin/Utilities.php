<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Utilities {

	static function format_listing_street_address( $record ){
		$first_line_address = self::is_not_blank_or_restricted( $record[ 'StandardFields' ][ 'UnparsedFirstLineAddress' ] ) ? sanitize_text_field( $record[ 'StandardFields' ][ 'UnparsedFirstLineAddress' ] ) : '';
		$second_line_address = array();
		if( self::is_not_blank_or_restricted( $record[ 'StandardFields' ][ 'City' ] ) ){
			$second_line_address[] = sanitize_text_field( $record[ 'StandardFields' ][ 'City' ] );
		}
		if( self::is_not_blank_or_restricted( $record[ 'StandardFields' ][ 'StateOrProvince' ] ) ){
			$second_line_address[] = sanitize_text_field( $record[ 'StandardFields' ][ 'StateOrProvince' ] );
		}
		$second_line_address = implode( ', ', $second_line_address );
		$second_line_address = array( $second_line_address );
		if( self::is_not_blank_or_restricted( $record[ 'StandardFields' ][ 'PostalCode' ] ) ){
			$second_line_address[] = sanitize_text_field( $record[ 'StandardFields' ][ 'PostalCode' ] );
		}
		$second_line_address = implode( ' ', $second_line_address );
		$one_line_address = array();
		if( !empty( $first_line_address ) ){
			$one_line_address[] = $first_line_address;
		}
		if( !empty( $second_line_address ) ){
			$one_line_address[] = $second_line_address;
		}
		$one_line_address = implode( ', ', $one_line_address );
		return array( $first_line_address, $second_line_address, $one_line_address );
	}

	public static function gentle_price_rounding( $price ){
		$price = preg_replace( '/[^0-9\.]/', '', $price );
		if( empty( $price ) ){
			return;
		}
		if( strpos( $price, '.' ) ){
			$price_pieces = explode( '.', $price );
			if( '00' != $price_pieces[ 1 ] ){
				return number_format( $price, 2 );
			}
		}
		return number_format( $price, 0 );
	}

	public static function get_current_url(){
		global $_SERVER, $wp;
		if( $wp->did_permalink ){
			$url = home_url( add_query_arg( array(), $wp->request ) );
			if( isset( $_SERVER[ 'QUERY_STRING' ] ) && !empty( $_SERVER[ 'QUERY_STRING' ] ) ){
				$url .= '?' . $_SERVER[ 'QUERY_STRING' ];
			}
			return $url;
		}
		$qs = $wp->query_string;
		if( isset( $_SERVER[ 'QUERY_STRING' ] ) && !empty( $_SERVER[ 'QUERY_STRING' ] ) ){
			$qs .= empty( $qs ) ? $_SERVER[ 'QUERY_STRING' ] : '&' . $_SERVER[ 'QUERY_STRING' ];
		}
		return home_url( '?' . $qs );
	}

	static function is_not_blank_or_restricted( $val ){
		$result = true;
		if( !is_array( $val ) ){
			$val = sanitize_text_field( $val );
			if( empty( $val ) || false !== strpos( $val, '********' ) ){
				return false;
			}
		} else {
			foreach ( $val as $v ){
				if( !self::is_not_blank_or_restricted( $v ) ){
					$result = false;
				}
			}
		}
		return $result;
	}

	public static function mls_required_fields_and_values( $listing ){
		$System = new \SparkAPI\System();
		$get_system_info = $System->get_system_info();
		$mls_id = $get_system_info[ 'MlsId' ];
    $compList = ($api_system_info["DisplayCompliance"][$mlsId]["View"][$type]['DisplayCompliance']);
    $sf = $record["StandardFields"];
    //Get Adresses
    //Since these fields take a considerable amount of time to get, check if they are required from the compliance list beforehand.
    $OfficeAddress = '';
    if (in_array('ListOfficeAddress',$compList)){
        $OfficeInfo = $fmc_api->GetAccountsByOffice($sf["ListOfficeId"]);
        $OfficeAddress = ($OfficeInfo[0]["Addresses"][0]["Address"]);
    }
    $AgentAddress = '';
    if (in_array('ListMemberAddress',$compList)){
        $AgentInfo  = $fmc_api->GetAccount($sf["ListAgentId"]);
        $AgentAddress = ($AgentInfo["Addresses"][0]["Address"]);
          }
          $CoAgentAddress = '';
    if (in_array('CoListAgentAddress',$compList)){
      $CoAgentInfo  = $fmc_api->GetAccount($sf["CoListAgentId"]);
      $CoAgentAddress = ($CoAgentInfo["Addresses"][0]["Address"]);
          }
    //Names
    $AgentName = "";
    $CoAgentName = "";
                if ((flexmlsConnect::is_not_blank_or_restricted($sf["ListAgentFirstName"])) && (flexmlsConnect::is_not_blank_or_restricted($sf["ListAgentLastName"])))
                        $AgentName = "{$sf["ListAgentFirstName"]} {$sf["ListAgentLastName"]}";
    if ((flexmlsConnect::is_not_blank_or_restricted($sf["CoListAgentFirstName"])) && (flexmlsConnect::is_not_blank_or_restricted($sf["CoListAgentLastName"])))
                        $CoAgentName = "{$sf["CoListAgentFirstName"]} {$sf["CoListAgentLastName"]}";
    //Primary Phone Numbers and Extensions
    $ListOfficePhone = "";
    $ListAgentPhone = "";
    $CoListAgentPhone = "";
    if (flexmlsConnect::is_not_blank_or_restricted($sf["ListOfficePhone"]))
      $ListOfficePhone = $sf["ListOfficePhone"];
      if (flexmlsConnect::is_not_blank_or_restricted($sf["ListOfficePhoneExt"]))
                          $ListOfficePhone .= " ext. " . $sf["ListOfficePhoneExt"];
    if (flexmlsConnect::is_not_blank_or_restricted($sf["ListAgentPreferredPhone"]))
                        $ListAgentPhone = $sf["ListAgentPreferredPhone"];
                        if (flexmlsConnect::is_not_blank_or_restricted($sf["ListAgentPreferredPhone"]))
                                $ListAgentPhone .= " ext. " . $sf["ListAgentPreferredPhone"];
                if (flexmlsConnect::is_not_blank_or_restricted($sf["CoListAgentPreferredPhone"]))
                        $CoListAgentPhone = $sf["CoListAgentPreferredPhone"];
                        if (flexmlsConnect::is_not_blank_or_restricted($sf["CoListAgentPreferredPhone"]))
                                $CoListAgentPhone .= " ext. " . $sf["CoListAgentPreferredPhone"];
    //format last modified date
    $LastModifiedDate = flexmlsConnect::format_date("F - d - Y", $sf["ModificationTimestamp"]);
    $logo="";
    if ($api_system_info['Configuration'][0]['IdxLogoSmall']){
      $logo = $api_system_info['Configuration'][0]['IdxLogoSmall'];
    }
    elseif ($api_system_info['Configuration'][0]['IdxLogo']){
        $logo = $api_system_info['Configuration'][0]['IdxLogo'];
    }
    else{
      $logo = "IDX";
    }

    $possibleRequired = array(
		"ListOfficeName"  => array("Listing Office",$sf["ListOfficeName"]),
		"ListOfficePhone"   => array("Office Phone",$ListOfficePhone),
		"ListOfficeEmail"   => array("Office Email",$sf["ListOfficeEmail"]),
		"ListOfficeURL"   => array("Office Website",$sf["ListOfficeURL"]),
		"ListOfficeAddress"   => array("Office Address",$OfficeAddress),
		"ListAgentName"   => array("Listing Agent",$AgentName),//Agent name is done below to make sure first and last name are present
		"ListMemberPhone"   => array("Agent Phone",$sf["ListAgentPreferredPhone"] ),
		"ListMemberEmail"   => array("Agent Email",$sf["ListAgentEmail"]),
		"ListMemberURL"   => array("Agent Website",$sf["ListAgentURL"]),
		"ListMemberAddress"   => array("Agent Address",$AgentAddress),
		"CoListOfficeName"  => array("Co Office Name",$sf["CoListOfficeName"]),
		"CoListOfficePhone" => array("Co Office Phone",$sf["CoListOfficePhone"]),
		"CoListOfficeEmail" => array("Co Office Email",$sf["CoListOfficeEmail"]),
		"CoListOfficeURL" => array("Co Office Website",$sf["CoListOfficeURL"]),
		"CoListOfficeAddress" => array("Co Office Address","$CoAgentAddress"),
		"CoListAgentName" => array("Co Listing Agent",$CoAgentName),
		"CoListAgentPhone"  => array("Co Agent Phone",$CoListAgentPhone),
		"CoListAgentEmail"  => array("Co Agent Email",$sf["CoListAgentEmail"]),
		"CoListAgentURL"  => array("Co Agent Webpage",$sf["CoListAgentURL"]),
		"CoListAgentAddress"  => array("Co Agent Address",$CoAgentAddress),
		"ListingUpdateTimestamp"=> array("Last Updated",$LastModifiedDate),
		"IDXLogo"               => array("LOGO",$logo),//Todo -- Print Logo?
    );
    //var_dump($logo);
    $values= array();
    /*foreach ($compList as $test){
        array_push($values,array($possibleRequired[$test][0],$possibleRequired[$test][1]));
    } */
    foreach ($possibleRequired as $key => $value){
      if (in_array($key, $compList))
        array_push($values, array($value[0], $value[1]));
    }
    return $values;
	}

}