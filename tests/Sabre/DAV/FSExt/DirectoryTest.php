<?php

namespace Sabre\DAV\FSExt;

class DirectoryTest extends \PHPUnit\Framework\TestCase {

    function create() {

        return new Directory(SABRE_TEMPDIR);

    }

    function testCreate() {

        $dir = $this->create();
        $this->assertEquals(basename(SABRE_TEMPDIR), $dir->getName());

    }

    function testChildExistDot() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $dir = $this->create();
        $dir->childExists('..');

    }

}
