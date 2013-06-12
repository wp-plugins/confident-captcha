<?php
/**
 * Created by ConfidentTechnologies.
 * User: byao@confidenttech.com
 * Date: 8/27/12
 * Time: 9:05 PM
 * PHP ConfidentCaptcha Implementation
 */
/**
 * Response from a Confident CAPTCHA API call
 * @package confidentcaptcha-php
 */
class ConfidentCaptchaResponse
{
    /**
     * HTTP status code returned by API
     *
     * Standard HTTP codes are used, as well as 0 to signify that the server
     * did not respond.
     * @var integer
     */
    private $status;

    /**
     * HTTP body returned by API
     *
     * If the {@link $status} is 200, then the body is the response from the
     * API. Otherwise, the response is the cURL error.
     * @var string
     */
    private $body;

    /**
     * Request method
     *
     * @var string
     */
    private $method;

    /**
     * Request URL
     *
     * @var string
     */
    private $url;

    /**
     * Request form parameters, or NULL if not a POST
     *
     * @var string
     */
    private $form;

    /**
     * If there is an issue with the request to the confident captcha server,
     * it should be saved here.
     *
     * @var string
     */
    private $errorMessage;


    /**
     * Construct a BaseCaptchaResponse
     *
     * @param integer $status           HTTP status code
     * @param string  $body             HTTP response body
     * @param string  $method           HTTP request method
     * @param string  $url              Request URL
     * @param string  $form             Form parameters (or NULL if not a POST)
     *  if response is due to a shortcut check
     * @return ConfidentCaptchaResponse
     */

    public function __construct($status, $body, $method=null, $url=null, $form=null)
    {
        $this->status       = $status;
        $this->body         = $body;
        $this->method       = $method;
        $this->url          = $url;
        $this->form         = $form;

        if($status !=200 ){
            $this->errorMessage = $body;
        }
    }

    /**
     * Getter for Response Status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Setter for Response Status
     *
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Getter for Response Body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Setter for Response Body
     *
     * @param $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Getter for HTTP Request Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Getter for Request URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Getter for Response in Form
     *
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * If the request to CAPTCHA API is successful
     *
     * @return bool
     */
    public function wasRequestSuccessful()
    {
        return $this->status === 200;
    }

    public function getErrorMessage(){
        if($this->wasRequestSuccessful() == false){

            return $this->errorMessage;
        }
    }



}

/**
 * Response from Confident CAPTCHA CREDENTIAL CHECK API call
 * @package confidentcaptcha-php
 */
class CheckCredentialsResponse extends ConfidentCaptchaResponse
{
    /**
     * If valid credentials
     * @return bool
     */
    public function wasValidCredentials()
    {
        return $this->getStatus() === 200;
    }
}

/**
 * Response from Confident CAPTCHA CONFIG CHECK API call
 * @package confidentcaptcha-php
 */
class CheckClientSetupResponse extends ConfidentCaptchaResponse
{
    private $html;
    private $apiPassed;

    /**
     * @static                          Static function to construct the class object
     * @param $status                   HTTP status code
     * @param $html                     HTML page of the API call response
     * @param $apiPassed                Whether the provided credential passed the API
     * @return CheckClientSetupResponse
     */
    public function __construct($status, $html, $apiPassed)
    {
        $this->setStatus($status);
        $this->html = $html;
        $this->apiPassed = $apiPassed;
    }



    /**
     * Getter for HTML page of the API call response
     *
     * @return mixed
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Whether the provided credential passed the API
     *
     * @return mixed
     */
    public function wasApiPassed()
    {
        return $this->apiPassed;
    }
}

/**
 * Response from Confident CAPTCHA CHECK API call
 * @package confidentcaptcha-php
 */
class CheckCaptchaResponse extends ConfidentCaptchaResponse
{

    /**
     * Getter for created CAPTCHA ID
     *
     * @return string
     */
    public function getCaptchaId()
    {
        $captchIdPos = strrpos($this->getUrl(), "captcha") + strlen("captcha") + 1;
        return substr($this->getUrl(), $captchIdPos);
    }

    /**
     * Whether the solution to the CAPTCHA is valid
     *
     * @return boolean
     */
    public function wasCaptchaSolved()
    {
        if ($this->getStatus() != 200)
        {
            return false;
        }

        $response = json_decode($this->getBody(), true);
        if (strtolower($response['answer']) == true ){
            return true;
        }

        return false;
    }
}


/**
 *
 * A ConfidentCaptchaResponse variant that helps keep track of the block ID of a new AJAX verified CAPTCHA
 *
 */
class CreateBlockResponse extends ConfidentCaptchaResponse
{
    private $_blockId = "defaultId";

    /**
     * Constructs a CreateBlockResponse
     * @param int $status integer representing the status of this response
     * @param string $body String representing the body of the response, usually the block ID
     * @param null $method the HTTP method used to get this response
     * @param null $url the url used to get this response
     * @param null $form the HTTP request variables used to get this response
     */
    function __construct($status, $body, $method=null, $url=null, $form=null)
    {
        parent::__construct($status, $body, $method=null, $url=null, $form=null);
        if($status == 200 ){
            $this->_blockId = $body;
        }
    }

    /**
     * Gets the block ID associated with this CreateBlockResponse
     * @return string representing the block ID of this object or "defaultId" if something went wrong with the request for a block Id
     */
    public function getBlockId()
    {
        return $this->_blockId;
    }
}