<?php

/**
 * Simple Iovation Library
 */
class IOvation
{

    const DRA_ADMIN = "OLTP";

    /**
     * Check Transaction Details URL
     * @var string
     */
    protected $CTDurl;
    /**
     * Subscriber ID provided by IOvation
     * @var int
     */
    protected $Subscriber;

    /**
     * Password for the API provided by IOvation
     * @var string
     */
    protected $Password;

    public function __construct($options = array())
    {
        $this->CTDurl = $options['CTDurl'];
        $this->Subscriber = $options['Subscriber'];
        $this->Password = $options['Password'];
    }

    /**
     * Check Iovation Client
     *
     * @param  string $userName
     * @param  string $blackbox
     * @param  string $ip
     * @param  string $rules
     * @param  array $data
     * @return
     */
    public function getTransactionDetails($userName, $blackbox, $ip, $rules, $data)
    {

        $result = array();

        try {

            //create soap client
            $client = new SoapClient(null,
                array(
                    'connection_timeout' => 3,
                    'location' => $this->CTDurl,
                    'style' => SOAP_RPC,
                    'use' => SOAP_ENCODED,
                    'uri' => $this->CTDurl . "#CheckTransactionDetails"
                )
            );

            // create list of transaction properties we want to send. It is important to do a SoapVar around each property as you add
            // it to the array to ensure you don't get extra tags added to the XML. You must also convert the arrays to objects otherwise
            // the indexing wrappers (key and val) will be added to the XML as well)
            // also note that SoapParam will want to add extra structure so we need to use SoapVar to add to the array
            $transactionProperties = (object)array(
                new SoapVar((object)array('name' => 'onlineId', 'value' => $data['onlineId']), SOAP_ENC_OBJECT, null, null, 'property'),
                new SoapVar((object)array('name' => 'Email', 'value' => Session::get('email')), SOAP_ENC_OBJECT, null, null, 'property'),
                new SoapVar((object)array('name' => 'BillingStreet', 'value' => $data['BillingStreet']), SOAP_ENC_OBJECT, null, null, 'property'),
                new SoapVar((object)array('name' => 'BillingCity', 'value' => $data['BillingCity']), SOAP_ENC_OBJECT, null, null, 'property'),
                new SoapVar((object)array('name' => 'BillingPostalCode', 'value' => $data['BillingPostalCode']), SOAP_ENC_OBJECT, null, null, 'property')
            );

            //Snare Vars
            $result = $client->CheckTransactionDetails(
                new SoapParam($userName, 'accountcode'),
                new SoapParam($ip, 'enduserip'),
                new SoapParam($blackbox, 'beginblackbox'),
                new SoapParam($this->Subscriber, 'subscriberid'),
                new SoapParam(self::DRA_ADMIN, 'subscriberaccount'),
                new SoapParam($this->Password, 'subscriberpasscode'),
                new SoapParam($rules, 'type'),
                new SoapParam($transactionProperties, 'txn_properties')
            );

        } catch (SoapFault $e) {
            return $e->getMesssage();
        }

        //unset the current client
        unset($client);

        return $result;
    }
}

#-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-#
#         	   SAMPLE USAGE  	         	  #
#-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-#

$iovation = new Iovation(
    array(
        'CTDurl' => 'https://soap.iovation.com/api/CheckTransactionDetails',
        'Subscriber' => 'YourSubscriberCode',
        'Password' => 'YourIovationPassword'
    )
);

$username = 'YourUserName';
$blackbox = ''; //combination of Iovation BB and FB created by Iovation Script
$ip = ''; //customer ip address
$rules = ''; //iovation rules

//customer data
$customer = array(
    'onlineId' => '',
    'Email' => '',
    'BillingStreet' => '',
    'BillingCity' => '',
    'BillingPostalCode' => '',
);

$result = $iovation->getTransactionDetails(
    $username,
    $blackbox,
    $ip,
    $rules,
    $customer
);

print_r($result);

?>
