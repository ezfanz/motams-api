<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, $message = 'Operation successful', $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'Operation failed', $statusCode = 400)
    {
        // Ensure the status code is always an integer and valid HTTP status code
        $statusCode = is_numeric($statusCode) && $statusCode > 0 ? (int) $statusCode : 400;
    
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null
        ], $statusCode);
    }
    
}
