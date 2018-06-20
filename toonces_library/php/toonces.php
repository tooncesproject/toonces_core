<?php

include_once LIBPATH . 'php/utility/static/SearchPathString.php';
include_once LIBPATH . 'php/utility/static/Enumeration.php';
include_once LIBPATH . 'php/utility/static/EnumHTTPResponse.php';
include_once LIBPATH . 'php/utility/interface/iResourceClient.php';
include_once LIBPATH . 'php/utility/ResourceClient.php';
include_once LIBPATH . 'php/utility/LocalResourceClient.php';
include_once LIBPATH . 'php/resource/interface/iResource.php';
include_once LIBPATH . 'php/resourcefactory/interface/iResourceFactory.php';
include_once LIBPATH . 'php/resourcefactory/TooncesResourceFactory.php';
include_once LIBPATH . 'php/resource/abstract/Resource.php';
include_once LIBPATH . 'php/renderer/interface/iRenderer.php';
include_once LIBPATH . 'php/utility/SessionManager.php';
include_once LIBPATH . 'php/utility/static/GrabParentResourceId.php';
include_once LIBPATH . 'php/utility/static/CheckResourceUserAccess.php';
include_once LIBPATH . 'php/resource/abstract/Resource.php';
include_once LIBPATH . 'php/resource/interface/iDataResource.php';
include_once LIBPATH . 'php/resource/abstract/DataResource.php';
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
include_once LIBPATH . 'php/utility/datavalidator/DomDataResourcePostApiDataValidator.php';
include_once LIBPATH . 'php/utility/datavalidator/DomDataResourcePutApiDataValidator.php';
include_once LIBPATH . 'php/resource/core_services/CoreServicesDataResource.php';
include_once LIBPATH . 'php/resource/core_services/ResourceDataResource.php';
include_once LIBPATH . 'php/resource/interface/iFileResource.php';
include_once LIBPATH . 'php/resource/core_services/FileResource.php';
include_once LIBPATH . 'php/resource/core_services/DomResourceDataResource.php';
include_once LIBPATH . 'php/renderer/abstract/Renderer.php';
include_once LIBPATH . 'php/renderer/FileRenderer.php';
include_once LIBPATH . 'php/renderer/JsonRenderer.php';

include_once LIBPATH . 'php/utility/static/UniversalConnect.php';
include_once LIBPATH . 'php/utility/static/GrabResourceURL.php';
include_once LIBPATH . 'php/utility/static/GrabParentResourceURL.php';
include_once LIBPATH . 'php/utility/UserManager.php';

include_once LIBPATH . 'php/resource/interface/iDomDocumentResource.php';
include_once LIBPATH . 'php/resource/abstract/DomDocumentResource.php';
include_once LIBPATH . 'php/resource/interface/iNestedDomDocumentResource.php';
include_once LIBPATH . 'php/resource/abstract/NestedDomDocumentResource.php';
include_once LIBPATH . 'php/resource/defaults/TooncesWelcomeDomDocumentResource.php';
include_once LIBPATH . 'php/resource/defaults/Toonces404DomDocumentResource.php';

// custom
include_once LIBPATH . 'custom/toonces_custom.php';

