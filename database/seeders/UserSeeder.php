<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create active "admin" role
        $known = User::whereEmail($email = 'admin@example.com')->first();
        if (! $known) {
            $known = User::factory()->admin()->active()->create([
                'name' => 'Admin User',
                'email' => $email,
            ]);
        }

        // Create active "manager" role
        $known = User::whereEmail($email = 'manager@example.com')->first();
        if (! $known) {
            $known = User::factory()->manager()->active()->create([
                'name' => 'Manager User',
                'email' => $email,
            ]);
        }

        // Create active "user" role
        $known = User::whereEmail($email = 'test@example.com')->first();
        if (! $known) {
            $known = User::factory()->user()->active()->create([
                'name' => 'Test User',
                'email' => $email,
            ]);
        }

        // Create more users with the "user" role
        foreach (range(1, 10) as $index) {
            User::factory()->user()->create([
                'email' => 'user'.$index.'@example.com',
            ]);
        }

        // Create Personal Access Client for Passport
        $this->createPersonalAccessClient();
    }

    protected function createPersonalAccessClient()
    {
        // Create a Personal access Client for Passport
        if (Client::where('name', $name = 'AUTH Personal Access Client')->doesntExist()) {
            $client = app(ClientRepository::class)->createPersonalAccessClient(
                userId: null,
                name: $name,
                redirect: url('/')
            );
            $this->updateEnvFileForPassport($client->id, $client->plainSecret);
        }
    }

    /** Update the .env file with the Passport client ID and secret. */
    protected function updateEnvFileForPassport($clientId, $clientSecret)
    {
        // Read the current .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Update or add the entries for Passport client
        $envContent = preg_replace(
            '/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=.*/m',
            "PASSPORT_PERSONAL_ACCESS_CLIENT_ID=\"$clientId\"",
            $envContent
        );

        $envContent = preg_replace(
            '/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=.*/m',
            "PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=\"$clientSecret\"",
            $envContent
        );

        // If the lines do not exist, add them
        if (! preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=/m', $envContent)) {
            $envContent .= "\nPASSPORT_PERSONAL_ACCESS_CLIENT_ID=\"$clientId\"";
        }

        if (! preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=/m', $envContent)) {
            $envContent .= "\nPASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=\"$clientSecret\"";
        }

        // Write the updated content back to .env
        file_put_contents($envPath, $envContent);
    }
}
