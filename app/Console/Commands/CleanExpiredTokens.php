<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InvalidatedToken;
use Carbon\Carbon;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Remove expired tokens from the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get current time
        $now = Carbon::now();

        // Delete expired tokens
        InvalidatedToken::where('expired_time', '<', $now)->delete();

        $this->info('Expired tokens cleaned successfully.');
    }
}
