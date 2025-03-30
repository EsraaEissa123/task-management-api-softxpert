<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnauthorizedActionException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->message ?: 'You are not authorized to perform this action.',
            'error_code' => 'unauthorized_action',
        ], $this->code ?: 403);
    }
}
