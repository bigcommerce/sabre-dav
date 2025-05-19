<?php

namespace Sabre\CardDAV;

use Sabre\DAV\MkCol;

class AddressBookHomeTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var Sabre\CardDAV\AddressBookHome
     */
    protected $s;
    protected $backend;

    public function setUp(): void {

        $this->backend = new Backend\Mock();
        $this->s = new AddressBookHome(
            $this->backend,
            'principals/user1'
        );

    }

    function testGetName() {

        $this->assertEquals('user1', $this->s->getName());

    }

    function testSetName() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->s->setName('user2');

    }

    function testDelete() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->s->delete();

    }

    function testGetLastModified() {

        $this->assertNull($this->s->getLastModified());

    }

    function testCreateFile() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->s->createFile('bla');

    }

    function testCreateDirectory() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->s->createDirectory('bla');

    }

    function testGetChild() {

        $child = $this->s->getChild('book1');
        $this->assertInstanceOf('Sabre\\CardDAV\\AddressBook', $child);
        $this->assertEquals('book1', $child->getName());

    }

    function testGetChild404() {
        $this->expectException(\Sabre\DAV\Exception\NotFound::class);

        $this->s->getChild('book2');

    }

    function testGetChildren() {

        $children = $this->s->getChildren();
        $this->assertEquals(2, count($children));
        $this->assertInstanceOf('Sabre\\CardDAV\\AddressBook', $children[0]);
        $this->assertEquals('book1', $children[0]->getName());

    }

    function testCreateExtendedCollection() {

        $resourceType = [
            '{' . Plugin::NS_CARDDAV . '}addressbook',
            '{DAV:}collection',
        ];
        $this->s->createExtendedCollection('book2', new MkCol($resourceType, ['{DAV:}displayname' => 'a-book 2']));

        $this->assertEquals([
            'id'                => 'book2',
            'uri'               => 'book2',
            '{DAV:}displayname' => 'a-book 2',
            'principaluri'      => 'principals/user1',
        ], $this->backend->addressBooks[2]);

    }

    function testCreateExtendedCollectionInvalid() {
        $this->expectException(\Sabre\DAV\Exception\InvalidResourceType::class);

        $resourceType = [
            '{DAV:}collection',
        ];
        $this->s->createExtendedCollection('book2', new MkCol($resourceType, ['{DAV:}displayname' => 'a-book 2']));

    }


    function testACLMethods() {

        $this->assertEquals('principals/user1', $this->s->getOwner());
        $this->assertNull($this->s->getGroup());
        $this->assertEquals([
            [
                'privilege' => '{DAV:}all',
                'principal' => '{DAV:}owner',
                'protected' => true,
            ],
        ], $this->s->getACL());

    }

    function testSetACL() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $this->s->setACL([]);

    }

    function testGetSupportedPrivilegeSet() {

        $this->assertNull(
            $this->s->getSupportedPrivilegeSet()
        );

    }
}
