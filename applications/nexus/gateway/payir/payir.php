<?php


namespace IPS\nexus\Gateway;


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}


class _payir extends \IPS\nexus\Gateway
{

    public function checkValidity( \IPS\nexus\Money $amount, \IPS\GeoLocation $billingAddress = NULL, \IPS\nexus\Customer $customer = NULL, $recurrings = array() )
	{
		if ($amount->currency != 'IRR')
		{
			return FALSE;
		}
				
		return parent::checkValidity( $amount, $billingAddress, $customer, $recurrings );
	}
		

	public function auth( \IPS\nexus\Transaction $transaction, $values, \IPS\nexus\Fraud\MaxMind\Request $maxMind = NULL, $recurrings = array() )
	{
		$transaction->save();
		$redirect = (string) \IPS\Settings::i()->base_url . 'applications/nexus/interface/gateways/payir.php?nexusTransactionId=' . $transaction->id ;
		$res = $this->send($transaction->amount->amountAsString(),$redirect,$transaction->id);
        $res = json_decode($res);
        if($res->status) {
    	    $go = "https://pay.ir/payment/gateway/$res->transId";
    	    \IPS\Output::i()->redirect( $go );
        } 
	
		throw new \RuntimeException;
	}
	public function capture( \IPS\nexus\Transaction $transaction ) {
	}
	public function settings( &$form )
	{
		$settings = json_decode( $this->settings, TRUE );
		$form->add( new \IPS\Helpers\Form\Text( 'payir_client_id', $this->id ?$settings['client_id']:'', TRUE ) );
	}
	public function testSettings( $settings )
	{		
		return $settings;
	}

	public function send($amount, $redirect, $factorNumber=null) {
	    $settings = json_decode( $this->settings, TRUE );
		$api = $settings['client_id'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://pay.ir/payment/send');
		curl_setopt($ch, CURLOPT_POSTFIELDS,"api=$api&amount=$amount&redirect=$redirect&factorNumber=$factorNumber");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	public function verifypayir($transId) {
	    $settings = json_decode( $this->settings, TRUE );
		$api = $settings['client_id'];
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, 'https://pay.ir/payment/verify');
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&transId=$transId");
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;
    }
    public function test() {
	    $settings = json_decode( $this->settings, TRUE );
		$api = $settings['client_id'];
    	return $api;
    }
}
