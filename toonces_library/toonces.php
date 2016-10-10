<?php

include_once LIBPATH.'static_classes/Enumeration.php';
include_once LIBPATH.'static_classes/EnumInputTypes.php';
include_once LIBPATH.'interfaces/iConnectInfo.php';
include_once LIBPATH.'interfaces/iElement.php';
include_once LIBPATH.'Element.php';
include_once LIBPATH.'ViewElement.php';
include_once LIBPATH.'interfaces/iView.php';
require_once LIBPATH.'PageView.php';
include_once LIBPATH.'abstract/PageBuilder.php';
include_once LIBPATH.'abstract/StandardPageBuilder.php';
include_once LIBPATH.'custom/toonces_custom.php';
include_once LIBPATH.'utility/SessionManager.php';
include_once LIBPATH.'abstract/FormElement.php';
include_once LIBPATH.'utility/DynamicNavigationLink.php';
include_once LIBPATH.'abstract/ToolbarElement.php';
include_once LIBPATH.'abstract/LinkActionControlElement.php';
include_once LIBPATH.'control/PublishLinkControlElement.php';
include_once LIBPATH.'control/UnPublishLinkControlElement.php';
include_once LIBPATH.'userinterface/BlogToolbarElement.php';
include_once LIBPATH.'userinterface/BlogPostToolbarElement.php';
include_once LIBPATH.'userinterface/DefaultToolbarElement.php';

include_once LIBPATH.'pagebuilders/AdminPageBuilder.php';
include_once LIBPATH.'admin/AdminHomeBuilder.php';
include_once LIBPATH.'admin/UserAdminPageBuilder.php';
include_once LIBPATH.'admin/CreateUserAdminPageBuilder.php';
include_once LIBPATH.'admin/ManageUserAdminPageBuilder.php';
include_once LIBPATH.'admin/PageAdminPageBuilder.php';
include_once LIBPATH.'admin/EditPageAdminPageBuilder.php';
include_once LIBPATH.'abstract/StandardPageBuilder.php';
include_once LIBPATH.'pagebuilders/BlogPageBuilder.php';
include_once LIBPATH.'pagebuilders/BlogPostSinglePageBuilder.php';

include_once LIBPATH.'admin/AdminViewElement.php';
include_once LIBPATH.'BodyViewElement.php';

include_once LIBPATH.'static_classes/SQLConn.php';
include_once LIBPATH.'utility/UniversalConnect.php';
include_once LIBPATH.'static_classes/GrabPageURL.php';
include_once LIBPATH.'static_classes/GrabParentPageURL.php';


include_once LIBPATH.'BlogReader.php';
include_once LIBPATH.'BlogPageReader.php';
include_once LIBPATH.'DivElement.php';
include_once LIBPATH.'TagElement.php';
include_once LIBPATH.'HeadElement.php';
include_once LIBPATH.'BlogReaderSingle.php';
include_once LIBPATH.'utility/LoginFormElement.php';
include_once LIBPATH.'utility/CreateUserFormElement.php';
include_once LIBPATH.'utility/UserManager.php';
include_once LIBPATH.'abstract/NavElement.php';
include_once LIBPATH.'admin/AdminNavElement.php';
include_once LIBPATH.'userinterface/FormElementInput.php';
include_once LIBPATH.'utility/LogoutFormElement.php';
include_once LIBPATH.'BlogFormElement.php';
include_once LIBPATH.'BlogEditorFormElement.php';
include_once LIBPATH.'URLCheckFormElement.php';
include_once LIBPATH.'DeleteBlogPostFormElement.php';
include_once LIBPATH.'interfaces/iInteractionDelegate.php';
include_once LIBPATH.'abstract/InteractionDelegate.php';
include_once LIBPATH.'userinterface/InteractionElement.php';
include_once LIBPATH.'interfaces/iFormInput.php';
include_once LIBPATH.'userinterface/FormInput.php';
include_once LIBPATH.'userinterface/TextareaFormInput.php';


//test
include_once LIBPATH.'test/TestInteractionElement.php';
include_once LIBPATH.'test/TestInteractionDelegate.php';