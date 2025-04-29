<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Laravel\Passport\Client;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateFixedApiToken extends Command
{
    protected $signature = 'passport:fixed-token {user_id} {token_name}';
    protected $description = 'Create a fixed API token that never changes';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $tokenName = $this->argument('token_name');
        
        // Get the user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }
        
        // Find a personal access client
        $client = Client::where('personal_access_client', 1)->first();
        if (!$client) {
            $this->error("No personal access client found. Run 'php artisan passport:install' first");
            return 1;
        }
        
        // Generate a unique ID for the token
        $tokenId = Str::random(40);
        
        // Store the token in the database with no expiration
        DB::table('oauth_access_tokens')->insert([
            'id' => $tokenId,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => $tokenName,
            'scopes' => json_encode(['*']), // Or specific scopes
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => null // This makes the token never expire
        ]);
        
        // Create a JWT token that matches the stored token ID
        $privateKeyPath = storage_path('oauth-private.key');
        if (!file_exists($privateKeyPath)) {
            $this->error("OAuth private key not found. Run 'php artisan passport:install' first");
            return 1;
        }
        
        $privateKey = file_get_contents($privateKeyPath);
        
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey)
        );
        
        $now = new \DateTimeImmutable();
        
        $token = $configuration->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor($client->id)
            ->identifiedBy($tokenId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+100 years'))
            ->relatedTo((string) $user->id)
            ->withClaim('scopes', ['*'])
            ->getToken($configuration->signer(), $configuration->signingKey());
        
        $accessToken = $token->toString();
        
        $this->info("Fixed API token created successfully");
        $this->info("
Use this token in your Authorization header: Bearer {$accessToken}");
        
        return 0;
    }
}