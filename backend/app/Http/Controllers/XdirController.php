<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class XdirController extends Controller
{
    private $actions;

    public function __construct()
    {
        $this->actions = [
            'createUser' => 'createUser'
        ];
    }

    public function action(Request $request)
    {
        $actionName = $request->action;
        $data = $request->data;

        if (method_exists($this, $actionName)) {
            if (is_string($actionName) && method_exists($this, $actionName)) {
                return $this->{$actionName}($data);
            } else {
                throw new Exception("Method name must be a string and a valid callable method.");
            }
        } else {
            throw new Exception("Action {$actionName} not found.");
        }
    }

    private function createUser($data)
    {
        try {
            $user = User::where('email', $data['user']['email'])->first();
            if ($user) {
                return response()->json(['success' => true], Response::HTTP_OK);
            }
            $data['user']['password'] = $data['password'];
            
            $user = User::create($data['user']);
            $user->save();
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'success' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function checkIfUserExists($data)
    {
        $data = json_decode($data);
        $user = User::FindOrFail($data->id);

        if ($user) {
            return response()->json(['exists' => true], Response::HTTP_OK);
        }

        return response()->json(['exists' => false], Response::HTTP_OK);
    }
}
