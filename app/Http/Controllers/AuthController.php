<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        if (!$token = auth('api')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            return $this->httpBadRequest([
                'message' => 'Tài khoản hoặc mật khẩu chưa chính xác'
            ]);
        }
        $user = User::where('email',$request->email)->whereNotNull('email_verified_at')->count();
        if(!$user){
            return $this->httpBadRequest([
                'message' => 'Tài khoản chưa xác thực vui lòng kiểm tra lại email'
            ]);
        }
        auth('api')->setToken($token);
        return $this->httpOK([
            'token' => $token,
        ]);
    }

    public function logout(Request $request){
        auth('api')->logout();
        return $this->httpNoContent();
    }

    public function signUp(Request $request){

        try{
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password,
                'verify_token' => Str::random(50)
            ]);
            $this->sendWithHtml("verified", $user, 'Xác nhận tài khoản');
            return $this->httpCreated($user);
        }catch (\Exception $e){
            return $this->error(400, 'Email hoặc username đã được đăng ký')->respond(400, ['x-foo' => false]);
        }

    }

    private function sendWithHtml($htmlPath, $data, $emailSubject)
    {
        $data = (object) $data;
        Mail::send(
            $htmlPath, ['data' => $data],
            function ($message) use ($data, $emailSubject) {
                $message->to($data->email);
                $message->subject($emailSubject);
            });
    }

    public function verifyAccount($email,$token){
        $user = User::query()->where('email', $email)
            ->where('verify_token', $token)
            ->where('email_verified_at', null)
            ->count();
        if ($user != 0) {
            User::query()->where('email', $email)
                ->update([
                    'email_verified_at' => Carbon::now(),
                    'verify_token'      => null,
                    'updated_at'        => Carbon::now(),
                ]);

            return 'Tài khoản đã được xác thực thành công';
        }

        return 'Liên kết không còn khả dụng, vui lòng kiểm tra lại';
    }

    public function forgotPassword(Request $request){
        $user = User::query()->where('email', $request->email)->count();
        if ($user != 0) {
            if (DB::table('password_resets')->where('email', $request->email)->count() == 0) {
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => Str::random(5),
                ]);
            }
            $user = DB::table('password_resets')->where('email', $request->email)->first();
            $this->sendWithHtml("forgot_password", $user, 'Khôi phục mật khẩu');

            return $this->httpNoContent();
        }

        return $this->error(404, 'Không tìm thấy tài khoản')->respond(404, ['x-foo' => false]);
    }

    public function checkVerifyToken($token)
    {
        $reset = DB::table('password_resets')
            ->where('token', $token)
            ->count();
        if ($reset != 0) {
            return $this->success(['token' => $token]);
        }

        return $this->error(404, 'token không còn khả dụng')->respond(404, ['x-foo' => false]);
    }

    public function resetPassword(Request $request)
    {
        $reset = DB::table('password_resets')->where('token', $request->token)->count();
        if ($reset != 0) {
            $reset = DB::table('password_resets')->where('token', $request->token)->first();
            User::query()->where('email', $reset->email)
                ->update([
                    'password'   => Hash::make($request->password),
                ]);

            DB::table('password_resets')->where('token', $request->token)->delete();
            return $this->httpNoContent();
        }

        return $this->error(404)->respond(404, ['x-foo' => false]);
    }

    public function changePassword(Request $request)
    {
        if ($request->user('api')) {
            if (Hash::check($request->old_password, $request->user('api')->password)) {
                User::query()->where('id', $request->user('api')->id)
                    ->update([
                        'password' => Hash::make($request->new_password),
                    ]);

                return $this->success();
            }

            return $this->error(500)->respond(500, ['x-foo' => false]);
        }

        return $this->error(500)->respond(500, ['x-foo' => false]);
    }

    public function updateProfile(Request $request){
        $user = $request->user('api');
        $user->update($request->all());
        return $this->httpOK($user);
    }

    public function profile(Request $request){
        return $this->httpOK($request->user('api'));
    }
}
