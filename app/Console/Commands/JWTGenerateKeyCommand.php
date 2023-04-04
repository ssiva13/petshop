<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class JWTGenerateKeyCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'jwt:key
        {--s|show : Display the key instead of modifying files.}
        {--always-no : Skip generating key if it already exists.}
        {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the JWT secret key used to sign the tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $key = Str::random(64);
        if ($this->option('show')) {
            $this->comment($key);
            return;
        }
        if (file_exists($path = $this->envPath()) === false) {
            $this->displayKey($key);
            return;
        }
        if (Str::contains(file_get_contents($path), 'JWT_KEY') === false) {
            // create new entry
            file_put_contents($path, PHP_EOL."JWT_KEY=$key".PHP_EOL, FILE_APPEND);
        } else {
            if ($this->option('always-no')) {
                $this->comment('Secret key already exists. Skipping...');

                return;
            }
            if ($this->isConfirmed() === false) {
                $this->comment('Phew... No changes were made to your secret key.');
                return;
            }
            // update existing entry
            file_put_contents($path, str_replace(
                'JWT_KEY='.$this->laravel['config']['jwt.key'],
                'JWT_KEY='.$key, file_get_contents($path)
            ));
        }
        $this->displayKey($key);
    }

    /**
     * Display the key.
     *
     * @param string $key
     *
     * @return void
     */
    protected function displayKey(string $key): void
    {
        $this->laravel['config']['jwt.key'] = $key;
        $this->info("jwt-auth secret key [$key] set successfully.");
    }

    /**
     * Check if the modification is confirmed.
     *
     * @return bool
     */
    protected function isConfirmed(): bool
    {
        return $this->option('force') || $this->confirm('This will invalidate all existing tokens. Are you sure you want to override the secret key?');
    }

    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath(): string
    {
        return $this->laravel->basePath('.env');
    }

}
