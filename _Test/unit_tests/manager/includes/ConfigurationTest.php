<?php

require_once dirname(__FILE__) . '/../../../../manager/includes/configuration.class.inc.php';

/**
 * Test class for Configuration.
 * Generated by PHPUnit
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    DocumentParserTest
 * @package MODX
 * @subpackage unitTests
 * @license LGPL
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Configuration
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $iniFile = '../../manager/includes/' . Configuration::CONFIG_INI_FILE;
        
        // Create a copy of the current ini file
        if (is_writable($iniFile)) {
            copy($iniFile, $iniFile . 'backup');
        }
        
        $this->object = Configuration::getInstance();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
        
        $iniFile = '../../manager/includes/' . Configuration::CONFIG_INI_FILE;
        
        // Restore the original ini file
        if (is_writable($iniFile)) {
            unlink($iniFile);
            copy($iniFile . 'backup', $iniFile);
            unlink($iniFile . 'backup');
        }
    } // tearDown

    /**
     * @covers Configuration::getInstance
     */
    public function testGetInstance() {
        $result = Configuration::getInstance();
        
        $this->assertTrue(is_a($result, 'Configuration'));
    } // testGetInstance

    /**
     * @covers Configuration::getProperty
     */
    public function testGetProperty() {
        $this->assertEquals('mysql', $this->object->getProperty(Configuration::PROPERTY_DATABASE_TYPE));
        $this->assertEquals('localhost', $this->object->getProperty(Configuration::PROPERTY_DATABASE_SERVER));
    } // testGetProperty

    /**
     * @covers Configuration::isWritable
     */
    public function testIsWritable() {
        $this->assertTrue($this->object->isWritable());
    } // testIsWritable

    /**
     * @covers Configuration::writeProperty
     */
    public function testWriteProperty() {
        $oldValue = $this->object->getProperty(Configuration::PROPERTY_DATABASE_TYPE);
        
        $value = 'postgresql';
        
        $this->assertTrue($this->object->writeProperty(Configuration::PROPERTY_DATABASE_TYPE, $value));
        
        $this->assertEquals($value, $this->object->getProperty(Configuration::PROPERTY_DATABASE_TYPE));
        
        $this->assertTrue($this->object->writeProperty(Configuration::PROPERTY_DATABASE_TYPE, $oldValue));
        
        $this->assertEquals($oldValue, $this->object->getProperty(Configuration::PROPERTY_DATABASE_TYPE));
    } // testWriteProperty

} // ConfigurationTest