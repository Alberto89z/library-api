<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getResponseSuccess(){
        return [
            "error" => false,
            "message" => "Success",
            "data" => []
        ];
    }
    public function getResponseError(){
        return [
            "error" => true,
            "message" => "Error! something went wrong",
            "data" => []
        ];
    }

    public function getResponse200($data)
    {
        return response()->json([
            'message' => 'Successful operation',
            'data' => $data,
        ], 200);
    }

    public function getResponseDelete200($resource)
    {
        return response()->json([
            'message' => "Your $resource has been successfully deleted!"
        ], 200);
    }

    /**
     * This feature allows you to customize the message for creating and updating a resource
     * $resource: affected object name (book, author, category, editorial, etc.)
     * Possible values for the variable $operation: created or updated.
     */
    public function getResponse201($resource, $operation, $data)
    {
        return response()->json([
            'message' => "Your $resource has been successfully $operation!",
            'data' => $data,
        ], 201);
    }

    public function getResponse401()
    {
        return response()->json([
            'message' => "Unauthorized"
        ], 401);
    }

    public function getResponse403()
    {
        return response()->json([
            'message' => "You do not have permission to access this resource"
        ], 403);
    }

    public function getResponse404()
    {
        return response()->json([
            'message' => "The requested resource is not found"
        ], 404);
    }

    /**
     * $errors: correspond to an array with the error messages.
     * Otherwise send an empty array
     */
    public function getResponse500($errors)
    {
        return response()->json([
            'message' => "Something went wrong, please try again later",
            "errors" => $errors
        ], 500);
    }

    public function removewhitspace($e)
    {
        return response()->json([
            'message' => "Something went wrong, please try again later",
            "errors" => $e
        ], 500);
    }
}
