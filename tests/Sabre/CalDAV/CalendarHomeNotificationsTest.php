<?php

namespace Sabre\CalDAV;

class CalendarHomeNotificationsTest extends \PHPUnit\Framework\TestCase {

    function testGetChildrenNoSupport() {

        $backend = new Backend\Mock();
        $calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);

        $this->assertEquals(
            [],
            $calendarHome->getChildren()
        );

    }

    function testGetChildNoSupport() {
        $this->expectException(\Sabre\DAV\Exception\NotFound::class);

        $backend = new Backend\Mock();
        $calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);
        $calendarHome->getChild('notifications');

    }

    function testGetChildren() {

        $backend = new Backend\MockSharing();
        $calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);

        $result = $calendarHome->getChildren();
        $this->assertEquals('notifications', $result[0]->getName());

    }

    function testGetChild() {

        $backend = new Backend\MockSharing();
        $calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);
        $result = $calendarHome->getChild('notifications');
        $this->assertEquals('notifications', $result->getName());

    }

}
