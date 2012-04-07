-- MODx Database Script for New/Upgrade Installations
-- MODx was created By Raymond Irving - Nov 2004
--
-- Each sql command is separated by double lines \n\n


CREATE TABLE "{PREFIX}active_users" (
  "internalKey" integer NOT NULL default 0,
  "username" varchar(50) NOT NULL default '',
  "lasthit" bigint NOT NULL default 0,
  "id" bigint default NULL,
  "action" varchar(10) NOT NULL default '',
  "ip" varchar(20) NOT NULL default '',
  PRIMARY KEY  ("internalKey")
);
COMMENT ON TABLE "{PREFIX}active_users" IS 'Contains data about active users.';


CREATE TABLE "{PREFIX}categories" (
  "id" serial NOT NULL,
  "category" varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}active_users" IS 'Categories to be used snippets,tv,chunks, etc';


CREATE TABLE "{PREFIX}document_groups" (
  "id" bigserial NOT NULL,
  "document_group" bigint NOT NULL default 0,
  "document" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
CREATE INDEX "document_groups_document" ON "{PREFIX}document_groups"("document");
CREATE INDEX "document_groups_document_group" ON "{PREFIX}document_groups"("document_group");
COMMENT ON TABLE "{PREFIX}active_users" IS 'Contains data used for access permissions.';

CREATE TABLE "{PREFIX}documentgroup_names" (
  "id" bigserial NOT NULL,
  "name" varchar(255) NOT NULL default '',
  "private_memgroup" smallint DEFAULT 0,
  "private_webgroup" smallint DEFAULT 0,
  PRIMARY KEY  ("id"),
  CONSTRAINT "documentgroup_names_name" UNIQUE ("name")
);
COMMENT ON TABLE "{PREFIX}documentgroup_names" IS 'Contains data used for access permissions.';
COMMENT ON COLUMN "{PREFIX}documentgroup_names"."private_memgroup" IS 'determine whether the document group is private to manager users';
COMMENT ON COLUMN "{PREFIX}documentgroup_names"."private_webgroup" IS 'determines whether the document is private to web users';


CREATE TABLE "{PREFIX}event_log" (
  "id" serial NOT NULL,
  "eventid" integer DEFAULT 0,
  "createdon" integer NOT NULL DEFAULT 0,
  "type" smallint NOT NULL DEFAULT 1,
  "user" integer NOT NULL DEFAULT 0,
  "usertype" smallint NOT NULL DEFAULT 0,
  "source" varchar(50) NOT NULL DEFAULT '',
  "description" text,
  PRIMARY KEY("id")
);
CREATE INDEX "event_log_user" ON "{PREFIX}event_log"("user");
COMMENT ON TABLE "{PREFIX}event_log" IS 'Stores event and error logs';
COMMENT ON COLUMN "{PREFIX}event_log"."type" IS '1- information, 2 - warning, 3- error';
COMMENT ON COLUMN "{PREFIX}event_log"."user" IS 'link to user table';
COMMENT ON COLUMN "{PREFIX}event_log"."usertype" IS '0 - manager, 1 - web';

CREATE TABLE "{PREFIX}keyword_xref" (
  "content_id" integer NOT NULL default 0,
  "keyword_id" integer NOT NULL default 0
);
CREATE INDEX "keyword_xref_content_id" ON "{PREFIX}keyword_xref"("content_id");
CREATE INDEX "keyword_xref_keyword_id" ON "{PREFIX}keyword_xref"("keyword_id");
COMMENT ON TABLE "{PREFIX}keyword_xref" IS 'Cross reference bewteen keywords and content';


CREATE TABLE "{PREFIX}manager_log" (
  "id" bigserial NOT NULL,
  "bigint" bigint NOT NULL default 0,
  "internalKey" bigint NOT NULL default 0,
  "username" varchar(255) default NULL,
  "action" bigint NOT NULL default 0,
  "itemid" varchar(10) default 0,
  "itemname" varchar(255) default NULL,
  "message" varchar(255) NOT NULL default '',
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}manager_log" IS 'Contains a record of user interaction.';


CREATE TABLE "{PREFIX}manager_users" (
  "id" bigserial NOT NULL,
  "username" varchar(100) NOT NULL default '',
  "password" varchar(100) NOT NULL default '',
  PRIMARY KEY  ("id"),
  CONSTRAINT "manager_users_username" UNIQUE ("username")
);
COMMENT ON TABLE "{PREFIX}manager_users" IS 'Contains login information for backend users.';


CREATE TABLE "{PREFIX}member_groups" (
  "id" bigserial NOT NULL,
  "user_group" bigint NOT NULL default 0,
  "member" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX "ix_group_member" ON "{PREFIX}member_groups"("user_group","member");
COMMENT ON TABLE "{PREFIX}member_groups" IS 'Contains data used for access permissions.';


CREATE TABLE "{PREFIX}membergroup_access" (
  "id" bigserial NOT NULL,
  "membergroup" bigint NOT NULL default 0,
  "documentgroup" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}membergroup_access" IS 'Contains data used for access permissions.';


CREATE TABLE "{PREFIX}membergroup_names" (
  "id" bigserial NOT NULL,
  "name" varchar(255) NOT NULL default '',
  PRIMARY KEY  ("id"),
  CONSTRAINT "membergroup_names_name" UNIQUE ("name")
);
COMMENT ON TABLE "{PREFIX}membergroup_names" IS 'Contains data used for access permissions.';


CREATE TABLE "{PREFIX}site_content" (
  "id" bigserial NOT NULL,
  "type" varchar(20) NOT NULL default 'document',
  "contentType" varchar(50) NOT NULL default 'text/html',
  "pagetitle" varchar(255) NOT NULL default '',
  "longtitle" varchar(255) NOT NULL default '',
  "description" varchar(255) NOT NULL default '',
  "alias" varchar(255) default '',
  "link_attributes" varchar(255) NOT NULL default '',
  "published" smallint NOT NULL default 0,
  "pub_date" bigint NOT NULL default 0,
  "unpub_date" bigint NOT NULL default 0,
  "parent" bigint NOT NULL default 0,
  "isfolder" smallint NOT NULL default 0,
  "introtext" text,
  "content" text,
  "richtext" smallint NOT NULL default '1',
  "template" bigint NOT NULL default '1',
  "menuindex" bigint NOT NULL default 0,
  "searchable" smallint NOT NULL default '1',
  "cacheable" smallint NOT NULL default '1',
  "createdby" bigint NOT NULL default 0,
  "createdon" bigint NOT NULL default 0,
  "editedby" bigint NOT NULL default 0,
  "editedon" bigint NOT NULL default 0,
  "deleted" smallint NOT NULL default 0,
  "deletedon" bigint NOT NULL default 0,
  "deletedby" bigint NOT NULL default 0,
  "publishedon" bigint NOT NULL default 0,
  "publishedby" bigint NOT NULL default 0,
  "menutitle" varchar(255) NOT NULL DEFAULT '',
  "donthit" smallint NOT NULL default 0,
  "haskeywords" smallint NOT NULL default 0,
  "hasmetatags" smallint NOT NULL default 0,
  "privateweb" smallint NOT NULL default 0,
  "privatemgr" smallint NOT NULL default 0,
  "content_dispo" smallint NOT NULL default 0,
  "hidemenu" smallint NOT NULL DEFAULT 0,
  PRIMARY KEY  ("id")
);
CREATE INDEX "site_content_id" ON "{PREFIX}site_content"("id");
CREATE INDEX "site_content_parent" ON "{PREFIX}site_content"("parent");
CREATE INDEX "site_content_alias" ON "{PREFIX}site_content"("alias");
COMMENT ON TABLE "{PREFIX}site_content" IS 'Contains the site document tree.';
COMMENT ON COLUMN "{PREFIX}site_content"."introtext" IS 'Used to provide quick summary of the document';
COMMENT ON COLUMN "{PREFIX}site_content"."menutitle" IS 'Menu title';
COMMENT ON COLUMN "{PREFIX}site_content"."donthit" IS 'Disable page hit count';
COMMENT ON COLUMN "{PREFIX}site_content"."haskeywords" IS 'has links to keywords';
COMMENT ON COLUMN "{PREFIX}site_content"."hasmetatags" IS 'has links to meta tags';
COMMENT ON COLUMN "{PREFIX}site_content"."privateweb" IS 'Private web document';
COMMENT ON COLUMN "{PREFIX}site_content"."privatemgr" IS 'Private manager document';
COMMENT ON COLUMN "{PREFIX}site_content"."content_dispo" IS '0-inline, 1-attachment';
COMMENT ON COLUMN "{PREFIX}site_content"."hidemenu" IS 'Hide document from menu';

CREATE TABLE "{PREFIX}site_content_metatags" (
  "content_id" integer NOT NULL default 0,
  "metatag_id" integer NOT NULL default 0
);
CREATE INDEX "site_content_metatags_content_id" ON "{PREFIX}site_content_metatags"("content_id");
CREATE INDEX "site_content_metatags_metatag_id" ON "{PREFIX}site_content_metatags"("metatag_id");
COMMENT ON TABLE "{PREFIX}site_content_metatags" IS 'Reference table between meta tags and content';


CREATE TABLE "{PREFIX}site_htmlsnippets" (
  "id" bigserial NOT NULL,
  "name" varchar(50) NOT NULL default '',
  "description" varchar(255) NOT NULL default 'Chunk',
  "editor_type" integer NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "cache_type"  smallint NOT NULL default 0,
  "snippet" text,
  "locked" smallint NOT NULL default 0,
  PRIMARY KEY  ("id")
) ;
COMMENT ON TABLE "{PREFIX}site_htmlsnippets" IS 'Contains the site chunks.';
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."cache_type" IS 'Cache option';


CREATE TABLE "{PREFIX}site_keywords" (
  "id" serial,
  "keyword" varchar(40) NOT NULL default '',
  PRIMARY KEY  ("id"),
  CONSTRAINT "site_keywords_keyword" UNIQUE ("keyword")
);
COMMENT ON TABLE "{PREFIX}site_keywords" IS 'Site keyword list';


CREATE TABLE "{PREFIX}site_metatags" (
  "id" serial NOT NULL,
  "name" varchar(50) NOT NULL DEFAULT '',
  "tag" varchar(50) NOT NULL DEFAULT '',
  "tagvalue" varchar(255) NOT NULL DEFAULT '',
  "http_equiv" smallint NOT NULL DEFAULT 0,
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}site_metatags" IS 'Site meta tags';
COMMENT ON COLUMN "{PREFIX}site_metatags"."tag" IS 'tag name';
COMMENT ON COLUMN "{PREFIX}site_metatags"."http_equiv" IS '1 - use http_equiv tag style, 0 - use name';


CREATE TABLE "{PREFIX}site_modules" (
  "id" serial NOT NULL,
  "name" varchar(50) NOT NULL DEFAULT '',
  "description" varchar(255) NOT NULL DEFAULT 0,
  "editor_type" integer NOT NULL DEFAULT 0,
  "disabled" smallint NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "wrap" smallint NOT NULL DEFAULT 0,
  "locked" smallint NOT NULL default 0,
  "icon" varchar(255) NOT NULL DEFAULT '',
  "enable_resource" smallint NOT NULL DEFAULT 0,
  "resourcefile" varchar(255) NOT NULL DEFAULT '',
  "createdon" integer NOT NULL DEFAULT 0,
  "editedon" integer NOT NULL DEFAULT 0,
  "guid" varchar(32) NOT NULL DEFAULT '',
  "enable_sharedparams" smallint NOT NULL DEFAULT 0,
  "properties" text,
  "modulecode" text,
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}site_modules" IS 'Site Modules';
COMMENT ON COLUMN "{PREFIX}site_modules"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_modules"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_modules"."icon" IS 'url to module icon';
COMMENT ON COLUMN "{PREFIX}site_modules"."enable_resource" IS 'enables the resource file feature';
COMMENT ON COLUMN "{PREFIX}site_modules"."resourcefile" IS 'a physical link to a resource file';
COMMENT ON COLUMN "{PREFIX}site_modules"."guid" IS 'globally unique identifier';
COMMENT ON COLUMN "{PREFIX}site_modules"."modulecode" IS 'module boot up code';


CREATE TABLE "{PREFIX}site_module_depobj" (
  "id" serial NOT NULL,
  "module" integer NOT NULL DEFAULT 0,
  "resource" integer NOT NULL DEFAULT 0,
  "type" integer NOT NULL DEFAULT 0,
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}site_module_depobj" IS 'Module Dependencies';
COMMENT ON COLUMN "{PREFIX}site_module_depobj"."type" IS '10-chunks, 20-docs, 30-plugins, 40-snips, 50-tpls, 60-tvs';


CREATE TABLE "{PREFIX}site_module_access" (
  "id" serial NOT NULL,
  "module" integer NOT NULL DEFAULT 0,
  "usergroup" integer NOT NULL DEFAULT 0,
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}site_module_access" IS 'Module users group access permission';


CREATE TABLE "{PREFIX}site_plugins" (
  "id" bigserial NOT NULL,
  "name" varchar(50) NOT NULL default '',
  "description" varchar(255) NOT NULL default 'Plugin',
  "editor_type" integer NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "cache_type" smallint NOT NULL default 0,
  "plugincode" text,
  "locked" smallint NOT NULL default 0,
  "properties" text,
  "disabled" smallint NOT NULL DEFAULT 0,
  "moduleguid" varchar(32) NOT NULL default '',
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}site_plugins" IS 'Contains the site plugins.';
COMMENT ON COLUMN "{PREFIX}site_plugins"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_plugins"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_plugins"."cache_type" IS 'Cache option';
COMMENT ON COLUMN "{PREFIX}site_plugins"."properties" IS 'Default Properties';
COMMENT ON COLUMN "{PREFIX}site_plugins"."disabled" IS 'Disables the plugin';
COMMENT ON COLUMN "{PREFIX}site_plugins"."moduleguid" IS 'GUID of module from which to import shared parameters';


CREATE TABLE "{PREFIX}site_plugin_events" (
  "pluginid" bigint NOT NULL,
  "evtid" bigint NOT NULL default 0,
  "priority" bigint NOT NULL default 0
);
COMMENT ON TABLE "{PREFIX}site_plugin_events" IS 'Links to system events';
COMMENT ON COLUMN "{PREFIX}site_plugin_events"."priority" IS 'determines plugin run order';


CREATE TABLE "{PREFIX}site_snippets" (
  "id" bigserial NOT NULL,
  "name" varchar(50) NOT NULL default '',
  "description" varchar(255) NOT NULL default 'Snippet',
  "editor_type" integer NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "cache_type" smallint NOT NULL DEFAULT 0,
  "snippet" text,
  "locked" smallint NOT NULL default 0,
  "properties" text,
  "moduleguid" varchar(32) NOT NULL default '',
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}site_snippets" IS 'Contains the site snippets.';
COMMENT ON COLUMN "{PREFIX}site_snippets"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_snippets"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_snippets"."cache_type" IS 'Cache option';
COMMENT ON COLUMN "{PREFIX}site_snippets"."properties" IS 'Default Properties';
COMMENT ON COLUMN "{PREFIX}site_snippets"."moduleguid" IS 'GUID of module from which to import shared parameters';


CREATE TABLE "{PREFIX}site_templates" (
  "id" bigserial NOT NULL,
  "templatename" varchar(50) NOT NULL default '',
  "description" varchar(255) NOT NULL default 'Template',
  "editor_type" integer NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "icon" varchar(255) NOT NULL default '',
  "template_type" integer NOT NULL DEFAULT 0,
  "content" text,
  "locked" smallint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}site_templates" IS 'Contains the site templates.';
COMMENT ON COLUMN "{PREFIX}site_templates"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_templates"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_templates"."icon" IS 'url to icon file';
COMMENT ON COLUMN "{PREFIX}site_templates"."template_type" IS '0-page,1-content';


CREATE TABLE "{PREFIX}system_eventnames" (
  "id" bigserial NOT NULL,
  "name" varchar(50) NOT NULL default '',
  "service" smallint NOT NULL default 0,
  "groupname" varchar(20) NOT NULL default '',
  PRIMARY KEY("id")
);
COMMENT ON TABLE "{PREFIX}system_eventnames" IS 'System Event Names.';
COMMENT ON COLUMN "{PREFIX}system_eventnames"."service" IS 'System Service number';


CREATE TABLE "{PREFIX}system_settings" (
  "setting_name" varchar(50) NOT NULL default '',
  "setting_value" text,
  CONSTRAINT "system_settings_setting_name" UNIQUE ("setting_name")
);
COMMENT ON TABLE "{PREFIX}system_settings" IS 'Contains Content Manager settings.';


CREATE TABLE "{PREFIX}site_tmplvar_access" (
  "id" bigserial NOT NULL,
  "tmplvarid" bigint NOT NULL default 0,
  "documentgroup" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}site_tmplvar_access" IS 'Contains data used for template variable access permissions.';


CREATE TABLE "{PREFIX}site_tmplvar_contentvalues" (
  "id" serial,
  "tmplvarid" bigint NOT NULL default 0,
  "contentid" bigint NOT NULL default 0,
  "value" text,
  PRIMARY KEY  (id)
);
CREATE INDEX "site_tmplvar_contentvalues_tmplvarid" ON "{PREFIX}site_tmplvar_contentvalues"("tmplvarid");
CREATE INDEX "site_tmplvar_contentvalues_contentid" ON "{PREFIX}site_tmplvar_contentvalues"("contentid");
COMMENT ON TABLE "{PREFIX}site_tmplvar_contentvalues" IS 'Site Template Variables Content Values Link Table';
COMMENT ON COLUMN "{PREFIX}site_tmplvar_contentvalues"."tmplvarid" IS 'Template Variable id';
COMMENT ON COLUMN "{PREFIX}site_tmplvar_contentvalues"."contentid" IS 'Site Content Id';


CREATE TABLE "{PREFIX}site_tmplvar_templates" (
  "tmplvarid" bigint NOT NULL default 0,
  "templateid" integer NOT NULL default 0,
  "rank" integer NOT NULL default 0,
  PRIMARY KEY ("tmplvarid", "templateid")
);
COMMENT ON TABLE "{PREFIX}site_tmplvar_templates" IS 'Site Template Variables Templates Link Table';
COMMENT ON COLUMN "{PREFIX}site_tmplvar_templates"."tmplvarid" IS 'Template Variable id';


CREATE TABLE "{PREFIX}site_tmplvars" (
  "id" serial,
  "type" varchar(20) NOT NULL default '',
  "name" varchar(50) NOT NULL default '',
  "caption" varchar(80) NOT NULL default '',
  "description" varchar(255) NOT NULL default '',
  "editor_type" integer NOT NULL DEFAULT 0,
  "category" integer NOT NULL DEFAULT 0,
  "locked" smallint NOT NULL default 0,
  "elements" text,
  "rank" integer NOT NULL default 0,
  "display" varchar(20) NOT NULL default '',
  "display_params" text,
  "default_text" text,
  PRIMARY KEY  (id)
);
CREATE INDEX "site_tmplvars_indx_rank" ON "{PREFIX}site_tmplvars"("rank");
COMMENT ON TABLE "{PREFIX}site_tmplvars" IS 'Site Template Variables';
COMMENT ON COLUMN "{PREFIX}site_tmplvars"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_tmplvars"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_tmplvars"."display" IS 'Display Control';
COMMENT ON COLUMN "{PREFIX}site_tmplvars"."display_params" IS 'Display Control Properties';


CREATE TABLE "{PREFIX}user_attributes" (
  "id" bigserial NOT NULL,
  "internalKey" bigint NOT NULL default 0,
  "fullname" varchar(100) NOT NULL default '',
  "role" bigint NOT NULL default 0,
  "email" varchar(100) NOT NULL default '',
  "phone" varchar(100) NOT NULL default '',
  "mobilephone" varchar(100) NOT NULL default '',
  "blocked" smallint NOT NULL default 0,
  "blockeduntil" bigint,
  "blockedafter" bigint,
  "logincount" integer NOT NULL default 0,
  "lastlogin" bigint,
  "thislogin" bigint,
  "failedlogincount" integer NOT NULL default 0,
  "sessionid" varchar(100) NOT NULL default '',
  "dob" bigint NOT NULL DEFAULT 0,
  "gender" smallint NOT NULL DEFAULT 0,
  "country" varchar(5) NOT NULL default '',
  "state" varchar(25) NOT NULL default '',
  "zip" varchar(25) NOT NULL default '',
  "fax" varchar(100) NOT NULL default '',
  "photo" varchar(255) NOT NULL default '',
  "comment" varchar(255) NOT NULL default '',
  PRIMARY KEY  ("id")
);
CREATE INDEX "user_attributes_userid" ON "{PREFIX}user_attributes"("internalKey");
COMMENT ON TABLE "{PREFIX}user_attributes" IS 'Contains information about the backend users.';
COMMENT ON COLUMN "{PREFIX}user_attributes"."gender" IS '0 - unknown, 1 - Male 2 - female';
COMMENT ON COLUMN "{PREFIX}user_attributes"."photo" IS 'link to photo';
COMMENT ON COLUMN "{PREFIX}user_attributes"."comment" IS 'short comment';


CREATE TABLE "{PREFIX}user_messages" (
  "id" bigserial NOT NULL,
  "type" varchar(15) NOT NULL default '',
  "subject" varchar(60) NOT NULL default '',
  "message" text,
  "sender" bigint NOT NULL default 0,
  "recipient" bigint NOT NULL default 0,
  "private" smallint NOT NULL default 0,
  "postdate" bigint NOT NULL default 0,
  "messageread" smallint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}user_messages" IS 'Contains messages for the Content Manager messaging system.';


CREATE TABLE "{PREFIX}user_roles" (
  "id" bigserial NOT NULL,
  "name" varchar(50) NOT NULL default '',
  "description" varchar(255) NOT NULL default '',
  "frames" smallint NOT NULL default 0,
  "home" smallint NOT NULL default 0,
  "view_document" smallint NOT NULL default 0,
  "new_document" smallint NOT NULL default 0,
  "save_document" smallint NOT NULL default 0,
  "publish_document" smallint NOT NULL default 0,
  "delete_document" smallint NOT NULL default 0,
  "empty_trash" smallint NOT NULL default 0,
  "action_ok" smallint NOT NULL default 0,
  "logout" smallint NOT NULL default 0,
  "help" smallint NOT NULL default 0,
  "messages" smallint NOT NULL default 0,
  "new_user" smallint NOT NULL default 0,
  "edit_user" smallint NOT NULL default 0,
  "logs" smallint NOT NULL default 0,
  "edit_parser" smallint NOT NULL default 0,
  "save_parser" smallint NOT NULL default 0,
  "edit_template" smallint NOT NULL default 0,
  "settings" smallint NOT NULL default 0,
  "credits" smallint NOT NULL default 0,
  "new_template" smallint NOT NULL default 0,
  "save_template" smallint NOT NULL default 0,
  "delete_template" smallint NOT NULL default 0,
  "edit_snippet" smallint NOT NULL default 0,
  "new_snippet" smallint NOT NULL default 0,
  "save_snippet" smallint NOT NULL default 0,
  "delete_snippet" smallint NOT NULL default 0,
  "edit_chunk" smallint NOT NULL default 0,
  "new_chunk" smallint NOT NULL default 0,
  "save_chunk" smallint NOT NULL default 0,
  "delete_chunk" smallint NOT NULL default 0,
  "empty_cache" smallint NOT NULL default 0,
  "edit_document" smallint NOT NULL default 0,
  "change_password" smallint NOT NULL default 0,
  "error_dialog" smallint NOT NULL default 0,
  "about" smallint NOT NULL default 0,
  "file_manager" smallint NOT NULL default 0,
  "save_user" smallint NOT NULL default 0,
  "delete_user" smallint NOT NULL default 0,
  "save_password" smallint NOT NULL default 0,
  "edit_role" smallint NOT NULL default 0,
  "save_role" smallint NOT NULL default 0,
  "delete_role" smallint NOT NULL default 0,
  "new_role" smallint NOT NULL default 0,
  "access_permissions" smallint NOT NULL default 0,
  "bk_manager" smallint NOT NULL DEFAULT 0,
  "new_plugin" smallint NOT NULL DEFAULT 0,
  "edit_plugin" smallint NOT NULL DEFAULT 0,
  "save_plugin" smallint NOT NULL DEFAULT 0,
  "delete_plugin" smallint NOT NULL DEFAULT 0,
  "new_module" smallint NOT NULL DEFAULT 0,
  "edit_module" smallint NOT NULL DEFAULT 0,
  "save_module" smallint NOT NULL DEFAULT 0,
  "delete_module" smallint NOT NULL DEFAULT 0,
  "exec_module" smallint NOT NULL DEFAULT 0,
  "view_eventlog" smallint NOT NULL DEFAULT 0,
  "delete_eventlog" smallint NOT NULL DEFAULT 0,
  "manage_metatags" smallint NOT NULL DEFAULT 0,
  "edit_doc_metatags" smallint NOT NULL DEFAULT 0,
  "new_web_user" smallint NOT NULL default 0,
  "edit_web_user" smallint NOT NULL default 0,
  "save_web_user" smallint NOT NULL default 0,
  "delete_web_user" smallint NOT NULL default 0,
  "web_access_permissions" smallint NOT NULL default 0,
  "view_unpublished" smallint NOT NULL default 0,
  "import_static" smallint NOT NULL default 0,
  "export_static" smallint NOT NULL default 0,
  "remove_locks" smallint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}user_roles" IS 'Contains information describing the user roles.';
COMMENT ON COLUMN "{PREFIX}user_roles"."manage_metatags" IS 'manage site meta tags and keywords';
COMMENT ON COLUMN "{PREFIX}user_roles"."edit_doc_metatags" IS 'edit document meta tags and keywords';


CREATE TABLE "{PREFIX}user_settings" (
  "user" integer NOT NULL,
  "setting_name" varchar(50) NOT NULL default '',
  "setting_value" text
);
CREATE INDEX "user_settings_setting_name" ON "{PREFIX}user_settings"("setting_name");
CREATE INDEX "user_settings_user" ON "{PREFIX}user_settings"("user");
COMMENT ON TABLE "{PREFIX}user_settings" IS 'Contains backend user settings.';


CREATE TABLE "{PREFIX}web_groups" (
  "id" bigserial NOT NULL,
  "webgroup" bigint NOT NULL default 0,
  "webuser" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX "ix_group_user" ON "{PREFIX}web_groups" ("webgroup","webuser");
COMMENT ON TABLE "{PREFIX}web_groups" IS 'Contains data used for web access permissions.';


CREATE TABLE "{PREFIX}webgroup_access" (
  "id" bigserial NOT NULL,
  "webgroup" bigint NOT NULL default 0,
  "documentgroup" bigint NOT NULL default 0,
  PRIMARY KEY  ("id")
);
COMMENT ON TABLE "{PREFIX}webgroup_access" IS 'Contains data used for web access permissions.';


CREATE TABLE "{PREFIX}webgroup_names" (
  "id" bigserial NOT NULL,
  "name" varchar(255) NOT NULL default '',
  PRIMARY KEY  ("id"),
  CONSTRAINT "webgroup_namesname" UNIQUE ("name")
);
COMMENT ON TABLE "{PREFIX}webgroup_names" IS 'Contains data used for web access permissions.';


CREATE TABLE "{PREFIX}web_user_attributes" (
  "id" bigserial NOT NULL,
  "internalKey" bigint NOT NULL default 0,
  "fullname" varchar(100) NOT NULL default '',
  "role" bigint NOT NULL default 0,
  "email" varchar(100) NOT NULL default '',
  "phone" varchar(100) NOT NULL default '',
  "mobilephone" varchar(100) NOT NULL default '',
  "blocked" smallint NOT NULL default 0,
  "blockeduntil" bigint,
  "blockedafter" bigint,
  "logincount" integer NOT NULL default 0,
  "lastlogin" bigint,
  "thislogin" bigint,
  "failedlogincount" bigint NOT NULL default 0,
  "sessionid" varchar(100) NOT NULL default '',
  "dob" bigint NOT NULL DEFAULT 0,
  "gender" smallint NOT NULL DEFAULT 0,
  "country" varchar(5) NOT NULL default '',
  "state" varchar(25) NOT NULL default '',
  "zip" varchar(25) NOT NULL default '',
  "fax" varchar(100) NOT NULL default '',
  "photo" varchar(255) NOT NULL default '',
  "comment" varchar(255) NOT NULL default '',
  PRIMARY KEY  ("id")
);
CREATE INDEX "web_user_attributes_userid" ON "{PREFIX}web_user_attributes"("internalKey");
COMMENT ON TABLE "{PREFIX}web_user_attributes" IS 'Contains information for web users.';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."gender" IS '0 - unknown, 1 - Male 2 - female';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."photo" IS 'link to photo';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."comment" IS 'short comment';


CREATE TABLE "{PREFIX}web_users" (
  "id" bigserial NOT NULL,
  "username" varchar(100) NOT NULL default '',
  "password" varchar(100) NOT NULL default '',
  "cachepwd" varchar(100) NOT NULL default '',
  PRIMARY KEY  ("id"),
  CONSTRAINT "web_users_username" UNIQUE ("username")
);
COMMENT ON COLUMN "{PREFIX}web_users"."cachepwd" IS 'Store new unconfirmed password';


CREATE TABLE "{PREFIX}web_user_settings" (
  "webuser" integer NOT NULL,
  "setting_name" varchar(50) NOT NULL default '',
  "setting_value" text
);
CREATE INDEX "web_user_settings_setting_name" ON "{PREFIX}web_user_settings"("setting_name");
CREATE INDEX "web_user_settings_webuser" ON "{PREFIX}web_user_settings"("webuser");
COMMENT ON TABLE "{PREFIX}web_user_settings" IS 'Contains web user settings.';


-- upgrade-able[[ - This block of code will be executed during upgrades

-- For backward compatibilty with early versions
--::::::::::::::::::::::::::::::::::::::::::::::::::::::::::


ALTER TABLE "{PREFIX}web_users"
 ADD COLUMN "cachepwd" varchar(100) NOT NULL default '';
COMMENT ON COLUMN "{PREFIX}web_users"."cachepwd" IS 'Store new unconfirmed password';


ALTER TABLE "{PREFIX}site_tmplvars"
 ADD COLUMN "editor_type" integer NOT NULL DEFAULT 0,
 ADD COLUMN "category" integer NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_tmplvars"."editor_type" IS '0-plain text,1-rich text,2-code editor';


ALTER TABLE "{PREFIX}site_tmplvars"
 ALTER COLUMN "name" TYPE varchar(50);
ALTER TABLE "{PREFIX}site_tmplvars"
 ALTER COLUMN "name" SET DEFAULT'';
ALTER TABLE "{PREFIX}site_tmplvars"
 ALTER COLUMN "name" SET NOT NULL;


ALTER TABLE "{PREFIX}site_tmplvars"
 ADD INDEX "indx_rank"("rank");


ALTER TABLE "{PREFIX}site_content"
 ADD INDEX "aliasidx" (alias);


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "introtext" text;
COMMENT ON COLUMN "{PREFIX}site_content"."introtext" IS 'Used to provide quick summary of the document';


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "menutitle" varchar(255) NOT NULL default '',
 ADD COLUMN "donthit" smallint NOT NULL default 0,
 ADD COLUMN "haskeywords" smallint NOT NULL default 0,
 ADD COLUMN "hasmetatags" smallint NOT NULL default 0,
 ADD COLUMN "privateweb" smallint NOT NULL default 0,
 ADD COLUMN "privatemgr" smallint NOT NULL default 0;
COMMENT ON COLUMN "{PREFIX}site_content"."menutitle" IS 'Menu title';
COMMENT ON COLUMN "{PREFIX}site_content"."donthit" IS 'Disable page hit count';
COMMENT ON COLUMN "{PREFIX}site_content"."haskeywords" IS 'has links to keywords';
COMMENT ON COLUMN "{PREFIX}site_content"."hasmetatags" IS 'has links to meta tags';
COMMENT ON COLUMN "{PREFIX}site_content"."privateweb" IS 'Private web document';
COMMENT ON COLUMN "{PREFIX}site_content"."privatemgr" IS 'Private manager document';

ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "content_dispo" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_content"."content_dispo" IS '0-inline, 1-attachment';


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "hidemenu" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_content"."hidemenu" IS 'Hide document from menu';


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "publishedon" bigint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_content"."publishedon" IS 'Date the document was published';


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "publishedby" bigint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_content"."publishedby" IS 'ID of user who published the document';


ALTER TABLE "{PREFIX}site_plugins"
 ADD COLUMN "editor_type" integer NOT NULL DEFAULT 0,
 ADD COLUMN "category" integer NOT NULL DEFAULT 0,
 ADD COLUMN "cache_type" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_plugins"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_plugins"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_plugins"."cache_type" IS 'cache option';


ALTER TABLE "{PREFIX}site_plugins"
 ADD COLUMN "disabled" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_plugins"."disabled" IS 'Disables the plugin';


ALTER TABLE "{PREFIX}site_plugins"
 ADD COLUMN "moduleguid" varchar(32) NOT NULL DEFAULT '';
COMMENT ON COLUMN "{PREFIX}site_plugins"."moduleguid" IS 'GUID of module from which to import shared parameters';


ALTER TABLE "{PREFIX}site_htmlsnippets"
 ADD COLUMN "editor_type" integer NOT NULL DEFAULT 0,
 ADD COLUMN "category" integer NOT NULL DEFAULT 0,
 ADD COLUMN "cache_type" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_htmlsnippets"."cache_type" IS 'cache option';


ALTER TABLE "{PREFIX}site_snippets"
 ADD COLUMN "editor_type" integer NOT NULL DEFAULT 0,
 ADD COLUMN "category" integer NOT NULL DEFAULT 0,
 ADD COLUMN "cache_type" smallint NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_snippets"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_snippets"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_snippets"."cache_type" IS 'cache option';


ALTER TABLE "{PREFIX}site_snippets"
 ADD COLUMN "properties" varchar(255) NOT NULL default '';
COMMENT ON COLUMN "{PREFIX}site_snippets"."properties" IS 'Default Properties';


ALTER TABLE "{PREFIX}site_snippets"
 ADD COLUMN "moduleguid" varchar(32) NOT NULL default '';
COMMENT ON COLUMN "{PREFIX}site_snippets"."moduleguid" IS 'GUID of module from which to import shared parameters';


ALTER TABLE "{PREFIX}site_templates"
 ADD COLUMN "editor_type" integer NOT NULL DEFAULT 0,
 ADD COLUMN "category" integer NOT NULL DEFAULT 0,
 ADD COLUMN "icon" varchar(255) NOT NULL default '',
 ADD COLUMN "template_type" integer NOT NULL DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}site_templates"."editor_type" IS '0-plain text,1-rich text,2-code editor';
COMMENT ON COLUMN "{PREFIX}site_templates"."category" IS 'category id';
COMMENT ON COLUMN "{PREFIX}site_templates"."icon" IS 'url to icon file';
COMMENT ON COLUMN "{PREFIX}site_templates"."template_type" IS '0-page,1-content';


DROP INDEX "indx_doc_groups";


ALTER TABLE "{PREFIX}document_groups"
 ADD INDEX "document" ("document");


ALTER TABLE "{PREFIX}document_groups"
 ADD INDEX "document_group" ("document_group");


ALTER TABLE "{PREFIX}system_settings"
 ALTER COLUMN "setting_value" TYPE text;


ALTER TABLE "{PREFIX}site_plugins"
 ALTER COLUMN "properties" TYPE text;


ALTER TABLE "{PREFIX}site_snippets"
 ALTER COLUMN "properties" TYPE text;


ALTER TABLE "{PREFIX}system_eventnames"
 ADD COLUMN "groupname" varchar(20) NOT NULL default '';


ALTER TABLE "{PREFIX}documentgroup_names"
 ADD COLUMN "private_memgroup" smallint DEFAULT 0,
 ADD COLUMN "private_webgroup" smallint DEFAULT 0;
COMMENT ON COLUMN "{PREFIX}documentgroup_names"."private_memgroup" IS 'determine whether the document group is private to manager users';
COMMENT ON COLUMN "{PREFIX}documentgroup_names"."private_webgroup" IS 'determines whether the document is private to web users';


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "bk_manager" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "new_plugin" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "edit_plugin" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "save_plugin" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "delete_plugin" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "new_module" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "edit_module" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "save_module" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "delete_module" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "exec_module" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "view_eventlog" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "delete_eventlog" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "manage_metatags" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "edit_doc_metatags" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "new_web_user" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "edit_web_user" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "save_web_user" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "delete_web_user" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "web_access_permissions" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "view_unpublished" smallint NOT NULL DEFAULT 0;

ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "publish_document" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "new_chunk" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "edit_chunk" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "save_chunk" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "delete_chunk" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "import_static" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "export_static" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_roles"
  ADD COLUMN "empty_trash" smallint NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}user_attributes"
 ADD COLUMN "dob" integer NOT NULL DEFAULT 0,
 ADD COLUMN "gender" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "country" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "state" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "zip" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "fax" varchar(100) NOT NULL DEFAULT '',
 ADD COLUMN "blockedafter" integer NOT NULL DEFAULT 0,
 ADD COLUMN "photo" varchar(255) NOT NULL DEFAULT '',
 ADD COLUMN "comment" varchar(255) NOT NULL DEFAULT '';
COMMENT ON COLUMN "{PREFIX}user_attributes"."gender" IS '0 - unknown, 1 - Male 2 - female';
COMMENT ON COLUMN "{PREFIX}user_attributes"."photo" IS 'link to photo';
COMMENT ON COLUMN "{PREFIX}user_attributes"."comment" IS 'short comment';


ALTER TABLE "{PREFIX}web_users"
 ALTER COLUMN "username" TYPE varchar(100);
ALTER TABLE "{PREFIX}web_users"
  ALTER COLUMN "username" SET DEFAULT '';
ALTER TABLE "{PREFIX}web_users"
 ALTER COLUMN "username" SET NOT NULL;


ALTER TABLE "{PREFIX}web_user_attributes" 
ADD COLUMN "dob" integer NOT NULL DEFAULT 0,
 ADD COLUMN "gender" smallint NOT NULL DEFAULT 0,
 ADD COLUMN "country" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "state" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "zip" varchar(5) NOT NULL DEFAULT '',
 ADD COLUMN "fax" varchar(100) NOT NULL DEFAULT '',
 ADD COLUMN "blockedafter" integer NOT NULL DEFAULT 0,
 ADD COLUMN "photo" varchar(255) NOT NULL DEFAULT '',
 ADD COLUMN "comment" varchar(255) NOT NULL DEFAULT '';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."gender" IS '0 - unknown, 1 - Male 2 - female';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."photo" IS 'link to photo';
COMMENT ON COLUMN "{PREFIX}web_user_attributes"."comment" IS 'short comment';


ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "state" TYPE varchar(25);
ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "state" SET DEFAULT '';
ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "state" SET NOT NULL;
ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "zip" TYPE varchar(25);
ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "zip" SET DEFAULT '';
ALTER TABLE "{PREFIX}web_user_attributes" 
 ALTER COLUMN "zip" SET NOT NULL;


ALTER TABLE "{PREFIX}user_roles"
 ADD COLUMN "view_unpublished" smallint NOT NULL DEFAULT 0;

DROP INDEX "idx_tmplvarid";


DROP INDEX "idx_templateid";


ALTER TABLE "{PREFIX}site_tmplvar_templates"
 ADD PRIMARY KEY ( "tmplvarid" , "templateid" );


ALTER TABLE "{PREFIX}site_content"
  ALTER COLUMN "pagetitle" TYPE varchar(255), 
  ALTER COLUMN "pagetitle" SET DEFAULT '',
  ALTER COLUMN "pagetitle" SET NOT NULL,
  ALTER COLUMN "alias" TYPE varchar(255),
  ALTER COLUMN "alias" SET DEFAULT '',
  ALTER COLUMN "menutitle" TYPE varchar(255),
  ALTER COLUMN "menutitle" SET  DEFAULT '',
  ALTER COLUMN "menutitle" SET NOT NULL;
COMMENT ON COLUMN "{PREFIX}site_content"."menutitle" IS 'Menu title';


ALTER TABLE "{PREFIX}site_content"
 ADD COLUMN "link_attributes" varchar(255) NOT NULL DEFAULT '';
COMMENT ON COLUMN "{PREFIX}site_content"."link_attributes" IS 'Link attriubtes';


ALTER TABLE "{PREFIX}site_plugin_events"
 ADD COLUMN "priority" bigint NOT NULL default 0;
COMMENT ON COLUMN "{PREFIX}site_content"."priority" IS 'determines the run order of the plugin';


ALTER TABLE "{PREFIX}site_tmplvar_templates"
 ADD COLUMN "rank" integer NOT NULL DEFAULT 0;


ALTER TABLE "{PREFIX}manager_users"
 ALTER COLUMN "username" TYPE varchar(100),
 ALTER COLUMN "username" SET DEFAULT '',
 ALTER COLUMN "username" SET NOT NULL;


ALTER TABLE "{PREFIX}user_settings"
 ALTER COLUMN "setting_value" TYPE text;


ALTER TABLE "{PREFIX}web_user_settings"
 ALTER COLUMN "setting_value" TYPE text;


ALTER TABLE "{PREFIX}user_attributes"
 ALTER COLUMN "state" TYPE varchar(25),
 ALTER COLUMN "state" SET default '',
 ALTER COLUMN "state" SET NOT NULL,
 ALTER COLUMN "zip" TYPE varchar(25),
 ALTER COLUMN "zip" SET default '',
 ALTER COLUMN "zip" SET NOT NULL,
 ALTER COLUMN "comment" TYPE text;


ALTER TABLE "{PREFIX}site_metatags"
 ALTER COLUMN "name" TYPE varchar(50),
 ALTER COLUMN "name" SET default '',
 ALTER COLUMN "name" SET NOT NULL,
 ALTER COLUMN "tag" TYPE varchar(50),
 ALTER COLUMN "tag" SET default '',
 ALTER COLUMN "tag" SET NOT NULL,
 ALTER COLUMN "tagvalue" TYPE varchar(255),
 ALTER COLUMN "tagvalue" SET default '',
 ALTER COLUMN "tagvalue" SET NOT NULL;
COMMENT ON COLUMN "{PREFIX}site_content"."tag" IS 'tag name';


ALTER TABLE "{PREFIX}web_user_attributes"
 ALTER COLUMN "state" TYPE varchar(25),
 ALTER COLUMN "state" SET default '',
 ALTER COLUMN "state" SET NOT NULL,
 ALTER COLUMN "zip" TYPE varchar(25),
 ALTER COLUMN "zip" SET default '',
 ALTER COLUMN "zip" SET NOT NULL,
 ALTER COLUMN "comment" TYPE text;


ALTER TABLE "{PREFIX}user_roles"
  ADD COLUMN "remove_locks" smallint NOT NULL DEFAULT 0;


CREATE UNIQUE INDEX "ix_group_member" ON "{PREFIX}member_groups"("user_group","member");


CREATE UNIQUE INDEX "ix_group_user" ON "{PREFIX}web_groups"("webgroup","webuser");


-- Set the private manager group flag
UPDATE {PREFIX}documentgroup_names 
 SET dgn.private_memgroup = (mga.membergroup IS NOT NULL),
   dgn.private_webgroup = (wga.webgroup IS NOT NULL)
 FROM {PREFIX}documentgroup_names AS dgn
 LEFT OUTER JOIN {PREFIX}membergroup_access AS mga ON mga.documentgroup = dgn.id
 LEFT OUTER JOIN {PREFIX}webgroup_access AS wga ON wga.documentgroup = dgn.id;


UPDATE "{PREFIX}site_plugins"
 SET "disabled" = '1'
 WHERE "name" IN ('Bottom Button Bar');


UPDATE "{PREFIX}site_plugins"
 SET "disabled" = '1'
 WHERE "name" IN ('Inherit Parent Template');


UPDATE "{PREFIX}system_settings"
 SET "setting_value" = ''
 WHERE "setting_name" = 'settings_version';


UPDATE "{PREFIX}system_settings"
 SET "setting_value" = 0
 WHERE "setting_name" = 'validate_referer'
 AND "setting_value" = '00';


-- start related to --MODX-1321933


-- end related to --MODX-1321


-- ]]upgrade-able


-- Insert / Replace system records
--::::::::::::::::::::::::::::::::::::::::::::::::::::::::::


-- non-upgrade-able[[ - This block of code will not be executed during upgrades


-- Default Site Template

DELETE FROM "{PREFIX}site_templates"
 WHERE id = 3;
INSERT INTO "{PREFIX}site_templates" (id, templatename, description, editor_type, category, icon, template_type, content, locked)
 VALUES (3,'Minimal Template','Default minimal empty template (content returned only)',0,0,'',0,'[*content*]',0);


-- Default Site Documents

DELETE FROM "{PREFIX}site_content"
 WHERE id = 1;
INSERT INTO "{PREFIX}site_content"("id", "type", "contentType", "pagetitle", "longtitle", "description", "alias", "link_attributes", "published", "pub_date", "unpub_date", "parent", "isfolder", "introtext", "content", "richtext", "template", "menuindex", "searchable", "cacheable", "createdby", "createdon", "editedby", "editedon", "deleted", "deletedon", "deletedby", "publishedon", "publishedby", "menutitle", "donthit", "haskeywords", "hasmetatags", "privateweb", "privatemgr", "content_dispo", "hidemenu")
 VALUES (1,'document','text/html','MODx CMS Install Success','Welcome to the MODx Content Management System','','minimal-base','',1,0,0,0,0,'',E'<h3>Install Successful!</h3>\r\n<p>You have successfully installed MODx.</p>\r\n\r\n<h3>Getting Help</h3>\r\n<p>The <a href=\"http://modxcms.com/forums/\" target=\"_blank\">MODx Community</a> provides a great starting point to learn all things MODx, or you can also <a href=\b\b"http://modxcms.com/learn/it.html">see some great learning resources</a> (books, tutorials, blogs and screencasts).</p>\r\n<p>Welcome to MODx!</p>\r\n',1,3,0,1,1,1,1130304721,1,1130304927,0,0,0,1130304721,1,'Base Install',0,0,0,0,0,0,0);

DELETE FROM "{PREFIX}manager_users"
 WHERE "id" = 1;
INSERT INTO "{PREFIX}manager_users"("id", "username", "password")
 VALUES (1, '{ADMIN}', MD5('{ADMINPASS}'));


DELETE FROM "{PREFIX}user_attributes"
 WHERE "id" = 1;
INSERT INTO "{PREFIX}user_attributes"(id, internalKey, fullname, role, email, phone, mobilephone, blocked, blockeduntil, blockedafter, logincount, lastlogin, thislogin, failedlogincount, sessionid, dob, gender, country, state, zip, fax, photo, comment)
 VALUES (1, 1, 'Default admin account', 1, '{ADMINEMAIL}', '', '', 0, 0, 0, 0, 0, 0, 0, '', 0, 0, '', '', '', '', '', '');

DELETE FROM "{PREFIX}user_roles"
WHERE "id" IN (2, 3);
INSERT INTO "{PREFIX}user_roles"(id,name,description,frames,home,view_document,new_document,save_document,publish_document,delete_document,empty_trash,action_ok,logout,help,messages,new_user,edit_user,logs,edit_parser,save_parser,edit_template,settings,credits,new_template,save_template,delete_template,edit_snippet,new_snippet,save_snippet,delete_snippet,edit_chunk,new_chunk,save_chunk,delete_chunk,empty_cache,edit_document,change_password,error_dialog,about,file_manager,save_user,delete_user,save_password,edit_role,save_role,delete_role,new_role,access_permissions,bk_manager,new_plugin,edit_plugin,save_plugin,delete_plugin,new_module,edit_module,save_module,exec_module,delete_module,view_eventlog,delete_eventlog,manage_metatags,edit_doc_metatags,new_web_user,edit_web_user,save_web_user,delete_web_user,web_access_permissions,view_unpublished,import_static,export_static,remove_locks)
 VALUES(2,'Editor','Limited to managing content',1,1,1,1,1,1,1,0,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,1,0,1,0,1,1,1,1,1,1,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,1,0,0,1);
INSERT INTO "{PREFIX}user_roles"(id,name,description,frames,home,view_document,new_document,save_document,publish_document,delete_document,empty_trash,action_ok,logout,help,messages,new_user,edit_user,logs,edit_parser,save_parser,edit_template,settings,credits,new_template,save_template,delete_template,edit_snippet,new_snippet,save_snippet,delete_snippet,edit_chunk,new_chunk,save_chunk,delete_chunk,empty_cache,edit_document,change_password,error_dialog,about,file_manager,save_user,delete_user,save_password,edit_role,save_role,delete_role,new_role,access_permissions,bk_manager,new_plugin,edit_plugin,save_plugin,delete_plugin,new_module,edit_module,save_module,exec_module,delete_module,view_eventlog,delete_eventlog,manage_metatags,edit_doc_metatags,new_web_user,edit_web_user,save_web_user,delete_web_user,web_access_permissions,view_unpublished,import_static,export_static,remove_locks)
 VALUES(3,'Publisher',E'Editor with expanded permissions including manage users\r\n, update Elements and site settings',1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,0,1,1,1,1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,0,1,1,1,1,0,1,0,0,1);


-- ]]non-upgrade-able


-- Default Site Settings

TRUNCATE TABLE "{PREFIX}system_settings";
INSERT INTO "{PREFIX}system_settings"
(setting_name, setting_value) VALUES
('manager_theme','MODxCarbon'),
('settings_version',''),
('show_meta',0),
('server_offset_time',0),
('server_protocol','http'),
('manager_language','{MANAGERLANGUAGE}'),
('{PREFIX}charset','UTF-8'),
('site_name','My MODx Site'),
('site_start','1'),
('error_page','1'),
('unauthorized_page','1'),
('site_status','1'),
('site_unavailable_message','The site is currently unavailable'),
('track_visitors',0),
('top_howmany','10'),
('auto_template_logic','{AUTOTEMPLATELOGIC}'),
('default_template','3'),
('old_template',''),
('publish_default',0),
('cache_default','1'),
('search_default','1'),
('friendly_urls',0),
('friendly_url_prefix',''),
('friendly_url_suffix','.html'),
('friendly_alias_urls','1'),
('use_alias_path',0),
('use_udperms','1'),
('udperms_allowroot',0),
('failed_login_attempts','3'),
('blocked_minutes','60'),
('use_captcha',0),
('captcha_words','MODx,Access,Better,BitCode,Cache,Desc,Design,Excell,Enjoy,URLs,TechView,Gerald,Griff,Humphrey,Holiday,Intel,Integration,Joystick,Join(),Tattoo,Genetic,Light,Likeness,Marit,Maaike,Niche,Netherlands,Ordinance,Oscillo,Parser,Phusion,Query,Question,Regalia,Righteous,Snippet,Sentinel,Template,Thespian,Unity,Enterprise,Verily,Veri,Website,WideWeb,Yap,Yellow,Zebra,Zygote'),
('emailsender','{ADMINEMAIL}'),
('emailsubject','Your login details'),
('number_of_logs','100'),
('number_of_messages','30'),
('number_of_results','20'),
('use_editor','1'),
('use_browser','1'),
('rb_base_dir',''),
('rb_base_url',''),
('which_editor','TinyMCE'),
('fe_editor_lang','{MANAGERLANGUAGE}'),
('fck_editor_toolbar','standard'),
('fck_editor_autolang',0),
('editor_css_path',''),
('editor_css_selectors',''),
('strip_image_paths','1'),
('upload_images','bmp,ico,gif,jpeg,jpg,png,psd,tif,tiff'),
('upload_media','au,avi,mp3,mp4,mpeg,mpg,wav,wmv'),
('upload_flash','fla,flv,swf'),
('upload_files','aac,au,avi,css,cache,doc,docx,gz,gzip,htaccess,htm,html,js,mp3,mp4,mpeg,mpg,ods,odp,odt,pdf,ppt,pptx,rar,tar,tgz,txt,wav,wmv,xls,xlsx,xml,z,zip'),
('upload_maxsize','1048576'),
('new_file_permissions','0644'),
('new_folder_permissions','0755'),
('filemanager_path',''),
('theme_refresher',''),
('manager_layout','4'),
('custom_contenttype','application/rss+xml,application/pdf,application/vnd.ms-word,application/vnd.ms-excel,text/html,text/css,text/xml,text/javascript,text/plain'),
('auto_menuindex','1'),
('session.cookie.lifetime','604800'),
('mail_check_timeperiod','60'),
('manager_direction','ltr'),
('tinymce_editor_theme','editor'),
('tinymce_custom_plugins','style,advimage,advlink,searchreplace,print,contextmenu,paste,fullscreen,nonbreaking,xhtmlxtras,visualchars,media'),
('tinymce_custom_buttons1','undo,redo,selectall,separator,pastetext,pasteword,separator,search,replace,separator,nonbreaking,hr,charmap,separator,image,link,unlink,anchor,media,separator,cleanup,removeformat,separator,fullscreen,print,code,help'),
('tinymce_custom_buttons2','bold,italic,underline,strikethrough,sub,sup,separator,bullist,numlist,outdent,indent,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,styleselect,formatselect,separator,styleprops'),
('tree_show_protected', 0),
('rss_url_news', 'http://feeds.feedburner.com/modx-announce'),
('rss_url_security', 'http://feeds.feedburner.com/modxsecurity'),
('validate_referer', '1'),
('datepicker_offset','-10'),
('xhtml_urls','1'),
('allow_duplicate_alias',0),
('automatic_alias','1'),
('datetime_format','dd-mm-YYYY'),
('warning_visibility', '1'),
('remember_last_tab', 0);


DELETE FROM "{PREFIX}user_roles"
 WHERE "id" = 1;
INSERT INTO "{PREFIX}user_roles"(id,name,description,frames,home,view_document,new_document,save_document,publish_document,delete_document,empty_trash,action_ok,logout,help,messages,new_user,edit_user,logs,edit_parser,save_parser,edit_template,settings,credits,new_template,save_template,delete_template,edit_snippet,new_snippet,save_snippet,delete_snippet,edit_chunk,new_chunk,save_chunk,delete_chunk,empty_cache,edit_document,change_password,error_dialog,about,file_manager,save_user,delete_user,save_password,edit_role,save_role,delete_role,new_role,access_permissions,bk_manager,new_plugin,edit_plugin,save_plugin,delete_plugin,new_module,edit_module,save_module,exec_module,delete_module,view_eventlog,delete_eventlog,manage_metatags,edit_doc_metatags,new_web_user,edit_web_user,save_web_user,delete_web_user,web_access_permissions,view_unpublished,import_static,export_static,remove_locks)
 VALUES(1, 'Administrator', 'Site administrators have full access to all functions',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);


-- 1 - "Parser Service Events", 2 -  "Manager Access Events", 3 - "Web Access Service Events", 4 - "Cache Service Events", 5 - "Template Service Events", 6 - Custom Events

TRUNCATE TABLE "{PREFIX}system_eventnames";
INSERT INTO "{PREFIX}system_eventnames"
("id", "name", "service", "groupname") VALUES
('1','OnDocPublished','5',''),
('2','OnDocUnPublished','5',''),
('3','OnWebPagePrerender','5',''),
('4','OnWebLogin','3',''),
('5','OnBeforeWebLogout','3',''),
('6','OnWebLogout','3',''),
('7','OnWebSaveUser','3',''),
('8','OnWebDeleteUser','3',''),
('9','OnWebChangePassword','3',''),
('10','OnWebCreateGroup','3',''),
('11','OnManagerLogin','2',''),
('12','OnBeforeManagerLogout','2',''),
('13','OnManagerLogout','2',''),
('14','OnManagerSaveUser','2',''),
('15','OnManagerDeleteUser','2',''),
('16','OnManagerChangePassword','2',''),
('17','OnManagerCreateGroup','2',''),
('18','OnBeforeCacheUpdate','4',''),
('19','OnCacheUpdate','4',''),
('20','OnLoadWebPageCache','4',''),
('21','OnBeforeSaveWebPageCache','4',''),
('22','OnChunkFormPrerender','1','Chunks'),
('23','OnChunkFormRender','1','Chunks'),
('24','OnBeforeChunkFormSave','1','Chunks'),
('25','OnChunkFormSave','1','Chunks'),
('26','OnBeforeChunkFormDelete','1','Chunks'),
('27','OnChunkFormDelete','1','Chunks'),
('28','OnDocFormPrerender','1','Documents'),
('29','OnDocFormRender','1','Documents'),
('30','OnBeforeDocFormSave','1','Documents'),
('31','OnDocFormSave','1','Documents'),
('32','OnBeforeDocFormDelete','1','Documents'),
('33','OnDocFormDelete','1','Documents'),
('34','OnPluginFormPrerender','1','Plugins'),
('35','OnPluginFormRender','1','Plugins'),
('36','OnBeforePluginFormSave','1','Plugins'),
('37','OnPluginFormSave','1','Plugins'),
('38','OnBeforePluginFormDelete','1','Plugins'),
('39','OnPluginFormDelete','1','Plugins'),
('40','OnSnipFormPrerender','1','Snippets'),
('41','OnSnipFormRender','1','Snippets'),
('42','OnBeforeSnipFormSave','1','Snippets'),
('43','OnSnipFormSave','1','Snippets'),
('44','OnBeforeSnipFormDelete','1','Snippets'),
('45','OnSnipFormDelete','1','Snippets'),
('46','OnTempFormPrerender','1','Templates'),
('47','OnTempFormRender','1','Templates'),
('48','OnBeforeTempFormSave','1','Templates'),
('49','OnTempFormSave','1','Templates'),
('50','OnBeforeTempFormDelete','1','Templates'),
('51','OnTempFormDelete','1','Templates'),
('52','OnTVFormPrerender','1','Template Variables'),
('53','OnTVFormRender','1','Template Variables'),
('54','OnBeforeTVFormSave','1','Template Variables'),
('55','OnTVFormSave','1','Template Variables'),
('56','OnBeforeTVFormDelete','1','Template Variables'),
('57','OnTVFormDelete','1','Template Variables'),
('58','OnUserFormPrerender','1','Users'),
('59','OnUserFormRender','1','Users'),
('60','OnBeforeUserFormSave','1','Users'),
('61','OnUserFormSave','1','Users'),
('62','OnBeforeUserFormDelete','1','Users'),
('63','OnUserFormDelete','1','Users'),
('64','OnWUsrFormPrerender','1','Web Users'),
('65','OnWUsrFormRender','1','Web Users'),
('66','OnBeforeWUsrFormSave','1','Web Users'),
('67','OnWUsrFormSave','1','Web Users'),
('68','OnBeforeWUsrFormDelete','1','Web Users'),
('69','OnWUsrFormDelete','1','Web Users'),
('70','OnSiteRefresh','1',''),
('71','OnFileManagerUpload','1',''),
('72','OnModFormPrerender','1','Modules'),
('73','OnModFormRender','1','Modules'),
('74','OnBeforeModFormDelete','1','Modules'),
('75','OnModFormDelete','1','Modules'),
('76','OnBeforeModFormSave','1','Modules'),
('77','OnModFormSave','1','Modules'),
('78','OnBeforeWebLogin','3',''),
('79','OnWebAuthentication','3',''),
('80','OnBeforeManagerLogin','2',''),
('81','OnManagerAuthentication','2',''),
('82','OnSiteSettingsRender','1','System Settings'),
('83','OnFriendlyURLSettingsRender','1','System Settings'),
('84','OnUserSettingsRender','1','System Settings'),
('85','OnInterfaceSettingsRender','1','System Settings'),
('86','OnMiscSettingsRender','1','System Settings'),
('87','OnRichTextEditorRegister','1','RichText Editor'),
('88','OnRichTextEditorInit','1','RichText Editor'),
('89','OnManagerPageInit','2',''),
('90','OnWebPageInit','5',''),
('91','OnLoadWebDocument','5',''),
('92','OnParseDocument','5',''),
('93','OnManagerLoginFormRender','2',''),
('94','OnWebPageComplete','5',''),
('95','OnLogPageHit','5',''),
('96','OnBeforeManagerPageInit','2',''),
('97','OnBeforeEmptyTrash','1','Documents'),
('98','OnEmptyTrash','1','Documents'),
('99','OnManagerLoginFormPrerender','2',''),
('100','OnStripAlias','1','Documents'),
('200','OnCreateDocGroup','1','Documents'),
('201','OnManagerWelcomePrerender','2',''),
('202','OnManagerWelcomeHome','2',''),
('203','OnManagerWelcomeRender','2',''),
('204','OnBeforeDocDuplicate','1','Documents'),
('205','OnDocDuplicate','1','Documents'),
('206','OnManagerMainFrameHeaderHTMLBlock','2',''),
('999','OnPageUnauthorized','1',''),
('1000','OnPageNotFound','1','');


-- ^ I don't think we need more than 1000 built-in events. Custom events will start at 1001


-- Update System Tables
--::::::::::::::::::::::::::::::::::::::::::::::::::::::::::


UPDATE "{PREFIX}user_roles" SET
  bk_manager=1,
  new_plugin=1,
  edit_plugin=1,
  save_plugin=1,
  delete_plugin=1,
  new_module=1,
  edit_module=1,
  save_module=1,
  delete_module=1,
  exec_module=1,
  view_eventlog = 1,
  delete_eventlog = 1,
  manage_metatags = 1,
  edit_doc_metatags = 1,
  new_web_user = 1,
  edit_web_user = 1,
  save_web_user = 1,
  delete_web_user = 1,
  new_chunk = 1,
  edit_chunk = 1,
  save_chunk = 1,
  delete_chunk = 1,
  web_access_permissions = 1,
  view_unpublished = 1,
  publish_document = 1,
  import_static = 1,
  export_static = 1,
  empty_trash = 1,
  remove_locks = 1
  WHERE "id"=1;


-- Update any invalid Manager Themes in User Settings and reset the default theme


UPDATE "{PREFIX}user_settings" SET
  "setting_value"='MODxCarbon'
  WHERE "setting_name"='manager_theme';


DELETE FROM "{PREFIX}system_settings"
 WHERE "setting_name" = 'manager_theme';
INSERT INTO "{PREFIX}system_settings"("setting_name", "setting_value")
 VALUES ('manager_theme','MODxCarbon');