<?php

namespace Sabre\DAVACL\FS;

class FileTest extends \PHPUnit\Framework\TestCase {

    /**
     * System under test
     *
     * @var File
     */
    protected $sut;

    protected $path = 'foo';
    protected $acl = [
        [
            'privilege' => '{DAV:}read',
            'principal' => '{DAV:}authenticated',
        ]
    ];

    protected $owner = 'principals/evert';

    public function setUp(): void {

        $this->sut = new File($this->path, $this->acl, $this->owner);

    }

    function testGetOwner() {

        $this->assertEquals(
            $this->owner,
            $this->sut->getOwner()
        );

    }

    function testGetGroup() {

        $this->assertNull(
            $this->sut->getGroup()
        );

    }

    function testGetACL() {

        $this->assertEquals(
            $this->acl,
            $this->sut->getACL()
        );

    }

    function testSetAcl() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $this->sut->setACL([]);

    }

    function testGetSupportedPrivilegeSet() {

        $this->assertNull(
            $this->sut->getSupportedPrivilegeSet()
        );

    }

}
