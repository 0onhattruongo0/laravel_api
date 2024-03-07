<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $where = [];
        if ($request->name) {
            $where[] = ['name', 'like', '%' . $request->name . '%'];
        }

        if ($request->name) {
            $where[] = ['email', 'like', '%' . $request->email . '%'];
        }

        $users = User::orderBy('id', 'desc');
        if (!empty($where)) {
            $users->where($where);
        }

        // $users = $users->get();


        if ($users->count() > 0) {
            $statusCode = 200;
            $statusText = "success";
        } else {
            $statusCode = 404;
            $statusText = "no found";
        };

        // $users = UserResource::collection($users);

        // $response = [
        //     'status' => $status,
        //     'data' => $users
        // ];
        $users = $users->with('posts')->paginate();
        $response = new UserCollection($users, $statusCode, $statusText);
        return $response;
    }

    public function detail($id)
    {

        $user = User::with('posts')->find($id);
        if (!$user) {
            $statusCode = 404;
            $statusText = 'no found';
        } else {
            $statusCode = 200;
            $statusText = 'success';
            $user = new UserResource($user);
        }

        $response = [
            'status' => $statusCode,
            'title' => $statusText,
            'data' => $user
        ];
        return $response;
    }

    public function create(Request $request)
    {
        $this->validation($request);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($user->id) {
            $response = [
                'status' =>  201,
                'title' => 'success',
                'data' => $user
            ];
        } else {
            $response = [
                'status' => 500,
                'title' => 'error'
            ];
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            $response = [
                'status' => 404,
                'title' => 'not found',
            ];
        } else {
            if ($request->method() == 'PUT') {
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                } else {
                    $user->password = null;
                }
                $user->save();
            } else {
                if ($request->name) {
                    $user->name = $request->name;
                }
                if ($request->email) {
                    $user->email = $request->email;
                }
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
            }
            $response = [
                'status' => 200,
                'title' => 'success',
                'data' => $user
            ];
        }

        return $response;
    }

    public function delete(User $user)
    {
        $user = User::destroy($user->id);
        if ($user) {
            $response = [
                'status' => 204,
                'title' => 'success',
            ];
        } else {
            $response = [
                'status' => 404,
                'title' => 'not found',
            ];
        }
        return $response;
    }

    public function validation($request, $id = 0)
    {
        $validationEmail = 'required|email|unique:users';
        if ($id > 0) {
            $validationEmail .= ',email,' . $id;
        }
        $rules = [
            'name' => 'required|min:5',
            'email' => $validationEmail,
            'password' => 'required|min:8',
        ];
        $masages = [
            'name.required' => 'Tên bắt buộc phải nhập',
            'name.min' => 'Tên ít nhất có 5 ký tự',
            'email.required' => 'Email bắt buộc phải nhập',
            'email.email' => 'Định dạng email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'password.required' => 'Mật khẩu bắt buộc phải nhập',
            'password.min' => 'Mật khẩu ít nhất có 8 ký tự',
        ];
        $request->validate($rules, $masages);
    }
}
