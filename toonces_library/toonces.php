<?php

include_once LIBPATH.'utility/static/Enumeration.php';
include_once LIBPATH.'utility/static/EnumInputTypes.php';
include_once LIBPATH.'utility/interface/iConnectInfo.php';
include_once LIBPATH.'element/interface/iElement.php';
include_once LIBPATH.'element/Element.php';
include_once LIBPATH.'element/ViewElement.php';
include_once LIBPATH.'element/interface/iView.php';
require_once LIBPATH.'element/PageView.php';
include_once LIBPATH.'pagebuilders/abstract/PageBuilder.php';
include_once LIBPATH.'pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'utility/SessionManager.php';
include_once LIBPATH.'element/formelement/abstract/FormElement.php';
include_once LIBPATH.'utility/DynamicNavigationLink.php';
include_once LIBPATH.'element/toolbar/abstract/ToolbarElement.php';
include_once LIBPATH.'element/linkaction/abstract/LinkActionControlElement.php';
include_once LIBPATH.'element/linkaction/PublishLinkControlElement.php';
include_once LIBPATH.'element/linkaction/UnPublishLinkControlElement.php';
include_once LIBPATH.'element/toolbar/BlogToolbarElement.php';
include_once LIBPATH.'element/toolbar/BlogPostToolbarElement.php';
include_once LIBPATH.'element/toolbar/DefaultToolbarElement.php';

include_once LIBPATH.'pagebuilders/admin/abstract/AdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/admin/AdminHomeBuilder.php';
include_once LIBPATH.'pagebuilders/admin/UserAdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/admin/CreateUserAdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/admin/ManageUserAdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/admin/PageAdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/admin/EditPageAdminPageBuilder.php';
include_once LIBPATH.'pagebuilders/abstract/StandardPageBuilder.php';
include_once LIBPATH.'pagebuilders/blog/BlogPageBuilder.php';
include_once LIBPATH.'pagebuilders/blog/BlogPostSinglePageBuilder.php';

include_once LIBPATH.'element/extension/admin/AdminViewElement.php';
include_once LIBPATH.'element/html_component/BodyViewElement.php';

include_once LIBPATH.'utility/static/UniversalConnect.php';
include_once LIBPATH.'utility/static/GrabPageURL.php';
include_once LIBPATH.'utility/static/GrabParentPageURL.php';


include_once LIBPATH.'element/extension/blog/BlogReader.php';
include_once LIBPATH.'element/extension/blog/BlogPageReader.php';
include_once LIBPATH.'element/html_component/DivElement.php';
include_once LIBPATH.'element/html_component/TagElement.php';
include_once LIBPATH.'element/html_component/HeadElement.php';
include_once LIBPATH.'element/extension/blog/BlogReaderSingle.php';
include_once LIBPATH.'element/extension/admin/LoginFormElement.php';
include_once LIBPATH.'element/extension/admin/CreateUserFormElement.php';
include_once LIBPATH.'utility/UserManager.php';
include_once LIBPATH.'element/navelement/abstract/NavElement.php';
include_once LIBPATH.'element/extension/admin/AdminNavElement.php';
include_once LIBPATH.'element/formelement/delegate/FormElementInput.php';
include_once LIBPATH.'element/extension/admin/LogoutFormElement.php';
include_once LIBPATH.'element/extension/blog/BlogFormElement.php';
include_once LIBPATH.'element/extension/blog/BlogEditorFormElement.php';
include_once LIBPATH.'element/extension/blog/URLCheckFormElement.php';
include_once LIBPATH.'element/extension/blog/DeleteBlogPostFormElement.php';
include_once LIBPATH.'element/interactionelement/interface/iInteractionDelegate.php';
include_once LIBPATH.'element/interactionelement/delegate/abstract/InteractionDelegate.php';
include_once LIBPATH.'element/interactionelement/InteractionElement.php';
include_once LIBPATH.'element/interactionelement/interface/iFormInput.php';
include_once LIBPATH.'userinterface/FormInput.php';
include_once LIBPATH.'element/interactionelement/delegate/TextareaFormInput.php';

// custom
include_once LIBPATH.'custom/toonces_custom.php';


//test
include_once LIBPATH.'test/TestInteractionElement.php';
include_once LIBPATH.'test/TestInteractionDelegate.php';