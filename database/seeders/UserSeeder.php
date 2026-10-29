<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@my2ndwife.com'],
            [
                'name' => 'Super Administrator',
                'phone' => '+91 98000 00001',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@my2ndwife.com'],
            [
                'name' => 'Restaurant Manager',
                'phone' => '+91 98000 00002',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 3. Order Manager
        User::updateOrCreate(
            ['email' => 'manager@my2ndwife.com'],
            [
                'name' => 'Order Dispatcher',
                'phone' => '+91 98000 00003',
                'password' => Hash::make('password123'),
                'role' => 'order_manager',
                'is_active' => true,
            ]
        );

        // 4. Kitchen Staff
        User::updateOrCreate(
            ['email' => 'kitchen@my2ndwife.com'],
            [
                'name' => 'Head Chef',
                'phone' => '+91 98000 00004',
                'password' => Hash::make('password123'),
                'role' => 'kitchen_staff',
                'is_active' => true,
            ]
        );

        // 5. Delivery Partner 1
        User::updateOrCreate(
            ['email' => 'delivery1@my2ndwife.com'],
            [
                'name' => 'Rahul Sharma (Express Rider)',
                'phone' => '+91 98765 43210',
                'password' => Hash::make('password123'),
                'role' => 'delivery_person',
                'vehicle_type' => 'Bajaj Pulsar 150',
                'vehicle_number' => 'WB-02-AK-4421',
                'is_active' => true,
                'is_online' => true,
            ]
        );

        // 6. Delivery Partner 2
        User::updateOrCreate(
            ['email' => 'delivery2@my2ndwife.com'],
            [
                'name' => 'Amit Roy (Bike Delivery)',
                'phone' => '+91 98765 43211',
                'password' => Hash::make('password123'),
                'role' => 'delivery_person',
                'vehicle_type' => 'Honda Activa 6G',
                'vehicle_number' => 'WB-06-BQ-7712',
                'is_active' => true,
                'is_online' => true,
            ]
        );

        // 7. Demo Customer
        $customer = User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Sumanta Das',
                'phone' => '+91 98300 99887',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        // Add saved addresses for customer
        Address::updateOrCreate(
            [
                'user_id' => $customer->id,
                'type' => 'home',
            ],
            [
                'contact_name' => 'Sumanta Das',
                'contact_phone' => '+91 98300 99887',
                'address_line_1' => 'Flat 4B, Silver Oak Heights',
                'address_line_2' => 'Southern Avenue, Near Lake Mall',
                'landmark' => 'Behind SBI Bank',
                'city' => 'Kolkata',
                'postal_code' => '700029',
                'delivery_instructions' => 'Ring bell twice and leave package on shoe rack.',
                'is_default' => true,
            ]
        );

        Address::updateOrCreate(
            [
                'user_id' => $customer->id,
                'type' => 'work',
            ],
            [
                'contact_name' => 'Sumanta Das',
                'contact_phone' => '+91 98300 99887',
                'address_line_1' => 'Tech Tower 2, 5th Floor, Suite 502',
                'address_line_2' => 'Sector V, Salt Lake',
                'landmark' => 'Opposite RDB Cinemas',
                'city' => 'Kolkata',
                'postal_code' => '700091',
                'delivery_instructions' => 'Please call once reaching the reception lobby.',
                'is_default' => false,
            ]
        );
    }
}
