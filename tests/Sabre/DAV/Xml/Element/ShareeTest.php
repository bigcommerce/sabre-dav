<?php

namespace Sabre\DAV\Xml\Element;

use Sabre\DAV\Sharing\Plugin;
use Sabre\DAV\Xml\XmlTest;

class ShareeTest extends XmlTest {

    function testShareeUnknownPropertyInConstructor() {
        $this->expectException(\InvalidArgumentException::class);

        new Sharee(['foo' => 'bar']);

    }

    function testDeserialize() {

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<D:sharee xmlns:D="DAV:">
   <D:href>mailto:eric@example.com</D:href>
   <D:prop>
     <D:displayname>Eric York</D:displayname>
   </D:prop>
   <D:comment>Shared workspace</D:comment>
   <D:share-access>
     <D:read-write />
   </D:share-access>
</D:sharee>
XML;

        $result = $this->parse($xml, [
            '{DAV:}sharee' => 'Sabre\\DAV\\Xml\\Element\\Sharee'
        ]);

        $expected = new Sharee([
            'href'       => 'mailto:eric@example.com',
            'properties' => ['{DAV:}displayname' => 'Eric York'],
            'comment'    => 'Shared workspace',
            'access'     => Plugin::ACCESS_READWRITE,
        ]);
        $this->assertEquals(
            $expected,
            $result['value']
        );

    }

    function testDeserializeNoHref() {
        $this->expectException(\Sabre\DAV\Exception\BadRequest::class);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<D:sharee xmlns:D="DAV:">
   <D:prop>
     <D:displayname>Eric York</D:displayname>
   </D:prop>
   <D:comment>Shared workspace</D:comment>
   <D:share-access>
     <D:read-write />
   </D:share-access>
</D:sharee>
XML;

        $this->parse($xml, [
            '{DAV:}sharee' => 'Sabre\\DAV\\Xml\\Element\\Sharee'
        ]);

    }


    function testDeserializeNoShareeAccess() {
        $this->expectException(\Sabre\DAV\Exception\BadRequest::class);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<D:sharee xmlns:D="DAV:">
   <D:href>mailto:eric@example.com</D:href>
   <D:prop>
     <D:displayname>Eric York</D:displayname>
   </D:prop>
   <D:comment>Shared workspace</D:comment>
</D:sharee>
XML;

        $this->parse($xml, [
            '{DAV:}sharee' => 'Sabre\\DAV\\Xml\\Element\\Sharee'
        ]);

    }
}
