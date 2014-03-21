<?php
/**
 * Created by ConfidentTechnologies.
 * User: byao@confidenttech.com
 * Date: 8/26/12
 * Time: 4:11 PM
 * PHP ConfidentCaptcha Implementation
 */
/**
 * Confident CAPTCHA Properties Class
 * @package confidentcaptcha-php
 */
class ConfidentCaptchaProperties
{
    /**
     * @var  An array to store the CAPTCHA properties
     */
    private $properties;


    /**
     * Constructor
     */
    public function __construct($settingsXml = null)
    {
        if ($settingsXml != null) {
            $xml = simplexml_load_file($settingsXml);
            $properties = $xml->{'properties'};

            if (isset($properties)) {
                $json = json_encode($properties);
                $this->properties = json_decode($json, true);
            }
        }

       $this->setDefaultProperties();
    }


    /**
     * Getter for the array holding CAPTCHA properties
     *
     * @return
     */
    public function getProperties()
    {
        if (is_null($this->properties))
        {
            $this->setDefaultProperties();
        }

        return $this->properties;
    }

    /**
     * Setter for the array to hold CAPTCHA properties
     *
     * @param $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    private function setDefaultProperties()
    {
        if(!isset($this->properties['width'])){
            $this->properties['width'] = '3';
        }
        if(!isset($this->properties['height'])){
        $this->properties['height'] = '3';
        }
        if(!isset($this->properties['captcha_length'])){
            $this->properties['captcha_length'] = '3';
        }
        if(!isset($this->properties['display_style'])){
            $this->properties['display_style'] = 'lightbox';
        }
        if(!isset($this->properties['captcha_color'])){
            $this->properties['captcha_color'] = 'Pearl';
        }
        if(!isset($this->properties['image_code_color'])){
            $this->properties['image_code_color'] = 'White';
        }
        if(!isset($this->properties['failure_policy_math'])){
            $this->properties['failure_policy_math'] = 'math';	
        }
        if(!isset($this->properties['noise_level'])){
            $this->properties['noise_level'] = '0.2';
        }
        if(!isset($this->properties['ajax_verify'])){
            $this->properties['ajax_verify'] =  'FALSE';
        }
        if(!isset($this->properties['response_format'])){
            $this->properties['response_format'] =  'JSON';
        }
        if(!isset($this->properties['max_tries'])){
            $this->properties['max_tries'] =  '3';
        }
        if(!isset($this->properties['show_letters'])){
            $this->properties['show_letters'] =  'FALSE';
        }
        if(!isset($this->properties['callback_url']) || empty($this->properties['callback_url'])){
            unset($this->properties['callback_url']);
        }
    }

    public function getProperty($propertyName)
    {
        if(isset($this->properties[$propertyName])){
            return $this->properties[$propertyName];
        }
        return null;
    }

    public function setProperty($propertyName, $propertyValue)
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    // getters and setters provided for known properties
    public function setBillboardName($billboard_name)
    {
        
            $this->properties['billboard_name'] = $billboard_name;
    }

    public function getBillboardName()
    {
        if(isset($this->properties['billboard_name'])){
            return $this->properties['billboard_name'];
        }
        return null;
    }

    public function setCaptchaLength($captcha_length)
    {
        $this->properties['captcha_length'] = $captcha_length;
    }

    public function getCaptchaLength()
    {
        if(isset($this->properties['captcha_length'])){
            return $this->properties['captcha_length'];
        }
        return null;
    }

    public function setDisplayStyle($display_style)
    {
        $this->properties['display_style'] = $display_style;
    }

    public function getDisplayStyle()
    {
        if(isset($this->properties['display_style'])){
            return $this->properties['display_style'];
        }
        return null;
    }

    public function setHeight($height)
    {
        $this->properties['height'] = $height;
    }

    public function getHeight()
    {
        if(isset($this->properties['height'])){
            return $this->properties['height'];
        }
        return null;
    }

    public function setImageCodeColor($image_code_color)
    {
        $this->properties['image_code_color'] = $image_code_color;
    }

    public function getImageCodeColor()
    {
        if(isset($this->properties['image_code_color'])){
            return $this->properties['image_code_color'];
        }
        return null;
    }

    public function setLogoName($logo_name)
    {        
            $this->properties['logo_name'] = $logo_name;        
    }

    public function getLogoName()
    {
        if(isset($this->properties['logo_name'])){
            return  $this->properties['logo_name'];
        }
        return null;
    }

    public function setNoiseLevel($noise_level)
    {
        $this->properties['noise_level'] = $noise_level;
    }

    public function getNoiseLevel()
    {
        if(isset($this->properties['noise_level'])){
            return $this->properties['noise_level'];    
        }
        return null;
        
    }



    public function setAjaxVerify($ajax_verify)
    {
        $this->properties['ajax_verify'] = $ajax_verify;
    }

    public function getAjaxVerify()
    {
        if(isset($this->properties['ajax_verify'])){
            return $this->properties['ajax_verify'] == "TRUE";
        }
        return null;
    }

    public function setWidth($width)
    {
        $this->properties['width'] = $width;
    }

    public function getWidth()
    {
        if(isset($this->properties['width'])){
            return $this->properties['width'];
        }
        return null;
    }

    public function setApiServerUrl(){
        $this->properties['api_server_url'];
    }

    public function getApiServerUrl(){
        if(isset($this->properties['api_server_url'])){
            return $this->properties['api_server_url'];
        }
        return null;
    }

    public function setLibraryVersion($library_version)
    {
        $this->properties['library_version']  = $library_version;
    }

    public function getLibraryVersion()
    {
        if(isset($this->properties['library_version'])){
            return $this->properties['library_version'];
        }
        return null;
    }
}
