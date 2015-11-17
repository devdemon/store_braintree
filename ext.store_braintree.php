<?php

if (defined('PATH_THIRD')) {
    require PATH_THIRD.'store/autoload.php';
}

use Store\Action\PaymentReturnAction;

class Store_braintree_ext
{
    public $name = 'Store Braintree Payment Gateway';
    public $version = '1.0.0';
    public $description = 'A custom payment gateway for Expresso Store 2.1+.';
    public $settings_exist = 'n';
    public $docs_url = 'https://www.devdemon.com';
    public $hooks    = array('store_payment_gateways');
    public $settings = array();

    /**
     * This hook is called when Store is searching for available payment gateways
     * We will use it to tell Store about our custom gateway
     */
    public function store_payment_gateways($gateways)
    {
        //ee()->lang->loadfile('store_gestpay');

        if (ee()->extensions->last_call !== false) {
            $gateways = ee()->extensions->last_call;
        }

        // tell Store about our new payment gateway
        // (this must match the name of your gateway in the Omnipay directory)
        $gateways[] = 'Braintree';

        // tell PHP where to find the gateway classes
        // Store will automatically include your files when they are needed
        $composer = require(PATH_THIRD.'store/autoload.php');
        $composer->add('Braintree', __DIR__ .'/braintree_php/lib');
        $composer->add('Omnipay', __DIR__);

        return $gateways;
    }

    /**
     * Called by ExpressionEngine when the user activates the extension.
     *
     * @access      public
     * @return      void
     **/
    public function activate_extension()
    {
        foreach ($this->hooks as $hook) {
             $data = array( 'class'     =>  __CLASS__,
                            'method'    =>  $hook,
                            'hook'      =>  $hook,
                            'settings'  =>  serialize($this->settings),
                            'priority'  =>  10,
                            'version'   =>  $this->version,
                            'enabled'   =>  'y'
                );

            // insert in database
            ee()->db->insert('exp_extensions', $data);
        }
    }

    /**
     * Called by ExpressionEngine updates the extension
     *
     * @access public
     * @return void
     **/
    public function update_extension($current = '')
    {
        if ($current == $this->version) return false;

        $settings = array();

        //----------------------------------------
        // Get all existing hooks
        //----------------------------------------
        $dbexts = array();
        $query = ee()->db->select('*')->from('exp_extensions')->where('class', __CLASS__)->get();

        foreach ($query->result() as $row) {
            $dbexts[$row->hook] = $row;
            if ($row->settings) $settings = unserialize($row->settings);
        }

        //----------------------------------------
        // Add new hooks
        //----------------------------------------
        foreach ($this->hooks as $hook) {
            if (isset($dbexts[$hook]) === true) continue;

            $data = array(
                'class'     =>  __CLASS__,
                'method'    =>  $hook,
                'hook'      =>  $hook,
                'settings'  =>  serialize($settings),
                'priority'  =>  100,
                'version'   =>  $this->version,
                'enabled'   =>  'y'
            );

            // insert in database
            ee()->db->insert('exp_extensions', $data);
        }

        //----------------------------------------
        // Delete old hooks
        //----------------------------------------
        foreach ($dbexts as $hook => $ext) {
            if (in_array($hook, $this->hooks) === true) continue;

            ee()->db->where('hook', $hook);
            ee()->db->where('class', __CLASS__);
            ee()->db->delete('exp_extensions');
        }

        // Update the version number for all remaining hooks
        ee()->db->where('class', __CLASS__)->update('extensions', array('version' => $this->version));
    }

    /**
     * Called by ExpressionEngine when the user disables the extension.
     *
     * @access      public
     * @return      void
     **/
    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('exp_extensions');
    }
}