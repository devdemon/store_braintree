<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (defined('PATH_THIRD')) {
    require PATH_THIRD.'store/autoload.php';
}

$plugin_info = array(
    'pi_name'         => 'Store - Braintree',
    'pi_version'      => '1.0',
    'pi_author'       => 'DevDemon',
    'pi_author_url'   => 'http://www.devdemon.com/',
    'pi_description'  => 'Outputs Braintree Specific Tags',
    'pi_usage'        => Store_braintree::usage()
);

class Store_braintree
{

    public $return_data = '';

    // --------------------------------------------------------------------

    /**
     * Memberlist
     *
     * This function returns a list of members
     *
     * @access  public
     * @return  string
     */
    public function __construct()
    {

    }

    // --------------------------------------------------------------------

    public function token()
    {
        $gateway = ee()->store->payments->load_payment_method('Braintree');
        $clientToken = $gateway->clientToken()->send()->getToken();

        $this->return_data = $clientToken;
        return $clientToken;
    }

    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
        ob_start();  ?>



    {exp:store_braintree:token}



    <?php
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
    // END
}