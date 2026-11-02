<?php
namespace Database\Seeders;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class InitialAdminSeeder extends Seeder {
    public function run(): void {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');
        if (!$email || !$password) {
            $this->command?->warn('Skipped admin: set INITIAL_ADMIN_EMAIL/PASSWORD before running seeder.');
            return;
        }
        User::updateOrCreate(['email'=>$email], [
            'name'=>'Quản trị viên', 'role_id'=>Role::where('code','ADMIN')->firstOrFail()->id,
            'is_active'=>true, 'password'=>Hash::make($password),
        ]);
    }
}
