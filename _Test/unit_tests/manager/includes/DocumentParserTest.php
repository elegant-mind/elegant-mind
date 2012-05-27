<?php

require_once dirname(__FILE__) . '/../../../../manager/includes/document.parser.class.inc.php';

/**
 * Test class for DocumentParser.
 * Generated by PHPUnit
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    DocumentParserTest
 * @package MODX
 * @subpackage unitTests
 * @license LGPL
 */
class DocumentParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @var DocumentParser
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $_SERVER['REQUEST_URI'] = '';
        $GLOBALS['database_type'] = 'mysql';
        
        $this->object = new DocumentParser;
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;        
    } // tearDown

    /**
     * @covers DocumentParser::loadExtension
     */
    public function testLoadExtension() {
        $this->assertTrue($this->object->loadExtension(DocumentParser::EXTENSION_DBAPI));
        
        //$this->assertTrue(is_a($this->object->db, 'DBAPI'));
    } // testLoadExtension

    /**
     * @covers DocumentParser::getMicroTime
     */
    public function testGetMicroTime() {
        $this->assertTrue(is_float($this->object->getMicroTime()));
    } // testGetMicroTime

    /**
     * @covers DocumentParser::sendRedirect
     * @todo Implement testSendRedirect().
     */
    public function testSendRedirect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::sendForward
     * @todo Implement testSendForward().
     */
    public function testSendForward() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::sendErrorPage
     * @todo Implement testSendErrorPage().
     */
    public function testSendErrorPage() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::sendUnauthorizedPage
     * @todo Implement testSendUnauthorizedPage().
     */
    public function testSendUnauthorizedPage() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::dbConnect
     * @todo Implement testDbConnect().
     */
    public function testDbConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::dbQuery
     * @todo Implement testDbQuery().
     */
    public function testDbQuery() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::recordCount
     * @todo Implement testRecordCount().
     */
    public function testRecordCount() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::fetchRow
     * @todo Implement testFetchRow().
     */
    public function testFetchRow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::affectedRows
     * @todo Implement testAffectedRows().
     */
    public function testAffectedRows() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::insertId
     * @todo Implement testInsertId().
     */
    public function testInsertId() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::dbClose
     * @todo Implement testDbClose().
     */
    public function testDbClose() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getSettings
     * @todo Implement testGetSettings().
     */
    public function testGetSettings() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentMethod
     * @todo Implement testGetDocumentMethod().
     */
    public function testGetDocumentMethod() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentIdentifier
     * @todo Implement testGetDocumentIdentifier().
     */
    public function testGetDocumentIdentifier() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::checkSession
     * @todo Implement testCheckSession().
     */
    public function testCheckSession() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::checkPreview
     * @todo Implement testCheckPreview().
     */
    public function testCheckPreview() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::checkSiteStatus
     * @todo Implement testCheckSiteStatus().
     */
    public function testCheckSiteStatus() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::cleanDocumentIdentifier
     * @todo Implement testCleanDocumentIdentifier().
     */
    public function testCleanDocumentIdentifier() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::checkCache
     * @todo Implement testCheckCache().
     */
    public function testCheckCache() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::outputContent
     * @todo Implement testOutputContent().
     */
    public function testOutputContent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::checkPublishStatus
     * @todo Implement testCheckPublishStatus().
     */
    public function testCheckPublishStatus() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::postProcess
     * @todo Implement testPostProcess().
     */
    public function testPostProcess() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::mergeDocumentMETATags
     * @todo Implement testMergeDocumentMETATags().
     */
    public function testMergeDocumentMETATags() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::mergeDocumentContent
     * @todo Implement testMergeDocumentContent().
     */
    public function testMergeDocumentContent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::mergeSettingsContent
     * @todo Implement testMergeSettingsContent().
     */
    public function testMergeSettingsContent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::mergeChunkContent
     * @todo Implement testMergeChunkContent().
     */
    public function testMergeChunkContent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::mergePlaceholderContent
     * @todo Implement testMergePlaceholderContent().
     */
    public function testMergePlaceholderContent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::evalPlugin
     * @todo Implement testEvalPlugin().
     */
    public function testEvalPlugin() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::evalSnippet
     * @todo Implement testEvalSnippet().
     */
    public function testEvalSnippet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::evalSnippets
     * @todo Implement testEvalSnippets().
     */
    public function testEvalSnippets() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::makeFriendlyURL
     * @todo Implement testMakeFriendlyURL().
     */
    public function testMakeFriendlyURL() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::rewriteUrls
     * @todo Implement testRewriteUrls().
     */
    public function testRewriteUrls() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentObject
     * @todo Implement testGetDocumentObject().
     */
    public function testGetDocumentObject() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::parseDocumentSource
     * @todo Implement testParseDocumentSource().
     */
    public function testParseDocumentSource() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::executeParser
     * @todo Implement testExecuteParser().
     */
    public function testExecuteParser() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::prepareResponse
     * @todo Implement testPrepareResponse().
     */
    public function testPrepareResponse() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getParentIds
     * @todo Implement testGetParentIds().
     */
    public function testGetParentIds() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getChildIds
     * @todo Implement testGetChildIds().
     */
    public function testGetChildIds() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::webAlert
     * @todo Implement testWebAlert().
     */
    public function testWebAlert() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::hasPermission
     * @todo Implement testHasPermission().
     */
    public function testHasPermission() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::logEvent
     * @todo Implement testLogEvent().
     */
    public function testLogEvent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::isBackend
     * @todo Implement testIsBackend().
     */
    public function testIsBackend() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::isFrontend
     * @todo Implement testIsFrontend().
     */
    public function testIsFrontend() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getAllChildren
     * @todo Implement testGetAllChildren().
     */
    public function testGetAllChildren() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getActiveChildren
     * @todo Implement testGetActiveChildren().
     */
    public function testGetActiveChildren() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentChildren
     * @todo Implement testGetDocumentChildren().
     */
    public function testGetDocumentChildren() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocuments
     * @todo Implement testGetDocuments().
     */
    public function testGetDocuments() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocument
     * @todo Implement testGetDocument().
     */
    public function testGetDocument() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getPageInfo
     * @todo Implement testGetPageInfo().
     */
    public function testGetPageInfo() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getParent
     * @todo Implement testGetParent().
     */
    public function testGetParent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getSnippetId
     * @todo Implement testGetSnippetId().
     */
    public function testGetSnippetId() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getSnippetName
     * @todo Implement testGetSnippetName().
     */
    public function testGetSnippetName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::clearCache
     * @todo Implement testClearCache().
     */
    public function testClearCache() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::makeUrl
     * @todo Implement testMakeUrl().
     */
    public function testMakeUrl() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getConfig
     * @todo Implement testGetConfig().
     */
    public function testGetConfig() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getVersionData
     * @todo Implement testGetVersionData().
     */
    public function testGetVersionData() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::makeList
     * @todo Implement testMakeList().
     */
    public function testMakeList() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::userLoggedIn
     * @todo Implement testUserLoggedIn().
     */
    public function testUserLoggedIn() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getKeywords
     * @todo Implement testGetKeywords().
     */
    public function testGetKeywords() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getMETATags
     * @todo Implement testGetMETATags().
     */
    public function testGetMETATags() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::runSnippet
     * @todo Implement testRunSnippet().
     */
    public function testRunSnippet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getChunk
     * @todo Implement testGetChunk().
     */
    public function testGetChunk() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::putChunk
     * @todo Implement testPutChunk().
     */
    public function testPutChunk() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::parseChunk
     * @todo Implement testParseChunk().
     */
    public function testParseChunk() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getUserData
     * @todo Implement testGetUserData().
     */
    public function testGetUserData() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::toDateFormat
     * @todo Implement testToDateFormat().
     */
    public function testToDateFormat() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::toTimeStamp
     * @todo Implement testToTimeStamp().
     */
    public function testToTimeStamp() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentChildrenTVars
     * @todo Implement testGetDocumentChildrenTVars().
     */
    public function testGetDocumentChildrenTVars() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocumentChildrenTVarOutput
     * @todo Implement testGetDocumentChildrenTVarOutput().
     */
    public function testGetDocumentChildrenTVarOutput() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getTemplateVar
     * @todo Implement testGetTemplateVar().
     */
    public function testGetTemplateVar() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getTemplateVars
     * @todo Implement testGetTemplateVars().
     */
    public function testGetTemplateVars() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getTemplateVarOutput
     * @todo Implement testGetTemplateVarOutput().
     */
    public function testGetTemplateVarOutput() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getFullTableName
     * @todo Implement testGetFullTableName().
     */
    public function testGetFullTableName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getPlaceholder
     * @todo Implement testGetPlaceholder().
     */
    public function testGetPlaceholder() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::setPlaceholder
     * @todo Implement testSetPlaceholder().
     */
    public function testSetPlaceholder() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::toPlaceholders
     * @todo Implement testToPlaceholders().
     */
    public function testToPlaceholders() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::toPlaceholder
     * @todo Implement testToPlaceholder().
     */
    public function testToPlaceholder() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getManagerPath
     * @todo Implement testGetManagerPath().
     */
    public function testGetManagerPath() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getCachePath
     * @todo Implement testGetCachePath().
     */
    public function testGetCachePath() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::sendAlert
     * @todo Implement testSendAlert().
     */
    public function testSendAlert() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::insideManager
     * @todo Implement testInsideManager().
     */
    public function testInsideManager() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getLoginUserID
     * @todo Implement testGetLoginUserID().
     */
    public function testGetLoginUserID() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getLoginUserName
     * @todo Implement testGetLoginUserName().
     */
    public function testGetLoginUserName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getLoginUserType
     * @todo Implement testGetLoginUserType().
     */
    public function testGetLoginUserType() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getUserInfo
     * @todo Implement testGetUserInfo().
     */
    public function testGetUserInfo() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getWebUserInfo
     * @todo Implement testGetWebUserInfo().
     */
    public function testGetWebUserInfo() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getUserDocGroups
     * @todo Implement testGetUserDocGroups().
     */
    public function testGetUserDocGroups() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getDocGroups
     * @todo Implement testGetDocGroups().
     */
    public function testGetDocGroups() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::changeWebUserPassword
     * @todo Implement testChangeWebUserPassword().
     */
    public function testChangeWebUserPassword() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::changePassword
     * @todo Implement testChangePassword().
     */
    public function testChangePassword() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::isMemberOfWebGroup
     * @todo Implement testIsMemberOfWebGroup().
     */
    public function testIsMemberOfWebGroup() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::regClientCSS
     * @todo Implement testRegClientCSS().
     */
    public function testRegClientCSS() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::regClientStartupScript
     * @todo Implement testRegClientStartupScript().
     */
    public function testRegClientStartupScript() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::regClientScript
     * @todo Implement testRegClientScript().
     */
    public function testRegClientScript() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::regClientStartupHTMLBlock
     * @todo Implement testRegClientStartupHTMLBlock().
     */
    public function testRegClientStartupHTMLBlock() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::regClientHTMLBlock
     * @todo Implement testRegClientHTMLBlock().
     */
    public function testRegClientHTMLBlock() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::stripTags
     * @todo Implement testStripTags().
     */
    public function testStripTags() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::addEventListener
     * @todo Implement testAddEventListener().
     */
    public function testAddEventListener() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::removeEventListener
     * @todo Implement testRemoveEventListener().
     */
    public function testRemoveEventListener() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::removeAllEventListener
     * @todo Implement testRemoveAllEventListener().
     */
    public function testRemoveAllEventListener() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::invokeEvent
     * @todo Implement testInvokeEvent().
     */
    public function testInvokeEvent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::parseProperties
     * @todo Implement testParseProperties().
     */
    public function testParseProperties() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getIntTableRows
     * @todo Implement testGetIntTableRows().
     */
    public function testGetIntTableRows() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::putIntTableRow
     * @todo Implement testPutIntTableRow().
     */
    public function testPutIntTableRow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::updIntTableRow
     * @todo Implement testUpdIntTableRow().
     */
    public function testUpdIntTableRow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getExtTableRows
     * @todo Implement testGetExtTableRows().
     */
    public function testGetExtTableRows() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::putExtTableRow
     * @todo Implement testPutExtTableRow().
     */
    public function testPutExtTableRow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::updExtTableRow
     * @todo Implement testUpdExtTableRow().
     */
    public function testUpdExtTableRow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::dbExtConnect
     * @todo Implement testDbExtConnect().
     */
    public function testDbExtConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getFormVars
     * @todo Implement testGetFormVars().
     */
    public function testGetFormVars() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::phpError
     * @todo Implement testPhpError().
     */
    public function testPhpError() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::messageQuit
     * @todo Implement testMessageQuit().
     */
    public function testMessageQuit() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getRegisteredClientScripts
     * @todo Implement testGetRegisteredClientScripts().
     */
    public function testGetRegisteredClientScripts() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::getRegisteredClientStartupScripts
     * @todo Implement testGetRegisteredClientStartupScripts().
     */
    public function testGetRegisteredClientStartupScripts() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers DocumentParser::stripAlias
     * @todo Implement testStripAlias().
     */
    public function testStripAlias() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

} // DocumentParser
