<?php

include_once LIBPATH.'php/utility/static/Enumeration.php';
include_once LIBPATH.'php/utility/static/EnumInputTypes.php';
include_once LIBPATH.'php/utility/interface/iConnectInfo.php';
include_once LIBPATH.'php/element/interface/iElement.php';
include_once LIBPATH.'php/element/Element.php';
include_once LIBPATH.'php/element/ViewElement.php';
include_once LIBPATH.'php/element/interface/iView.php';
require_once LIBPATH.'php/element/PageView.php';
include_once LIBPATH.'php/pagebuilders/abstract/PageBuilder.php';
include_once LIBPATH.'php/pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'php/utility/SessionManager.php';
include_once LIBPATH.'php/element/formelement/abstract/FormElement.php';
include_once LIBPATH.'php/utility/DynamicNavigationLink.php';
include_once LIBPATH.'php/element/toolbar/abstract/ToolbarElement.php';
include_once LIBPATH.'php/element/linkaction/abstract/LinkActionControlElement.php';
include_once LIBPATH.'php/element/linkaction/PublishLinkControlElement.php';
include_once LIBPATH.'php/element/linkaction/UnPublishLinkControlElement.php';
include_once LIBPATH.'php/element/toolbar/BlogToolbarElement.php';
include_once LIBPATH.'php/element/toolbar/BlogPostToolbarElement.php';
include_once LIBPATH.'php/element/toolbar/DefaultToolbarElement.php';

include_once LIBPATH.'php/pagebuilders/admin/abstract/AdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/AdminHomeBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/UserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/CreateUserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/ManageUserAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/PageAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/admin/EditPageAdminPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/blog/BlogPageBuilder.php';
include_once LIBPATH.'php/pagebuilders/blog/BlogPostSinglePageBuilder.php';

include_once LIBPATH.'php/element/extension/admin/AdminViewElement.php';
include_once LIBPATH.'php/element/html_component/BodyViewElement.php';

include_once LIBPATH.'php/utility/static/UniversalConnect.php';
include_once LIBPATH.'php/utility/static/GrabPageURL.php';
include_once LIBPATH.'php/utility/static/GrabParentPageURL.php';


include_once LIBPATH.'php/element/extension/blog/BlogReader.php';
include_once LIBPATH.'php/element/extension/blog/BlogPageReader.php';
include_once LIBPATH.'php/element/html_component/DivElement.php';
include_once LIBPATH.'php/element/html_component/TagElement.php';
include_once LIBPATH.'php/element/html_component/HeadElement.php';
include_once LIBPATH.'php/element/extension/blog/BlogReaderSingle.php';
include_once LIBPATH.'php/element/extension/admin/LoginFormElement.php';
include_once LIBPATH.'php/element/extension/admin/CreateUserFormElement.php';
include_once LIBPATH.'php/utility/UserManager.php';
include_once LIBPATH.'php/element/navelement/abstract/NavElement.php';
include_once LIBPATH.'php/element/extension/admin/AdminNavElement.php';
include_once LIBPATH.'php/element/formelement/delegate/FormElementInput.php';
include_once LIBPATH.'php/element/extension/admin/LogoutFormElement.php';
include_once LIBPATH.'php/element/extension/blog/BlogFormElement.php';
include_once LIBPATH.'php/element/extension/blog/BlogEditorFormElement.php';
include_once LIBPATH.'php/element/extension/blog/URLCheckFormElement.php';
include_once LIBPATH.'php/element/extension/blog/DeleteBlogPostFormElement.php';
include_once LIBPATH.'php/element/interactionelement/interface/iInteractionDelegate.php';
include_once LIBPATH.'php/element/interactionelement/delegate/abstract/InteractionDelegate.php';
include_once LIBPATH.'php/element/interactionelement/InteractionElement.php';
include_once LIBPATH.'php/element/interactionelement/interface/iFormInput.php';
include_once LIBPATH.'php/element/interactionelement/delegate/FormInput.php';
include_once LIBPATH.'php/element/interactionelement/delegate/TextareaFormInput.php';

// custom
include_once LIBPATH.'php/custom/toonces_custom.php';


//test
include_once LIBPATH.'php/test/TestInteractionElement.php';
include_once LIBPATH.'php/test/TestInteractionDelegate.php';