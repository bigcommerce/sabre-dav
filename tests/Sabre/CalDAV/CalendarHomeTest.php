<?php

namespace Sabre\CalDAV;

use Sabre\DAV;
use Sabre\DAV\MkCol;

class CalendarHomeTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var Sabre\CalDAV\CalendarHome
     */
    protected $usercalendars;

    /**
     * @var Backend\BackendInterface
     */
    protected $backend;

    public function setUp(): void {

        $this->backend = TestUtil::getBackend();
        $this->usercalendars = new CalendarHome($this->backend, [
            'uri' => 'principals/user1'
        ]);

    }

    function testSimple() {

        $this->assertEquals('user1', $this->usercalendars->getName());

    }

    /**
     * @depends testSimple
     */
    function testGetChildNotFound() {
        $this->expectException(\Sabre\DAV\Exception\NotFound::class);

        $this->usercalendars->getChild('randomname');

    }

    function testChildExists() {

        $this->assertFalse($this->usercalendars->childExists('foo'));
        $this->assertTrue($this->usercalendars->childExists('UUID-123467'));

    }

    function testGetOwner() {

        $this->assertEquals('principals/user1', $this->usercalendars->getOwner());

    }

    function testGetGroup() {

        $this->assertNull($this->usercalendars->getGroup());

    }

    function testGetACL() {

        $expected = [
            [
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1',
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-read',
                'protected' => true,
            ],
        ];
        $this->assertEquals($expected, $this->usercalendars->getACL());

    }

    function testSetACL() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $this->usercalendars->setACL([]);

    }

    /**
     * @depends testSimple
     */
    function testSetName() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $this->usercalendars->setName('bla');

    }

    /**
     * @depends testSimple
     */
    function testDelete() {
        $this->expectException(\Sabre\DAV\Exception\Forbidden::class);

        $this->usercalendars->delete();

    }

    /**
     * @depends testSimple
     */
    function testGetLastModified() {

        $this->assertNull($this->usercalendars->getLastModified());

    }

    /**
     * @depends testSimple
     */
    function testCreateFile() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->usercalendars->createFile('bla');

    }


    /**
     * @depends testSimple
     */
    function testCreateDirectory() {
        $this->expectException(\Sabre\DAV\Exception\MethodNotAllowed::class);

        $this->usercalendars->createDirectory('bla');

    }

    /**
     * @depends testSimple
     */
    function testCreateExtendedCollection() {

        $mkCol = new MkCol(
            ['{DAV:}collection', '{urn:ietf:params:xml:ns:caldav}calendar'],
            []
        );
        $result = $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);
        $this->assertNull($result);
        $cals = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3, count($cals));

    }

    /**
     * @depends testSimple
     */
    function testCreateExtendedCollectionBadResourceType() {
        $this->expectException(\Sabre\DAV\Exception\InvalidResourceType::class);

        $mkCol = new MkCol(
            ['{DAV:}collection', '{DAV:}blabla'],
            []
        );
        $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);

    }

    /**
     * @depends testSimple
     */
    function testCreateExtendedCollectionNotACalendar() {
        $this->expectException(\Sabre\DAV\Exception\InvalidResourceType::class);

        $mkCol = new MkCol(
            ['{DAV:}collection'],
            []
        );
        $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);

    }

    function testGetSupportedPrivilegesSet() {

        $this->assertNull($this->usercalendars->getSupportedPrivilegeSet());

    }

    function testShareReplyFail() {
        $this->expectException(\Sabre\DAV\Exception\NotImplemented::class);

        $this->usercalendars->shareReply('uri', DAV\Sharing\Plugin::INVITE_DECLINED, 'curi', '1');

    }

}
