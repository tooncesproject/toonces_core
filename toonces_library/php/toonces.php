<?php


include_once LIBPATH . 'php/exception/TooncesException.php';
include_once LIBPATH . 'php/exception/EndpointNotFoundException.php';
include_once LIBPATH . 'php/exception/XmlCreateEndpointException.php';
include_once LIBPATH . 'php/exception/XmlReadWriteException.php';

include_once LIBPATH . 'php/utility/static/StaticRequestFactory.php';
include_once LIBPATH . 'php/utility/static/StaticResourceFactory.php';
include_once LIBPATH . 'php/record/Request.php';

include_once LIBPATH . 'php/record/Endpoint.php';
include_once LIBPATH . 'php/abstract/iEndpointOperator.php';
include_once LIBPATH . 'php/XmlEndpointOperator.php';

include_once LIBPATH . 'php/abstract/Response.php';
include_once LIBPATH . 'php/abstract/Responder.php';

include_once LIBPATH . 'php/DefaultResponse.php';

include_once LIBPATH . 'php/DefaultResponder.php';
include_once LIBPATH . 'php/HelloWorldResponder.php';
include_once LIBPATH . 'php/FourOhFourResponder.php';

include_once LIBPATH . 'php/abstract/iAuthenticator.php';
include_once LIBPATH . 'php/DefaultAuthenticator.php';
include_once LIBPATH . 'php/abstract/Resource.php';
include_once LIBPATH . 'php/HelloWorldResource.php';
include_once LIBPATH . 'php/FourOhFourResource.php';

