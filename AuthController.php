<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function loginForm() { return view('auth.login'); }
    public function registerForm() { return view('auth.register'); }

    public function login(Request $request)
    {
        $data = $request->validate(['email'=>'required|email', 'password'=>'required|string']);
        if (!Auth::attempt(['email'=>$data['email'],'password'=>$data['password'],'is_active'=>1])) {
            return back()->withErrors(['email'=>'Email hoặc mật khẩu không đúng, hoặc tài khoản bị khóa.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(in_array(Auth::user()->role?->code, ['ADMIN','STAFF']) ? route('panel.dashboard') : route('home'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120', 'email'=>'required|email|max:191|unique:users,email',
            'phone'=>'required|string|max:20',
            'password'=>['required','confirmed', Password::min(8)],
        ]);
        // Không bao giờ nhận role_id từ form đăng ký công khai.
        $user = User::create([...$data, 'role_id'=>Role::where('code','CLIENT')->firstOrFail()->id, 'is_active'=>true]);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('home')->with('success','Đăng ký thành công. Bạn có thể đặt phòng ngay.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success','Đã đăng xuất.');
    }
}
