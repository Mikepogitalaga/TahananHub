<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Bed;
use App\Models\Expense;
use App\Models\HouseOwner;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceUpdate;
use App\Models\OwnerAccessRequest;
use App\Models\PrivacyAuditLog;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\RentBilling;
use App\Models\Room;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PrivacyAccessService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DEVELOPMENT SEED DATA — all names and records are clearly fake.
 * Never use real people's private information in this file.
 *
 * Run with: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private BillingService $billingService;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::where('email', 'admin@eboarding.test')->exists()) {
            $this->command?->warn('Seed data already present. Run: php artisan migrate:fresh --seed');

            return;
        }

        $this->billingService = app(BillingService::class);

        $this->seedSettings();

        [$admin, $owner1User, $owner2User] = $this->seedUsers();

        $owner1 = HouseOwner::create([
            'user_id'           => $owner1User->id,
            'business_name'     => 'Casa Verde Lodging House',
            'business_address'  => 'Brgy. San Isidro, Santa Rosa, Laguna',
            'subscription_plan' => 'basic',
        ]);

        $owner2 = HouseOwner::create([
            'user_id'           => $owner2User->id,
            'business_name'     => 'Sunrise Dorm Connect',
            'business_address'  => 'Brgy. Poblacion, Calamba, Laguna',
            'subscription_plan' => 'free',
        ]);

        $properties = $this->seedProperties($owner1, $owner2);
        $beds = $this->seedRoomsAndBeds($properties);
        [$tenants, $tenantUsers] = $this->seedTenants($owner1, $owner2);
        $contracts = $this->seedAssignmentsAndContracts($tenants, $properties, $beds);
        $this->seedBillingAndPayments($contracts, $tenantUsers, $owner1User, $owner2User);
        $this->seedExpenses($properties, $owner1, $owner2);
        $this->seedMaintenance($tenants, $properties, $owner1User);
        $this->seedAnnouncements($owner1, $owner2, $properties, $admin);
        $this->seedAccessRequests($admin, $owner1, $owner2);

        $this->command?->info('Development seed data created successfully.');
    }

    // -------------------------------------------------------------------------
    // Platform settings
    // -------------------------------------------------------------------------

    private function seedSettings(): void
    {
        Setting::set('platform_name', 'E-Boarding', 'general');
        Setting::set('support_email', 'support@eboarding.test', 'general');
        Setting::set('currency_symbol', '₱', 'general');
        Setting::set('strict_privacy_enforcement', '1', 'privacy');
        Setting::set('auto_expire_sessions', '1', 'privacy');
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    /**
     * @return array{0: User, 1: User, 2: User}
     */
    private function seedUsers(): array
    {
        $admin = User::create([
            'name'     => 'Alex Platform (Super Admin)',
            'email'    => 'admin@eboarding.test',
            'password' => Hash::make('password'),
            'role'     => User::ROLE_SUPER_ADMIN,
            'status'   => 'active',
        ]);

        $owner1User = User::create([
            'name'     => 'Juan Dela Cruz (Fake Owner)',
            'email'    => 'owner1@eboarding.test',
            'password' => Hash::make('password'),
            'role'     => User::ROLE_OWNER,
            'status'   => 'active',
        ]);

        $owner2User = User::create([
            'name'     => 'Maria Santos (Fake Owner)',
            'email'    => 'owner2@eboarding.test',
            'password' => Hash::make('password'),
            'role'     => User::ROLE_OWNER,
            'status'   => 'active',
        ]);

        return [$admin, $owner1User, $owner2User];
    }

    // -------------------------------------------------------------------------
    // Properties
    // -------------------------------------------------------------------------

    /**
     * @return array<string, Property>
     */
    private function seedProperties(HouseOwner $owner1, HouseOwner $owner2): array
    {
        $p1 = Property::create([
            'house_owner_id'     => $owner1->id,
            'name'               => 'Casa Verde Main House',
            'code'               => 'CASA-HQ-01',
            'address'            => '123 Sample Street',
            'barangay'           => 'San Isidro',
            'municipality'       => 'Santa Rosa',
            'province'           => 'Laguna',
            'contact_number'     => '0917-000-0001',
            'description'        => 'Two-storey lodging house near the industrial park. (Fake demo property.)',
            'status'             => 'active',
            'is_listed'          => true,
            'public_description' => 'Comfortable two-storey boarding house near the industrial park. Walking distance to public transport, convenience stores, and eateries. (Fake demo property.)',
            'public_contact'     => '0917-000-0001',
            'amenities'          => ['WiFi', 'Water', 'Electricity', 'Parking', 'Laundry', 'Common Kitchen'],
            'house_rules'        => "No smoking inside the premises.\nCurfew at 11:00 PM.\nGuests must be registered at the front desk.\nKeep common areas clean.",
            'latitude'           => 14.2833,
            'longitude'          => 121.1167,
        ]);

        $p2 = Property::create([
            'house_owner_id'     => $owner1->id,
            'name'               => 'Casa Verde Annex',
            'code'               => 'CASA-ANX-02',
            'address'            => '456 Example Avenue',
            'barangay'           => 'San Isidro',
            'municipality'       => 'Santa Rosa',
            'province'           => 'Laguna',
            'contact_number'     => '0917-000-0002',
            'description'        => 'Annex building for long-stay tenants. (Fake demo property.)',
            'status'             => 'active',
            'is_listed'          => true,
            'public_description' => 'Quiet annex building ideal for long-stay tenants. Fully furnished rooms with individual lockers. (Fake demo property.)',
            'public_contact'     => '0917-000-0002',
            'amenities'          => ['WiFi', 'Water', 'Electricity', 'Laundry'],
            'house_rules'        => "No loud music after 10:00 PM.\nMonthly rent due on the 5th.",
            'latitude'           => 14.2840,
            'longitude'          => 121.1180,
        ]);

        $p3 = Property::create([
            'house_owner_id'     => $owner2->id,
            'name'               => 'Sunrise Dormitory Building A',
            'code'               => 'SUNR-DOM-01',
            'address'            => '789 Demo Road',
            'barangay'           => 'Poblacion',
            'municipality'       => 'Calamba',
            'province'           => 'Laguna',
            'contact_number'     => '0918-000-0003',
            'description'        => 'Dormitory-style beds for students and young professionals. (Fake demo property.)',
            'status'             => 'active',
            'is_listed'          => true,
            'public_description' => 'Affordable dormitory-style accommodation for students and young professionals near Calamba City Hall. (Fake demo property.)',
            'public_contact'     => '0918-000-0003',
            'amenities'          => ['WiFi', 'Water', 'Electricity', 'Study Area', 'CCTV'],
            'house_rules'        => "Quiet hours: 10:00 PM – 6:00 AM.\nNo cooking inside rooms.\nVisitors allowed until 9:00 PM only.",
            'latitude'           => 14.2113,
            'longitude'          => 121.1644,
        ]);

        return ['p1' => $p1, 'p2' => $p2, 'p3' => $p3];
    }

    // -------------------------------------------------------------------------
    // Rooms & Beds
    // -------------------------------------------------------------------------

    /**
     * Creates 8 rooms and 27 beds across 3 properties.
     *
     * @param  array<string, Property>  $properties
     * @return array<string, Bed>  keyed by "PROPERTY-ROOM-BED" (e.g. "101-A")
     */
    private function seedRoomsAndBeds(array $properties): array
    {
        $layout = [
            // room_number => [name, floor, type, capacity, rate, beds]
            '101' => ['Room 101 Dorm', '1', 'dormitory', 4, 3500, ['A', 'B', 'C', 'D']],
            '102' => ['Room 102 Double', '1', 'double', 2, 4000, ['A', 'B']],
            '103' => ['Room 103 Shared', '1', 'shared', 3, 3000, ['A', 'B', 'C']],
            '201' => ['Room 201 Single', '2', 'single', 4, 4500, ['A', 'B', 'C', 'D']],
        ];

        $layoutAnnex = [
            'A1' => ['Annex A1', '1', 'shared', 3, 3200, ['A', 'B', 'C']],
            'A2' => ['Annex A2', '1', 'shared', 3, 3200, ['A', 'B', 'C']],
        ];

        $layoutSunrise = [
            'D1' => ['Dorm 101', '1', 'dormitory', 4, 2800, ['A', 'B', 'C', 'D']],
            'D2' => ['Dorm 102', '1', 'dormitory', 4, 2800, ['A', 'B', 'C', 'D']],
        ];

        $beds = [];

        $build = function (Property $property, array $layout) use (&$beds) {
            foreach ($layout as $roomNumber => [$roomName, $floor, $type, $capacity, $rate, $bedLetters]) {
                $room = Room::create([
                    'property_id'  => $property->id,
                    'room_number'  => $roomNumber,
                    'room_name'    => $roomName,
                    'floor'        => $floor,
                    'room_type'    => $type,
                    'capacity'     => $capacity,
                    'rental_rate'  => $rate,
                    'description'  => null,
                    'status'       => 'available',
                ]);

                foreach ($bedLetters as $letter) {
                    $bed = Bed::create([
                        'room_id'     => $room->id,
                        'bed_number'  => $letter,
                        'bed_code'    => "{$property->code}-{$roomNumber}-{$letter}",
                        'rental_rate' => $rate,
                        'status'      => 'available',
                    ]);

                    $beds["{$roomNumber}-{$letter}"] = $bed;
                }
            }
        };

        $build($properties['p1'], $layout);
        $build($properties['p2'], $layoutAnnex);
        $build($properties['p3'], $layoutSunrise);

        return $beds;
    }

    // -------------------------------------------------------------------------
    // Tenants (fake people)
    // -------------------------------------------------------------------------

    /**
     * @return array{0: array<string, Tenant>, 1: array<string, User>}
     */
    private function seedTenants(HouseOwner $owner1, HouseOwner $owner2): array
    {
        $people = [
            // key, owner, first, last, gender, dob, contact, status, email
            ['carlo',   $owner1, 'Carlo',   'Reyes',     'male',   '1998-03-14', '0917-111-0001', 'active',    'carlo.reyes@tenant.test'],
            ['angela',  $owner1, 'Angela',  'Cruz',      'female', '1999-07-22', '0917-111-0002', 'active',    'angela.cruz@tenant.test'],
            ['bryan',   $owner1, 'Bryan',   'Tan',       'male',   '1997-11-05', '0917-111-0003', 'active',    'bryan.tan@tenant.test'],
            ['kim',     $owner1, 'Kim',     'Flores',    'female', '2000-01-30', '0917-111-0004', 'active',    'kim.flores@tenant.test'],
            ['nico',    $owner1, 'Nico',    'Ramos',     'male',   '1996-05-18', '0917-111-0005', 'active',    'nico.ramos@tenant.test'],
            ['sara',    $owner1, 'Sara',    'Lim',       'female', '1998-09-09', '0917-111-0006', 'active',    'sara.lim@tenant.test'],
            ['paolo',   $owner1, 'Paolo',   'Mendiola',  'male',   '1995-12-01', '0917-111-0007', 'moved_out', 'paolo.mendiola@tenant.test'],
            ['grace',   $owner1, 'Grace',   'Abad',      'female', '1994-02-27', '0917-111-0008', 'inactive',  'grace.abad@tenant.test'],
            ['dexter',  $owner2, 'Dexter',  'Villanueva','male',   '1999-04-11', '0918-222-0001', 'active',    'dexter.villanueva@tenant.test'],
            ['lovely',  $owner2, 'Lovely',  'Chan',      'female', '2001-06-03', '0918-222-0002', 'active',    'lovely.chan@tenant.test'],
            ['ryan',    $owner2, 'Ryan',    'Ocampo',    'male',   '1997-08-19', '0918-222-0003', 'active',    'ryan.ocampo@tenant.test'],
            ['trisha',  $owner2, 'Trisha',  'Gomez',     'female', '2000-10-25', '0918-222-0004', 'active',    'trisha.gomez@tenant.test'],
        ];

        $tenants = [];
        $tenantUsers = [];

        foreach ($people as [$key, $owner, $first, $last, $gender, $dob, $contact, $status, $email]) {
            $user = User::create([
                'name'     => "{$first} {$last} (Fake Tenant)",
                'email'    => $email,
                'password' => Hash::make('password'),
                'role'     => User::ROLE_TENANT,
                'status'   => 'active',
                'phone'    => $contact,
            ]);

            $tenants[$key] = Tenant::create([
                'house_owner_id'          => $owner->id,
                'user_id'                 => $user->id,
                'first_name'              => $first,
                'last_name'               => $last,
                'date_of_birth'           => $dob,
                'gender'                  => $gender,
                'contact_number'          => $contact,
                'email'                   => $email,
                'address'                 => 'Fake Address, Santa Rosa, Laguna',
                'emergency_contact'       => "Fake Emergency Contact of {$first}",
                'emergency_relationship'  => 'Parent',
                'emergency_contact_number'=> '0918-999-0000',
                'gov_id_type'             => 'Student ID',
                'gov_id_number_encrypted' => encrypt('DEMO-ID-' . strtoupper($first) . '-000'),
                'status'                  => $status,
            ]);

            $tenantUsers[$key] = $user;
        }

        return [$tenants, $tenantUsers];
    }

    // -------------------------------------------------------------------------
    // Tenant assignments & rental contracts
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, Tenant>  $tenants
     * @param  array<string, Property>  $properties
     * @param  array<string, Bed>  $beds
     * @return array<string, RentalContract>
     */
    private function seedAssignmentsAndContracts(array $tenants, array $properties, array $beds): array
    {
        // key => [bed key, contract start, end, status]
        $active = [
            'carlo'  => ['101-A', '2026-01-01', '2027-06-30'],
            'angela' => ['101-B', '2026-02-01', '2027-06-30'],
            'bryan'  => ['102-A', '2026-03-01', '2027-02-28'],
            'kim'    => ['103-A', '2026-01-15', '2027-01-14'],
            'nico'   => ['201-A', '2026-04-01', '2027-03-31'],
            'sara'   => ['A1-A',  '2026-05-01', '2027-04-30'],
            'dexter' => ['D1-A',  '2026-02-01', '2027-01-31'],
            'lovely' => ['D1-B',  '2026-03-01', '2027-02-28'],
            'ryan'   => ['D2-A',  '2026-04-01', '2027-03-31'],
            'trisha' => ['D2-B',  '2026-06-01', '2027-05-31'],
        ];

        // key => [bed key, assigned, vacated, contract start, end, contract status]
        $historical = [
            'paolo' => ['102-B', '2026-01-15', '2026-06-30', '2026-01-15', '2026-06-30', 'terminated'],
            'grace' => ['103-B', '2025-09-01', '2026-08-31', '2025-09-01', '2026-08-31', 'expired'],
        ];

        $contracts = [];
        $seq = 1;

        $makeContract = function (Tenant $tenant, Property $property, Room $room, Bed $bed, string $start, string $end, string $status) use (&$contracts, &$seq) {
            $contract = RentalContract::create([
                'contract_number' => 'CTR-2026-' . str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
                'tenant_id'       => $tenant->id,
                'property_id'     => $property->id,
                'room_id'         => $room->id,
                'bed_id'          => $bed->id,
                'start_date'      => $start,
                'end_date'        => $end,
                'monthly_rent'    => $bed->rental_rate,
                'security_deposit'=> $bed->rental_rate,
                'advance_payment' => 0,
                'due_day'         => 5,
                'late_fee'        => 200,
                'terms'           => 'Standard boarding house terms (fake demo contract). Rent is due on or before the 5th of each month. Late fees apply after the due date.',
                'status'          => $status,
            ]);

            return $contract;
        };

        foreach ($active as $key => [$bedKey, $start, $end]) {
            $tenant = $tenants[$key];
            $bed = $beds[$bedKey];
            $room = Room::find($bed->room_id);
            $property = Property::find($room->property_id);

            TenantAssignment::create([
                'tenant_id'   => $tenant->id,
                'property_id' => $property->id,
                'room_id'     => $room->id,
                'bed_id'      => $bed->id,
                'assigned_at' => Carbon::parse($start),
                'vacated_at'  => null,
                'is_current'  => true,
            ]);

            $bed->update(['status' => 'occupied']);
            // Room becomes full when filled to capacity.
            $occupied = Bed::where('room_id', $room->id)->where('status', 'occupied')->count();
            if ($occupied >= $room->capacity) {
                $room->update(['status' => 'full']);
            }

            $contracts[$key] = $makeContract($tenant, $property, $room, $bed, $start, $end, 'active');
        }

        foreach ($historical as $key => [$bedKey, $assigned, $vacated, $start, $end, $status]) {
            $tenant = $tenants[$key];
            $bed = $beds[$bedKey];
            $room = Room::find($bed->room_id);
            $property = Property::find($room->property_id);

            // Historical assignment is preserved; bed is already 'available'.
            TenantAssignment::create([
                'tenant_id'   => $tenant->id,
                'property_id' => $property->id,
                'room_id'     => $room->id,
                'bed_id'      => $bed->id,
                'assigned_at' => Carbon::parse($assigned),
                'vacated_at'  => Carbon::parse($vacated),
                'is_current'  => false,
            ]);

            $contracts[$key] = $makeContract($tenant, $property, $room, $bed, $start, $end, $status);
        }

        return $contracts;
    }

    // -------------------------------------------------------------------------
    // Billing & payments (paid / partial / unpaid / overdue)
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, RentalContract>  $contracts
     * @param  array<string, User>  $tenantUsers
     */
    private function seedBillingAndPayments(array $contracts, array $tenantUsers, User $owner1User, User $owner2User): void
    {
        $owner2Keys = ['dexter', 'lovely', 'ryan', 'trisha'];
        $recordedBy = fn (string $key): User => in_array($key, $owner2Keys, true) ? $owner2User : $owner1User;

        $activeKeys = ['carlo', 'angela', 'bryan', 'kim', 'nico', 'sara', 'dexter', 'lovely', 'ryan', 'trisha'];

        $september = Carbon::parse('2026-09-01');
        $october = Carbon::parse('2026-10-01');

        // --- September billings -------------------------------------------------
        $sepBillings = [];
        foreach ($activeKeys as $key) {
            $sepBillings[$key] = $this->billingService->generateMonthlyBilling($contracts[$key], $september);
        }

        // Fully paid in September
        foreach (['carlo', 'angela', 'bryan', 'kim', 'dexter', 'lovely', 'trisha'] as $key) {
            $this->billingService->recordPayment($sepBillings[$key], [
                'amount'         => (float) $sepBillings[$key]->total_amount,
                'payment_date'   => '2026-09-05',
                'payment_method' => 'cash',
                'notes'          => 'Full September rent (fake demo payment).',
            ], $recordedBy($key));
        }

        // Partially paid in September (₱2,000 of ₱4,500)
        $this->billingService->recordPayment($sepBillings['nico'], [
            'amount'         => 2000,
            'payment_date'   => '2026-09-06',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-DEMO-0906',
            'notes'          => 'Partial payment; balance carried over (fake demo).',
        ], $owner1User);

        // Never paid → flagged overdue
        foreach (['sara', 'ryan'] as $key) {
            $sepBillings[$key]->update(['status' => 'overdue']);
        }

        // --- October billings (created AFTER September payments so that
        //     previous balances are computed correctly) -------------------------
        $octBillings = [];
        foreach ($activeKeys as $key) {
            $octBillings[$key] = $this->billingService->generateMonthlyBilling($contracts[$key], $october);
        }

        // Fully paid in October
        $this->billingService->recordPayment($octBillings['carlo'], [
            'amount'         => (float) $octBillings['carlo']->total_amount,
            'payment_date'   => '2026-10-05',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-DEMO-1005',
            'notes'          => 'Full October rent (fake demo payment).',
        ], $owner1User);

        // Partially paid in October (₱2,000 of ₱3,500 → PARTIAL)
        $this->billingService->recordPayment($octBillings['angela'], [
            'amount'         => 2000,
            'payment_date'   => '2026-10-06',
            'payment_method' => 'cash',
            'notes'          => 'Partial payment; remaining ₱1,500 next payday (fake demo).',
        ], $owner1User);

        // Remaining October billings stay UNPAID (bryan, kim, nico, sara, dexter, lovely, ryan, trisha)
    }

    // -------------------------------------------------------------------------
    // Expenses
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, Property>  $properties
     */
    private function seedExpenses(array $properties, HouseOwner $owner1, HouseOwner $owner2): void
    {
        $rows = [
            // [owner, property, category, description, amount, date, method]
            [$owner1, $properties['p1'], 'electricity', 'Meralco bill — September (fake)', 8420.50, '2026-09-15', 'bank_transfer'],
            [$owner1, $properties['p1'], 'water',       'Water utility bill — September (fake)', 2150.00, '2026-09-20', 'cash'],
            [$owner1, $properties['p1'], 'repairs',     'Replace broken ceiling fan in Room 201 (fake)', 1300.00, '2026-10-02', 'cash'],
            [$owner1, $properties['p2'], 'internet',    'Fiber internet subscription — September (fake)', 1899.00, '2026-09-12', 'gcash'],
            [$owner1, $properties['p2'], 'cleaning',    'Deep cleaning service — October (fake)', 900.00, '2026-10-01', 'cash'],
            [$owner2, $properties['p3'], 'electricity', 'Meralco bill — September (fake)', 6750.75, '2026-09-18', 'bank_transfer'],
            [$owner2, $properties['p3'], 'supplies',    'Bed linens and toiletries restock (fake)', 2400.00, '2026-09-28', 'gcash'],
        ];

        foreach ($rows as $i => [$owner, $property, $category, $description, $amount, $date, $method]) {
            Expense::create([
                'expense_number' => 'EXP-2026-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'property_id'    => $property->id,
                'house_owner_id' => $owner->id,
                'category'       => $category,
                'description'    => $description,
                'amount'         => $amount,
                'expense_date'   => $date,
                'payment_method' => $method,
                'receipt_path'   => null,
                'notes'          => 'Fake demo expense record.',
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Maintenance
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, Tenant>  $tenants
     * @param  array<string, Property>  $properties
     */
    private function seedMaintenance(array $tenants, array $properties, User $owner1User): void
    {
        // 1. In-progress plumbing request (owner1)
        $m1 = MaintenanceRequest::create([
            'request_number' => 'MNT-202610-0001',
            'tenant_id'      => $tenants['carlo']->id,
            'property_id'    => $properties['p1']->id,
            'room_id'        => $tenants['carlo']->currentAssignment->room_id,
            'bed_id'         => $tenants['carlo']->currentAssignment->bed_id,
            'category'       => 'plumbing',
            'title'          => 'Leaking faucet in the shared bathroom',
            'description'    => 'The faucet has been dripping continuously since yesterday. (Fake demo request.)',
            'priority'       => 'high',
            'status'         => 'in_progress',
        ]);

        MaintenanceUpdate::create([
            'maintenance_request_id' => $m1->id,
            'user_id'                => $owner1User->id,
            'note'                   => 'Acknowledged. Plumber scheduled for October 8. (Fake demo update.)',
            'status'                 => 'in_progress',
        ]);

        // 2. Pending electrical request (owner2)
        MaintenanceRequest::create([
            'request_number' => 'MNT-202610-0002',
            'tenant_id'      => $tenants['dexter']->id,
            'property_id'    => $properties['p3']->id,
            'room_id'        => $tenants['dexter']->currentAssignment->room_id,
            'bed_id'         => $tenants['dexter']->currentAssignment->bed_id,
            'category'       => 'electrical',
            'title'          => 'Power socket near the desk not working',
            'description'    => 'Socket stopped working after the last brownout. (Fake demo request.)',
            'priority'       => 'medium',
            'status'         => 'pending',
        ]);

        // 3. Resolved internet request (owner1)
        $m3 = MaintenanceRequest::create([
            'request_number' => 'MNT-202609-0003',
            'tenant_id'      => $tenants['kim']->id,
            'property_id'    => $properties['p1']->id,
            'room_id'        => $tenants['kim']->currentAssignment->room_id,
            'bed_id'         => $tenants['kim']->currentAssignment->bed_id,
            'category'       => 'internet',
            'title'          => 'No WiFi signal in Room 103',
            'description'    => 'WiFi has not been reachable from Room 103 since last night. (Fake demo request.)',
            'priority'       => 'low',
            'status'         => 'resolved',
            'resolved_at'    => Carbon::parse('2026-09-29 16:00:00'),
        ]);

        MaintenanceUpdate::create([
            'maintenance_request_id' => $m3->id,
            'user_id'                => $owner1User->id,
            'note'                   => 'Router restarted and firmware updated. Signal restored. (Fake demo update.)',
            'status'                 => 'resolved',
        ]);
    }

    // -------------------------------------------------------------------------
    // Announcements
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, Property>  $properties
     */
    private function seedAnnouncements(HouseOwner $owner1, HouseOwner $owner2, array $properties, User $admin): void
    {
        Announcement::create([
            'house_owner_id'   => $owner1->id,
            'property_id'      => $properties['p1']->id,
            'title'            => 'Water Interruption — October 10',
            'content'          => "Dear tenants,\n\nThere will be a water interruption on October 10 from 9:00 AM to 1:00 PM for pipe maintenance. Please store enough water in advance. (Fake demo announcement.)",
            'is_platform_wide' => false,
            'publish_at'       => now(),
            'expires_at'       => now()->addDays(7),
            'status'           => 'active',
        ]);

        Announcement::create([
            'house_owner_id'   => $owner2->id,
            'property_id'      => null,
            'title'            => 'October Rent Reminder',
            'content'          => "Hi everyone!\n\nOctober rent is due on or before the 5th. Late fees apply afterwards. You may pay via GCash or cash at the office. (Fake demo announcement.)",
            'is_platform_wide' => false,
            'publish_at'       => now(),
            'expires_at'       => now()->addMonth(),
            'status'           => 'active',
        ]);

        Announcement::create([
            'house_owner_id'   => null,
            'property_id'      => null,
            'title'            => 'Welcome to E-Boarding (Demo Platform)',
            'content'          => "This is a platform-wide announcement from the Super Admin.\n\nAll data in this environment is fake demo data. Enjoy exploring the platform!",
            'is_platform_wide' => true,
            'publish_at'       => now(),
            'expires_at'       => null,
            'status'           => 'active',
        ]);
    }

    // -------------------------------------------------------------------------
    // Privacy access requests
    // -------------------------------------------------------------------------

    private function seedAccessRequests(User $admin, HouseOwner $owner1, HouseOwner $owner2): void
    {
        // 1. Pending request → owner1 (owner receives a database notification)
        app(PrivacyAccessService::class)->requestAccess(
            $admin,
            $owner1,
            ['VIEW_TENANTS', 'VIEW_PAYMENTS', 'VIEW_BILLING'],
            'Quarterly platform security audit of the billing integration (fake demo request).',
            60,
            'Demo pending request — approve or deny from Privacy & Access.'
        );

        // 2. Historical denied request → owner2
        $denied = OwnerAccessRequest::create([
            'requested_by_user_id'       => $admin->id,
            'house_owner_id'             => $owner2->id,
            'reason'                     => 'Investigating a duplicate billing report from last month (fake demo request).',
            'requested_duration_minutes' => 30,
            'permissions'                => ['VIEW_BILLING'],
            'status'                     => 'denied',
            'request_notes'              => 'Demo historical request.',
            'owner_notes'                => 'Please email us the ticket instead. (fake demo)',
            'responded_at'               => now()->subDays(3),
        ]);

        PrivacyAuditLog::create([
            'admin_user_id'     => $admin->id,
            'house_owner_id'    => $owner2->id,
            'access_session_id' => null,
            'action'            => 'access_denied',
            'resource'          => 'OwnerAccessRequest',
            'resource_id'       => $denied->id,
            'description'       => 'Owner denied access request (fake demo).',
            'ip_address'        => '127.0.0.1',
            'user_agent'        => 'Seeder',
        ]);
    }
}








