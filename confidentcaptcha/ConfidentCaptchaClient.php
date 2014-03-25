<?php

/**
 * Confident CAPTCHA API Client
 * This is the entry point to call CAPTCHA API. The front end UI (JSP pages) should call
 * this Client to interact with CAPTCHA server.
 */
class ConfidentCaptchaClient
{
    /**
     * @var                             ConfidentCaptchaCredentials class instance
     */
    private $credentials;

    /**
     * @var                             ConfidentCaptchaProperties class instance
     */
    private $captchaProperties;

    /**
     * @var                             CAPTCHA Server URL
     */
    private $apiServerUrl = "http://captcha.confidenttechnologies.com";

    /**
     * @var                             CAPTCHA API Version
     */
    private $libraryVersion = "20130514_WORDPRESS_2.5";

    /**
     * @var                             ConfidentCaptchaResponse error object
     */
    private $responseError;

    /**
     * @var                             ConfidentCaptchaSession class instance
     */
    private $confidentCaptchaSession;

    /**
     * Public Constructor
     * usually you will want to set the SettingsXml, ApiServerUrl and LibraryVersion, however you can also set these
     * after the object is instanciated
     *
     * @param $settingsXmlFilePath              Configuration XML file containing credentials and properties
     *
     * @return ConfidentCaptchaClient
     */
    public function __construct( $settingsXmlFilePath=null)
    {

        if($settingsXmlFilePath != null){
            $this->credentials = ConfidentCaptchaCredentials::confidentCaptchaCredentialsFromXml($settingsXmlFilePath);
            $this->captchaProperties = new ConfidentCaptchaProperties($settingsXmlFilePath);
        }
        else{
            $this->captchaProperties = new ConfidentCaptchaProperties();
        }


        // if library version or api server url were set in the properties file, 
        // we should use them
        $settingsApiServerUrl = $this->captchaProperties->getApiServerUrl();
        if(isset($settingsApiServerUrl)){
            $this->apiServerUrl = $this->captchaProperties->getApiServerUrl();
        }

        $settingsLibraryVersion = $this->captchaProperties->getLibraryVersion();
        if(isset($settingsLibraryVersion)){
            $this->libraryVersion  = $this->captchaProperties->getLibraryVersion();
        }


        if($this->captchaProperties->getAjaxVerify() == true){
            $this->confidentCaptchaSession = new ConfidentCaptchaSession();
        }
         
      }



    /**
     * Getter for Credentials
     *
     * @return ConfidentCaptchaCredentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Setter for Credentials
     *
     * @param $confidentCaptchaCredentials
     */
    public function setCredentials($confidentCaptchaCredentials)
    {
        $this->credentials = $confidentCaptchaCredentials;
    }

    /**
     * Getter for Properties
     *
     * @return ConfidentCaptchaProperties
     */
    public function getCaptchaProperties()
    {
        return $this->captchaProperties;
    }

    /**
     * Setter for Properties
     *
     * @param $confidentCaptchaProperties
     */
    public function setCaptchaProperties($confidentCaptchaProperties)
    {
        $this->captchaProperties = $confidentCaptchaProperties;
    }

    /**
     * Getter for CAPTCHA API Server URL
     *
     * @return
     */
    public function getApiServerUrl()
    {
        return $this->apiServerUrl;
    }

    /**
     * Setter for CAPTCHA API Server URL
     *
     * @param $apiServerUrl
     */
    public function setApiServerUrl($apiServerUrl)
    {
        $this->apiServerUrl = $apiServerUrl;
    }

    /**
     * Getter for CAPTCHA API library version
     *
     * @return
     */
    public function getLibraryVersion()
    {
        return $this->libraryVersion;
    }

    /**
     * Setter for CAPTCHA API library version
     *
     * @param $libraryVersion
     */
    public function setLibraryVersion($libraryVersion)
    {
        $this->libraryVersion = $libraryVersion;
    }

    /**
     * Check the environment to see if it meets with API requirements
     *
     * @return mixed
     */
    public function checkClientSetup()
    {
        $redNo = "<span style=\"color:red\">No</span>";
        // Local checks
        $localClientCheckpointsHolder = array(array("Item", "Value", "Required Value", "Acceptable?"));

        // Check PHP version 5.x
        $runningPhpVersion = phpversion();
        $minimumSupportedPhpVersion = "5.0.0";

        if (version_compare($runningPhpVersion, $minimumSupportedPhpVersion, '>=')) {
            $phpVersionSupported = 'Yes';
        }
        else {
            $phpVersionSupported = $redNo;
        }

        $localClientCheckpointsHolder[] = array('PHP version', $runningPhpVersion, $minimumSupportedPhpVersion, $phpVersionSupported);

        // Check cURL extension
        if (extension_loaded('curl')) {
            $curlVersion = phpversion('cURL');
            if (empty($curlVersion)){
                $curlVersion = '(installed)';
            }
            $curlSupported = 'Yes';
        }
        else {
            $curlVersion = "(not installed)";
            $curlSupported = $redNo;
        }

        $localClientCheckpointsHolder[] = array('cURL extension', $curlVersion, '(installed)', $curlSupported);

        // Check SimpleXML extension
        if (extension_loaded('SimpleXML')) {
            $simpleXmlVersion = phpversion('SimpleXML');
            if (empty($simpleXmlVersion)){
                $simpleXmlVersion = '(installed)';
            }
            $simpleXmlSupported = 'Yes';
        }
        else {
            $simpleXmlVersion = "(not installed)";
            $simpleXmlSupported = $redNo;
        }
        $localClientCheckpointsHolder[] = array('SimpleXML extension', $simpleXmlVersion, '(installed)', $simpleXmlSupported);

        // Check CAPTCHA API server URL
        $notSet = '(NOT SET)';

        $apiServerUrl = $this->apiServerUrl;

        $expectedApiServerUrl = 'http://captcha.confidenttechnologies.com/';
        $sslExpectedApiServerUrl = 'https://captcha.confidenttechnologies.com/';
        $dispExpectedApiServerUrl  = 'http(s)://captcha.confidenttechnologies.com/';

        if ($apiServerUrl == $expectedApiServerUrl or $apiServerUrl == $sslExpectedApiServerUrl) {
            $ApiServerUrlSupported = 'Yes';
        }
        elseif (empty($apiServerUrl)) {
            $apiServerUrl = $notSet;
            $ApiServerUrlSupported = $redNo;
        }
        elseif (0 == substr_compare($apiServerUrl, 'http', 0, 4)) {
            $ApiServerUrlSupported = 'Maybe';
        }
        else {
            $ApiServerUrlSupported = $redNo;
        }

        $localClientCheckpointsHolder[] = array('api_server_url', $apiServerUrl, $dispExpectedApiServerUrl, $ApiServerUrlSupported);

        $credentials = $this->credentials;

        // Check API parameters
        $apiParameterSet = array('customer_id', 'site_id', 'api_username', 'api_password');

        foreach ($apiParameterSet as $apiParameterName) {
            switch ($apiParameterName)
            {
                case 'customer_id':
                    $parameterValue = $credentials->getCustomerId();
                    break;
                case 'site_id':
                    $parameterValue = $credentials->getSiteId();
                    break;
                case 'api_username':
                    $parameterValue = $credentials->getApiUsername();
                    break;
                case 'api_password':
                    $parameterValue = $credentials->getApiPassword();
                    break;
            }

            if (empty($parameterValue)) {
                $parameterValue = $notSet;
                $parameterValueOk = $redNo;
                $apiParameterName = $apiParameterName . "<span style=\"color:red\"> (get this value from your site's overview page <a href=\"http://login.confidenttechnologies.com\" target=\"_blank\">here</a>)</span>";
            }
            else {
                $parameterValueOk = 'Yes';
            }

            $localClientCheckpointsHolder[] = array($apiParameterName, $parameterValue, '(some value)', $parameterValueOk);
        }

        //Check Library Version
        $libraryVersionSupported = $redNo;
        $localLibraryVersion = $this->libraryVersion;
        if(isset($localLibraryVersion) && !empty($localLibraryVersion)){
            $libraryVersionSupported = 'Yes';
        }
        else{
            $localLibraryVersion = $notSet;
        }
        $localClientCheckpointsHolder[] = array("library_version", $localLibraryVersion, 'example: 20120625_PHP_2.3.2', $libraryVersionSupported);


        $properties = $this->captchaProperties->getProperties();

        // Check CAPTCHA options
        $currentKnownProperties = array(
            'display_style'=> '(default: lightbox)',
            'height'=> '(default: 3)',
            'width'=> '(default: 3)',
            'captcha_length'=> '(default: 4)',
            'image_code_color'=> '(default: White)',
            'failure_policy_math'=> '(default: math)',
            'show_letters'=> '(default: TRUE)',
            'ajax_verify'=> '(default: FALSE)',
            'max_tries'=> '(default: 3)',
            'callback_url'=> '(default: empty)',
            'billboard'=> '(default: empty)',
            'logo'=> '(default: empty)',
            'audio_noise_level' => '(default: 0.2)'
        );

        foreach ($currentKnownProperties as $propertyName => $expected) {
            if (isset($properties[$propertyName]) && !empty($properties[$propertyName])) {
                $property = $properties[$propertyName];
                $propertyOk = $redNo;
                switch($propertyName){
                    case "display_style":
                        if($property == "lightbox" || $property == "flyout" || $property == "modal"){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this is not a recognized display style)</span>";
                        }
                        break;
                    case "height":
                        $width = $properties['width'];
                        $length = $properties['captcha_length'];
                        if(is_numeric($property) && intval($property) > 0){
                            $propertyOk = 'Yes';
                            if(is_numeric($width) && is_numeric($length) && intval($property) * intval($width) < intval($length)){
                                $propertyOk = $redNo;
                                $propertyName = $propertyName . "<span style=\"color:red\"> (height x width must be greater than or equal to captcha length)</span>";
                            }
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this must be a numeric value greater than zero)</span>";
                        }
                        break;
                    case "width":
                        $height = $properties['height'];
                        $length = $properties['captcha_length'];
                        if(is_numeric($property) && intval($property) > 0){
                            $propertyOk = 'Yes';
                            if(is_numeric($height) && is_numeric($length) && intval($property) * intval($height) < intval($length)){
                                $propertyOk = $redNo;
                                $propertyName = $propertyName . "<span style=\"color:red\"> (height x width must be greater than or equal to captcha length)</span";
                            }
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this must be a numeric value greater than zero)</span>";
                        }
                        break;
                    case "captcha_length":
                        if(is_numeric($property) && intval($property) > 1){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (captcha length must be greater than 1)</span>";
                        }
                        break;
                    case "image_code_color":
                        switch($property){
                            case "White":
                                $propertyOk = 'Yes';
                                break;
                            case "Red":
                                $propertyOk = 'Yes';
                                break;
                            case "Orange":
                                $propertyOk = 'Yes';
                                break;
                            case "Yellow":
                                $propertyOk = 'Yes';
                                break;
                            case "Green":
                                $propertyOk = 'Yes';
                                break;
                            case "Teal":
                                $propertyOk = 'Yes';
                                break;
                            case "Blue":
                                $propertyOk = 'Yes';
                                break;
                            case "Indigo":
                                $propertyOk = 'Yes';
                                break;
                            case "Violet":
                                $propertyOk = 'Yes';
                                break;
                            case "Gray":
                                $propertyOk = 'Yes';
                                break;
                            default:
                                $propertyName = $propertyName . "<span style=\"color:red\"> (the color you entered is not supported)</span>";
                                break;
                        }
                        break;
                    case "failure_policy_math":
                        if($property == "math" || $property == "open" || $property == "closed"){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (the failure policy you entered is not recognized)</span>";
                        }
                        break;
                    case "ajax_verify":
                        if($property == 'FALSE' || $property == 'TRUE'){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this value must be either TRUE or FALSE)</span>";
                        }
                        break;
                    case "max_tries":
                        if(is_numeric($property) && intval($property) <= 5 && intval($property) > 0){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this must be a numeric value greater than zero and less than 5)</span>";
                        }
                        break;
                    case "callback_url":
                        if(strpos($property, "http") !== false){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this needs to be a url)</span>";
                        }
                        break;
                    case "audio_noise_level":
                        if(is_numeric($property) && bccomp($property, "0.0", 1) > 0 && bccomp($property, "1.0", 1) < 0){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this must be a decimal number greater than 0 and less than 1)</span>";
                        }
                        break;
                    case "show_letters":
                        if($property == 'FALSE' || $property == 'TRUE'){
                            $propertyOk = 'Yes';
                        }
                        else{
                            $propertyName = $propertyName . "<span style=\"color:red\"> (this value must be either TRUE or FALSE)</span>";
                        }
                        break;
                }
            }

            else {
                $property = $notSet;
                $propertyOk = 'Yes';
                switch($propertyName){
                    case "callback_url":
                        if($properties['ajax_verify'] == 'TRUE'){
                            $propertyOk = $redNo;
                            $propertyName = $propertyName . "<span style=\"color:red\"> (You need to specify a callback url if you plan to use AJAX verification)</span>";
                        }
                }

            }
            $localClientCheckpointsHolder[] = array($propertyName, $property, $expected, $propertyOk);
        }

        # Make local tables
        $localClientCheckpointsReturnHtml = "<h1>Local Configuration</h1>\n";
        $localClientCheckpointsReturnHtml.= "<table border=\"1\">\n<tr><th>";

        $headRow = array_shift($localClientCheckpointsHolder);
        $localClientCheckpointsReturnHtml.= implode('</th><th>', $headRow) . "</th></tr>\n";
        $localOk = TRUE;

        foreach($localClientCheckpointsHolder as $checkpoint) {
            $localClientCheckpointsReturnHtml .= '<tr><td>'.implode('</td><td>', $checkpoint)."</td></tr>\n";
            if (end($checkpoint) == 'No') $localOk = FALSE;
        }

        $localClientCheckpointsReturnHtml .= '</table><br/>';

        $localClientCheckpointsReturnHtml .= "\n<h1>Remote Configuration</h1>\n";

        // Check credentials with API server
        $checkCredentialsResponse = $this->checkCredentials();

        if ($checkCredentialsResponse->getStatus() == 200) {
            $checkCredentialsResponseHtml = $checkCredentialsResponse->getBody();
            $goodCredentials = (false === strstr($checkCredentialsResponseHtml, "api_failed='True'"));
        } else {
            $checkCredentialsResponseHtml  = "check_credentials call failed with status code: ";
            $checkCredentialsResponseHtml .= $checkCredentialsResponse->getStatus().'.<br />';
            $checkCredentialsResponseHtml .= 'response body: <br />'.$checkCredentialsResponse->getBody();
            $goodCredentials = false;
        }

        $checkClientSetupResponse = new CheckClientSetupResponse($checkCredentialsResponse->getStatus(),
                $localClientCheckpointsReturnHtml . $checkCredentialsResponseHtml,
                $localOk and $goodCredentials);

        return $checkClientSetupResponse;
    }

    /**
     * Check if valid for the Credentials in settings.xml
     *
     * @return CheckCredentialsResponse
     */
    public function checkCredentials()
    {
        $endPointUrl = $this->apiServerUrl . '/check_credentials';
        $httpMethod = 'GET';

        $apiResponse = $this->makeRequest($endPointUrl, $httpMethod);

        $checkCredentialsResponse = new CheckCredentialsResponse($apiResponse->getStatus(),$apiResponse->getBody(), $apiResponse->getMethod(), $apiResponse->getUrl(), $apiResponse->getForm());
        return  $checkCredentialsResponse;
    }




    //Request a block ID if user has chosen to use AJAX verification
    public function createBlock(){
        $properties = $this->captchaProperties->getProperties();
        if(!isset($this->confidentCaptchaSession)){
            $this->confidentCaptchaSession = new ConfidentCaptchaSession();
        }
        if ( isset($properties['callback_url']) && !empty($properties['callback_url'])) {
            $this->confidentCaptchaSession->reset();

            $endPointUrl = $this->apiServerUrl . '/block';

            $httpMethod = 'POST';

            $apiResponse = $this->makeRequest($endPointUrl,  $httpMethod);

            $createBlockResponse = new CreateBlockResponse($apiResponse->getStatus(),$apiResponse->getBody(), $apiResponse->getMethod(), $apiResponse->getUrl(), $apiResponse->getForm());

            $this->confidentCaptchaSession->save($createBlockResponse->getBlockId());
        }
        else{
            // create a response object and make it give you a error
            $createBlockResponse = new CreateBlockResponse(500,"You Need to specify a callback URL in your settings");
        }
        return $createBlockResponse;
    }







    /**
     * Crate a visual CAPTCHA
     * The number of images is defined in settings.xml by width times height
     * The length of CAPTCHA (or solution) is also defined in settings.xml (captcha_length)
     *
     * @return CreateCaptchaResponse
     */
    public function requestCaptcha($blockId = null)
    {
            $endPointUrl = $this->apiServerUrl . '/captcha';

            if($blockId != null){
                $endPointUrl = $this->apiServerUrl . '/block/' . $blockId . '/visual';
            }
            $httpMethod = 'POST';

            $properties = $this->captchaProperties->getProperties();

            $apiResponse = $this->makeRequest($endPointUrl,  $httpMethod);
            if ($apiResponse->getStatus() != 200) {
                //The server is down, so check the failure policy and react accordingly
                $failure_policy = $this->captchaProperties->getProperty('failure_policy_math');

                if($failure_policy == "math"){
                    $apiResponse->setBody( $this->createArithmeticCaptcha());
                }
                else{
                    $apiResponse->setBody( $this->createDummyCaptcha());
                }
            }






        return $apiResponse;
    }


    /**
     * Create a visual CAPTCHA and parse out and return
     * only the HTML Body of the returned response object
     *
     * @return String of HTML Body to represent a CAPTCHA
     */
    public function createCaptcha($blockId=null)
    {
        $requestCaptchaResponse = $this->requestCaptcha($blockId);

        if ($requestCaptchaResponse != null)
        {
            return $requestCaptchaResponse->getBody();
        }
    }


    /**
     * Validate a created CAPTCHA
     * You may parse out the CAPTCHA ID, CODE and Click Coordinates
     * from REQUEST object instead of passing them in.
     * This function is designed in a way that it can be used to
     * validate CAPTCHA when you have the CAPTCHA ID, CODE and
     * Click Coordinates in some other routes.
     *
     * @param $request                      HTTP Request
     * @return CheckCaptchaResponse
     */
    public function checkCaptcha($request, $captchaId=null, $code=null, $clickCoordinates=null, $blockId=null)
    {

        if(!isset($captchaId) || empty($captchaId)){
            if(empty($request['confidentcaptcha_captcha_id']) == false){
                $captchaId=$request['confidentcaptcha_captcha_id'];
            }
        }

        // was this a math captcha?
        if (!isset($captchaId) || empty($captchaId)){
            return $this->_checkMathCaptcha($request);
        }

        if((!isset($blockId) || empty($blockId))){
            if(empty($request['confidentcaptcha_block_id']) == false){
                $blockId = $request['confidentcaptcha_block_id'];
            }
        }
        // was this an ajax capcha?
        if((!isset($blockId) || empty($blockId)) == false){
            return $this->checkBlockVisual($request, $blockId,  $captchaId, $code, $clickCoordinates);
        }


        // not math or ajax, continue with plain version.

        if((!isset($code) || empty($code))){
           if(empty($request['confidentcaptcha_code']) == false){
              $code=$request['confidentcaptcha_code'];
           }
        }

        if((!isset($clickCoordinates) || empty($clickCoordinates))){
           if(empty($request['confidentcaptcha_click_coordinates']) == false){
              $clickCoordinates=$request['confidentcaptcha_click_coordinates'];
           }
        }

        $endPointUrl = $this->apiServerUrl . "/captcha/" . $captchaId;

        $httpParameters = $this->captchaProperties->getProperties();
        $httpParameters['captcha_id']           = $captchaId;
        $httpParameters['code']                 = $code;
        $httpParameters['click_coordinates']    = $clickCoordinates;

        $httpMethod = 'POST';

        $apiResponse = $this->makeRequest($endPointUrl, $httpMethod, $httpParameters);


        return new CheckCaptchaResponse($apiResponse->getStatus(),$apiResponse->getBody(), $apiResponse->getMethod(), $apiResponse->getUrl(), $apiResponse->getForm());
    }



    // this is the client to: /block/{block_id}/visual/{visual_id}
    public function checkBlockVisual($request, $blockId=null,  $captchaId=null, $code=null, $clickCoordinates=null){

        if(is_null($blockId)){
            if(empty($request['confidentcaptcha_block_id']) == false){
                $blockId = $request['confidentcaptcha_block_id'];
            }
        }
        if(!isset($this->confidentCaptchaSession)){
            $this->confidentCaptchaSession = new ConfidentCaptchaSession();
        }

        // check if this captcha has been solved on the server already.
        if($this->confidentCaptchaSession->check($blockId) == true){
            // yes it has been solved. // generate our response object
            $checkCaptchaResponse = new CheckCaptchaResponse(200,'{"answer": true, "server_auth_token": ""}');
            // we only let them pass a solved captcha once, so reset the block in the session.
            $this->confidentCaptchaSession->reset();

            return $checkCaptchaResponse;

        }
        else{

            // we need to check on the server, grab the needed params
            if(is_null($captchaId)){
                if(empty($request['confidentcaptcha_captcha_id']) == false){
                    $captchaId=$request['confidentcaptcha_captcha_id'];
                }
            }

            // was this a math captcha?
            if (empty($request['confidentcaptcha_captcha_id']) && empty($request['confidentcaptcha_code'])){
                return $this->_checkMathCaptcha($request);
            }

            if(is_null($code)){
                if(empty($request['confidentcaptcha_code']) == false){
                    $code=$request['confidentcaptcha_code'];
                }
            }

            if(is_null($clickCoordinates)){
                if(empty($request['confidentcaptcha_click_coordinates']) == false){
                    $clickCoordinates=$request['confidentcaptcha_click_coordinates'];
                }
            }

            $endPointUrl = $this->apiServerUrl . "/block/" . $blockId . "/visual/" . $captchaId;

            $httpParameters['captcha_id']           = $captchaId;
            $httpParameters['code']                 = $code;
            $httpParameters['click_coordinates']    = $clickCoordinates;

            $httpMethod = 'POST';

            $apiResponse = $this->makeRequest($endPointUrl, $httpMethod, $httpParameters);

            $checkCaptchaResponse = new CheckCaptchaResponse($apiResponse->getStatus(),$apiResponse->getBody(), $apiResponse->getMethod(), $apiResponse->getUrl(), $apiResponse->getForm());

            $this->confidentCaptchaSession->save($blockId, $checkCaptchaResponse->wasCaptchaSolved());

            return $checkCaptchaResponse;
        }

    }

    private function _checkMathCaptcha($request){

        // Don't bother to validate against server
        // Use arithmetic captcha instead
        $status = 404;

        //Check the failure policy to determine what to do when the server is unresponsive
        $failure_policy = $this->captchaProperties->getProperty('failure_policy_math');

        if($failure_policy == "math"){
            $body = $this->validateArithmeticCaptcha($request);
        }
        elseif($failure_policy == "open"){
            $body = true;
        }
        else{
            $body = false;
        }

        if ($body == true)
        {
            $status = 200;
            $body = '{"answer": true, "server_auth_token": ""}';
        }
        else{
            $status = 200;
            $body = '{"answer": false, "server_auth_token": ""}';
        }

        return new CheckCaptchaResponse($status, $body);

    }


    /**
     * An Arithmetic CAPTCHA is created when creating a CAPTCHA and CAPTCHA Server is not reachable,
     * or when validating a CAPTCHA and CAPTCHA Server is not reachable and the failure policy is set to "math".
     *
     * @return string
     */
    private function createArithmeticCaptcha($sendData=true)
    {
        $callback_url = $this->captchaProperties->getProperty("callback_url");
        if($this->captchaProperties->getAjaxVerify() && !empty($callback_url)){

            $captcha = <<<CAPTCHA
                <div id="mathcaptcha">
                    <div id='mathcaptcha_message'></div><input name='arithmeticCaptchaUserInput' id='arithmeticCaptchaUserInput' type='text' maxlength='2' size='2' onkeyup='ajaxMathCaptcha();'/>
                    <input name='arithmeticCaptchaNumberA' id='arithmeticCaptchaNumberA' type='hidden' value=''/>
                    <input name='arithmeticCaptchaNumberB' id='arithmeticCaptchaNumberB' type='hidden' value=''/>
                    <script type="text/javascript">
                        var a = Math.ceil(Math.random() * 10);
                        var b = Math.ceil(Math.random() * 10);
                        function createArithmeticCaptcha(){
                            $('#mathcaptcha_message').html("What is " + a + " + " + b + "? ");
                            document.getElementById('arithmeticCaptchaNumberA').value = a;
                            document.getElementById('arithmeticCaptchaNumberB').value = b;
                        }
                        createArithmeticCaptcha();
                        function ajaxMathCaptcha(){
                            var answerIsSingle = (a + b - ((a + b) %% 10) == 0);
                            var input = document.getElementById("arithmeticCaptchaUserInput").value;
                            if(answerIsSingle && input.length == 1){
                                validateArithmeticCaptcha();
                            }
                            if(!answerIsSingle && input.length == 2){
                                validateArithmeticCaptcha();
                            }
                        }

                        function validateArithmeticCaptcha(){
                            var input = document.getElementById("arithmeticCaptchaUserInput").value;
                            $.post(
                                "%s",
                                {
                                    endpoint: 'verify_block_captcha',
                                    arithmeticCaptchaUserInput: input,
                                    arithmeticCaptchaNumberA: a,
                                    arithmeticCaptchaNumberB: b,
                                    confidentcaptcha_block_id: 'mathcaptcha'
                                },
                                function (resp, textStatus, jqXHR) {
                                    if (resp == "true") {
                                        $('#mathcaptcha_message').html("Thank you. Please Continue. ");
                                        $("#arithmeticCaptchaUserInput").prop('readonly', true);
                                    }
                                    else{
                                        $.ajax(
                                        {
                                            type: 'POST',
                                            url: "%s",
                                            data: {
                                                endpoint: 'create_captcha_instance',
                                                confidentcaptcha_block_id: 'mathcaptcha'
                                            },
                                            success: function(xml){
                                                $('#mathcaptcha').replaceWith(xml);
                                            },
                                            error: function (xhr, status, error) {
                                                $('#mathcaptcha_message').html("Please refresh the page. ");
                                                $("#arithmeticCaptchaUserInput").prop('disabled', true);
                                            }
                                        });
                                    }
                                },
                                'text'
                            );
                        }
                    </script>
                </div>
CAPTCHA;
            $captcha = sprintf($captcha, $this->getCaptchaProperties()->getProperty("callback_url"), $this->getCaptchaProperties()->getProperty("callback_url"));
        }
        else{
            $captcha = <<<CAPTCHA
                <script type="text/javascript">
                    var a = Math.ceil(Math.random() * 10);
                    var b = Math.ceil(Math.random() * 10);
                    function createArithmeticCaptcha(){
                        document.write("What is "+ a + " + " + b +"? ");
                        document.write("<input name='arithmeticCaptchaUserInput' id='arithmeticCaptchaUserInput' type='text' maxlength='2' size='2'/>");
                        document.write("<input name='arithmeticCaptchaNumberA' id='arithmeticCaptchaNumberA' type='hidden' value='var a'/>");
                        document.write("<input name='arithmeticCaptchaNumberB' id='arithmeticCaptchaNumberB' type='hidden' value='var b'/>");
                        document.getElementById('arithmeticCaptchaNumberA').value = a;
                        document.getElementById('arithmeticCaptchaNumberB').value = b;
                    }
                    createArithmeticCaptcha();
                </script>
CAPTCHA;
        }
        if($sendData){
            $this->sendToMathCaptchaTracker();
        }

        return $captcha;
    }

    /**
     * A dummy CAPTCHA is created when creating a CAPTCHA and CAPTCHA Server is not reachable,
     * or when validating a CAPTCHA and CAPTCHA Server is not reachable and the failure policy is set to "open".
     *
     * @return string
     */
    private function createDummyCaptcha()
    {
        $captcha = <<<CAPTCHA
            <script type="text/javascript">
                function createDummyCaptcha(){
                    document.write("Captcha disabled.  Please continue.");
                    document.write("<input name='confidentcaptcha_code' id='confidentcaptcha_code' type='hidden' value='0'/>");
                    document.write("<input name='confidentcaptcha_captcha_id' id='confidentcaptcha_captcha_id' type='hidden' value='0'/>");
                    document.getElementById('confidentcaptcha_code').value = 0;
                    document.getElementById('confidentcaptcha_captcha_id').value = 0;
                }
                createDummyCaptcha();
            </script>
CAPTCHA;
        $this->sendToMathCaptchaTracker();
        return $captcha;
    }

    /**
     * Validating the user's response to the Arithmetic CAPTCHA challenge
     *
     * @param $request                      HTTP Request
     * @return bool
     */
    private function validateArithmeticCaptcha($request)
    {

        // init our return value
        $matchCaptchaRequestPassed = false;

        if (! is_null($request) && ! empty($request))
        {
            $numberA = $request['arithmeticCaptchaNumberA'];
            $numberB = $request['arithmeticCaptchaNumberB'];
            $userGivenAnswer = $request['arithmeticCaptchaUserInput'];
        }

        if (isset($numberA) && isset($numberB) && isset($userGivenAnswer))
        {
            // see if they got the answer correct
            if (intval($numberA) + intval($numberB) == intval($userGivenAnswer))
            {
                $matchCaptchaRequestPassed = true;
            }
        }

        return $matchCaptchaRequestPassed;
    }

    /**
     * Interface to call CAPTCHA API
     *
     * @param $endPointUrl
     * @param $httpParameters
     * @param $httpMethod
     * @return ConfidentCaptchaResponse
     */
    public function makeRequest($endPointUrl, $httpMethod, $httpParameters=null )
    {
        $serverRequestUrl = $endPointUrl;

        $mandatoryRequestParameters = $this->buildMandatoryRequestParameters($httpParameters);
        $configurableParameters = $this->captchaProperties->getProperties();
        $requestParameters =   $mandatoryRequestParameters + $configurableParameters;

        if($httpParameters != null){
            $requestParameters = $requestParameters + $httpParameters;
        }

        $form = NULL;
        if (strtoupper($httpMethod) == 'GET') {
            $serverRequestUrl .= '?' . http_build_query($requestParameters, '', '&');
        } elseif (strtoupper($httpMethod) == 'POST' and $requestParameters) {
            $form = http_build_query($requestParameters, '', '&');
        }

        $curlHandle = curl_init();

        if (strtoupper($httpMethod) == 'POST') {
            curl_setopt($curlHandle, CURLOPT_POST, TRUE);

            if ($form) {
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $form);
            }
        }

        # Set SSL verification options if we are requesting a ssl endpoint
        $sslProtocolUrl = "https://";
        $secureServerRequestUrl = substr($serverRequestUrl, 0, strlen($sslProtocolUrl));
        if (strcmp($secureServerRequestUrl, $sslProtocolUrl) == 0) {
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($curlHandle, CURLOPT_URL, $serverRequestUrl);

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);

        //shorten timeout to 8 seconds for curl calls
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 8);

        $body = curl_exec($curlHandle);

        $httpResponseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        if ($body === FALSE || strtolower($body) === "false" || $httpResponseCode != 200) {
            $response = new ConfidentCaptchaResponse($httpResponseCode, $body, strtoupper($httpMethod), $serverRequestUrl, $form);
            $this->responseError = $response;
        }
        else {
            $response = new ConfidentCaptchaResponse($httpResponseCode, $body, strtoupper($httpMethod), $serverRequestUrl, $form);
        }

        curl_close($curlHandle);

        return $response;
    }

    /**
     * Helper function to prepare the request parameters
     * It will add some dynamic properties such as IP Address and User Agent
     * Each of these are mandatory and pulled out of the environment/generated
     *
     * @param $httpParameters
     * @return array
     */
    private function buildMandatoryRequestParameters()
    {

        $mandatoryParameters['customer_id']  = $this->credentials->getCustomerId();
        $mandatoryParameters['site_id']      = $this->credentials->getSiteId();
        $mandatoryParameters['api_username'] = $this->credentials->getApiUsername();
        $mandatoryParameters['api_password'] = $this->credentials->getApiPassword();

        $mandatoryParameters['ip_addr']      = $this->getRealIpAddr();
        $mandatoryParameters['user_agent']   = $_SERVER['HTTP_USER_AGENT'];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $mandatoryParameters['language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $mandatoryParameters['language'] = "en";
        }
        
        $mandatoryParameters['library_version'] = $this->getLibraryVersion();
        $mandatoryParameters['local_server_name'] = $this->getLocalServerName();
        $mandatoryParameters['local_server_address'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';

        return $mandatoryParameters;
    }

    /**
     * Helper function to get client IP Address
     *
     * @return mixed
     */
    private function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))             // to check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   // to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Helper function to get local server name
     *
     * @return string
     */
    private function getLocalServerName()
    {
        $protocol = 'http';

        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')
        {
            $protocol = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $baseUrl = $protocol . '://' . $host . $requestUri;

        if (substr($baseUrl, -1)=='/') {
            $baseUrl = substr($baseUrl, 0, strlen($baseUrl)-1);
        }

        return $baseUrl;
    }


    /**
     * This method handles four use cases.
     * 1- Checking a block-visual (a ajax captcha)
     * 2- Creating a new block-visual (reloading the ajax captcha with a new captcha)
     * 3- Failing a block that has been expired (tried to many times)
     * 4- Checking that the callback is working successfully
     *
     * @param $request
     * @return array contains header and the content of this callback response which will be "true", "false", or a new CAPTCHA instance
     */
    public function callback($request){
        $endpoint = $request['endpoint'];

        $header = $_SERVER["SERVER_PROTOCOL"]." 400 Bad Request";

        $content = null;

        if ($endpoint == 'create_captcha_instance') {

            if($request['confidentcaptcha_block_id'] == "mathcaptcha"){
                $header = $_SERVER["SERVER_PROTOCOL"]." 200 OK";
                $content = $this->createArithmeticCaptcha(false);
            }
            else{
                if(!isset($this->confidentCaptchaSession)){
                    $this->confidentCaptchaSession = new ConfidentCaptchaSession();
                }
                $blockId = $this->confidentCaptchaSession->getBlockIdFromSession();

                $header = $_SERVER["SERVER_PROTOCOL"]." 200 OK";

                $blockCaptchaRequest = $this->requestCaptcha($blockId);

                if($blockCaptchaRequest->wasRequestSuccessful()){
                    $content = $blockCaptchaRequest->getBody();
                }
                else{
                    $header = $_SERVER["SERVER_PROTOCOL"]." 410 Gone";
                }
            }
        }
        elseif ($endpoint == 'verify_block_captcha') {
            $header = $_SERVER["SERVER_PROTOCOL"]." 200 OK";
            $check = $this->checkBlockVisual($request);
            $content = $check->wasCaptchaSolved() ? 'true' : 'false';
        }
        elseif ($endpoint == 'callback_check') {
            $header = $_SERVER["SERVER_PROTOCOL"]." 200 OK";
            $content = 'The callback is working';
        }

        return Array($header, $content);
    }

    /**
     * Processes the variables required by our math CAPTCHA tracker
     * @return string a String with URL request variables concatenated together
     */
    private function getMathCaptchaRequestForm()
    {
        $request = "";

        //Originating URL
        $request = $request . $this->encodeAndConcatenate("originating_url", $this->getLocalServerName()) . "&";

        //Referrer
        if(isset($_SERVER["HTTP_REFERER"])){
            $request = $request . $this->encodeAndConcatenate("referrer", $_SERVER["HTTP_REFERER"]) . "&";
        }

        //User Agent
        $request = $request . $this->encodeAndConcatenate("user_agent", $_SERVER["HTTP_USER_AGENT"]) . "&";

        //Language
        $request = $request . $this->encodeAndConcatenate("language", $_SERVER['HTTP_ACCEPT_LANGUAGE']) . "&";


        //Client IP Address
        $request = $request . $this->encodeAndConcatenate("client_ip", $_SERVER["REMOTE_ADDR"]) . "&";

        //Host IP
        $request = $request . $this->encodeAndConcatenate("host_ip", $_SERVER["SERVER_ADDR"]) . "&";

        //Customer ID
        $request = $request . $this->encodeAndConcatenate("customer_id", $this->credentials->getCustomerId()) . "&";

        //Site Name
        $request = $request . $this->encodeAndConcatenate("site_id", $this->credentials->getSiteId()) . "&";

        //API Server URL
        $request = $request . $this->encodeAndConcatenate("api_server_url", $this->apiServerUrl) . "&";

        //Library Version
        $request = $request . $this->encodeAndConcatenate("library_version", $this->libraryVersion) . "&";

        //CAPTCHA length
        $request = $request . $this->encodeAndConcatenate("captcha_length", $this->captchaProperties->getProperty("captcha_length")) . "&";

        //CAPTCHA width
        $request = $request . $this->encodeAndConcatenate("width", $this->captchaProperties->getProperty("width")) . "&";

        //CAPTCHA height
        $request = $request . $this->encodeAndConcatenate("height", $this->captchaProperties->getProperty("height")) . "&";

        //Image Code Color
        $request = $request . $this->encodeAndConcatenate("image_code_color", $this->captchaProperties->getProperty("image_code_color")) . "&";

        //Display Style
        $request = $request . $this->encodeAndConcatenate("display_style", $this->captchaProperties->getProperty("display_style")) . "&";

        //Noise Level
        $request = $request . $this->encodeAndConcatenate("audio_noise_level", $this->captchaProperties->getProperty("audio_noise_level")) . "&";

        //Failure Policy
        $request = $request . $this->encodeAndConcatenate("failure_policy_math", $this->captchaProperties->getProperty("failure_policy_math")) . "&";

        //Ajax Verify
        $request = $request . $this->encodeAndConcatenate("ajax_verify", $this->captchaProperties->getProperty("ajax_verify")) . "&";

        //Max Tries
        $request = $request . $this->encodeAndConcatenate("max_tries", $this->captchaProperties->getProperty("max_tries")) . "&";

        //Callback URL
        $request = $request . $this->encodeAndConcatenate("callback_url", $this->captchaProperties->getProperty("callback_url")) . "&";

        //Show Letters
        $request = $request . $this->encodeAndConcatenate("show_letters", $this->captchaProperties->getProperty("show_letters")) . "&";

        //Endpoint URL
        $request = $request . $this->encodeAndConcatenate("endpoint_url", $this->responseError->getUrl()) . "&";

        //Method
        $request = $request . $this->encodeAndConcatenate("method", $this->responseError->getMethod()) . "&";

        //Status Code
        $request = $request . $this->encodeAndConcatenate("status_code", $this->responseError->getStatus());


        return $request;
    }

    /**
     * Takes a key and value and mashes them together to form a URL request variable
     * @param $key represents the key of a URL request variable
     * @param $value represents the value of a URL request variable
     * @return string a String with the proper URL request variable syntax
     */
    private function encodeAndConcatenate($key, $value){
        return $key . "=" . ($value);
    }

    /**
     * Sends data to our math CAPTCHA tracker
     */
    private function sendToMathCaptchaTracker(){
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_POST, TRUE);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->getMathCaptchaRequestForm());
        curl_setopt($curlHandle, CURLOPT_URL, "http://math.confidenttechnologies.com/mathcaptcha.php");

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 1);

        curl_exec($curlHandle);
        curl_close($curlHandle);
    }

    /**
     * Gets the last error that this plugin processed when trying to request a new CAPTCHA from our server. The status is 200, with a message
     * of "No Error." if no errors have been detected yet
     * @return ConfidentCaptchaResponse  a ConfidentCaptchaResponse object representing the error the server sent back
     */
    public function getError(){
        if(isset($this->responseError) && !empty($this->responseError)){
            return $this->responseError;
        }
        else{
            return new ConfidentCaptchaResponse(200, "No error.");
        }
    }
}
