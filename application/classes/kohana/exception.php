<?php defined('SYSPATH') or die('No direct script access.'); 

class Kohana_Exception extends Kohana_Kohana_Exception {
 
    public static function handler(Exception $e)
    {
    	if (Kohana::DEVELOPMENT === Kohana::$environment)
        {
            parent::handler($e);
        }
        else
        {
            try
            {
                Kohana::$log->add(Log::ERROR, parent::text($e));
 
                $code = 500;
                $message = rawurlencode($e->getMessage());
 
                if ($e instanceof HTTP_Exception)
                {
                    $code = $e->getCode();
                }
				
				// Exit with an error status                
				echo "ERROR!";
				exit(1);
            }
            catch (Exception $e)
            {
                // Clean the output buffer if one exists
                ob_get_level() and ob_clean();
 
                // Exit with an error status
                echo "ERROR!";               
                exit(1);
            }
        }
    }
}