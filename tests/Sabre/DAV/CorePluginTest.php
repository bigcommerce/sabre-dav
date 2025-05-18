<?php

namespace Sabre\DAV;

class CorePluginTest extends \PHPUnit\Framework\TestCase {

    function testGetInfo() {

        $corePlugin = new CorePlugin();
        $this->assertEquals('core', $corePlugin->getPluginInfo()['name']);

    }

}
