<?php

include_once LIBPATH.'php/utility/static/SearchPathString.php';
include_once LIBPATH.'php/utility/static/Enumeration.php';
include_once LIBPATH.'php/utility/static/EnumInputTypes.php';
include_once LIBPATH.'php/utility/static/EnumHTTPResponse.php';
include_once LIBPATH.'php/utility/interface/iResourceClient.php';
include_once LIBPATH.'php/resource/interface/iResource.php';
include_once LIBPATH.'php/resource/abstract/Resource.php';
include_once LIBPATH.'php/resource/HTMLResource.php';
include_once LIBPATH.'php/resource/HTMLViewResource.php';
include_once LIBPATH.'php/resource/interface/iPageView.php';
include_once LIBPATH.'php/resource/interface/iHTMLView.php';
include_once LIBPATH.'php/resource/HTMLPageView.php';
include_once LIBPATH.'php/pagebuilders/abstract/PageBuilder.php';
include_once LIBPATH.'php/pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'php/utility/SessionManager.php';
include_once LIBPATH.'php/utility/static/GrabParentPageId.php';
include_once LIBPATH.'php/utility/static/CheckPageUserAccess.php';
include_once LIBPATH.'php/resource/formelement/abstract/FormElement.php';
include_once LIBPATH.'php/utility/DynamicNavigationLink.php';
include_once LIBPATH.'php/resource/toolbar/abstract/ToolbarElement.php';
include_once LIBPATH.'php/resource/linkaction/abstract/LinkActionControlResource.php';
include_once LIBPATH.'php/resource/linkaction/PublishLinkControlResource.php';
include_once LIBPATH.'php/resource/linkaction/UnPublishLinkControlResource.php';
include_once LIBPATH.'php/resource/toolbar/BlogToolbarElement.php';
include_once LIBPATH.'php/resource/toolbar/BlogPostToolbarElement.php';
include_once LIBPATH.'php/resource/toolbar/DefaultToolbarElement.php';
include_once LIBPATH.'php/resource/abstract/ApiResource.php';
include_once LIBPATH.'php/resource/abstract/DataResource.php';
include_once LIBPATH.'php/resource/ExtHtmlResource.php';
include_once LIBPATH.'php/utility/abstract/FieldValidator.php';
include_once LIBPATH.'php/utility/interface/iFieldValidator.php';
include_once LIBPATH.'php/utility/StringFieldValidator.php';
include_once LIBPATH.'php/utility/HtmlFieldValidator.php';
include_once LIBPATH.'php/utility/IntegerFieldValidator.php';
include_once LIBPATH.'php/utility/BooleanFieldValidator.php';
include_once LIBPATH.'php/resource/extension/core_services/CoreServicesDataResource.php';
include_once LIBPATH.'php/resource/extension/core_services/PageDataResource.php';
include_once LIBPATH.'php/resource/extension/core_services/BlogDataResource.php';
include_once LIBPATH.'php/resource/extension/core_services/BlogPostDataResource.php';
include_once LIBPATH.'php/resource/extension/core_services/FileResource.php';
include_once LIBPATH.'php/resource/abstract/ApiPageView.php';
include_once LIBPATH.'php/resource/FilePageView.php';
include_once LIBPATH.'php/resource/JsonPageView.php';

include_once LIBPATH.'php/pagebuilders/admin/abstract/AdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/AdminHomeBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/UserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/CreateUserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/ManageUserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/PageAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/EditPageAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/ExtHTMLPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/blog/BlogPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/blog/BlogPostSinglePageBuilder.php';
include_once LIBPATH.'php/pagebuilders/Toonces404PageBuilder.php';
include_once LIBPATH.'php/pagebuilders/abstract/APIPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/core_services/CoreServicesAPIPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/core_services/DocumentEndpointPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/core_services/PageApiPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/TestPageBuilder.php';

include_once LIBPATH.'php/pagebuilders/core_services/BlogPostAPIPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/core_services/BlogsAPIPageBuilder.php';

include_once LIBPATH.'php/resource/extension/admin/AdminHTMLViewResource.php';
include_once LIBPATH.'php/resource/html_component/BodyHTMLViewResource.php';

include_once LIBPATH.'php/utility/static/UniversalConnect.php';
include_once LIBPATH.'php/utility/static/GrabPageURL.php';
include_once LIBPATH.'php/utility/static/GrabParentPageURL.php';


include_once LIBPATH.'php/resource/extension/blog/BlogReader.php';
include_once LIBPATH.'php/resource/extension/blog/BlogPageReader.php';
include_once LIBPATH.'php/resource/html_component/HeadElement.php';
include_once LIBPATH.'php/resource/extension/blog/BlogReaderSingle.php';
include_once LIBPATH.'php/resource/extension/admin/LoginFormElement.php';
include_once LIBPATH.'php/resource/extension/admin/CreateUserFormElement.php';
include_once LIBPATH.'php/utility/UserManager.php';
include_once LIBPATH.'php/resource/navelement/abstract/NavElement.php';
include_once LIBPATH.'php/resource/extension/admin/AdminNavElement.php';
include_once LIBPATH.'php/resource/formelement/delegate/FormElementInput.php';
include_once LIBPATH.'php/resource/extension/admin/LogoutFormElement.php';
include_once LIBPATH.'php/resource/extension/blog/BlogFormElement.php';
include_once LIBPATH.'php/resource/extension/blog/BlogEditorFormElement.php';
include_once LIBPATH.'php/resource/extension/blog/URLCheckFormElement.php';
include_once LIBPATH.'php/resource/extension/blog/DeleteBlogPostFormElement.php';
include_once LIBPATH.'php/resource/interactionelement/interface/iInteractionDelegate.php';
include_once LIBPATH.'php/resource/interactionelement/delegate/abstract/InteractionDelegate.php';
include_once LIBPATH.'php/resource/interactionelement/InteractionElement.php';
include_once LIBPATH.'php/resource/interactionelement/interface/iFormInput.php';
include_once LIBPATH.'php/resource/interactionelement/delegate/FormInput.php';
include_once LIBPATH.'php/resource/interactionelement/delegate/TextareaFormInput.php';

// custom
include_once LIBPATH.'custom/toonces_custom.php';


//test
include_once LIBPATH.'php/test/TestInteractionElement.php';
include_once LIBPATH.'php/test/TestInteractionDelegate.php';