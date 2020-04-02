<?php
/*
 * develper hasan ghasminia
 * emial ghasminia@gmail.com
 * phone 09116516722
 * shop http://armital.ir/
 * ips user http://forums.ipbfarsi.ir/profile/3217-%D8%AD%D8%B3%D9%86-%D9%82%D8%A7%D8%B3%D9%85%DB%8C-%D9%86%DB%8C%D8%A7/
 * donate & update linke http://armital.ir/index.php?route=product/product&product_id=65
 * version nexus 4.1.13.2
 */

namespace IPS\nexus\Gateway\zarinpal;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Exception extends \DomainException
{

	protected $name;
	protected $details = array();
	
	public function __construct( \IPS\Http\Response $response, $refund=FALSE )
	{
		\IPS\Log::debug( (string) $response, 'zarinpal' );
		
		$details = $response->decodeJson();
		$this->name = $details['name'];
		if ( isset( $details['details'] ) )
		{
			$this->details = $details['details'];
		}
		
		switch ( $this->name )
		{				
			case 'EXPIRED_CREDIT_CARD':
				$message = \IPS\Member::loggedIn()->language()->get( 'card_expire_expired' );
				break;
							
			case 'CREDIT_CARD_REFUSED':
				$message = \IPS\Member::loggedIn()->language()->get( 'card_refused' );
				break;
			
			case 'CREDIT_CARD_CVV_CHECK_FAILED':
				$message = \IPS\Member::loggedIn()->language()->get( 'ccv_invalid' );
				break;
				
			case 'REFUND_EXCEEDED_TRANSACTION_AMOUNT':
			case 'FULL_REFUND_NOT_ALLOWED_AFTER_PARTIAL_REFUND':
				$message = \IPS\Member::loggedIn()->language()->get( 'refund_amount_exceeds' );
				break;
				
			case 'REFUND_TIME_LIMIT_EXCEEDED':
				$message = \IPS\Member::loggedIn()->language()->get( 'refund_time_limit' );
				break;
				
			case 'TRANSACTION_ALREADY_REFUNDED':
				$message = \IPS\Member::loggedIn()->language()->get( 'refund_already_processed' );
				break;
			
			case 'ADDRESS_INVALID':
			case 'VALIDATION_ERROR':
				$message = \IPS\Member::loggedIn()->language()->get( 'address_invalid' );
				break;
			
			default:
				if ( $refund )
				{
					$message = \IPS\Member::loggedIn()->language()->get( 'refund_failed' );
				}
				else
				{
					$message = \IPS\Member::loggedIn()->language()->get( 'gateway_err' );
				}
				break;
		}
		
		return parent::__construct( $message, $response->httpResponseCode );
	}
	
	public function getName()
	{
		return $this->name;
	}
}