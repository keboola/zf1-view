<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Test class for Zend_View_Helper_HeadStyle.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class Zend_View_Helper_HeadStyleTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Zend_View_Helper_HeadStyle
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp(): void
    {
        $regKey = Zend_View_Helper_Placeholder_Registry::REGISTRY_KEY;
        if (Zend_Registry::isRegistered($regKey)) {
            $registry = Zend_Registry::getInstance();
            unset($registry[$regKey]);
        }
        $this->basePath = dirname(__FILE__) . '/_files/modules';
        $this->helper   = new Zend_View_Helper_HeadStyle();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->helper);
    }

    public function testNamespaceRegisteredInPlaceholderRegistryAfterInstantiation()
    {
        $registry = Zend_View_Helper_Placeholder_Registry::getRegistry();
        if ($registry->containerExists('Zend_View_Helper_HeadStyle')) {
            $registry->deleteContainer('Zend_View_Helper_HeadStyle');
        }
        $this->assertFalse($registry->containerExists('Zend_View_Helper_HeadStyle'));
        $helper = new Zend_View_Helper_HeadStyle();
        $this->assertTrue($registry->containerExists('Zend_View_Helper_HeadStyle'));
    }

    public function testHeadStyleReturnsObjectInstance()
    {
        $placeholder = $this->helper->headStyle();
        $this->assertInstanceOf(Zend_View_Helper_HeadStyle::class, $placeholder);
    }

    public function testAppendPrependAndSetThrowExceptionsWhenNonStyleValueProvided()
    {
        try {
            $this->helper->append('foo');
            $this->fail('Non-style value should not append');
        } catch (Zend_View_Exception $e) {
            $this->assertSame('Invalid value passed to append; please use appendStyle()', $e->getMessage());
        }
        try {
            $this->helper->offsetSet(5, 'foo');
            $this->fail('Non-style value should not offsetSet');
        } catch (Zend_View_Exception $e) {
            $this->assertSame('Invalid value passed to offsetSet; please use offsetSetStyle()', $e->getMessage());
        }
        try {
            $this->helper->prepend('foo');
            $this->fail('Non-style value should not prepend');
        } catch (Zend_View_Exception $e) {
            $this->assertSame('Invalid value passed to prepend; please use prependStyle()', $e->getMessage());
        }
        try {
            $this->helper->set('foo');
            $this->fail('Non-style value should not set');
        } catch (Zend_View_Exception $e) {
            $this->assertSame('Invalid value passed to set; please use setStyle()', $e->getMessage());
        }
    }

    public function testOverloadAppendStyleAppendsStyleToStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->appendStyle($string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));
            $item = $values[$i];

            $this->assertInstanceOf(stdClass::class, $item);
            $this->assertObjectHasProperty('content', $item);
            $this->assertObjectHasProperty('attributes', $item);
            $this->assertEquals($string, $item->content);
        }
    }

    public function testOverloadPrependStylePrependsStyleToStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->prependStyle($string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));
            $item = array_shift($values);

            $this->assertInstanceOf(stdClass::class, $item);
            $this->assertObjectHasProperty('content', $item);
            $this->assertObjectHasProperty('attributes', $item);
            $this->assertEquals($string, $item->content);
        }
    }

    public function testOverloadSetOversitesStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $this->helper->appendStyle($string);
            $string .= PHP_EOL . 'a {}';
        }
        $this->helper->setStyle($string);
        $values = $this->helper->getArrayCopy();
        $this->assertCount(1, $values);
        $item = array_shift($values);

        $this->assertInstanceOf(stdClass::class, $item);
        $this->assertObjectHasProperty('content', $item);
        $this->assertObjectHasProperty('attributes', $item);
        $this->assertEquals($string, $item->content);
    }

    public function testCanBuildStyleTagsWithAttributes()
    {
        $this->helper->setStyle('a {}', array(
            'lang'  => 'us_en',
            'title' => 'foo',
            'media' => 'projection',
            'dir'   => 'rtol',
            'bogus' => 'unused'
        ));
        $value = $this->helper->getValue();

        $this->assertObjectHasProperty('attributes', $value);
        $attributes = $value->attributes;

        $this->assertTrue(isset($attributes['lang']));
        $this->assertTrue(isset($attributes['title']));
        $this->assertTrue(isset($attributes['media']));
        $this->assertTrue(isset($attributes['dir']));
        $this->assertTrue(isset($attributes['bogus']));
        $this->assertEquals('us_en', $attributes['lang']);
        $this->assertEquals('foo', $attributes['title']);
        $this->assertEquals('projection', $attributes['media']);
        $this->assertEquals('rtol', $attributes['dir']);
        $this->assertEquals('unused', $attributes['bogus']);
    }

    public function testRenderedStyleTagsContainHtmlEscaping()
    {
        $this->helper->setStyle('a {}', array(
            'lang'  => 'us_en',
            'title' => 'foo',
            'media' => 'screen',
            'dir'   => 'rtol',
            'bogus' => 'unused'
        ));
        $value = $this->helper->toString();
        $this->assertStringContainsString('<!--' . PHP_EOL, $value);
        $this->assertStringContainsString(PHP_EOL . '-->', $value);
    }

    public function testRenderedStyleTagsContainsDefaultMedia()
    {
        $this->helper->setStyle('a {}', array(
        ));
        $value = $this->helper->toString();
        $this->assertMatchesRegularExpression('#<style [^>]*?media="screen"#', $value, $value);
    }

    /**
     * @group ZF-8056
     */
    public function testMediaAttributeCanHaveSpaceInCommaSeparatedString()
    {
        $this->helper->appendStyle('a { }', array('media' => 'screen, projection'));
        $string = $this->helper->toString();
        $this->assertStringContainsString('media="screen,projection"', $string);
    }

    public function testHeadStyleProxiesProperly()
    {
        $style1 = 'a {}';
        $style2 = 'a {}' . PHP_EOL . 'h1 {}';
        $style3 = 'a {}' . PHP_EOL . 'h2 {}';

        $this->helper->headStyle($style1, 'SET')
                     ->headStyle($style2, 'PREPEND')
                     ->headStyle($style3, 'APPEND');
        $this->assertCount(3, $this->helper);
        $values = $this->helper->getArrayCopy();
        $this->assertTrue((strstr($values[0]->content, $style2)) ? true : false);
        $this->assertTrue((strstr($values[1]->content, $style1)) ? true : false);
        $this->assertTrue((strstr($values[2]->content, $style3)) ? true : false);
    }

    public function testToStyleGeneratesValidHtml()
    {
        $style1 = 'a {}';
        $style2 = 'body {}' . PHP_EOL . 'h1 {}';
        $style3 = 'div {}' . PHP_EOL . 'li {}';

        $this->helper->headStyle($style1, 'SET')
                     ->headStyle($style2, 'PREPEND')
                     ->headStyle($style3, 'APPEND');
        $html = $this->helper->toString();
        $doc  = new DOMDocument;
        $dom  = $doc->loadHtml($html);
        $this->assertTrue(($dom !== false));

        $styles = substr_count($html, '<style type="text/css"');
        $this->assertEquals(3, $styles);
        $styles = substr_count($html, '</style>');
        $this->assertEquals(3, $styles);
        $this->assertStringContainsString($style3, $html);
        $this->assertStringContainsString($style2, $html);
        $this->assertStringContainsString($style1, $html);
    }

    public function testCapturingCapturesToObject()
    {
        $this->helper->captureStart();
        echo 'foobar';
        $this->helper->captureEnd();
        $values = $this->helper->getArrayCopy();
        $this->assertCount(1, $values);
        $item = array_shift($values);
        $this->assertStringContainsString('foobar', $item->content);
    }

    public function testOverloadingOffsetSetWritesToSpecifiedIndex()
    {
        $this->helper->offsetSetStyle(100, 'foobar');
        $values = $this->helper->getArrayCopy();
        $this->assertCount(1, $values);
        $this->assertTrue(isset($values[100]));
        $item = $values[100];
        $this->assertStringContainsString('foobar', $item->content);
    }

    public function testInvalidMethodRaisesException()
    {
        $this->expectException(Zend_View_Exception::class);
        $this->helper->bogusMethod();
    }

    public function testTooFewArgumentsRaisesException()
    {
        $this->expectException(Zend_View_Exception::class);
        $this->helper->appendStyle();
    }

    public function testIndentationIsHonored()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}');
        $this->helper->appendStyle('
h1 {
    font-weight: bold
}');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(2, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString('    h1 {', $string);
        $this->assertStringContainsString('        display', $string);
        $this->assertStringContainsString('        font-weight', $string);
        $this->assertStringContainsString('    }', $string);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSerialCapturingWorks()
    {
        $this->helper->headStyle()->captureStart();
        echo 'Captured text';
        $this->helper->headStyle()->captureEnd();

        try {
            $this->helper->headStyle()->captureStart();
        } catch (Zend_View_Exception $e) {
            $this->fail('Serial capturing should work');
        }
        $this->helper->headStyle()->captureEnd();
    }

    public function testNestedCapturingFails()
    {
        $this->helper->headStyle()->captureStart();
        echo 'Captured text';
        try {
            $this->helper->headStyle()->captureStart();
            $this->helper->headStyle()->captureEnd();
            $this->fail('Nested capturing should fail');
        } catch (Zend_View_Exception $e) {
            $this->helper->headStyle()->captureEnd();
            $this->assertStringContainsString('Cannot nest', $e->getMessage());
        }
    }

    public function testMediaAttributeAsArray()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}', array('media' => array('screen', 'projection')));
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(1, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString(' media="screen,projection"', $string);
    }

    public function testMediaAttributeAsCommaSeperatedString()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}', array('media' => 'screen,projection'));
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(1, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString(' media="screen,projection"', $string);
    }

    public function testConditionalScript()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', array('media' => 'screen,projection', 'conditional' => 'lt IE 7'));
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if lt IE 7]>', $test);
    }

    /**
     * @group ZF-5435
     */
    public function testContainerMaintainsCorrectOrderOfItems()
    {
        $style1 = 'a {display: none;}';
        $this->helper->offsetSetStyle(10, $style1);

        $style2 = 'h1 {font-weight: bold}';
        $this->helper->offsetSetStyle(5, $style2);

        $test     = $this->helper->toString();
        $expected = '<style type="text/css" media="screen">' . PHP_EOL
                  . '<!--' . PHP_EOL
                  . $style2 . PHP_EOL
                  . '-->' . PHP_EOL
                  . '</style>' . PHP_EOL
                  . '<style type="text/css" media="screen">' . PHP_EOL
                  . '<!--' . PHP_EOL
                  . $style1 . PHP_EOL
                  . '-->' . PHP_EOL
                  . '</style>';

        $this->assertEquals($expected, $test);
    }

    /**
     * @group ZF-9532
     */
    public function testRenderConditionalCommentsShouldNotContainHtmlEscaping()
    {
        $style = 'a{display:none;}';
        $this->helper->appendStyle($style, array(
            'conditional' => 'IE 8'
        ));
        $value = $this->helper->toString();

        $this->assertStringNotContainsString('<!--' . PHP_EOL, $value);
        $this->assertStringNotContainsString(PHP_EOL . '-->', $value);
    }

    /**
     * @group GH-515
     */
    public function testConditionalScriptNoIE()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', array('media' => 'screen,projection', 'conditional' => '!IE'));
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if !IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }

    /**
     * @group GH-515
     */
    public function testConditionalScriptNoIEWidthSpace()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', array('media' => 'screen,projection', 'conditional' => '! IE'));
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if ! IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }
}
