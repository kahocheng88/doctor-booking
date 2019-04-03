<?php

namespace App\Booking;

use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

// require_once(__DIR__ . '\..\Database\DatabaseConnector.php');

class CreateBookingHandler implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {   
        try {
            $params = $request->getQueryParams();
            $this->checkRequiredParams($params);
            
            $bookingModel = new BookingModel();
            $id = $bookingModel->insert($params['user_name'], $params['visitation_reason'], $params['start_time'], $params['end_time']);

            if ($id) {
                return new JsonResponse(['status' => 'success', 'id' => $id]);
            }
            else {
                return new JsonResponse(['error' => "Error adding new booking"], 400);
            }
        }
        catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function checkRequiredParams($params) {
        if (empty($params['user_name'])) {
            throw new \Exception("Missing parameter: user_name");  
        }

        if (empty($params['visitation_reason'])) {
            throw new \Exception("Missing parameter: visitation_reason");  
        }

        if (empty($params['start_time'])) {
            throw new \Exception("Missing parameter: start_time");  
        }

        if (empty($params['end_time'])) {
            throw new \Exception("Missing parameter: end_time");  
        }
    }
}
