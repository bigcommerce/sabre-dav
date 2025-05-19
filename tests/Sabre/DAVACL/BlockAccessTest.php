<?php

namespace Sabre\DAVACL;

use Sabre\DAV;

class BlockAccessTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var DAV\Server
     */
    protected $server;
    protected $plugin;

    public function setUp(): void {

        $nodes = [
            new DAV\SimpleCollection('testdir'),
        ];

        $this->server = new DAV\Server($nodes);
        $this->plugin = new Plugin();
        $this->plugin->setDefaultAcl([]);
        $this->server->addPlugin(
            new DAV\Auth\Plugin(
                new DAV\Auth\Backend\Mock()
            )
        );
        // Login
        $this->server->getPlugin('auth')->beforeMethod(
            new \Sabre\HTTP\Request(),
            new \Sabre\HTTP\Response()
        );
        $this->server->addPlugin($this->plugin);

    }

    function testGet() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testGetDoesntExist() {

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/foo');

        $r = $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);
        $this->assertTrue($r);

    }

    function testHEAD() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('HEAD');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testOPTIONS() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('OPTIONS');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testPUT() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('PUT');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testPROPPATCH() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('PROPPATCH');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testCOPY() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('COPY');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testMOVE() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('MOVE');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testACL() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('ACL');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testLOCK() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->httpRequest->setMethod('LOCK');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testBeforeBind() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->emit('beforeBind', ['testdir/file']);

    }

    function testBeforeUnbind() {
        $this->expectException(\Sabre\DAVACL\Exception\NeedPrivileges::class);

        $this->server->emit('beforeUnbind', ['testdir']);

    }

    function testPropFind() {

        $propFind = new DAV\PropFind('testdir', [
            '{DAV:}displayname',
            '{DAV:}getcontentlength',
            '{DAV:}bar',
            '{DAV:}owner',
        ]);

        $r = $this->server->emit('propFind', [$propFind, new DAV\SimpleCollection('testdir')]);
        $this->assertTrue($r);

        $expected = [
            200 => [],
            404 => [],
            403 => [
                '{DAV:}displayname'      => null,
                '{DAV:}getcontentlength' => null,
                '{DAV:}bar'              => null,
                '{DAV:}owner'            => null,
            ],
        ];

        $this->assertEquals($expected, $propFind->getResultForMultiStatus());

    }

    function testBeforeGetPropertiesNoListing() {

        $this->plugin->hideNodesFromListings = true;
        $propFind = new DAV\PropFind('testdir', [
            '{DAV:}displayname',
            '{DAV:}getcontentlength',
            '{DAV:}bar',
            '{DAV:}owner',
        ]);

        $r = $this->server->emit('propFind', [$propFind, new DAV\SimpleCollection('testdir')]);
        $this->assertFalse($r);

    }
}
