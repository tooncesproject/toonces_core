<?php


include_once LIBPATH . 'php/exception/TooncesException.php';
include_once LIBPATH . 'php/exception/EndpointNotFoundException.php';
include_once LIBPATH . 'php/exception/XmlCreateEndpointException.php';
include_once LIBPATH . 'php/exception/XmlReadWriteException.php';

include_once LIBPATH . 'php/utility/static/StaticRequestFactory.php';
include_once LIBPATH . 'php/Request.php';
include_once LIBPATH . 'php/utility/static/SearchPathString.php';

include_once LIBPATH . 'php/Endpoint.php';
include_once LIBPATH . 'php/resourcefactory/interface/iEndpointSystem.php';
include_once LIBPATH . 'php/XmlEndpointSystem.php';

include_once LIBPATH . 'php/Response.php';
include_once LIBPATH . 'php/iResponder.php';
include_once LIBPATH . 'php/Responder.php';

include_once LIBPATH . 'php/DefaultResponse.php';

include_once LIBPATH . 'php/DefaultResponder.php';
include_once LIBPATH . 'php/HelloWorldResponder.php';
include_once LIBPATH . 'php/FourOhFourResponder.php';

include_once LIBPATH . 'php/iAuthenticator.php';
include_once LIBPATH . 'php/DefaultAuthenticator.php';
include_once LIBPATH . 'php/resource/abstract/Resource.php';
include_once LIBPATH . 'php/HelloWorldResource.php';
include_once LIBPATH . 'php/FourOhFourResource.php';

include_once LIBPATH . 'php/utility/static/UniversalConnect.php';

// custom
include_once LIBPATH . 'custom/toonces_custom.php';

