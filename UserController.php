<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index() { return view('panel.users',['users'=>User::with('role')->orderByDesc('created_at')->paginate(20),'roles'=>Role::orderBy('id')->get()]); }
    public function store(Request $request) {
        $data=$request->validate([
            'name'=>'required|string|max:120','email'=>'required|email|max:191|unique:users,email',
            'phone'=>'nullable|string|max:20','password'=>['required',Password::min(8)],
            'role_id'=>['required',Rule::exists('roles','id')]
        ]);
        User::create([...$data,'is_active'=>true]);
        return back()->with('success','Đã tạo tài khoản.');
    }
    public function update(Request $request, User $user) {
        $data=$request->validate(['role_id'=>['required',Rule::exists('roles','id')],'is_active'=>'required|boolean']);
        $oldRole=$user->role?->code;
        $newRole=Role::findOrFail($data['role_id'])->code;
        if ($user->id===$request->user()->id && ($newRole!=='ADMIN' || !$data['is_active'])) {
            return back()->withErrors(['role_id'=>'Không được tự hạ quyền hoặc khóa tài khoản đang sử dụng.']);
        }
        if ($oldRole==='ADMIN' && ($newRole!=='ADMIN'||!$data['is_active']) && User::whereHas('role',fn($q)=>$q->where('code','ADMIN'))->where('is_active',1)->count()<=1) {
            return back()->withErrors(['role_id'=>'Phải giữ ít nhất một Admin đang hoạt động.']);
        }
        $user->update($data);
        return back()->with('success','Đã cập nhật vai trò/trạng thái.');
    }
}
