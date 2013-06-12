<?php

/**
 *
 * The ConfidentCaptchaSession is in charge of keeping track of AJAX blocks stored inside of the user's session
 *
 */
class ConfidentCaptchaSession{
    private $_sessionNamespace = "CONFIDENTCAPTCHA_BLOCK";

    /**
     * When constructing a ConfidentCaptchaSession, start the session if it hasn't already been started
     */
    public function __construct(){
        //The plugin handles the starting of the session
    }

    /**
     * Save whether or not a block ID has been verified
     * @param $blockId the block ID to save
     * @param null $verified whether or not this block has been verified
     */
    public function save($blockId, $verified=null){
        $_SESSION[$this->_sessionNamespace][$blockId] = $verified;
    }


    /**
     * Checks whether or not the passed in block id has been successfully solved
     * @param $block_id The block ID to check
     * @return bool true if this block has been successfully solved, false otherwise
     */
    public function check($block_id){
        if (isset($_SESSION) && isset($_SESSION[$this->_sessionNamespace])) {
            foreach($_SESSION[$this->_sessionNamespace] as $session_block_id => $verified){
                if ($session_block_id == $block_id){
                    return $verified;
                }
            }
        }
        return false;
    }

    /**
     * Resets the session namespace associated with this ConfidentCaptchaSession
     */
    public function reset(){
        if(isset($_SESSION)){
            unset($_SESSION[$this->_sessionNamespace]);
        }
    }

    /**
     * Sets the namespace to use for saving to the session
     * @param $sessionNamespace the name to use for saving block states to the session
     */
    public function setSessionNamespace($sessionNamespace)
    {
        $this->_sessionNamespace = $sessionNamespace;
    }

    /**
     * Gets the namespace
     * @return string the namespace of this ConfidentCaptchaSession
     */
    public function getSessionNamespace()
    {
        return $this->_sessionNamespace;
    }

    /**
     * If the block ID is stored in the session, this method will retrieve it.
     * @return mixed
     */
    public function getBlockIdFromSession(){
        reset($_SESSION[$this->_sessionNamespace]);
        $blockId = key($_SESSION[$this->_sessionNamespace]);
        return $blockId;
    }
}