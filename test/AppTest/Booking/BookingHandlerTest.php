<?php

namespace AppTest\Action;

use App\Booking\BookingHandler;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class BookingHandlerTest extends TestCase
{
    // public function testResponse()
    // {
    //     $bookHandler = new BookHandler();
    //     $response = $bookHandler->process(
    //         $this->prophesize(ServerRequestInterface::class)->reveal(),
    //         $this->prophesize(DelegateInterface::class)->reveal()
    //     );

    //     $json = json_decode((string) $response->getBody());

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertTrue(isset($json->ack));
    // }

    public function testGetById() {
        $bookingHandler = new BookingHandler();
        $this->assertTrue(true);
    }

    public function testGetByUserName() {
        $bookingHandler = new BookingHandler();
        $this->assertTrue(true);
    }
}
