<?php

namespace Karolina\Http;

Class SecurityHeaders {
    
    public function setForPaymentForm () {

        $csp = new \ParagonIE\CSPBuilder\CSPBuilder();
        
        $csp->addSource('image', "'self'");
        $csp->addSource('script-src', 'https://ecommerce.borgun.is');
        $csp->addSource('connect-src', "'self'");
        $csp->addSource('connect-src', 'https://ecommerce.borgun.is');
        $csp->addSource('connect-src', '*.payline.com');
        $csp->addSource('connect-src', 'api.mangopay.com');
        $csp->addSource('script-src', "'self'");
        $csp->addSource('style-src', "'self'");

        if (getenv('environment') == "development") {
    
            $csp->addSource('connect-src', 'api.sandbox.mangopay.com');

        }


    	header("strict-transport-security: max-age=1200");
    	header("X-XSS-Protection: 1; mode=block");
    	header("X-Content-Type-Options: nosniff");
    	header("X-Frame-Options: SAMEORIGIN");

    	$csp->sendCSPHeader();        
    	
    }    
    
}