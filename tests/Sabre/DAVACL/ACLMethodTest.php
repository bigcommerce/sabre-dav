<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class ACLMethodTest extends \PHPUnit\Framework\TestCase {

    function testCallback() {
        $this->expectException(\Sabre\DAV\Exception\BadRequest::class);

        $acl = new Plugin();
        $server = new DAV\Server();
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpAcl($server->httpRequest, $server->httpResponse);

    }

    function testNotSupportedByNode() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $tree = [
            new DAV\SimpleCollection('test'),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request();
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testSuccessSimple() {

        $tree = [
            new MockACLNode('test', []),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request();
        $server->httpRequest->setUrl('/test');

        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $this->assertFalse($acl->httpACL($server->httpRequest, $server->httpResponse));

    }

    function testUnrecognizedPrincipal() {
        $this->expectException(\Sabre\DAVACL\Exception\NotRecognizedPrincipal::class);

        $tree = [
            new MockACLNode('test', []),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:read /></d:privilege></d:grant>
        <d:principal><d:href>/principals/notfound</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testUnrecognizedPrincipal2() {
        $this->expectException(\Sabre\DAVACL\Exception\NotRecognizedPrincipal::class);

        $tree = [
            new MockACLNode('test', []),
            new DAV\SimpleCollection('principals', [
                new DAV\SimpleCollection('notaprincipal'),
            ]),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:read /></d:privilege></d:grant>
        <d:principal><d:href>/principals/notaprincipal</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testUnknownPrivilege() {
        $this->expectException(\Sabre\DAVACL\Exception\NotSupportedPrivilege::class);

        $tree = [
            new MockACLNode('test', []),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:bananas /></d:privilege></d:grant>
        <d:principal><d:href>/principals/notfound</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testAbstractPrivilege() {
        $this->expectException(\Sabre\DAVACL\Exception\NoAbstract::class);

        $tree = [
            new MockACLNode('test', []),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->on('getSupportedPrivilegeSet', function($node, &$supportedPrivilegeSet) {
            $supportedPrivilegeSet['{DAV:}foo'] = ['abstract' => true];
        });
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:foo /></d:privilege></d:grant>
        <d:principal><d:href>/principals/foo/</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testUpdateProtectedPrivilege() {
        $this->expectException(\Sabre\DAVACL\Exception\AceConflict::class);

        $oldACL = [
            [
                'principal' => 'principals/notfound',
                'privilege' => '{DAV:}write',
                'protected' => true,
            ],
        ];

        $tree = [
            new MockACLNode('test', $oldACL),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:read /></d:privilege></d:grant>
        <d:principal><d:href>/principals/notfound</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testUpdateProtectedPrivilege2() {
        $this->expectException(\Sabre\DAVACL\Exception\AceConflict::class);

        $oldACL = [
            [
                'principal' => 'principals/notfound',
                'privilege' => '{DAV:}write',
                'protected' => true,
            ],
        ];

        $tree = [
            new MockACLNode('test', $oldACL),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:write /></d:privilege></d:grant>
        <d:principal><d:href>/principals/foo</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testUpdateProtectedPrivilege3() {
        $this->expectException(\Sabre\DAVACL\Exception\AceConflict::class);

        $oldACL = [
            [
                'principal' => 'principals/notfound',
                'privilege' => '{DAV:}write',
                'protected' => true,
            ],
        ];

        $tree = [
            new MockACLNode('test', $oldACL),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:write /></d:privilege></d:grant>
        <d:principal><d:href>/principals/notfound</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);

        $acl->httpACL($server->httpRequest, $server->httpResponse);

    }

    function testSuccessComplex() {

        $oldACL = [
            [
                'principal' => 'principals/foo',
                'privilege' => '{DAV:}write',
                'protected' => true,
            ],
            [
                'principal' => 'principals/bar',
                'privilege' => '{DAV:}read',
            ],
        ];

        $tree = [
            $node = new MockACLNode('test', $oldACL),
            new DAV\SimpleCollection('principals', [
                new MockPrincipal('foo', 'principals/foo'),
                new MockPrincipal('baz', 'principals/baz'),
            ]),
        ];
        $acl = new Plugin();
        $server = new DAV\Server($tree);
        $server->httpRequest = new HTTP\Request('ACL', '/test');
        $body = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
    <d:ace>
        <d:grant><d:privilege><d:write /></d:privilege></d:grant>
        <d:principal><d:href>/principals/foo</d:href></d:principal>
        <d:protected />
    </d:ace>
    <d:ace>
        <d:grant><d:privilege><d:write /></d:privilege></d:grant>
        <d:principal><d:href>/principals/baz</d:href></d:principal>
    </d:ace>
</d:acl>';
        $server->httpRequest->setBody($body);
        $server->addPlugin(new DAV\Auth\Plugin());
        $server->addPlugin($acl);


        $this->assertFalse($acl->httpAcl($server->httpRequest, $server->httpResponse));

        $this->assertEquals([
            [
                'principal' => 'principals/foo',
                'privilege' => '{DAV:}write',
                'protected' => true,
            ],
            [
                'principal' => 'principals/baz',
                'privilege' => '{DAV:}write',
                'protected' => false,
            ],
        ], $node->getACL());

    }
}
