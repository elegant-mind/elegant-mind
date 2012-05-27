<?php
/**
 * MODX language File
 *
 * @author modxcms.cz
 * @package MODX
 * @subpackage installer_translations
 * @version 1.5
 * @updated 2010-12-11
 * 
 * Filename:       /install/lang/czech.inc.php
 * Language:       Czech
 * Encoding:       utf-8
 */

$_lang["agree_to_terms"] = 'Souhlasím s licenčními podmínkami a chci pokračovat v instalaci';
$_lang["alert_database_test_connection"] = 'Musíte vytvořit nebo zadat již existující databázi!';
$_lang["alert_database_test_connection_failed"] = 'Otestování vybrané databáze se nezdařilo!';
$_lang["alert_enter_adminconfirm"] = 'Heslo administrátora a ověření hesla nesouhlasí!';
$_lang["alert_enter_adminlogin"] = 'Musíte zadat uživatelské jméno pro výchozího administrátora!';
$_lang["alert_enter_adminpassword"] = 'Musíte zadat heslo pro výchozího administrátora!';
$_lang["alert_enter_database_name"] = 'Musíte zadat název databáze!';
$_lang["alert_enter_host"] = 'Musíte zadat hostitele databáze!';
$_lang["alert_enter_login"] = 'Musíte zadat uživatelské jméno do databáze!';
$_lang["alert_server_test_connection"] = 'Musíte otestovat připojení k serveru!';
$_lang["alert_server_test_connection_failed"] = 'Test spojení se serverem se nezdařil!';
$_lang["alert_table_prefixes"] = 'Prefix tabulky musí začínat písmenem!';
$_lang["all"] = 'Vše';
$_lang["and_try_again"] = ' a zkuste to znovu. Potřebujete-li pomoci vyřešit problém';
$_lang["and_try_again_plural"] = ' a zkuste to znovu. Potřebujete-li pomoci vyřešit problémy';
$_lang["begin"] = 'Začít';
$_lang["btnback_value"] = 'Zpět';
$_lang["btnclose_value"] = 'Zavřít';
$_lang["btnnext_value"] = 'Další';
$_lang["cant_write_config_file"] = 'MODx nemohl zapsat konfigurační soubor. Následující obsah vložte do souboru ';
$_lang["cant_write_config_file_note"] = 'Až bude tento obsah uložen v souboru, můžete se přihlásit do MODx správce obsahu na adrese AdresaVasichStranek.cz/manager/.';
$_lang["checkbox_select_options"] = 'Možnosti výběru zaškrtávacích polí:';
$_lang["checking_if_cache_exist"] = 'Kontrola existence adresářů <span class="mono">/assets/cache</span> a <span class="mono">/assets/cache/rss</span>: ';
$_lang["checking_if_cache_file_writable"] = 'Kontrola zda lze zapisovat do souboru <span class="mono">/assets/cache/siteCache.idx.php</span>: ';
$_lang["checking_if_cache_file2_writable"] = 'Kontrola zda lze zapisovat do souboru <span class="mono">/assets/cache/sitePublishing.idx.php</span>: ';
$_lang["checking_if_cache_writable"] = 'Kontrola zda lze zapisovat do adresářů <span class="mono">/assets/cache</span> a <span class="mono">/assets/cache/rss</span>: ';
$_lang["checking_if_config_exist_and_writable"] = 'Kontrola zda existuje soubor <span class="mono">/manager/includes/config.inc.php</span> a lze do něj zapisovat: ';
$_lang["checking_if_export_exists"] = 'Kontrola existence adresáře <span class="mono">/assets/export</span>: ';
$_lang["checking_if_export_writable"] = 'Kontrola zda lze zapisovat do adresáře <span class="mono">/assets/export</span>: ';
$_lang["checking_if_images_exist"] = 'Kontrola zda existují adresáře <span class="mono">/assets/images</span>, <span class="mono">/assets/files</span>, <span class="mono">/assets/flash</span> a <span class="mono">/assets/media</span>: ';
$_lang["checking_if_images_writable"] = 'Kontrola zda lze zapisovat do adresářů <span class="mono">/assets/images</span>, <span class="mono">/assets/files</span>, <span class="mono">/assets/flash</span> a <span class="mono">/assets/media</span>: ';
$_lang["checking_mysql_strict_mode"] = 'Kontrola MySQL strict sql_mode: ';
$_lang["checking_mysql_version"] = 'Kontrola verze MySQL: ';
$_lang["checking_php_version"] = 'Kontrola verze PHP: ';
$_lang["checking_registerglobals"] = 'Kontrola zda je nastavení Register_Globals nastaveno na Off: ';
$_lang["checking_registerglobals_note"] = 'Díky tomtuto nastavení je Váš portál mnohem více náchylný k hackerským útokům typu Cross Site Scripting (XSS). Měli by jste pohovořit se svým poskytovatelem hostingu a zjistit co je možné udělat k deaktivaci tohoto nastavení.'; //Look at changing this to provide a solution.
$_lang["checking_sessions"] = 'Kontrola zda jsou správně nakonfigurovány session: ';
$_lang["checking_table_prefix"] = 'Kontrola prefixů tabulek `';
$_lang["chunks"] = 'Chunky';
$_lang["config_permissions_note"] = 'V nových instalacích Linux/Unix vytvořte prázdná soubor s názvem <span class="mono">config.inc.php</span> v adresáři <span class="mono">/manager/includes/</span> s atributy 0666.';
$_lang["connection_screen_collation"] = 'Porovnání:';
$_lang["connection_screen_connection_method"] = 'Způsob připojení:';
$_lang["connection_screen_database_connection_information"] = 'Informace o databázi';
$_lang["connection_screen_database_connection_note"] = 'Zadejte název databáze, kterou chcete použít nebo kterou chcete vytvořit pro tuto instalaci systému MODx. Pokud databáze neexistuje pokusí se ji instalátor vytvořit. Toto se nemusí povést v závislosti na nastavení MySQL nebo na uživatelských právech pro vaši doménu/instalaci.';
$_lang["connection_screen_database_host"] = 'Hostitel databáze:';
$_lang["connection_screen_database_info"] = 'Databázové informace';
$_lang["connection_screen_database_login"] = 'Uživatelské jméno do databáze:';
$_lang["connection_screen_database_name"] = 'Název databáze:';
$_lang["connection_screen_database_pass"] = 'Heslo do databáze:';
$_lang["connection_screen_database_test_connection"] = 'Vytvořit nebo otestovat zvolenou databázi.';
$_lang["connection_screen_default_admin_email"] = 'Email administrátora:';
$_lang["connection_screen_default_admin_login"] = 'Uživatelské jméno administrátora:';
$_lang["connection_screen_default_admin_note"] = 'Nyní je třeba, aby jste zadali údaje pro výchozí administrátorský účet. Vyplněte uživatelské jméno a heslo, které si zapamatujte. Tyto údaje budete potřebovat po skončení instalace pro přístup do administračního rozhraní.';
$_lang["connection_screen_default_admin_password"] = 'Heslo administrátora:';
$_lang["connection_screen_default_admin_password_confirm"] = 'Ověření hesla:';
$_lang["connection_screen_default_admin_user"] = 'Výchozí účet administrátora';
$_lang["connection_screen_defaults"] = 'Nastavení výchozího administrátora';
$_lang["connection_screen_server_connection_information"] = 'Údaje pro připojení a přihlášení k databázi';
$_lang["connection_screen_server_connection_note"] = 'Před otestováním databáze zadejte hostitele (název serveru nebo IP adresu), uživatelské jméno a heslo.';
$_lang["connection_screen_server_test_connection"] = 'Otestovat připojení k databázi a zobrazit porovnání.';
$_lang["connection_screen_table_prefix"] = 'Prefix tabulek:';
$_lang["creating_database_connection"] = 'Vytvářím připojení k databázi: ';
$_lang["database_alerts"] = 'Upozornění databáze!';
$_lang["database_connection_failed"] = 'Připojení k databázi se nezdařilo!';
$_lang["database_connection_failed_note"] = 'Zkontrolujte přihlašovací údaje k databázi a zkuste to znovu.';
$_lang["database_use_failed"] = 'Nelze vybrat tuto databázi!';
$_lang["database_use_failed_note"] = 'Zkontrolujte oprávnění daného uživatele pro tuto databázi a zkuste to znovu.';
$_lang["default_language"] = 'Výchozí jazyk správce obsahu';
$_lang["default_language_description"] = 'Toto bude výchozí jazyk, který bude použit v MODx správci obsahu.';
$_lang["during_execution_of_sql"] = ' při provádění SQL ';
$_lang["encoding"] = 'utf-8'; //charset encoding for html header
$_lang["error"] = 'chyba';
$_lang["errors"] = 'chyby';
$_lang["failed"] = 'SELHALO!';
$_lang["help"] = 'Nápověda!';
$_lang["help_link"] = 'http://www.modxcms.cz/forums/';
$_lang["help_title"] = 'Pomoc při instalaci využitím MODx diskuse';
$_lang["iagree_box"] = 'Souhlasím s podmínkami <a href="../assets/docs/license.txt" target="_blank">MODx licence</a>. Pro překlady GPL verze 2 licence, navštivte prosím <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0-translations.html" target="_blank">stránku GNU Operating System</a>.';
$_lang["install"] = 'Instalovat';
$_lang["install_overwrite"] = 'Instalovat/Přepsat';
$_lang["install_results"] = 'Výsledky instalace';
$_lang["install_update"] = 'Instalovat/Aktualizovat';
$_lang["installation_error_occured"] = 'Během instalace došlo k následujícím chybám';
$_lang["installation_install_new_copy"] = 'Instalace nové kopie ';
$_lang["installation_install_new_note"] = 'Pamatujte, že tato volba může přepsat data ve Vaší databázi.';
$_lang["installation_mode"] = 'Typ instalace';
$_lang["installation_new_installation"] = 'Nová instalace';
$_lang["installation_note"] = '<strong>Poznámka:</strong> Po přihlášení do správce obsahu byste měli nejdříve upravit a uložit konfiguraci systému v menu <strong>Nástroje</strong> -> Konfigurace systému.';
$_lang["installation_successful"] = 'Instalace byla úspěšná!';
$_lang["installation_upgrade_advanced"] = 'Rozšířená aktualizace';
$_lang["installation_upgrade_advanced_note"] = 'Pro pokročilé správce nebo při přechodu na server s jiným kódováním znaků databáze. <strong>Je třeba znát celý název databáze, uživatelské jméno, heslo a detaily připojení/porovnávání.</strong>';
$_lang["installation_upgrade_existing"] = 'Aktualizace existující instalace';
$_lang["installation_upgrade_existing_note"] = 'Aktualizace Vašich současných souborů a databáze.';
$_lang["installed"] = 'Nainstalováno';
$_lang["installing_demo_site"] = 'Instalovat ukázkový portál: ';
$_lang["language_code"] = 'cs';	// for html element e.g. <html xml:lang="en" lang="en">
$_lang["loading"] = 'Načítám...';
$_lang["modules"] = 'Moduly';
$_lang["modx_footer1"] = '&copy; 2005-2011 the <a href="http://www.modxcms.com/" target="_blank" style="color: green; text-decoration:underline">MODx</a> Content Management Framework (CMF) projekt. Všechna práva vyhrazena. MODx je licencován pod GNU GPL.';
$_lang["modx_footer2"] = 'MODx je free software. Doporučujeme Vám být kreativní a používat MODx jak jen uznáte za vhodné. Pouze se ujistěte, že pokud uděláte nějaké změny a budete chtít upravený MODx distribuovat dál, musí být zdrojové kódy volně přístupné!!';
$_lang["modx_install"] = 'MODx &raquo; Instalace';
$_lang["modx_requires_php"] = ', a MODx vyžaduje PHP 4.2.0 nebo novější';
$_lang["mysql_5051"] = ' Verze MySQL serveru je 5.0.51!';
$_lang["mysql_5051_warning"] = 'Jsou známy problémy s MySQL 5.0.51. Je doporučeno, abyste před pokračováním aktualizovali.';
$_lang["mysql_version_is"] = ' Verze Vaší MySQL je: ';
$_lang["no"] = 'Ne';
$_lang["none"] = 'Žádný';
$_lang["not_found"] = 'nenalezen';
$_lang["ok"] = 'OK!';
$_lang["optional_items"] = 'Volitelné položky';
$_lang["optional_items_note"] = 'Vyberte si položky, které chcete při instalaci nainstalovat/aktualizovat a klikněte na "Instalovat":';
$_lang["php_security_notice"] = '<legend>Bezpečnostní oznámení</legend><p>Dokud bude běžet MODx na verzi PHP (%s), nedoporučujeme vám MODx v této verzi používat. Vaše verze PHP je zranitelná mnoha bezpečnostními dírami. Aktualizujte PHP na verzi 4.3.11 nebo novější, které obsahují záplaty těchto děr. Dopuručujeme Vám aktualizovat na tuto verzi pro zvýšení bezpečnosti Vašich vlastních webových stránek.</p>';
$_lang["please_correct_error"] = '. Opravte chybu';
$_lang["please_correct_errors"] = '.Opravte chyby';
$_lang["plugins"] = 'Pluginy';
$_lang["preinstall_validation"] = 'Před-instalační kontrola';
$_lang["recommend_setting_change_title"] = 'Doporučené změny konfigurace';
$_lang["recommend_setting_change_validate_referer_confirmation"] = 'Změna nastavení: <em>Ověřit hlavičky HTTP_REFERER?</em>';
$_lang["recommend_setting_change_validate_referer_description"] = 'Na portálu není nastaveno ověřování hlaviček HTTP_REFERER pří přístupu do správce obsahu. Důrazně doporučujeme aktivovat toto nastavení, které vede ke snížení rizika útoků CSRF (Cross Site Request Forgery).';
$_lang["remove_install_folder_auto"] = 'Odstranit z portálu adresář instalátoru a jeho soubory <br />&nbsp;(Tato operace vyžaduje práva pro mazání přidělená adresáři instalátoru).';
$_lang["remove_install_folder_manual"] = 'Před tím než se přihlásíte do správce obsahu nezapomeňte odstranit adresář "<b>instalátoru</b>".';
$_lang["retry"] = 'Znovu';
$_lang["running_database_updates"] = 'Probíhající aktualizace databáze: ';
$_lang["sample_web_site"] = 'Ukázkový portál';
$_lang["sample_web_site_note"] = 'Berte na vědomí, že tato možnost <b>přepíše</b> existující dokumenty a zdroje.';
$_lang["session_problem"] = 'Byl detekován problém se session na Vašem serveru. Proberte tento problém se svým administrátorem serveru.';
$_lang["session_problem_try_again"] = 'Zkusit znovu?'; 
$_lang["setup_cannot_continue"] = 'Bohužel, instalátor nemůže pokračovat vzhledem k výše uvedeným ';
$_lang["setup_couldnt_install"] = 'MODx instalátor nemůže instalovat/změnit některé tabulky ve zvolené databázi.';
$_lang["setup_database"] = 'Instalátor se nyní pokusí nastavit databázi:<br />';
$_lang["setup_database_create_connection"] = 'Vytvářím připojení k databázi: ';
$_lang["setup_database_create_connection_failed"] = 'Připojení k databázi se nezdařilo!';
$_lang["setup_database_create_connection_failed_note"] = 'Zkontrolujte si údaje pro přihlášení k databázi a zkuste to znovu.';
$_lang["setup_database_creating_tables"] = 'Vytvářím databázové tabulky: ';
$_lang["setup_database_creation"] = 'Vytvářím databázi `';
$_lang["setup_database_creation_failed"] = 'Vytvoření databáze se nezdařilo!';
$_lang["setup_database_creation_failed_note"] = ' - Instalátor nemohl vytvořit databázi!';
$_lang["setup_database_creation_failed_note2"] = 'Instalátor nemohl vytvořit databázi a ani nenalezl existující databázi s tímto názvem. Pravděpodobně je to proto, že bezpečnostní politika Vašeho poskytovatele hostingu nepovoluje vytváření databáze externími skripty. Vytvořte databázi dle postupu poskytovatele hostingu a spusťte instalátor znovu.';
$_lang["setup_database_selection"] = 'Vybírám databázi `';
$_lang["setup_database_selection_failed"] = 'Vybrání databáze se nezdařilo...';
$_lang["setup_database_selection_failed_note"] = 'Databáze neexistuje. Instalátor se ji pokusí vytvořit.';
$_lang["snippets"] = 'Snippety';
$_lang["some_tables_not_updated"] = 'Některé tabulky nebyly aktualizovány. To může být způsobeno předchozími úpravami těchto tabulek.';
$_lang["status_checking_database"] = 'Kontrola databáze: ';
$_lang["status_connecting"] = ' Připojení k hostiteli: ';
$_lang["status_failed"] = 'nezdařilo se!';
$_lang["status_failed_could_not_create_database"] = 'nezdařilo se - nelze vytvořit databázi';
$_lang["status_failed_database_collation_does_not_match"] = 'nezdařilo se - nesoulad porovnání databáze; použijte SET NAMES nebo vyberte %s';
$_lang["status_failed_table_prefix_already_in_use"] = 'nezdařilo se - zvolený prefix tabulek se již používá!';
$_lang["status_passed"] = 'v pořádku - databáze vybrána';
$_lang["status_passed_database_created"] = 'v pořádku - databáze vytvořena';
$_lang["status_passed_server"] = 'v pořádku - porovnání je dostupné';
$_lang["strict_mode"] = ' MySQL  strict sql_mode je aktivní!';
$_lang["strict_mode_error"] = 'Určité vlastnosti systému MODx nemusí fungovat správně jestliže je STRICT_TRANS_TABLES sql_mode neaktivní. MySQL mód lze změnit úpravou souboru "my.cnf" nebo kontaktujte administrátora serveru.';
$_lang["summary_setup_check"] = 'Instalátor provedl řadu kontrol, které je nutné provést před spuštěním instalátoru.';
$_lang["system_configuration"] = 'Konfigurace systému';
$_lang["system_configuration_validate_referer_description"] = 'Nastavení <strong>Ověřit hlavičky HTTP_REFERER</strong> je doporučeno a může ochránit Váš portál před útoky CSRF, ale na některých serverech může zapříčinit nedostupnost MODx správce obsahu.';
$_lang["table_prefix_already_inuse"] = ' - Prefix tabulek se již v této databázi používá!';
$_lang["table_prefix_already_inuse_note"] = 'Instalátor nemohl provést instalaci do vybrané databáze, neboť ta již obsahuje tabulky s tímto prefixem. Vyberte nový prefix tabulek a spusťte instalátor znovu.';
$_lang["table_prefix_not_exist"] = ' - Tabulky s daným prefixem v této databázi neexistují!';
$_lang["table_prefix_not_exist_note"] = 'Instalátor nemohl provést instalaci do vybrané databáze, neboť neobsahuje tabulky se zadaným prefixem. Vyberte existující prefix tabulek a spusťte instalátor znovu.';
$_lang["templates"] = 'Šablony';
$_lang["to_log_into_content_manager"] = 'Pro přihlášení do správce obsahu (manager/index.php) klikněte na tlačítko "Zavřít".';
$_lang["toggle"] = 'Přepnutí';
$_lang['tvs'] = 'Template Variables';
$_lang["unable_install_chunk"] = 'Nepodařilo se nainstalovat chunk. Soubor';
$_lang["unable_install_module"] = 'Nepodařilo se nainstalovat modul.  Soubor';
$_lang["unable_install_plugin"] = 'Nepodařilo se nainstalovat plugin.  Soubor';
$_lang["unable_install_snippet"] = 'Nepodařilo se nainstalovat snippet.  Soubor';
$_lang["unable_install_template"] = 'Nepodařilo se nainstalovat šablonu.  Soubor';
$_lang["upgrade_note"] = '<strong>Poznámka:</strong> Před tím než začnete procházet web by jste se měl přihlásit do MODx správce obsahu pod administrátorským účtem a zkontrolovat Konfiguraci systému.';
$_lang["upgraded"] = 'Aktualizováno';
$_lang["validate_referer_title"] = 'Ověřit hlavičky HTTP_REFERER?';
$_lang["visit_forum"] = ', navštivte <a href="http://www.modxcms.com/forums/" target="_blank">MODx diskusi</a>.';
$_lang["warning"] = 'VAROVÁNÍ!';
$_lang["welcome_message_start"] = 'Nejdříve si vyberte typ instalace:';
$_lang["welcome_message_text"] = 'Tento program Vás provede zbytkem instalace.';
$_lang["welcome_message_welcome"] = 'Vítejte v instalačním programu systému MODx.';
$_lang["writing_config_file"] = 'Zapisuji konfigurační soubor: ';
$_lang["yes"] = 'Ano';
$_lang["you_running_php"] = ' - Váš server běží na PHP ';
?>