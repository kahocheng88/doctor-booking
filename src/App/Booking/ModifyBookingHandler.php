<?php

namespace App\Booking;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ModifyBookingHandler implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {   
        try {
            $params = $request->getQueryParams();
            $this->checkRequiredParams($params);

            $updateParams = $params;
            unset($updateParams['id']);
            
            $bookingModel = new BookingModel();
            $rowsAffected = $bookingModel->update($params['id'], $updateParams);

            if ($rowsAffected) {
                return new JsonResponse(['status' => 'success', 'id' => $params['id']]);
            }
            else {
                return new JsonResponse(['status' => 'no_update_required']);
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
