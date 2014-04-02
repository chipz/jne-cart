<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jne_lib 
{
	var $customer;
    var $apikey = 'INPUTYOURAPIKEYHERE';

    function __construct()
    {
        $this->CI =& get_instance();
		$this->CI->load->library('session');
        $this->CI->load->library('REST_Ongkir');
		$this->cart = $this->CI->session->userdata('cart');
    }

    public function cost_to($city)
    {
        $rest = new REST_Ongkir(array(
            'server' => 'http://api.ongkir.info/'
        ));

        $result = $rest->post('cost/find', array(
            'from'      => 'jakarta',
            'to'        => $city,
            'weight'    => 1,
            'courier'   => 'jne',
            'API-Key'   => $this->apikey
        ));

        try
        {
            return $result;
        }
        catch (Exception $e)
        {
            return false;
        }

    }


}
