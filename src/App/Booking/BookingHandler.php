<?php

namespace App\Booking;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class BookingHandler implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {   
        try {
            $params = $request->getQueryParams();
            $this->checkRequiredParams($params);

            $bookings = false;
            $bookingModel = new BookingModel();

            if (!empty($params['id'])) {
                $bookings = $bookingModel->getById($params['id']);
            }
            elseif (!empty($params['user_name'])) {
                $bookings = $bookingModel->getByUsername($params['user_name']);
            }

            return new JsonResponse(['status' => 'success', 'bookings' => $bookings]);
        }
        catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function checkRequiredParams($params) {
        if (empty($params['user_name']) && empty($params['id'])) {
            throw new \Exception("Missing parameter: either user_name or id must be provided");
        }
    }
}

