<?php

namespace Sabre\DAV\Exception;

class PaymentRequiredTest extends \PHPUnit\Framework\TestCase {

    function testGetHTTPCode() {

        $ex = new PaymentRequired();
        $this->assertEquals(402, $ex->getHTTPCode());

    }

}
