<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*

	USPS Domestic.
	
	For use in the USA 
	
*/

class jne_indonesia
{
	var $CI;
	
    var $service_list;
    
	function jne_indonesia()
	{
		//we're going to have this information in the back end for editing eventually
		//username password, origin zip code etc.
		$this->CI =& get_instance();
		$this->CI->load->model('Settings_model');
		$this->CI->lang->load('jne_indonesia');

	}
	function form($post	= false)
	{
		$this->CI->load->helper('form');
		
		//this same function processes the form
		if(!$post)
		{
			$settings	= $this->CI->Settings_model->get_settings('jne_indonesia');
			
		}

		ob_start();
		?>

		<label><?php echo lang('enabled');?></label>
		<select name="enabled" class="span3">
			<option value="1"<?php echo((bool)$settings['enabled'])?' selected="selected"':'';?>><?php echo lang('enabled');?></option>
			<option value="0"<?php echo((bool)$settings['enabled'])?'':' selected="selected"';?>><?php echo lang('disabled');?></option>
		</select>
		<?php
		$form =ob_get_contents();
		ob_end_clean();

		return $form;
	}
	function rates()
	{

		$this->CI->load->library('session');
        $this->CI->load->library('Jne_lib');

		// get customer info
		$customer = $this->CI->go_cart->customer();
		$dest_zip 	= $customer['ship_address']['zip'];
        	$dest_city  = $customer['ship_address']['city'];
		$dest_country = $customer['ship_address']['country'];


		//grab this information from the config file
		$country	= $this->CI->config->item('country');
		$orig_zip	= $this->CI->config->item('zip');

		// retrieve settings
		$settings	= $this->CI->Settings_model->get_settings('jne_indonesia');

		//check if we're enabled
		//if(!$settings['enabled'] || $settings['enabled'] < 1)
		//{
		//	return array();
		//}

		$user	 		= $settings['username'];
		$pass 			= $settings['password'];
		// $service		= explode(',',$settings['service']);
		$service		= explode(',','service1, service2');
		$container 		= $settings['container'];
		$size 			= 'Regular';//$settings['size'];
		$machinable 	= $settings['machinable'];
		$handling_method = $settings['handling_method'];
		$handling_amount = $settings['handling_amount'];

		// build allowed service list
		foreach($service as $s)
		{
			$service_list[] = $this->service_list[$s];
		}

		//set the weight
		$weight	= $this->CI->go_cart->order_weight();

		// value of contents
		$total = $this->CI->go_cart->order_insurable_value();

		//strip the decimal
		$oz		= ($weight-(floor($weight)))*100;
		//set pounds
		$lbs	= floor($weight);
		//set ounces based on decimal
		$oz	= round(($oz*16)/100);

        $result = $this->CI->jne_lib->cost_to($dest_city);

        try
        {
            $status = $result['status'];

            // Handling the data
            if (0 == $status->code)
            {
                $prices = $result['price'];
                $city	= $result['city'];
                
                foreach ($prices->item as $item)
                {
                    $rates['service'][] = (string) $item->service;
                    $rates['value'][] = (int) $item->value;
                }	

                //we have no choice, use regular service instead
                //$rates = array(
                //    'Super Speed (SS)' => $rates['value'][0],
                //    'Yakin Esok Sampai (YES)' => $rates['value'][1],
                //    'Regular (REG)' => $rates['value'][2],
                //    'Ongkos Kirim Ekonomis (OKE)' => $rates['value'][3]);

                //return only regular service
                $rates = array(
                    'Regular (Reg)' => $rates['value'][2]
                );

                return $rates;
            }
            else
            {
                $rates = array(
                    'City Not Found - Please check shipping address' => 'XXX'
                );

                return $rates;
            }
            
        }
        catch (Exception $e)
        {
            echo 'Processing error.';
        }

		return false;
	}
	
	function install()
	{
		$default_settings	= array(
			'mode'=>'test',
			'username'=>'',
			'password'=>'',
			'container'=>'Flat Rate Box',
			'size'=>'LARGE',
			'length'=>'',
			'width'=>'',
			'height'=>'',
			'girth'=>'',
			'machinable'=>'true',
			'handling_method'=>'$',
			'handling_amount'=>5,
			'enabled'=>'0'
		);
		//set a default blank setting for flatrate shipping
		$this->CI->Settings_model->save_settings('jne_indonesia', $default_settings);
	}
	
	function uninstall()
	{
		$this->CI->Settings_model->delete_settings('jne_indonesia');
	}

	function check()
	{	
		//we save the settings if it gets here
		$this->CI->Settings_model->save_settings('jne_indonesia', $save);
		
		return false;
	}
}