<?php

include_once LIBPATH . 'php/utility/static/SearchPathString.php';
include_once LIBPATH . 'php/utility/static/Enumeration.php';
include_once LIBPATH . 'php/utility/static/EnumInputTypes.php';
include_once LIBPATH . 'php/utility/static/EnumHTTPResponse.php';
include_once LIBPATH . 'php/utility/interface/iResourceClient.php';
include_once LIBPATH . 'php/utility/ResourceClient.php';
include_once LIBPATH . 'php/utility/LocalResourceClient.php';
include_once LIBPATH . 'php/resource/interface/iResource.php';
include_once LIBPATH . 'php/resource/abstract/Resource.php';
include_once LIBPATH . 'php/resource/HTMLResource.php';
include_once LIBPATH . 'php/resource/HTMLViewResource.php';
include_once LIBPATH . 'php/resource/interface/iPageView.php';
include_once LIBPATH . 'php/pagebuilder/abstract/PageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/abstract/StandardPageBuilder.php';
include_once LIBPATH . 'php/utility/SessionManager.php';
include_once LIBPATH . 'php/utility/static/GrabParentResourceId.php';
include_once LIBPATH . 'php/utility/static/CheckResourceUserAccess.php';
include_once LIBPATH . 'php/resource/abstract/ApiResource.php';
include_once LIBPATH . 'php/resource/abstract/DataResource.php';
include_once LIBPATH . 'php/resource/ExtHtmlResource.php';
include_once LIBPATH . 'php/utility/abstract/FieldValidator.php';
include_once LIBPATH . 'php/utility/interface/iFieldValidator.php';
include_once LIBPATH . 'php/utility/StringFieldValidator.php';
include_once LIBPATH . 'php/utility/HtmlFieldValidator.php';
include_once LIBPATH . 'php/utility/IntegerFieldValidator.php';
include_once LIBPATH . 'php/utility/BooleanFieldValidator.php';
include_once LIBPATH . 'php/utility/interface/iApiDataValidator.php';
include_once LIBPATH . 'php/utility/abstract/ApiDataValidator.php';
include_once LIBPATH . 'php/utility/datavalidator/PagePostApiDataValidator.php';
include_once LIBPATH . 'php/utility/datavalidator/PagePutApiDataValidator.php';
include_once LIBPATH . 'php/utility/datavalidator/ExtHtmlPagePostApiDataValidator.php';
include_once LIBPATH . 'php/utility/datavalidator/ExtHtmlPagePutApiDataValidator.php';
include_once LIBPATH . 'php/resource/core_services/CoreServicesDataResource.php';
include_once LIBPATH . 'php/resource/core_services/PageDataResource.php';
include_once LIBPATH . 'php/resource/core_services/FileResource.php';
include_once LIBPATH . 'php/resource/core_services/ExtHtmlPageDataResource.php';
include_once LIBPATH . 'php/resource/abstract/ApiPageView.php';
include_once LIBPATH . 'php/resource/FilePageView.php';
include_once LIBPATH . 'php/resource/JsonPageView.php';

include_once LIBPATH . 'php/pagebuilder/abstract/StandardPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/ExtHTMLPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/Toonces404PageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/abstract/APIPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/core_services/CoreServicesAPIPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/core_services/DocumentEndpointPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/core_services/PageApiPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/core_services/ExtPageApiPageBuilder.php';
include_once LIBPATH . 'php/pagebuilder/TestPageBuilder.php';


include_once LIBPATH . 'php/utility/static/UniversalConnect.php';
include_once LIBPATH . 'php/utility/static/GrabResourceURL.php';
include_once LIBPATH . 'php/utility/static/GrabParentResourceURL.php';
include_once LIBPATH . 'php/utility/UserManager.php';


// custom
include_once LIBPATH . 'custom/toonces_custom.php';

