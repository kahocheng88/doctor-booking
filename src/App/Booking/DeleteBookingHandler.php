<?php

namespace App\Booking;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class DeleteBookingHandler implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {   
        try {
            $params = $request->getQueryParams();
            $this->checkRequiredParams($params);

            $bookingModel = new BookingModel();
            $rowsAffected = $bookingModel->delete($params['id']);

            if ($rowsAffected) {
                return new JsonResponse(['status' => 'success']);
            }
            else {
                return new JsonResponse(['error' => 'Booking does not exist'], 400);
            }
        }
        catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function checkRequiredParams($params) {
        if (empty($params['id'])) {
            throw new \Exception("Missing parameter: id");
        }
    }
}
