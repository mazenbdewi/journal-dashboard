<?php

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    public function run()
    {
        // التحقق من عدم وجود الدور أولاً
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => Utils::getFilamentAuthGuard(),
        ]);

        // منحه جميع الصلاحيات (إذا لم تكن موجودة)
        $permissions = Permission::all();
        $superAdmin->syncPermissions($permissions);

        // تعيين الدور للمستخدم الأول (إذا لم يكن معيناً له)
        $adminUser = User::first();
        if ($adminUser && ! $adminUser->hasRole('super_admin')) {
            $adminUser->assignRole('super_admin');
        }
    }
}
