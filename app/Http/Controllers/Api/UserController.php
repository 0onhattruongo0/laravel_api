<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

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

        $users = $users->get();
        if ($users->count() > 0) {
            $status = 'success';
        } else {
            $status = 'no data';
        };
        $response = [
            'status' => $status,
            'data' => $users
        ];
        return $response;
    }

    public function detail($id)
    {

        $user = User::find($id);
        if (!$user) {
            $status = 'no data';
        } else {
            $status = 'success';
        }
        $response = [
            'status' => $status,
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
                'status' => 'success',
                'data' => $user
            ];
        } else {
            $response = [
                'status' => 'errors',
            ];
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            $response = [
                'status' => 'errors',
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
                'status' => 'success',
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
                'status' => 'success',
            ];
        } else {
            $response = [
                'status' => 'error',
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
