<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                //Set data
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password); //encrypt password
                $user->save();
                DB::commit();
                return $this->getResponse201('user account', 'created', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if (!$validator->fails()) {
                $user = User::where('email', '=', $request->email)->first();
                if (isset($user->id)) {
                    if (Hash::check($request->password, $user->password)) {
                        //Create token
                        $token = $user->createToken('auth_token')->plainTextToken;
                        return response()->json([
                            'message' => "Successful authentication",
                            'access_token' => $token,
                        ], 200);
                    } else { //Invalid credentials
                        return $this->getResponse401();
                    }
                } else { //User not found
                    return $this->getResponse401();
                }
            } else {
                return $this->getResponse500([$validator->errors()]);
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
    }
    public function login2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
            $user = User::where('email', '=', $request->email)->first();
            if (isset($user->id)) {
                if (Hash::check($request->password, $user->password)) {
                    foreach ($user->tokens as $token) { //Revoke all previous tokens
                        $token->delete();
                    }
                    //Create new token
                    $token = $user->createToken('auth_token')->plainTextToken;
                    return response()->json([
                        'message' => "Successful authentication",
                        'access_token' => $token,
                    ], 200);
                } else { //Invalid credentials
                    return $this->getResponse401();
                }
            } else { //User not found
                return $this->getResponse401();
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function userProfile()
    {
        try {
            if(auth()->user())
            return $this->getResponse200(auth()->user());
            else
            return $this->getResponse403();
        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
        
    }

    public function logout(Request $request)
    {
        // $request->user()->tokens()->delete(); //Revoke all tokens
        $request->user()->currentAccessToken()->delete(); //Revoke current token
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $user = $request->user();
                $user->password = Hash::make($request->password); //encrypt password
                $user->update();
                DB::commit();
                $request->user()->tokens()->delete();
                return $this->getResponse201('user password', 'updated', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
        return $request->user();
    }
}
